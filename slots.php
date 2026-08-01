<?php
/**
 * Fruitmachine. De gedeelde casinologica staat in inc/casino.php.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';
require BV_INC . '/casino.php';

/** De symbolen op de rollen, van vaak naar zeldzaam. */
function slots_symbolen(): array
{
    return [
        ['naam' => 'Kers',        'teken' => '🍒'],
        ['naam' => 'Citroen',     'teken' => '🍋'],
        ['naam' => 'Sinaasappel', 'teken' => '🍊'],
        ['naam' => 'Klok',        'teken' => '🔔'],
        ['naam' => 'Ster',        'teken' => '⭐'],
        ['naam' => 'Zeven',       'teken' => '7️⃣'],
    ];
}

/** Uitbetalingsfactor bij drie gelijke symbolen. */
function slots_uitbetaling(int $symbool): int
{
    return [0 => 10, 1 => 15, 2 => 20, 3 => 25, 4 => 30, 5 => 50][$symbool] ?? 10;
}

$user = require_login();

if (is_dead()) {
    redirect('rip.php');
}
block_if_jailed();

$melding = null;
$type    = 'info';
$rollen  = [];

if (is_post()) {
    csrf_check();
    try {
        [$melding, $type, $rollen] = verwerk($user);
        $user = current_user(true);
    } catch (SpelFout $e) {
        $melding = $e->getMessage();
        $type    = 'fout';
    }
}

$casino = casino_spel('fruitmachine', (string) $user['stad']);

layout_header('Fruitmachine');

if ($melding !== null) {
    notice(e($melding), $type);
}

if ($rollen !== []) {
    echo '<div class="rollen">';
    foreach ($rollen as $rol) {
        echo '<span title="' . e($rol['naam']) . '">' . $rol['teken'] . '</span>';
    }
    echo '</div>';
}

if (casino_kop($user, $casino)) {
    panel_open('Gokken');
    echo '<p>Drie gelijke symbolen betalen uit. Je hebt ' . money((int) $user['zak']) . ' op zak.</p>';
    echo '<form method="post">' . csrf_field();
    echo '<input type="hidden" name="actie" value="speel">';
    echo '<div class="veldenraster">';
    echo '<label for="inzet">Inzet</label>';
    echo '<input id="inzet" name="inzet" type="number" min="1" max="' . (int) $casino['inzet']
       . '" step="1" required>';
    echo '<span></span><button type="submit">Draaien</button>';
    echo '</div></form>';

    echo '<h3>Uitbetaling bij drie gelijke</h3><ul>';
    foreach (slots_symbolen() as $nr => $symbool) {
        echo '<li>' . $symbool['teken'] . ' ' . e($symbool['naam'])
           . ' — ' . slots_uitbetaling($nr) . '× je inzet</li>';
    }
    echo '</ul>';
    panel_close();
}

layout_footer();

// ==========================================================================

/**
 * @return array{0:string, 1:string, 2:array}
 * @throws SpelFout
 */
function verwerk(array $user): array
{
    $casino = casino_spel('fruitmachine', (string) $user['stad']);

    return match (post('actie')) {
        'koop'  => [casino_kopen($user, 'fruitmachine', (string) $user['stad']), 'ok', []],
        'inzet' => [casino_inzet_zetten($user, $casino, int_input('maxinzet')), 'ok', []],
        'speel' => spelen($user, $casino, int_input('inzet', 0)),
        default => throw new SpelFout('Onbekende handeling.'),
    };
}

/**
 * @return array{0:string, 1:string, 2:array}
 * @throws SpelFout
 */
function spelen(array $user, array $casino, int $inzet): array
{
    if ($inzet < 1) {
        throw new SpelFout('Vul een inzet van minstens 1 in.');
    }
    if ($inzet > (int) $casino['inzet']) {
        throw new SpelFout('De maximale inzet is ' . money((int) $casino['inzet']) . '.');
    }
    if ($casino['owner'] === $user['login']) {
        throw new SpelFout('Je kunt niet in je eigen casino spelen.');
    }

    $symbolen = slots_symbolen();
    $rollen   = [];
    $nummers  = [];

    // Hogere symbolen komen minder vaak voor: twee worpen, de laagste telt.
    for ($i = 0; $i < 3; $i++) {
        $nr        = min(random_int(0, 5), random_int(0, 5));
        $nummers[] = $nr;
        $rollen[]  = $symbolen[$nr];
    }

    $winst = ($nummers[0] === $nummers[1] && $nummers[1] === $nummers[2])
        ? $inzet * slots_uitbetaling($nummers[0])
        : 0;

    $uitslag = casino_afrekenen($user, 'fruitmachine', (string) $user['stad'], $inzet, $winst);

    return [$uitslag['tekst'], $uitslag['type'], $rollen];
}
