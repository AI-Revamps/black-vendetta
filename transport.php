<?php
/**
 * Reizen tussen steden.
 *
 * Wat hier gerepareerd is ten opzichte van de oude versie:
 *
 *  - Reizen ging via een GET-link (transport.php?x=Amsterdam). Een afbeelding
 *    met die URL in een forumbericht verplaatste de lezer naar een andere stad,
 *    inclusief de reiskosten. Nu POST met CSRF-token.
 *  - Regel 74 luidde `if ($data->rijbewijs = 0)`: één isgelijkteken, dus een
 *    toewijzing in plaats van een vergelijking. De uitkomst was altijd onwaar,
 *    waardoor de rijbewijscontrole nooit werkte, én de waarde in het geheugen
 *    op nul werd gezet.
 *  - Enschede toonde € 4.500, controleerde op € 4.500 maar schreef € 3.500 af.
 *  - De prijzen stonden twee keer in het bestand: één keer voor de controle en
 *    één keer voor de tabel. Ze konden dus uit de pas lopen, zoals bij Enschede.
 *  - De stadsnamen waren kale woorden, wat in PHP 8 een fatale fout geeft.
 *  - De prijzen stonden daarna nog steeds hardcoded in dit bestand, waardoor een
 *    stad die je in de configuratie toevoegde onbereikbaar bleef. Ze komen nu
 *    uit `stad`.`transp`.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

/**
 * Reiskosten per bestemming, uit de kolom `stad`.`transp`.
 *
 * Stond hier eerder als vaste lijst. Een stad die je in de configuratie
 * toevoegde ontbrak dan hier, en was daardoor onbereikbaar. Nu komt de prijs
 * uit de database en stel je hem in op de beheerpagina Steden.
 *
 * Alleen steden die óók in de configuratie staan tellen mee: een oude rij die
 * in `stad` is blijven staan hoort geen bestemming te zijn.
 */
function reisprijzen(): array
{
    $prijzen = [];

    foreach (q_all('SELECT `stad`, `transp` FROM `stad` ORDER BY `transp`, `stad`') as $rij) {
        if (is_city((string) $rij['stad'])) {
            $prijzen[(string) $rij['stad']] = (int) $rij['transp'];
        }
    }

    return $prijzen;
}

$user = require_login();

if (is_dead()) {
    redirect('rip.php');
}
block_if_jailed();

$melding = null;
$type    = 'info';

if (is_post()) {
    csrf_check();
    try {
        $melding = reizen($user, post('stad'));
        $type    = 'ok';
        $user    = current_user(true);
    } catch (SpelFout $e) {
        $melding = $e->getMessage();
        $type    = 'fout';
    }
}

$vervoer   = (int) $user['trans'];
$wachttijd = cooldown_left((int) $user['transport_ts']);

layout_header('Transport');

if ($melding !== null) {
    notice(e($melding), $type);
}

panel_open('Transport');

if ($vervoer < 1) {
    echo '<p>Je hebt geen vervoermiddel. Koop er een in de '
       . '<a href="' . e(url('shop.php')) . '">winkel</a> voordat je kunt reizen.</p>';
} elseif ((int) $user['rijbewijs'] !== 1) {
    echo '<p>Je hebt geen rijbewijs. Haal er eerst een bij het '
       . '<a href="' . e(url('rijbewijs.php')) . '">examencentrum</a>.</p>';
} elseif ($wachttijd > 0) {
    echo '<p>Je bent net gereisd. Je kunt over <strong data-tot="' . (time() + $wachttijd) . '">'
       . e(duration($wachttijd)) . '</strong> weer weg.</p>';
} else {
    toon_bestemmingen($user);
}

panel_close();
layout_footer();

// ==========================================================================

/** @throws SpelFout */
function reizen(array $user, string $naar): string
{
    $prijzen = reisprijzen();

    if (!isset($prijzen[$naar]) || !is_city($naar)) {
        throw new SpelFout('Die stad bestaat niet.');
    }
    if ($naar === $user['stad']) {
        throw new SpelFout('Je bent al in ' . $naar . '.');
    }

    return db_transaction(static function () use ($user, $naar, $prijzen): string {
        $speler = lock_user((int) $user['id']);

        if ((int) $speler['trans'] < 1) {
            throw new SpelFout('Je hebt geen vervoermiddel.');
        }
        if ((int) $speler['rijbewijs'] !== 1) {
            throw new SpelFout('Je hebt geen rijbewijs.');
        }
        if (cooldown_left((int) q_val('SELECT UNIX_TIMESTAMP(`transport`) FROM `users` WHERE `id` = ?',
                [$speler['id']], 0)) > 0) {
            throw new SpelFout('Je moet nog even wachten voor je weer kunt reizen.');
        }

        $prijs = $prijzen[$naar];

        if (!afboeken((int) $speler['id'], $prijs, 'zak')) {
            throw new SpelFout('De reis kost ' . money($prijs) . ' en zoveel heb je niet op zak.');
        }

        // Hoe beter je vervoermiddel, hoe korter je moet wachten.
        $wachttijd = (int) item_effect_trans((int) $speler['trans']);

        q('UPDATE `users` SET `stad` = ?, `transport` = DATE_ADD(NOW(), INTERVAL ? SECOND) WHERE `id` = ?',
            [$naar, $wachttijd, $speler['id']]);

        return 'Je bent aangekomen in ' . $naar . '. De reis kostte ' . money($prijs) . '.';
    });
}

/** Wachttijd in seconden die bij dit vervoermiddel hoort. */
function item_effect_trans(int $nr): int
{
    $effect = q_val("SELECT `effect` FROM `items` WHERE `type` = 'trans' AND `nr` = ?", [$nr]);
    return $effect === null ? 3600 : max(60, (int) round((float) $effect));
}

function toon_bestemmingen(array $user): void
{
    $vervoernaam = (string) q_val(
        "SELECT `naam` FROM `items` WHERE `type` = 'trans' AND `nr` = ?",
        [(int) $user['trans']],
        'onbekend'
    );
    $wacht = item_effect_trans((int) $user['trans']);

    echo '<p>Je reist met je <strong>' . e($vervoernaam) . '</strong>. Na aankomst moet je '
       . e(duration($wacht)) . ' wachten voor je weer verder kunt.</p>';
    echo '<p>Je bent nu in <strong>' . e((string) $user['stad']) . '</strong>. '
       . 'Je hebt ' . money((int) $user['zak']) . ' op zak.</p>';

    echo '<div class="tabelwikkel"><table class="lijst">';
    echo '<thead><tr><th>Stad</th><th class="getal">Prijs</th><th></th></tr></thead><tbody>';

    foreach (reisprijzen() as $stad => $prijs) {
        if ($stad === $user['stad']) {
            continue;
        }

        $tekort = (int) $user['zak'] < $prijs;

        echo '<tr>';
        echo '<td>' . e($stad) . '</td>';
        echo '<td class="getal">' . money($prijs) . '</td>';
        echo '<td><form method="post" style="margin:0">' . csrf_field()
           . '<input type="hidden" name="stad" value="' . e($stad) . '">'
           . '<button type="submit"' . ($tekort ? ' disabled' : '') . '>Reis</button></form></td>';
        echo '</tr>';
    }

    echo '</tbody></table></div>';
}
