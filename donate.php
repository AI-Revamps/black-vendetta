<?php
/**
 * Donateurscodes inwisselen.
 *
 * LET OP - de betaalkoppeling is bewust NIET meegenomen.
 *
 * De oude versie hing aan een Mollie-widget uit 2007: een hardcoded partner-id
 * in een extern JavaScript-bestand dat niet meer bestaat, en een terugmelding
 * die alleen gecontroleerd werd op twee vaste IP-adressen. Dat is geen werkende
 * betaling meer en het opnieuw aansluiten van een betaalprovider vraagt om
 * accountgegevens en keuzes die de beheerder zelf moet maken.
 *
 * Wat hier wél werkt: een beheerder maakt codes aan en spelers wisselen ze in.
 * Wil je later echt betalingen aannemen, dan sluit je een provider aan op
 * donatie_code_maken() en hoeft de rest niet te veranderen.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

const DONATIE_DAGEN  = 14;
const DONATIE_GELD   = 50_000;
const DONATIE_KOGELS = 500;
const DONATIE_MAX    = 3;      // hoeveel donaties tegelijk actief kunnen zijn

$user = require_login();

if (is_dead()) {
    redirect('rip.php');
}

$melding = null;
$type    = 'info';

if (is_post()) {
    csrf_check();
    try {
        $melding = match (post('actie')) {
            'inwisselen' => inwisselen($user, post('code')),
            'aanmaken'   => aanmaken($user, post('voor')),
            default      => throw new SpelFout('Onbekende handeling.'),
        };
        $type = 'ok';
        $user = current_user(true);
    } catch (SpelFout $e) {
        $melding = $e->getMessage();
        $type    = 'fout';
    }
}

$actief = actieve_donaties($user);

layout_header('Doneren');

if ($melding !== null) {
    notice(e($melding), $type);
}

// --- Voordelen ---
panel_open('Donateur worden');
echo '<p>Als donateur krijg je een aantal voordelen, ' . DONATIE_DAGEN
   . ' dagen lang. Bij elke donatie ontvang je bovendien ' . money(DONATIE_GELD)
   . ' en ' . num(DONATIE_KOGELS) . ' kogels.</p>';

echo '<div class="tabelwikkel"><table class="lijst">';
echo '<thead><tr><th>Voordeel</th><th class="getal">Geen</th><th class="getal">Brons</th>'
   . '<th class="getal">Zilver</th><th class="getal">Goud</th></tr></thead><tbody>';
foreach ([
    ['Kliklimiet',        '15',   '25',   '35',   '50'],
    ['Profieltekens',     '500',  '1000', '1500', '2000'],
    ['Beschermingen',     '1',    '2',    '3',    '4'],
    ['Afbeelding',        'Geen', 'Ja',   'Ja',   'Ja'],
    ['Timers zichtbaar',  'Nee',  'Nee',  'Ja',   'Ja'],
] as $rij) {
    echo '<tr><th scope="row">' . e($rij[0]) . '</th>';
    for ($i = 1; $i <= 4; $i++) {
        echo '<td class="getal">' . e($rij[$i]) . '</td>';
    }
    echo '</tr>';
}
echo '</tbody></table></div>';

echo '<p class="uitleg">Je hebt op dit moment <strong>' . $actief['aantal']
   . ' van de ' . DONATIE_MAX . '</strong> donaties actief.';
if ($actief['tot'] > 0) {
    echo ' De eerstvolgende loopt af op ' . e(date('d-m-Y H:i', $actief['tot'])) . '.';
}
echo '</p>';
panel_close();

// --- Code inwisselen ---
panel_open('Code inwisselen');
echo '<p>Heb je een donatiecode? Vul hem hier in.</p>';
echo '<form method="post">' . csrf_field();
echo '<input type="hidden" name="actie" value="inwisselen">';
echo '<div class="veldenraster">';
echo '<label for="code">Donatiecode</label>';
echo '<input id="code" name="code" maxlength="32" required autocomplete="off" spellcheck="false">';
echo '<span></span><button type="submit">Inwisselen</button>';
echo '</div></form>';
panel_close();

// --- Beheer ---
if ((int) $user['level'] >= LEVEL_ADMIN) {
    $open = q_all('SELECT * FROM `donate` WHERE `status` = 0 ORDER BY `id` DESC LIMIT 25');

    panel_open('Codes aanmaken (beheer)');
    echo '<form method="post">' . csrf_field();
    echo '<input type="hidden" name="actie" value="aanmaken">';
    echo '<div class="veldenraster">';
    echo '<label for="voor">Voor speler</label>';
    echo '<input id="voor" name="voor" maxlength="16" required>';
    echo '<span></span><button type="submit">Code aanmaken</button>';
    echo '</div></form>';

    if ($open !== []) {
        echo '<h3>Nog niet ingewisseld</h3><div class="tabelwikkel"><table class="lijst">';
        echo '<thead><tr><th>Voor</th><th>Code</th></tr></thead><tbody>';
        foreach ($open as $rij) {
            echo '<tr><td>' . e((string) $rij['door']) . '</td>'
               . '<td><code>' . e((string) $rij['code']) . '</code></td></tr>';
        }
        echo '</tbody></table></div>';
    }
    panel_close();
}

layout_footer();

// ==========================================================================

/** Hoeveel donaties zijn nog actief, en wanneer loopt de eerste af? */
function actieve_donaties(array $user): array
{
    $nu     = time();
    $tijden = array_filter([
        (int) $user['paidtime1'],
        (int) $user['paidtime2'],
        (int) $user['paidtime3'],
    ], static fn (int $t): bool => $t > $nu);

    return [
        'aantal' => count($tijden),
        'tot'    => $tijden === [] ? 0 : min($tijden),
    ];
}

/**
 * Wissel een code in.
 *
 * @throws SpelFout
 */
function inwisselen(array $user, string $code): string
{
    if ($code === '') {
        throw new SpelFout('Vul een code in.');
    }

    return db_transaction(static function () use ($user, $code): string {
        // Vergrendel de code, zodat hij niet twee keer tegelijk ingewisseld wordt.
        $donatie = q_row('SELECT * FROM `donate` WHERE `code` = ? FOR UPDATE', [$code]);

        if ($donatie === null) {
            throw new SpelFout('Deze donatiecode is ongeldig of al gebruikt.');
        }
        if ((int) $donatie['status'] !== 0) {
            throw new SpelFout('Deze donatiecode staat te koop op de veiling.');
        }

        $speler = lock_user((int) $user['id']);
        $nu     = time();
        $tot    = $nu + DONATIE_DAGEN * 86400;

        // Vul het vakje dat het eerst afloopt. Zo stapelen donaties netjes op
        // tot het maximum, in plaats van elkaar te overschrijven.
        $vakjes = [
            'paidtime1' => (int) $speler['paidtime1'],
            'paidtime2' => (int) $speler['paidtime2'],
            'paidtime3' => (int) $speler['paidtime3'],
        ];
        asort($vakjes);
        $vakje = array_key_first($vakjes);

        if ($vakjes[$vakje] > $nu) {
            throw new SpelFout(
                'Je hebt al ' . DONATIE_MAX . ' donaties tegelijk actief. '
                . 'Wacht tot er een afloopt.'
            );
        }

        q("UPDATE `users` SET `{$vakje}` = ? WHERE `id` = ?", [$tot, $speler['id']]);

        // `paid` is het aantal actieve donaties; opnieuw berekend zodat het
        // niet uit de pas kan lopen zoals in de oude versie.
        q(
            "UPDATE `users`
                SET `paid` = (`paidtime1` > ?) + (`paidtime2` > ?) + (`paidtime3` > ?),
                    `zak` = `zak` + ?,
                    `kogels` = `kogels` + ?
              WHERE `id` = ?",
            [$nu, $nu, $nu, DONATIE_GELD, DONATIE_KOGELS, $speler['id']]
        );

        q('DELETE FROM `donate` WHERE `id` = ?', [$donatie['id']]);

        log_action((string) $user['login'], 'donate', 'Code ingewisseld', DONATIE_GELD);

        return 'Bedankt voor je donatie. Je hebt ' . money(DONATIE_GELD)
             . ' en ' . num(DONATIE_KOGELS) . ' kogels ontvangen. Je voordelen lopen '
             . DONATIE_DAGEN . ' dagen.';
    });
}

/**
 * Maak een nieuwe code aan. Alleen voor beheerders.
 *
 * Wil je later een echte betaalprovider aansluiten, roep dan deze functie aan
 * zodra de betaling bevestigd is.
 *
 * @throws SpelFout
 */
function aanmaken(array $user, string $voor): string
{
    if ((int) $user['level'] < LEVEL_ADMIN) {
        throw new SpelFout('Je hebt niet genoeg rechten.');
    }

    $ontvanger = q_row('SELECT `login` FROM `users` WHERE `login` = ?', [$voor]);
    if ($ontvanger === null) {
        throw new SpelFout('Die speler bestaat niet.');
    }

    $code = donatie_code_maken((string) $ontvanger['login']);

    notify((string) $ontvanger['login'], 'Donatie',
        'Je donatiecode is: ' . $code . ' — wissel hem in op de donatiepagina.');

    log_action((string) $user['login'], 'donate',
        'Code aangemaakt voor ' . $ontvanger['login'], 0, (string) $ontvanger['login']);

    return 'Code ' . $code . ' aangemaakt voor ' . $ontvanger['login'] . '.';
}

/** Maak een unieke donatiecode en sla hem op. */
function donatie_code_maken(string $voor): string
{
    do {
        // Zonder tekens die op elkaar lijken, zodat overtypen makkelijk blijft.
        $code = '';
        $tekens = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        for ($i = 0; $i < 12; $i++) {
            $code .= $tekens[random_int(0, strlen($tekens) - 1)];
        }
        $bestaat = (int) q_val('SELECT COUNT(*) FROM `donate` WHERE `code` = ?', [$code], 0);
    } while ($bestaat > 0);

    q('INSERT INTO `donate` (`door`, `code`, `status`) VALUES (?, ?, 0)', [$voor, $code]);

    return $code;
}
