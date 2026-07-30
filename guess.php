<?php
/**
 * Nummerraden: raad een getal van 1 tot 10 en win drie keer je inzet.
 * De gedeelde casinologica staat in inc/casino.php.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';
require BV_INC . '/casino.php';

const RAAD_MAX      = 10;
const RAAD_FACTOR   = 3;

$user = require_login();

if (is_dead()) {
    redirect('rip.php');
}
block_if_jailed();

$melding  = null;
$type     = 'info';
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

$casino = casino_spel('nummerraden', (string) $user['stad']);

layout_header('Nummerraden');

if ($melding !== null) {
    notice(e($melding), $type);
}

if ($getrokken !== null) {
    echo '<p class="getrokken">Het getal was <strong>' . $getrokken . '</strong>.</p>';
}

if (casino_kop($user, $casino)) {
    panel_open('Nummerraden');
    echo '<p>Raad een getal van 1 tot ' . RAAD_MAX . '. Goed geraden betaalt '
       . RAAD_FACTOR . '× je inzet. Je hebt ' . money((int) $user['zak']) . ' op zak.</p>';

    echo '<form method="post">' . csrf_field();
    echo '<input type="hidden" name="actie" value="speel">';
    echo '<div class="veldenraster">';
    echo '<label for="getal">Jouw getal</label>';
    echo '<input id="getal" name="getal" type="number" min="1" max="' . RAAD_MAX . '" step="1" required>';
    echo '<label for="inzet">Inzet</label>';
    echo '<input id="inzet" name="inzet" type="number" min="1" max="' . (int) $casino['inzet']
       . '" step="1" required>';
    echo '<span></span><button type="submit">Raden</button>';
    echo '</div></form>';
    panel_close();
}

layout_footer();

// ==========================================================================

/**
 * @return array{0:string, 1:string, 2:?int}
 * @throws SpelFout
 */
function verwerk(array $user): array
{
    $casino = casino_spel('nummerraden', (string) $user['stad']);

    return match (post('actie')) {
        'koop'  => [casino_kopen($user, 'nummerraden', (string) $user['stad']), 'ok', null],
        'inzet' => [casino_inzet_zetten($user, $casino, int_input('maxinzet')), 'ok', null],
        'speel' => spelen($user, $casino, int_input('getal', 0), int_input('inzet', 0)),
        default => throw new SpelFout('Onbekende handeling.'),
    };
}

/**
 * @return array{0:string, 1:string, 2:?int}
 * @throws SpelFout
 */
function spelen(array $user, array $casino, int $getal, int $inzet): array
{
    if ($getal < 1 || $getal > RAAD_MAX) {
        throw new SpelFout('Kies een getal van 1 tot ' . RAAD_MAX . '.');
    }
    if ($inzet < 1) {
        throw new SpelFout('Vul een inzet van minstens 1 in.');
    }
    if ($inzet > (int) $casino['inzet']) {
        throw new SpelFout('De maximale inzet is ' . money((int) $casino['inzet']) . '.');
    }
    if ($casino['owner'] === $user['login']) {
        throw new SpelFout('Je kunt niet in je eigen casino spelen.');
    }

    $getrokken = random_int(1, RAAD_MAX);
    $winst     = $getrokken === $getal ? $inzet * RAAD_FACTOR : 0;

    $uitslag = casino_afrekenen($user, 'nummerraden', (string) $user['stad'], $inzet, $winst);

    return [$uitslag['tekst'], $uitslag['type'], $getrokken];
}
