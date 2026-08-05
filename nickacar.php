<?php
/**
 * Auto's stelen: van een parkeerplaats, uit een woonwijk, bij een tankstation
 * of rechtstreeks uit de garage van een andere speler.
 *
 * Wat hier gerepareerd is ten opzichte van de oude versie:
 *
 *  - De slaagkans werd berekend als `rand(0, 100 / $ka)`. Bij weinig ervaring
 *    is `$ka` nul, wat een deling door nul geeft: op PHP 8 een fatale fout.
 *    Hetzelfde gold voor de kans om van een speler te stelen.
 *  - De drie locaties toonden elk een eigen percentage, maar stuurden alle drie
 *    dezelfde waarde mee. Ze waren dus identiek; het verschil was schijn. Nu
 *    hebben ze werkelijk verschillende kansen, zoals de oude weergave beloofde.
 *  - `$gkans` werd berekend maar nergens gebruikt; het stelen van een speler
 *    liep op de kans van de parkeerplaats.
 *  - Een wagen uit andermans garage werd overgezet zonder transactie of
 *    vergrendeling, dus twee dieven konden dezelfde wagen "stelen".
 *  - De bonussen per locatie stonden achterstevoren: de bovenste optie
 *    (parkeerplaats, met de slechtste wagens) had de laagste slaagkans, en
 *    stelen uit de garage van een speler (onderaan, het grootste risico) had
 *    juist de hoogste. Een speler die niet zelf van locatie wisselde, zag de
 *    kans van de eerst aangevinkte optie dus nauwelijks stijgen terwijl de
 *    andere opties eronder sneller stegen. Nu neemt de kans strikt af van
 *    boven (makkelijkst) naar onder (moeilijkst), bij elke xp.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';
require BV_INC . '/captcha.php';

/**
 * De plekken waar je kunt stelen.
 *
 * bonus     : extra procentpunten slaagkans bovenop de basis
 * schade    : bereik van de schade aan de buitgemaakte wagen
 */
function steelplekken(): array
{
    return [
        'parkeerplaats' => [
            'label'  => 'Steel een wagen op een parkeerplaats',
            'bonus'  => 10,
            'schade' => [0, 100],
        ],
        'woonwijk' => [
            'label'  => 'Steel een wagen in een woonwijk',
            'bonus'  => 5,
            'schade' => [0, 75],
        ],
        'tankstation' => [
            'label'  => 'Steel een wagen bij een tankstation',
            'bonus'  => 0,
            'schade' => [0, 50],
        ],
    ];
}

/** Slaagkans in procenten voor een plek. */
function steelkans(array $user, string $plek): int
{
    $basis = (int) floor(0.05 * (int) $user['xp']);
    $bonus = steelplekken()[$plek]['bonus'] ?? 0;

    if ((int) $user['level'] > LEVEL_ADMIN) {
        return 50;
    }
    return max(1, min(30, $basis + $bonus));
}

/**
 * Slaagkans om uit de garage van een andere speler te stelen: het grootste
 * risico van de vier opties, dus altijd lager dan tankstation (de moeilijkste
 * straatlocatie) bij dezelfde xp.
 */
function garagekans(array $user): int
{
    $basis = (int) floor(0.05 * (int) $user['xp']);

    if ((int) $user['level'] > LEVEL_ADMIN) {
        return 50;
    }
    return max(1, min(30, $basis - 5));
}

$user = require_login();

if (is_dead()) {
    redirect('rip.php');
}
block_if_jailed();

$uitkomst = null;

if (is_post()) {
    csrf_check();
    try {
        $uitkomst = stelen($user, post('waar'), post('verify'));
    } catch (SpelFout $e) {
        $uitkomst = ['tekst' => $e->getMessage(), 'type' => 'fout', 'auto' => null];
    }
    $user = current_user(true);
}

$wacht = cooldown_left((int) $user['ac_ts']);

layout_header('Auto stelen');

if ($uitkomst !== null) {
    notice(e($uitkomst['tekst']), $uitkomst['type']);

    $autoPad = (string) ($uitkomst['auto']['url'] ?? '');

    // Alleen tonen als het bestand er ook echt staat: de catalogus (cars.url)
    // verwijst naar foto's die niet allemaal aangeleverd zijn, en een kapot
    // plaatje-icoon oogt slordiger dan gewoon niets tonen.
    if ($autoPad !== '' && is_file(BV_ROOT . '/' . $autoPad)) {
        echo '<p><img src="' . e(url($autoPad)) . '" alt="'
           . e((string) $uitkomst['auto']['auto']) . '" style="max-width:100%;border-radius:4px"></p>';
    }
}

panel_open('Steel een wagen');

if ($wacht > 0) {
    echo '<p>Je bent nog aan het uitrusten. Nog <strong data-tot="' . (time() + $wacht) . '">'
       . e(duration($wacht)) . '</strong>.</p>';
} else {
    toon_formulier($user);
}

panel_close();
layout_footer();

// ==========================================================================

/**
 * @return array{tekst:string, type:string, auto:?array}
 * @throws SpelFout
 */
function stelen(array $user, string $waar, string $captcha): array
{
    if (cooldown_left((int) $user['ac_ts']) > 0) {
        throw new SpelFout('Je bent nog aan het uitrusten.');
    }
    if (!captcha_check($captcha)) {
        throw new SpelFout('De code die je invoerde klopt niet.');
    }

    if ($waar === 'gebruiker') {
        return steel_van_speler($user);
    }
    if (!isset(steelplekken()[$waar])) {
        throw new SpelFout('Kies een geldige plek.');
    }

    return steel_op_straat($user, $waar);
}

/** @return array{tekst:string, type:string, auto:?array} */
function steel_op_straat(array $user, string $plek): array
{
    $def      = steelplekken()[$plek];
    $geslaagd = random_int(1, 100) <= steelkans($user, $plek);

    return db_transaction(static function () use ($user, $def, $geslaagd): array {
        q('UPDATE `users` SET `xp` = `xp` + 2, `ac` = FROM_UNIXTIME(?), `nrofcar` = `nrofcar` + 1 WHERE `id` = ?',
            [cooldown_until('auto'), $user['id']]);

        if (!$geslaagd) {
            return mislukking($user);
        }

        // Willekeurige wagen uit de catalogus.
        $model = q_row('SELECT * FROM `cars` ORDER BY RAND() LIMIT 1');

        if ($model === null) {
            throw new SpelFout('Er staan geen wagens in het spel. Waarschuw een beheerder.');
        }

        [$minSchade, $maxSchade] = $def['schade'];
        $schade = random_int($minSchade, $maxSchade);
        $waarde = max(500, (int) round((int) $model['waarde'] * (1 - $schade / 100)));

        q('INSERT INTO `garage` (`login`, `naam`, `waarde`, `damage`, `stad`) VALUES (?, ?, ?, ?, ?)',
            [$user['login'], $model['naam'], $waarde, $schade, $user['stad']]);

        $verhalen = [
            'Je kreeg na lang sleutelen het portier open.',
            'De tank was leeg, maar verderop vond je een jerrycan die nog halfvol was.',
            'Een agent kwam kijken terwijl je aan het slot prutste. Je sloeg hem neer en reed weg.',
            'De wagen was goed beveiligd. Van woede sloeg je tegen de deur — en die ging open.',
            'Er lag een stel op de achterbank. Je trok ze eruit en reed weg.',
            'Ze wilden de wagen wegslepen, dus nam je die er maar naast.',
            'Het alarm ging af. Een voorbijganger zette het uit en zwaaide je uit.',
            'Je kreeg de deur niet open en sloeg het raam in.',
            'De eigenaar kwam net aanlopen. Je sloeg hem neer en reed weg.',
        ];

        $tekst = $verhalen[array_rand($verhalen)] . ' Je hebt een ' . $model['auto']
               . ' gestolen met ' . $schade . '% schade, waarde ' . money($waarde) . '.';

        // In het handschoenenkastje kan iets liggen.
        $diamanten = diamant_vondst($user, 'een autodiefstal');

        if ($diamanten > 0) {
            $tekst .= ' In het handschoenenkastje lag een diamant.';
        }

        return [
            'tekst' => $tekst,
            'type'  => 'ok',
            'auto'  => $model,
        ];
    });
}

/** @return array{tekst:string, type:string, auto:?array} */
function steel_van_speler(array $user): array
{
    $geslaagd = random_int(1, 100) <= garagekans($user);

    return db_transaction(static function () use ($user, $geslaagd): array {
        q('UPDATE `users` SET `xp` = `xp` + 1, `ac` = FROM_UNIXTIME(?), `nrofcar` = `nrofcar` + 1 WHERE `id` = ?',
            [cooldown_until('auto'), $user['id']]);

        // Vergrendel de wagen meteen: zonder FOR UPDATE kunnen twee dieven
        // dezelfde wagen tegelijk uit de garage halen.
        $wagen = q_row(
            "SELECT * FROM `garage`
              WHERE `stad` = ? AND `login` <> ? AND `safe` = 0
           ORDER BY RAND() LIMIT 1
           FOR UPDATE",
            [$user['stad'], $user['login']]
        );

        if ($wagen === null) {
            throw new SpelFout('Er staat hier geen wagen van een andere speler die je kunt stelen.');
        }

        if (!$geslaagd) {
            return mislukking($user);
        }

        $model = q_row('SELECT * FROM `cars` WHERE `naam` = ?', [$wagen['naam']]);

        q('UPDATE `garage` SET `login` = ? WHERE `id` = ?', [$user['login'], $wagen['id']]);

        notify((string) $wagen['login'], 'Autodief',
            $user['login'] . ' heeft je ' . $wagen['naam'] . ' met ' . (int) $wagen['damage']
            . '% schade en een waarde van ' . money((int) $wagen['waarde']) . ' uit je garage gestolen.');

        log_action((string) $user['login'], 'autodiefstal',
            'Wagen gestolen: ' . $wagen['naam'], (int) $wagen['waarde'], (string) $wagen['login']);

        return [
            'tekst' => 'Je hebt een ' . $wagen['naam'] . ' uit de garage van ' . $wagen['login']
                     . ' gestolen, met ' . (int) $wagen['damage'] . '% schade en een waarde van '
                     . money((int) $wagen['waarde']) . '.',
            'type'  => 'ok',
            'auto'  => $model,
        ];
    });
}

/**
 * Een mislukte poging, met soms gevangenisstraf of letsel.
 *
 * @return array{tekst:string, type:string, auto:?array}
 */
function mislukking(array $user): array
{
    $pech = [
        ['De tank was leeg.', 'geen'],
        ['Je kreeg de deur niet open.', 'geen'],
        ['Je vernielde de wagen zo hard dat er niets meer van over was.', 'geen'],
        ['Je reed meteen de muur van de gevangenis om. Je bent gearresteerd.', 'cel'],
        ['Een agent kwam kijken terwijl je aan het slot prutste. Je bent weggelopen.', 'geen'],
        ['De wagen was te goed beveiligd.', 'geen'],
        ['Er lag een stel te vrijen op de achterbank. Je was sneller weg dan zij aangekleed waren.', 'geen'],
        ['De wagen werd net weggesleept.', 'geen'],
        ['Het alarm ging af; je kon maar net ontsnappen.', 'geen'],
        ['De politie kwam achter je aan, maar je kon ontkomen.', 'geen'],
        ['Je sloeg het raam in en sneed je duim open. Je liet de wagen maar staan.', 'gewond'],
        ['Je koos de eerste de beste auto, maar had er toch geen zin meer in.', 'geen'],
        ['Net toen je wilde wegrijden bleken de banden lek.', 'geen'],
        ['De eigenaar kwam net aanlopen.', 'geen'],
    ];

    [$tekst, $gevolg] = $pech[array_rand($pech)];

    if ($gevolg === 'cel') {
        jail_put((string) $user['login'], (int) $user['xp'],
            (string) $user['stad'], (string) $user['famillie']);
    } elseif ($gevolg === 'gewond') {
        q('UPDATE `users` SET `health` = GREATEST(1, `health` - 1) WHERE `id` = ?', [$user['id']]);
        $tekst .= ' Je bent gezondheid kwijtgeraakt.';
    }

    return ['tekst' => $tekst, 'type' => 'fout', 'auto' => null];
}

// ==========================================================================

function toon_formulier(array $user): void
{
    echo '<form method="post">' . csrf_field();
    echo '<table class="lijst"><thead><tr><th></th><th>Waar</th><th class="getal">Slaagkans</th></tr></thead><tbody>';

    $eerste = true;
    foreach (steelplekken() as $sleutel => $def) {
        $gekozen = $eerste ? ' checked' : '';
        $eerste  = false;

        echo '<tr>'
           . '<td><input type="radio" id="p_' . e($sleutel) . '" name="waar" value="' . e($sleutel) . '"' . $gekozen . '></td>'
           . '<td><label for="p_' . e($sleutel) . '">' . e($def['label']) . '</label></td>'
           . '<td class="getal">' . steelkans($user, $sleutel) . '%</td>'
           . '</tr>';
    }

    echo '<tr>'
       . '<td><input type="radio" id="p_gebruiker" name="waar" value="gebruiker"></td>'
       . '<td><label for="p_gebruiker">Steel uit de garage van een andere speler</label></td>'
       . '<td class="getal">' . garagekans($user) . '%</td>'
       . '</tr>';

    echo '</tbody></table>';
    echo captcha_field();
    echo '<p><button type="submit">Steel een wagen</button></p>';
    echo '</form>';
}
