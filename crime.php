<?php
/**
 * Misdaden plegen.
 *
 * Elke misdaad heeft een slagingskans die met je ervaring meegroeit, een eigen
 * opbrengst en een lijst mislukkingsberichten. Sommige mislukkingen leveren
 * gevangenisstraf of gezondheidsverlies op.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';
require BV_INC . '/captcha.php';

$user = require_login();

if (is_dead()) {
    redirect('rip.php');
}
block_if_jailed();

$uitkomst = null;   // ['tekst' => ..., 'type' => 'ok'|'fout']

if (is_post()) {
    csrf_check();
    $uitkomst = misdaad_plegen($user, post('crime'), post('verify'));
    $user     = current_user(true);
}

$wacht = cooldown_left((int) $user['crime_ts']);

layout_header('Misdaad');
panel_open('Misdaad');

if ($uitkomst !== null) {
    notice(e($uitkomst['tekst']), $uitkomst['type']);
}

if ($wacht > 0) {
    echo '<p>Je moet nog <strong data-tot="' . (time() + $wacht) . '">'
       . e(duration($wacht)) . '</strong> wachten voor je volgende misdaad.</p>';
} else {
    toon_keuzes($user);
}

panel_close();
layout_footer();

// ==========================================================================
// De misdaden
// ==========================================================================

/**
 * Definitie per misdaad.
 *
 * kans      : functie die de slagingskans in procenten teruggeeft
 * opbrengst : functie die het buitgemaakte bedrag teruggeeft
 * berichten : mislukkingsteksten; de index bepaalt het gevolg
 * cel       : index van het bericht dat gevangenisstraf oplevert (of null)
 * schade    : index van het bericht dat gezondheid kost (of null)
 */
function misdaden(): array
{
    return [
        'kind' => [
            'label'     => 'Steel van een kind',
            'kans'      => static fn (int $xp): int => min(50, (int) round($xp / 2)),
            'opbrengst' => static fn (): int => random_int(1, 10),
            'berichten' => [
                'Het kind had niets bij zich.',
                'Het kind begon te schreeuwen, je bent er maar vandoor gegaan.',
                'Je greep naar de portemonnee, maar het kind rende weg.',
                'Een voorbijganger zag je bezig en riep om hulp. Je koos het hazenpad.',
                'Je werd betrapt terwijl je het kind vastgreep. De politie nam je mee.',
            ],
            'cel'    => 4,
            'schade' => null,
        ],

        'puber' => [
            'label'     => 'Steel van een puber',
            'kans'      => static fn (int $xp): int => min(50, (int) round($xp / 3.33)),
            'opbrengst' => static fn (): int => random_int(1, 100),
            'berichten' => [
                'Hij had niets bij zich.',
                'De puber kende je nog van een eerdere ruzie en kwam woedend op je af. Je moest vluchten.',
                'Ze zag je aankomen en stapte een winkel binnen.',
                'Hij liep snel weg.',
                'Je had zijn portemonnee al te pakken toen de politie arriveerde. Je zit nu vast.',
                'Zijn vrienden kwamen van alle kanten en sloegen je in elkaar.',
            ],
            'cel'    => 4,
            'schade' => 5,
        ],

        'juwelier' => [
            'label'     => 'Beroof een juwelier',
            'kans'      => static fn (int $xp): int => min(50, (int) round($xp / 6.66)),
            'opbrengst' => static fn (): int => random_int(500, 1000),
            'berichten' => [
                'Er liep net een andere gangster met een lading juwelen naar buiten.',
                'De juwelier haalde een geweer van achter de toonbank en begon te schieten. Je kon net op tijd wegkomen.',
                'De zaak was gesloten.',
                'Je wilde net naar binnen toen er een politiewagen voorbijreed.',
                'Het alarm ging af zodra je de vitrine opende. De politie stond binnen een minuut buiten.',
                'Je was de winkel uit, maar struikelde over een zwerver. Hij griste de juwelen mee en verdween.',
            ],
            'cel'    => 4,
            'schade' => null,
        ],

        'bar' => [
            'label'     => 'Schiet op brievenbussen',
            'kans'      => null,          // eigen berekening, zie hieronder
            'opbrengst' => null,          // levert moordervaring op, geen geld
            'berichten' => [
                'Je schoot een hond dood die toevallig passeerde.',
                'Je schoot in de grond. Zonde van de kogel.',
                'Je kogel boorde zich in de band van een auto verderop.',
                'Je schoot een vogel uit een boom.',
                'Je schoot de pet van een politieman. Hij besloot je te arresteren.',
                'Er vloog een stuk metaal uit de brievenbus, recht in je wang.',
            ],
            'cel'    => 4,
            'schade' => 5,
        ],

        'member' => [
            'label'     => 'Steel van een member',
            'kans'      => static fn (int $xp): int => 10,
            'opbrengst' => null,          // hangt af van het slachtoffer
            'berichten' => [
                'Hij had geen geld bij zich.',
                'Je wilde net zijn portemonnee pakken toen hij een wapen trok. Je ging ervandoor.',
                'Je werd betrapt en gearresteerd.',
                'Hij zag je aankomen en riep de hele straat bij elkaar. Je bent gaan lopen.',
                'Hij had geen portemonnee bij zich.',
                'Ze had door wat je van plan was en liep snel een café binnen.',
                'Je raakte in een handgemeen en kreeg een klap te verwerken.',
            ],
            'cel'    => 2,
            'schade' => 6,
        ],
    ];
}

/** Slagingskans in procenten voor deze speler. */
function slaagkans(string $sleutel, array $user): int
{
    $xp = (int) $user['xp'];

    if ($sleutel === 'bar') {
        // Schieten wordt beter naarmate je moordervaring stijgt.
        $se = (float) $user['se'];
        return match (true) {
            $se < 10 => 17,
            $se < 25 => 20,
            $se < 50 => 25,
            default  => 33,
        };
    }

    $def = misdaden()[$sleutel];
    return max(1, min(100, ($def['kans'])($xp)));
}

/**
 * Voer een misdaad uit.
 *
 * @return array{tekst:string, type:string}
 */
function misdaad_plegen(array $user, string $keuze, string $captcha): array
{
    $lijst = misdaden();

    if (!isset($lijst[$keuze])) {
        return ['tekst' => 'Kies een geldige misdaad.', 'type' => 'fout'];
    }
    if (cooldown_left((int) $user['crime_ts']) > 0) {
        return ['tekst' => 'Je moet nog even wachten voor je volgende misdaad.', 'type' => 'fout'];
    }
    if (!captcha_check($captcha)) {
        return ['tekst' => 'De code die je invoerde klopt niet. Probeer het opnieuw.', 'type' => 'fout'];
    }

    // Extra voorwaarden voor het schieten op brievenbussen.
    if ($keuze === 'bar') {
        if ((int) $user['wapon'] < 1) {
            return ['tekst' => 'Je hebt nog geen wapen. Koop er een in de winkel.', 'type' => 'fout'];
        }
        if ((float) $user['se'] >= 100) {
            return ['tekst' => 'Je moordervaring is al 100%. Hier leer je niets meer.', 'type' => 'fout'];
        }
    }

    $def     = $lijst[$keuze];
    $geslaagd = random_int(1, 100) <= slaagkans($keuze, $user);

    return db_transaction(static function () use ($user, $keuze, $def, $geslaagd): array {

        // Afkoeltijd en teller lopen altijd, geslaagd of niet.
        q(
            'UPDATE `users`
                SET `crime` = FROM_UNIXTIME(?), `nrofcrime` = `nrofcrime` + 1
              WHERE `id` = ?',
            [cooldown_until('crime'), $user['id']]
        );

        if (!$geslaagd) {
            return misdaad_mislukt($user, $def);
        }

        $uitslag = misdaad_geslaagd($user, $keuze, $def);

        // Bij een geslaagde misdaad kun je een diamant vinden. De kans staat
        // in de beheerinstellingen; standaard één op vijfhonderd.
        $diamanten = diamant_vondst($user, 'een misdaad');

        if ($diamanten > 0) {
            $uitslag['tekst'] .= ' ' . diamant_melding($diamanten);
        }

        return $uitslag;
    });
}

/** @return array{tekst:string, type:string} */
function misdaad_geslaagd(array $user, string $keuze, array $def): array
{
    // Schieten levert ervaring op in plaats van geld.
    if ($keuze === 'bar') {
        q('UPDATE `users` SET `se` = LEAST(100, `se` + 0.1) WHERE `id` = ?', [$user['id']]);
        return ['tekst' => schietbericht((float) $user['se']), 'type' => 'ok'];
    }

    // Van een member stelen: het geld komt van een echte speler.
    if ($keuze === 'member') {
        return steel_van_member($user);
    }

    $buit = ($def['opbrengst'])();
    q('UPDATE `users` SET `zak` = `zak` + ?, `xp` = `xp` + 1 WHERE `id` = ?', [$buit, $user['id']]);

    return ['tekst' => 'Het is gelukt. Je hebt ' . money($buit) . ' gestolen.', 'type' => 'ok'];
}

/** @return array{tekst:string, type:string} */
function misdaad_mislukt(array $user, array $def): array
{
    $index  = array_rand($def['berichten']);
    $tekst  = $def['berichten'][$index];

    q('UPDATE `users` SET `xp` = `xp` + 1 WHERE `id` = ?', [$user['id']]);

    if ($def['cel'] !== null && $index === $def['cel']) {
        jail_put((string) $user['login'], (int) $user['xp'], (string) $user['stad'], (string) $user['famillie']);
        $tekst .= ' Je zit nu in de gevangenis.';
    }

    if ($def['schade'] !== null && $index === $def['schade']) {
        q('UPDATE `users` SET `health` = GREATEST(1, `health` - 2) WHERE `id` = ?', [$user['id']]);
        $tekst .= ' Je bent gezondheid kwijtgeraakt.';
    }

    return ['tekst' => $tekst, 'type' => 'fout'];
}

/**
 * Steel van de rijkste speler in je stad.
 *
 * Het slachtoffer wordt met FOR UPDATE vergrendeld: twee dieven die tegelijk
 * toeslaan kunnen zo niet allebei hetzelfde geld buitmaken.
 */
function steel_van_member(array $user): array
{
    $slachtoffer = q_row(
        "SELECT `id`, `login`, `zak` FROM `users`
          WHERE `login` <> :login AND `stad` = :stad AND `status` = 'levend'
            AND `level` < :staf AND `zak` >= 10
       ORDER BY `zak` DESC LIMIT 1
       FOR UPDATE",
        ['login' => $user['login'], 'stad' => $user['stad'], 'staf' => LEVEL_MODERATOR]
    );

    if ($slachtoffer === null) {
        return ['tekst' => 'Er is niemand in je stad met genoeg geld op zak.', 'type' => 'fout'];
    }

    $buit = min(100_000, (int) round((int) $slachtoffer['zak'] * 0.25));

    if ($buit < 1) {
        return ['tekst' => 'Er is niemand in je stad met genoeg geld op zak.', 'type' => 'fout'];
    }

    q('UPDATE `users` SET `zak` = `zak` - ? WHERE `id` = ?', [$buit, $slachtoffer['id']]);
    q('UPDATE `users` SET `zak` = `zak` + ?, `xp` = `xp` + 1 WHERE `id` = ?', [$buit, $user['id']]);

    notify(
        (string) $slachtoffer['login'],
        'Zakkenroller',
        $user['login'] . ' heeft ' . money($buit) . ' uit je zak gestolen.'
    );

    return [
        'tekst' => 'Je hebt ' . money($buit) . ' gestolen van ' . $slachtoffer['login'] . '.',
        'type'  => 'ok',
    ];
}

/** Passend bericht bij het schieten, afhankelijk van je moordervaring. */
function schietbericht(float $se): string
{
    return match (true) {
        $se < 15 => 'Je kogel schampte langs de brievenbus. Blijf oefenen.',
        $se < 25 => 'Je schoot de vogelpoep van de brievenbus af.',
        $se < 30 => 'Je kogel boorde een gat in het dak van de brievenbus.',
        $se < 50 => 'Je schoot recht door de gleuf van de brievenbus.',
        $se < 75 => 'Je schoot de krant uit de brievenbus.',
        default  => 'Je schoot de brievenbus van zijn paal.',
    };
}

// ==========================================================================

function toon_keuzes(array $user): void
{
    echo '<form method="post">' . csrf_field();
    echo '<table class="lijst"><thead><tr><th></th><th>Misdaad</th><th class="getal">Slaagkans</th></tr></thead><tbody>';

    $eerste = true;
    foreach (misdaden() as $sleutel => $def) {
        $gekozen = $eerste ? ' checked' : '';
        $eerste  = false;

        echo '<tr>'
           . '<td><input type="radio" id="c_' . e($sleutel) . '" name="crime" value="' . e($sleutel) . '"' . $gekozen . '></td>'
           . '<td><label for="c_' . e($sleutel) . '">' . e($def['label']) . '</label></td>'
           . '<td class="getal">' . slaagkans($sleutel, $user) . '%</td>'
           . '</tr>';
    }

    echo '</tbody></table>';
    echo captcha_field();
    echo '<p><button type="submit">Pleeg de misdaad</button></p>';
    echo '</form>';
}
