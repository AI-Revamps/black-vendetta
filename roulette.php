<?php
/**
 * Roulette. De gedeelde casinologica staat in inc/casino.php.
 *
 * Wat hier gerepareerd is ten opzichte van de oude versie:
 *
 *  - GELDPERS: kon de eigenaar niet volledig uitbetalen, dan werd
 *    `$winst = $eigenaar->bank` berekend maar vervolgens `$prijs1` uitgekeerd,
 *    het volle bedrag. Het verschil kwam uit het niets. In slots.php en
 *    guess.php ging dat wel goed, dus het was een vergissing. De afhandeling
 *    zit nu in casino_afrekenen(), dat nooit meer uitkeert dan er in de kas zit.
 *  - Geen transactie: twee gelijktijdige winnende inzetten lazen allebei
 *    hetzelfde banksaldo van de eigenaar.
 *  - De 47 inzetvelden werden gecontroleerd met een reeks van 47 vergelijkingen
 *    `$_POST['x'] >= 0`. Op een niet-numerieke waarde is die vergelijking in
 *    PHP 8 waar, waarna de optelling erop stukloopt. Nu een inzettabel met
 *    controle per veld.
 *  - De totale inzet werd begrensd met strlen() in plaats van op waarde.
 *  - `if ($getal == 0) { $kleur == ""; }` gebruikte een vergelijking waar een
 *    toewijzing bedoeld was.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';
require BV_INC . '/casino.php';

/**
 * De mogelijke inzetten: sleutel => [label, uitbetaling, welke getallen winnen].
 * De uitbetaling is bruto, dus inclusief je eigen inzet.
 */
function roulette_inzetten(): array
{
    // Eenmalig opbouwen: deze tabel wordt bij het tekenen van het speelveld
    // tientallen keren opgevraagd.
    static $cache = null;

    if ($cache !== null) {
        return $cache;
    }

    $rood = [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36];
    $zwart = array_values(array_diff(range(1, 36), $rood));

    $inzetten = [];

    // Losse getallen, 36 keer je inzet.
    for ($n = 0; $n <= 36; $n++) {
        $inzetten['getal' . $n] = [
            'label'   => (string) $n,
            'factor'  => 36,
            'winnend' => [$n],
        ];
    }

    $inzetten['rood']    = ['label' => 'Rood',    'factor' => 2, 'winnend' => $rood];
    $inzetten['zwart']   = ['label' => 'Zwart',   'factor' => 2, 'winnend' => $zwart];
    $inzetten['even']    = ['label' => 'Even',    'factor' => 2,
                            'winnend' => array_values(array_filter(range(1, 36), static fn ($n) => $n % 2 === 0))];
    $inzetten['oneven']  = ['label' => 'Oneven',  'factor' => 2,
                            'winnend' => array_values(array_filter(range(1, 36), static fn ($n) => $n % 2 === 1))];
    $inzetten['118']     = ['label' => '1 t/m 18',  'factor' => 2, 'winnend' => range(1, 18)];
    $inzetten['1936']    = ['label' => '19 t/m 36', 'factor' => 2, 'winnend' => range(19, 36)];
    $inzetten['112']     = ['label' => '1 t/m 12',  'factor' => 3, 'winnend' => range(1, 12)];
    $inzetten['1324']    = ['label' => '13 t/m 24', 'factor' => 3, 'winnend' => range(13, 24)];
    $inzetten['2536']    = ['label' => '25 t/m 36', 'factor' => 3, 'winnend' => range(25, 36)];

    // Drie rijen van het speelveld.
    for ($r = 1; $r <= 3; $r++) {
        $inzetten['rij' . $r] = [
            'label'   => 'Rij ' . $r,
            'factor'  => 3,
            'winnend' => array_values(array_filter(range(1, 36), static fn ($n) => $n % 3 === $r % 3)),
        ];
    }

    return $cache = $inzetten;
}

/** Is dit getal rood, zwart of groen? */
function roulette_kleur(int $getal): string
{
    if ($getal === 0) {
        return 'groen';
    }
    return in_array($getal, roulette_inzetten()['rood']['winnend'], true) ? 'rood' : 'zwart';
}

$user = require_login();

if (is_dead()) {
    redirect('rip.php');
}
block_if_jailed();

$melding   = null;
$type      = 'info';
$getrokken = null;

if (is_post()) {
    csrf_check();
    try {
        [$melding, $type, $getrokken] = verwerk($user);
        $user = current_user(true);
    } catch (SpelFout $e) {
        $melding = $e->getMessage();
        $type    = 'fout';
    }
}

$casino = casino_spel('roulette', (string) $user['stad']);

layout_header('Roulette');

if ($melding !== null) {
    notice(e($melding), $type);
}

if ($getrokken !== null) {
    $kleur = roulette_kleur($getrokken);
    echo '<p class="getrokken">De bal viel op <strong class="bal bal-' . e($kleur) . '">'
       . $getrokken . '</strong> (' . e($kleur) . ').</p>';
}

if (casino_kop($user, $casino)) {
    toon_tafel($user, $casino);
}

layout_footer();

// ==========================================================================

/**
 * @return array{0:string, 1:string, 2:?int}
 * @throws SpelFout
 */
function verwerk(array $user): array
{
    $casino = casino_spel('roulette', (string) $user['stad']);

    return match (post('actie')) {
        'koop'  => [casino_kopen($user, 'roulette', (string) $user['stad']), 'ok', null],
        'inzet' => [casino_inzet_zetten($user, $casino, int_input('maxinzet')), 'ok', null],
        'speel' => spelen($user, $casino),
        default => throw new SpelFout('Onbekende handeling.'),
    };
}

/**
 * @return array{0:string, 1:string, 2:?int}
 * @throws SpelFout
 */
function spelen(array $user, array $casino): array
{
    if ($casino['owner'] === $user['login']) {
        throw new SpelFout('Je kunt niet in je eigen casino spelen.');
    }

    // --- Inzetten inlezen en controleren ---
    $mogelijk = roulette_inzetten();
    $gezet    = [];
    $totaal   = 0;

    foreach ($mogelijk as $sleutel => $def) {
        $ruw = $_POST[$sleutel] ?? '';

        if (!is_string($ruw) || trim($ruw) === '') {
            continue;
        }
        if (!preg_match('/^\d{1,9}$/', trim($ruw))) {
            throw new SpelFout('De inzet bij "' . $def['label'] . '" is geen geldig bedrag.');
        }

        $bedrag = (int) trim($ruw);

        if ($bedrag > 0) {
            $gezet[$sleutel] = $bedrag;
            $totaal += $bedrag;
        }
    }

    if ($totaal < 1) {
        throw new SpelFout('Je hebt nergens op ingezet.');
    }
    if ($totaal > (int) $casino['inzet']) {
        throw new SpelFout('Je mag hoogstens ' . money((int) $casino['inzet']) . ' inzetten. '
            . 'Je zette ' . money($totaal) . '.');
    }

    // --- Draaien ---
    $getal = random_int(0, 36);

    $bruto = 0;
    foreach ($gezet as $sleutel => $bedrag) {
        if (in_array($getal, $mogelijk[$sleutel]['winnend'], true)) {
            $bruto += $bedrag * $mogelijk[$sleutel]['factor'];
        }
    }

    $netto = $bruto - $totaal;

    // De hele afwikkeling in één transactie. lock_user gebeurt hier, zodat de
    // dekkingscontrole en de afrekening niet uit elkaar getrokken kunnen worden.
    $uitslag = db_transaction(static function () use ($user, $totaal, $netto): array {
        $speler = lock_user((int) $user['id']);

        if ((int) $speler['zak'] < $totaal) {
            throw new SpelFout('Je hebt niet genoeg geld op zak voor deze inzet.');
        }

        if ($netto > 0) {
            return casino_afrekenen($user, 'roulette', (string) $user['stad'], 0, $netto);
        }
        if ($netto < 0) {
            return casino_afrekenen($user, 'roulette', (string) $user['stad'], -$netto, 0);
        }

        return ['tekst' => 'Je speelt quitte.', 'type' => 'info', 'uitbetaald' => 0, 'failliet' => false];
    });

    return [$uitslag['tekst'], $uitslag['type'], $getal];
}

// ==========================================================================

function toon_tafel(array $user, array $casino): void
{
    $inzetten = roulette_inzetten();

    panel_open('Roulettetafel');
    echo '<p>Je hebt ' . money((int) $user['zak']) . ' op zak. De maximale totale inzet is '
       . money((int) $casino['inzet']) . '. Laat velden leeg waar je niet op inzet.</p>';

    echo '<form method="post">' . csrf_field();
    echo '<input type="hidden" name="actie" value="speel">';

    // --- Getallen 0 tot en met 36 ---
    echo '<h3>Losse getallen (36×)</h3>';
    echo '<div class="rouletteveld">';
    for ($n = 0; $n <= 36; $n++) {
        $kleur = roulette_kleur($n);
        echo '<label class="vak vak-' . e($kleur) . '"><span>' . $n . '</span>'
           . '<input type="text" inputmode="numeric" name="getal' . $n . '" maxlength="9" '
           . 'aria-label="Inzet op ' . $n . '"></label>';
    }
    echo '</div>';

    // --- Overige inzetten ---
    echo '<h3>Vakken</h3>';
    echo '<div class="veldenraster">';
    foreach ($inzetten as $sleutel => $def) {
        if (str_starts_with($sleutel, 'getal')) {
            continue;
        }
        echo '<label for="i_' . e($sleutel) . '">' . e($def['label'])
           . ' <small>(' . $def['factor'] . '×)</small></label>';
        echo '<input id="i_' . e($sleutel) . '" name="' . e($sleutel) . '" type="text" '
           . 'inputmode="numeric" maxlength="9">';
    }
    echo '</div>';

    echo '<p><button type="submit">Draai het wiel</button></p>';
    echo '</form>';

    echo '<p class="uitleg">De uitbetaling is bruto: bij 2× krijg je je inzet plus evenveel terug. '
       . 'Valt de bal op 0, dan wint alleen wie precies op 0 heeft ingezet.</p>';

    panel_close();
}
