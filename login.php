<?php
/**
 * Inloggen en wachtwoord vergeten.
 *
 * Routes:
 *   login.php                              inlogformulier
 *   login.php?x=lostpass                   wachtwoord opvragen
 *   login.php?x=lostpass&id=..&code=..     nieuw wachtwoord instellen
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

// Al ingelogd? Dan hoeft dit niet.
if (is_logged_in()) {
    redirect('home.php');
}

$actie = get('x');

match ($actie) {
    'lostpass' => toon_wachtwoord_vergeten(),
    default    => toon_inloggen(),
};

// ==========================================================================

function toon_inloggen(): void
{
    $fout = null;

    if (is_post()) {
        csrf_check();

        $login = post('login');
        $pass  = (string) ($_POST['password'] ?? '');

        if ($login === '' || $pass === '') {
            $fout = 'Vul je gebruikersnaam en wachtwoord in.';
        } elseif (te_veel_pogingen()) {
            $fout = 'Te veel mislukte pogingen. Wacht een minuut en probeer het opnieuw.';
        } else {
            $poging = auth_attempt($login, $pass);

            if ($poging['ok']) {
                poging_wissen();
                flash('Welkom terug, ' . $login . '.', 'ok');
                redirect('home.php');
            }

            poging_tellen();
            $fout = $poging['error'];
        }
    }

    layout_header('Inloggen');
    panel_open('Inloggen');

    if ($fout !== null) {
        notice(e($fout), 'fout');
    }

    echo '<form method="post">' . csrf_field();
    echo '<div class="veldenraster">';
    echo '<label for="login">Gebruikersnaam</label>';
    echo '<input id="login" name="login" maxlength="16" required autofocus autocomplete="username" value="' . e(post('login')) . '">';
    echo '<label for="password">Wachtwoord</label>';
    echo '<input id="password" name="password" type="password" required autocomplete="current-password">';
    echo '<span></span><button type="submit">Inloggen</button>';
    echo '</div></form>';

    echo '<p><a href="' . e(url('login.php?x=lostpass')) . '">Wachtwoord vergeten?</a>'
       . ' &middot; <a href="' . e(url('register.php')) . '">Nog geen account? Registreer je.</a></p>';

    panel_close();
    layout_footer();
}

// ==========================================================================

function toon_wachtwoord_vergeten(): void
{
    $melding = null;
    $type    = 'info';

    // --- Stap 2: link uit de mail geopend, nieuw wachtwoord instellen ---
    if (get('id') !== '' && get('code') !== '') {
        nieuw_wachtwoord_instellen();
        return;
    }

    // --- Stap 1: aanvraag ---
    if (is_post()) {
        csrf_check();

        $login = post('login');
        $email = post('email');

        $speler = q_row(
            'SELECT `login`, `email` FROM `users` WHERE `login` = ? AND `email` = ? AND `activated` = 1',
            [$login, $email]
        );

        if ($speler !== null) {
            $token = make_token($speler['login'], 'lostpass');
            $link  = url('login.php?x=lostpass&id=' . $token['id'] . '&code=' . $token['code']);

            send_mail(
                $speler['email'],
                config('site.name') . ' - nieuw wachtwoord',
                "Hallo {$speler['login']},\n\n"
                . "Er is een nieuw wachtwoord aangevraagd voor je account.\n"
                . "Klik op onderstaande link om er een in te stellen:\n\n"
                . $link . "\n\n"
                . "De link is 48 uur geldig en werkt één keer.\n\n"
                . "Heb je dit niet zelf aangevraagd? Dan kun je deze mail negeren; "
                . "je wachtwoord blijft dan ongewijzigd.\n"
            );
        }

        // Altijd dezelfde melding, of het account nu bestaat of niet. Anders
        // kan iemand met dit formulier uitzoeken welke namen in gebruik zijn.
        $melding = 'Als de gegevens kloppen, is er een e-mail met instructies verstuurd.';
        $type    = 'ok';
    }

    layout_header('Wachtwoord vergeten');
    panel_open('Wachtwoord vergeten');

    if ($melding !== null) {
        notice(e($melding), $type);
    }

    echo '<p>Vul je gebruikersnaam en het e-mailadres in waarmee je je hebt aangemeld.</p>';
    echo '<form method="post">' . csrf_field();
    echo '<div class="veldenraster">';
    echo '<label for="login">Gebruikersnaam</label>';
    echo '<input id="login" name="login" maxlength="16" required>';
    echo '<label for="email">E-mailadres</label>';
    echo '<input id="email" name="email" type="email" maxlength="255" required>';
    echo '<span></span><button type="submit">Versturen</button>';
    echo '</div></form>';

    echo '<p><a href="' . e(url('login.php')) . '">Terug naar inloggen</a></p>';

    panel_close();
    layout_footer();
}

function nieuw_wachtwoord_instellen(): void
{
    $id   = int_input('id');
    $code = get('code');
    $fout = null;

    if (is_post()) {
        csrf_check();

        $pass  = (string) ($_POST['pass'] ?? '');
        $pass2 = (string) ($_POST['pass2'] ?? '');

        if (mb_strlen($pass) < 10) {
            $fout = 'Kies een wachtwoord van minstens 10 tekens.';
        } elseif ($pass !== $pass2) {
            $fout = 'De twee wachtwoorden zijn niet gelijk.';
        } else {
            // Code pas hier innemen: bij een tikfout in het wachtwoord blijft
            // de link bruikbaar.
            $token = take_token($id, $code, 'lostpass');

            if ($token === null) {
                $fout = 'Deze link is verlopen of al gebruikt. Vraag een nieuwe aan.';
            } else {
                q('UPDATE `users` SET `pass` = ? WHERE `login` = ?',
                    [auth_hash($pass), $token['login']]);

                flash('Je wachtwoord is gewijzigd. Je kunt nu inloggen.', 'ok');
                redirect('login.php');
            }
        }
    } elseif (q_row('SELECT `id` FROM `temp` WHERE `id` = ? AND `area` = \'lostpass\'', [$id]) === null) {
        $fout = 'Deze link is verlopen of al gebruikt. Vraag een nieuwe aan.';
    }

    layout_header('Nieuw wachtwoord');
    panel_open('Nieuw wachtwoord instellen');

    if ($fout !== null) {
        notice(e($fout), 'fout');
        echo '<p><a href="' . e(url('login.php?x=lostpass')) . '">Nieuwe link aanvragen</a></p>';
    } else {
        echo '<form method="post">' . csrf_field();
        echo '<div class="veldenraster">';
        echo '<label for="pass">Nieuw wachtwoord</label>';
        echo '<input id="pass" name="pass" type="password" minlength="10" required autocomplete="new-password">';
        echo '<span></span><small>Minimaal 10 tekens.</small>';
        echo '<label for="pass2">Herhaal</label>';
        echo '<input id="pass2" name="pass2" type="password" minlength="10" required autocomplete="new-password">';
        echo '<span></span><button type="submit">Opslaan</button>';
        echo '</div></form>';
    }

    panel_close();
    layout_footer();
}

// ==========================================================================
// Eenvoudige rem op het raden van wachtwoorden.
// Per sessie én per IP, zodat één bezoeker niet eindeloos kan blijven proberen.
// ==========================================================================

function te_veel_pogingen(): bool
{
    $p = $_SESSION['_login_pogingen'] ?? null;

    if (!is_array($p) || time() - (int) $p['sinds'] > 300) {
        return false;   // venster van vijf minuten verlopen
    }
    return (int) $p['aantal'] >= 8;
}

function poging_tellen(): void
{
    $p = $_SESSION['_login_pogingen'] ?? null;

    if (!is_array($p) || time() - (int) $p['sinds'] > 300) {
        $p = ['aantal' => 0, 'sinds' => time()];
    }
    $p['aantal']++;
    $_SESSION['_login_pogingen'] = $p;
}

function poging_wissen(): void
{
    unset($_SESSION['_login_pogingen']);
}
