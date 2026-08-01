<?php
/**
 * Registreren en account activeren.
 *
 * Routes:
 *   register.php                    aanmeldformulier
 *   register.php?refer=12           aanmelden via een verwijzing
 *   register.php?id=..&code=..      activatielink uit de e-mail
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

if (is_logged_in()) {
    redirect('home.php');
}

// --- Activatielink geopend ------------------------------------------------
if (get('id') !== '' && get('code') !== '') {
    activeren(int_input('id'), get('code'));
}

// --- Aanmelding verwerken --------------------------------------------------
$fout   = null;
$gelukt = null;

if (is_post()) {
    csrf_check();
    try {
        $gelukt = aanmelden($_POST);
    } catch (RuntimeException $e) {
        $fout = $e->getMessage();
    }
}

toon_formulier($fout, $gelukt);

// ==========================================================================

/**
 * @return string Melding voor de speler.
 * @throws RuntimeException bij ongeldige invoer.
 */
function aanmelden(array $in): string
{
    $login    = trim((string) ($in['gebruiker'] ?? ''));
    $pass     = (string) ($in['pass'] ?? '');
    $pass2    = (string) ($in['passconfirm'] ?? '');
    $email    = trim((string) ($in['email'] ?? ''));
    $geslacht = ($in['geslacht'] ?? 'Man') === 'Vrouw' ? 'Vrouw' : 'Man';
    $refer    = trim((string) ($in['refer'] ?? ''));
    $ip       = client_ip();

    // --- Vorm van de invoer ---
    if (!preg_match('/^[a-zA-Z0-9_\-]{3,16}$/', $login)) {
        throw new RuntimeException(
            'De gebruikersnaam moet 3 tot 16 tekens lang zijn en mag alleen letters, '
            . 'cijfers, - en _ bevatten.'
        );
    }
    if (mb_strlen($pass) < 10) {
        throw new RuntimeException('Kies een wachtwoord van minstens 10 tekens.');
    }
    if ($pass !== $pass2) {
        throw new RuntimeException('De twee wachtwoorden zijn niet gelijk.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Het opgegeven e-mailadres is ongeldig.');
    }

    // --- Al in gebruik? ---
    if (q_val('SELECT COUNT(*) FROM `users` WHERE `login` = ?', [$login], 0) > 0) {
        throw new RuntimeException('Die gebruikersnaam is al in gebruik.');
    }
    // Ook hier tellen omgelegde accounts mee: één account per e-mailadres.
    if (q_val('SELECT COUNT(*) FROM `users` WHERE `email` = ?', [$email], 0) > 0) {
        throw new RuntimeException(
            'Er bestaat al een account met dit e-mailadres. Ben je je wachtwoord kwijt, '
            . 'gebruik dan "wachtwoord vergeten". Ben je omgelegd, log dan in: je begint '
            . 'met datzelfde account opnieuw.'
        );
    }

    // --- Meerdere accounts vanaf hetzelfde IP ---
    //
    // Alle accounts tellen mee, ook omgelegde. Eerder telden alleen levende
    // accounts, omdat je na een moord een nieuw account moest maken. Dat hoeft
    // niet meer: een omgelegd account begint via rip.php gewoon opnieuw. Er is
    // dus geen legitieme reden meer voor een tweede account, en de uitzondering
    // was een makkelijke manier om er alsnog meerdere te verzamelen.
    if (!config('game.allow_multi_accounts')) {
        $toegestaan = (int) q_val('SELECT `allo` FROM `multiple` WHERE `ip` = ?', [$ip], 0);
        $bestaat    = (int) q_val('SELECT COUNT(*) FROM `users` WHERE `ip` = ?', [$ip], 0);

        if ($bestaat > 0 && $toegestaan !== 1) {
            throw new RuntimeException(
                'Er bestaat al een account vanaf dit IP-adres. Ben je je wachtwoord kwijt, '
                . 'gebruik dan "wachtwoord vergeten". Ben je omgelegd, log dan in: je begint '
                . 'met datzelfde account opnieuw. Speel je samen met iemand op hetzelfde '
                . 'netwerk? Vraag dan een beheerder om toestemming.'
            );
        }
    }

    // --- Verwijzer ---
    $verwijzer = '';
    if ($refer !== '') {
        $verwijzer = (string) q_val('SELECT `login` FROM `users` WHERE `id` = ?', [(int) $refer], '');
        if ($verwijzer === '') {
            throw new RuntimeException('De opgegeven ReferrerID bestaat niet.');
        }
    }

    // --- Account aanmaken ---
    $activatieNodig = (bool) config('game.require_activation', true);
    $stad           = random_city();

    db_transaction(static function () use ($login, $pass, $email, $ip, $stad, $geslacht, $activatieNodig) {
        q(
            'INSERT INTO `users`
                  (`login`, `pass`, `email`, `ip`, `stad`, `geslacht`, `activated`,
                   `start`, `online`, `zak`)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?)',
            [
                $login,
                auth_hash($pass),
                $email,
                $ip,
                $stad,
                $geslacht,
                $activatieNodig ? 0 : 1,
                (int) config('game.start_money', 1000),
            ]
        );
        // Je begint met een huis in je startstad; dat geeft daar thuisvoordeel.
        huis_geven($login, $stad);
    });

    if (!$activatieNodig) {
        if ($verwijzer !== '') {
            verwijzing_belonen($verwijzer, $login);
        }
        return 'Je account is aangemaakt. Je kunt nu <a href="' . e(url('login.php')) . '">inloggen</a>.';
    }

    // --- Activatiemail ---
    $token = make_token($login, 'signup', $verwijzer);
    $link  = url('register.php?id=' . $token['id'] . '&code=' . $token['code']);

    $verstuurd = send_mail(
        $email,
        config('site.name') . ' - activeer je account',
        "Hallo {$login},\n\n"
        . 'Bedankt voor je aanmelding bij ' . config('site.name') . ".\n"
        . "Klik op onderstaande link om je account te activeren:\n\n"
        . $link . "\n\n"
        . "Je startstad is {$stad}.\n\n"
        . "Heb je je niet aangemeld? Dan kun je deze mail negeren.\n"
    );

    if (!$verstuurd) {
        // Het account bestaat al; de mail is het probleem, niet de aanmelding.
        return 'Je account is aangemaakt, maar de activatiemail kon niet verstuurd worden. '
             . 'Neem contact op met een beheerder om je account te laten activeren.';
    }

    return 'Je bent aangemeld. Er is een e-mail met een activatielink gestuurd naar '
         . e($email) . '. Kijk ook even in je spamfolder.';
}

/**
 * @return never
 */
function activeren(int $id, string $code): void
{
    $token = take_token($id, $code, 'signup');

    layout_header('Activeren');
    panel_open('Account activeren');

    if ($token === null) {
        notice('Deze activatielink is ongeldig, verlopen of al gebruikt.', 'fout');
        echo '<p>Is je account al geactiveerd? Probeer dan gewoon '
           . '<a href="' . e(url('login.php')) . '">in te loggen</a>.</p>';
    } else {
        q("UPDATE `users` SET `activated` = 1, `start` = NOW() WHERE `login` = ?", [$token['login']]);

        if (($token['forwardedFor'] ?? '') !== '') {
            verwijzing_belonen((string) $token['forwardedFor'], (string) $token['login']);
        }

        notice('Je account is geactiveerd.', 'ok');
        echo '<p><a class="knop" href="' . e(url('login.php')) . '">Inloggen</a></p>';
    }

    panel_close();
    layout_footer();
    exit;
}

/** Geef de verwijzer eerpunten en leg het vast in het logboek. */
function verwijzing_belonen(string $verwijzer, string $nieuweSpeler): void
{
    db_transaction(static function () use ($verwijzer, $nieuweSpeler) {
        q('UPDATE `users` SET `respect` = `respect` + 5 WHERE `login` = ?', [$verwijzer]);
        q(
            'INSERT INTO `logs` (`time`, `login`, `person`, `code`, `area`, `com`)
                  VALUES (NOW(), ?, ?, 5, ?, ?)',
            [$verwijzer, $nieuweSpeler, 'respect', 'Verwijzing']
        );
    });
}

// ==========================================================================

function toon_formulier(?string $fout, ?string $gelukt): void
{
    layout_header('Registreren');
    panel_open('Registreren');

    if ($fout !== null)   { notice(e($fout), 'fout'); }
    if ($gelukt !== null) { notice($gelukt, 'ok'); }

    if ($gelukt === null) {
        $refer = get('refer') !== '' ? get('refer') : post('refer');

        echo '<form method="post">' . csrf_field();
        echo '<div class="veldenraster">';

        echo '<label for="gebruiker">Gebruikersnaam</label>';
        echo '<input id="gebruiker" name="gebruiker" maxlength="16" pattern="[A-Za-z0-9_\-]{3,16}" '
           . 'required autocomplete="username" value="' . e(post('gebruiker')) . '">';
        echo '<span></span><small>3 tot 16 tekens. Deze naam kun je later niet meer wijzigen.</small>';

        echo '<label for="pass">Wachtwoord</label>';
        echo '<input id="pass" name="pass" type="password" minlength="10" required autocomplete="new-password">';
        echo '<span></span><small>Minimaal 10 tekens.</small>';

        echo '<label for="passconfirm">Herhaal wachtwoord</label>';
        echo '<input id="passconfirm" name="passconfirm" type="password" minlength="10" required autocomplete="new-password">';

        echo '<label for="email">E-mailadres</label>';
        echo '<input id="email" name="email" type="email" maxlength="255" required '
           . 'value="' . e(post('email')) . '">';
        echo '<span></span><small>Hierheen gaat je activatielink.</small>';

        $man   = post('geslacht') === 'Vrouw' ? '' : ' selected';
        $vrouw = post('geslacht') === 'Vrouw' ? ' selected' : '';
        echo '<label for="geslacht">Geslacht</label>';
        echo '<select id="geslacht" name="geslacht">'
           . '<option value="Man"' . $man . '>Man</option>'
           . '<option value="Vrouw"' . $vrouw . '>Vrouw</option></select>';
        echo '<span></span><small>Bepaalt alleen hoe je rang genoemd wordt.</small>';

        echo '<label for="refer">ReferrerID</label>';
        echo '<input id="refer" name="refer" maxlength="10" value="' . e($refer) . '">';
        echo '<span></span><small>Optioneel. Het spelersnummer van wie je heeft uitgenodigd.</small>';

        echo '<span></span><button type="submit">Aanmelden</button>';
        echo '</div></form>';
    }

    echo '<p><a href="' . e(url('login.php')) . '">Heb je al een account? Log in.</a></p>';

    panel_close();
    layout_footer();
}
