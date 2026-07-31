<?php
/**
 * Promotiegeld: wat je familie uitkeert als een lid een nieuwe rang haalt.
 *
 * LET OP - dit werd wel ingesteld maar nooit uitgekeerd.
 *
 * De bedragen werden opgeslagen in `famillie`.`rang2` tot en met `rang14`,
 * maar nergens in de hele codebase werd een van die kolommen uitgelezen. Er
 * bestond geen enkele uitbetalingscode. Het instelscherm deed dus niets.
 *
 * De uitbetaling zit nu in fam_promotie_uitbetalen() in inc/familie.php en
 * wordt bij een rangstijging aangeroepen vanuit bootstrap.php.
 *
 * Verder gerepareerd:
 *
 *  - De dertien bedragen werden gecontroleerd met dertien losse vergelijkingen
 *    `>= 0`. Op een niet-numerieke waarde is die vergelijking in PHP 8 waar,
 *    waarna de waarde ongefilterd in de query belandde.
 *  - Er stond geen rangcontrole op: elk familielid kon de bedragen aanpassen.
 *  - Geen CSRF-bescherming.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';
require_once BV_INC . '/familie.php';

/** De spelersrangen waarvoor promotiegeld ingesteld kan worden. */
const PROMOTIE_RANG_MIN = 2;
const PROMOTIE_RANG_MAX = 14;
const PROMOTIE_MAX      = 100_000_000;

$user = require_login();

$melding = null;
$type    = 'info';

if (is_post()) {
    csrf_check();
    try {
        $melding = opslaan($user);
        $type    = 'ok';
    } catch (SpelFout $e) {
        $melding = $e->getMessage();
        $type    = 'fout';
    }
}

layout_header('Promotiegeld');

if ($melding !== null) {
    notice(e($melding), $type);
}

panel_open('Promotiegeld');

$familie = fam_van($user);

if ($familie === null) {
    echo '<p>Je zit niet in een familie.</p>';
} elseif ((int) $user['famrang'] < FAM_HALFDON) {
    echo '<p>Alleen vanaf de rang ' . e(fam_rangnaam(FAM_HALFDON))
       . ' kun je het promotiegeld instellen.</p>';
    toon_overzicht($familie);
} else {
    echo '<p>Stel in wat je familie uitkeert wanneer een lid een nieuwe rang bereikt. '
       . 'Het bedrag komt uit de familiekas; is die te laag, dan krijgt het lid een '
       . 'bericht dat er niet uitbetaald kon worden.</p>';
    echo '<p>In kas: <strong>' . money((int) $familie['bank']) . '</strong></p>';

    echo '<form method="post">' . csrf_field();
    echo '<div class="veldenraster">';

    foreach (promotie_rangen() as $nr => $naam) {
        echo '<label for="rang' . $nr . '">' . e($naam) . '</label>';
        echo '<input id="rang' . $nr . '" name="rang' . $nr . '" type="number" min="0" max="'
           . PROMOTIE_MAX . '" step="1" value="' . (int) ($familie['rang' . $nr] ?? 0) . '">';
    }

    echo '<span></span><button type="submit">Opslaan</button>';
    echo '</div></form>';
}

panel_close();
layout_footer();

// ==========================================================================

/** De rangen waarvoor promotiegeld geldt, met hun naam uit de rangladder. */
function promotie_rangen(): array
{
    $ladder = rank_ladder();
    $rangen = [];

    for ($nr = PROMOTIE_RANG_MIN; $nr <= PROMOTIE_RANG_MAX; $nr++) {
        // rank_ladder() is nul-geïndexeerd, de rangnummers beginnen bij 1.
        $rangen[$nr] = $ladder[$nr - 1][1] ?? ('Rang ' . $nr);
    }

    return $rangen;
}

/** @throws SpelFout */
function opslaan(array $user): string
{
    $familie = fam_eis_rang($user, FAM_HALFDON);

    $waarden = [];

    foreach (array_keys(promotie_rangen()) as $nr) {
        $ruw = trim((string) ($_POST['rang' . $nr] ?? '0'));

        // In de oude versie werd hier alleen `>= 0` op losgelaten, wat op een
        // niet-numerieke waarde in PHP 8 waar is.
        if ($ruw !== '' && !preg_match('/^\d{1,9}$/', $ruw)) {
            throw new SpelFout('Het bedrag bij rang ' . $nr . ' is geen geldig getal.');
        }

        $bedrag = (int) ($ruw === '' ? 0 : $ruw);

        if ($bedrag > PROMOTIE_MAX) {
            throw new SpelFout('Het bedrag bij rang ' . $nr . ' is te hoog.');
        }

        $waarden[$nr] = $bedrag;
    }

    // Kolomnamen komen uit een vaste reeks getallen, niet uit invoer.
    $zetten = [];
    $params = [];

    foreach ($waarden as $nr => $bedrag) {
        $zetten[] = "`rang{$nr}` = ?";
        $params[] = $bedrag;
    }

    $params[] = $familie['name'];

    q('UPDATE `famillie` SET ' . implode(', ', $zetten) . ' WHERE `name` = ?', $params);

    return 'Het promotiegeld is bijgewerkt.';
}

function toon_overzicht(array $familie): void
{
    echo '<div class="tabelwikkel"><table class="lijst">';
    echo '<thead><tr><th>Rang</th><th class="getal">Uitkering</th></tr></thead><tbody>';

    foreach (promotie_rangen() as $nr => $naam) {
        echo '<tr><td>' . e($naam) . '</td><td class="getal">'
           . money((int) ($familie['rang' . $nr] ?? 0)) . '</td></tr>';
    }

    echo '</tbody></table></div>';
}
