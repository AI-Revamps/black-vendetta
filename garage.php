<?php
/**
 * Garage: je wagens verkopen, repareren, verschepen, crushen of veiligstellen.
 *
 * Wat hier gerepareerd is ten opzichte van de oude versie:
 *
 *  - Alle handelingen liepen via GET-links (garage.php?x=5 verkocht een wagen,
 *    ?crush=5 vernietigde er een). Een afbeelding met zo'n URL in een bericht
 *    liet de ontvanger zijn eigen garage leegruimen. Nu POST met CSRF-token.
 *  - Bij het verschepen werd de bestemming ongefilterd uit het formulier in de
 *    query gezet, zonder te controleren of het een bestaande stad was.
 *  - "Alles crushen" sloeg de familielimiet volledig over: de losse knop
 *    controleerde of er een crusher was ingehuurd en of het maximum bereikt was,
 *    de verzamelknop niet.
 *  - Repareren rekende `nieuwprijs - huidige waarde`. Was de huidige waarde
 *    hoger, dan werd de prijs negatief en leverde repareren geld op.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

const TRANSPORT_PRIJS = 1000;
const SAFEHOUSE_PRIJS = 10000;
const CRUSH_KOGELS    = 15;

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
        $melding = verwerk($user, post('actie'));
        $type    = 'ok';
        $user    = current_user(true);
    } catch (SpelFout $e) {
        $melding = $e->getMessage();
        $type    = 'fout';
    }
}

$wagens = q_all(
    'SELECT * FROM `garage` WHERE `login` = ? ORDER BY `stad`, `waarde` DESC',
    [$user['login']]
);

$totaal   = array_sum(array_map(static fn (array $w): int => (int) $w['waarde'], $wagens));
$hierNu   = array_filter($wagens, static fn (array $w): bool => $w['stad'] === $user['stad']);
$familie  = $user['famillie'] !== ''
    ? q_row('SELECT * FROM `famillie` WHERE `name` = ?', [$user['famillie']])
    : null;

layout_header('Garage');

if ($melding !== null) {
    notice(e($melding), $type);
}

panel_open('Garage');

echo '<p>Je hebt <strong>' . count($wagens) . '</strong> ' . (count($wagens) === 1 ? 'wagen' : 'wagens')
   . ' met een totale waarde van <strong>' . money($totaal) . '</strong>. '
   . 'Je bevindt je in ' . e((string) $user['stad']) . '.</p>';

if ($wagens === []) {
    echo '<p>Je garage is leeg. Steel een auto of koop er een op de zwarte markt.</p>';
} else {
    toon_wagens($wagens, $user, $familie);

    if ($hierNu !== []) {
        toon_verzamelknoppen($hierNu, $familie);
    }
}

panel_close();
layout_footer();

// ==========================================================================

/** @throws SpelFout */
function verwerk(array $user, string $actie): string
{
    return match ($actie) {
        'verkoop'    => verkopen($user, int_input('id')),
        'repareer'   => repareren($user, int_input('id')),
        'verscheep'  => verschepen($user, int_input('id'), post('stad')),
        'safehouse'  => veiligstellen($user, int_input('id')),
        'crush'      => crushen($user, int_input('id')),
        'verkoopall' => alles_verkopen($user),
        'crushall'   => alles_crushen($user),
        default      => throw new SpelFout('Onbekende handeling.'),
    };
}

/**
 * Haal een wagen op die van deze speler is, vergrendeld.
 *
 * @throws SpelFout
 */
function mijn_wagen(array $user, int $id, bool $moetHier = true): array
{
    $wagen = q_row('SELECT * FROM `garage` WHERE `id` = ? AND `login` = ? FOR UPDATE',
        [$id, $user['login']]);

    if ($wagen === null) {
        throw new SpelFout('Die wagen staat niet in jouw garage.');
    }
    if ($moetHier && $wagen['stad'] !== $user['stad']) {
        throw new SpelFout('Die wagen staat in ' . $wagen['stad'] . ', niet in ' . $user['stad'] . '.');
    }
    return $wagen;
}

/** @throws SpelFout */
function verkopen(array $user, int $id): string
{
    return db_transaction(static function () use ($user, $id): string {
        $wagen = mijn_wagen($user, $id);

        if ((int) $wagen['safe'] === 1) {
            throw new SpelFout('Deze wagen staat in een safehouse. Haal hem daar eerst uit.');
        }

        bijschrijven((int) $user['id'], (int) $wagen['waarde'], 'zak');
        q('DELETE FROM `garage` WHERE `id` = ?', [$id]);

        return 'Je ' . $wagen['naam'] . ' is verkocht voor ' . money((int) $wagen['waarde']) . '.';
    });
}

/** @throws SpelFout */
function repareren(array $user, int $id): string
{
    return db_transaction(static function () use ($user, $id): string {
        $wagen = mijn_wagen($user, $id);

        if ((int) $wagen['damage'] === 0) {
            throw new SpelFout('Deze wagen is niet beschadigd.');
        }

        $nieuwwaarde = (int) q_val('SELECT `waarde` FROM `cars` WHERE `naam` = ?', [$wagen['naam']], 0);

        // Kan niet negatief worden: in de oude versie leverde repareren geld op
        // zodra de huidige waarde hoger was dan de nieuwprijs.
        $prijs = max(0, $nieuwwaarde - (int) $wagen['waarde']);

        if ($prijs > 0 && !afboeken((int) $user['id'], $prijs, 'zak')) {
            throw new SpelFout('Repareren kost ' . money($prijs) . ' en zoveel heb je niet op zak.');
        }

        q('UPDATE `garage` SET `waarde` = ?, `damage` = 0 WHERE `id` = ?', [$nieuwwaarde, $id]);

        return 'Je ' . $wagen['naam'] . ' is gerepareerd voor ' . money($prijs) . '.';
    });
}

/** @throws SpelFout */
function verschepen(array $user, int $id, string $naar): string
{
    // Bestemming moet een bestaande stad zijn; in de oude versie ging elke
    // ingevoerde tekst rechtstreeks de database in.
    if (!is_city($naar)) {
        throw new SpelFout('Dat is geen bestaande stad.');
    }

    return db_transaction(static function () use ($user, $id, $naar): string {
        $wagen = mijn_wagen($user, $id);

        if ($wagen['stad'] === $naar) {
            throw new SpelFout('Die wagen staat al in ' . $naar . '.');
        }
        if (!afboeken((int) $user['id'], TRANSPORT_PRIJS, 'zak')) {
            throw new SpelFout('Verschepen kost ' . money(TRANSPORT_PRIJS) . '.');
        }

        q('UPDATE `garage` SET `stad` = ? WHERE `id` = ?', [$naar, $id]);

        return 'Je ' . $wagen['naam'] . ' is verscheept naar ' . $naar
             . ' voor ' . money(TRANSPORT_PRIJS) . '.';
    });
}

/** @throws SpelFout */
function veiligstellen(array $user, int $id): string
{
    return db_transaction(static function () use ($user, $id): string {
        $wagen = mijn_wagen($user, $id);

        if ((int) $wagen['safe'] === 1) {
            throw new SpelFout('Deze wagen staat al in een safehouse.');
        }
        if (!afboeken((int) $user['id'], SAFEHOUSE_PRIJS, 'zak')) {
            throw new SpelFout('Een safehouse kost ' . money(SAFEHOUSE_PRIJS) . '.');
        }

        q('UPDATE `garage` SET `safe` = 1 WHERE `id` = ?', [$id]);

        return 'Je ' . $wagen['naam'] . ' staat nu veilig, voor ' . money(SAFEHOUSE_PRIJS) . '.';
    });
}

/** @throws SpelFout */
function crushen(array $user, int $id): string
{
    return db_transaction(static function () use ($user, $id): string {
        $wagen = mijn_wagen($user, $id);
        crusher_controle($user, 1);

        q('DELETE FROM `garage` WHERE `id` = ?', [$id]);
        bijschrijven((int) $user['id'], CRUSH_KOGELS, 'kogels');
        q('UPDATE `famillie` SET `aantal` = `aantal` - 1 WHERE `name` = ?', [$user['famillie']]);

        return 'Je ' . $wagen['naam'] . ' is gecrusht. Je kreeg ' . CRUSH_KOGELS . ' kogels.';
    });
}

/**
 * Mag er gecrusht worden, en is er nog ruimte voor $aantal wagens?
 *
 * @throws SpelFout
 */
function crusher_controle(array $user, int $aantal): array
{
    if ($user['famillie'] === '') {
        throw new SpelFout('Je hebt geen familie. Crushen kan alleen met een familiecrusher.');
    }

    $familie = q_row('SELECT * FROM `famillie` WHERE `name` = ? FOR UPDATE', [$user['famillie']]);

    if ($familie === null) {
        throw new SpelFout('Je familie bestaat niet meer.');
    }
    if ((int) $familie['crusher'] === 0) {
        throw new SpelFout('Je familie heeft vandaag geen crusher ingehuurd.');
    }
    if ((int) $familie['aantal'] < $aantal) {
        throw new SpelFout('Je familie heeft nog ruimte voor ' . (int) $familie['aantal']
            . ' wagens vandaag; je probeert er ' . $aantal . ' te crushen.');
    }

    return $familie;
}

/** @throws SpelFout */
function alles_verkopen(array $user): string
{
    return db_transaction(static function () use ($user): string {
        $wagens = q_all(
            'SELECT * FROM `garage` WHERE `login` = ? AND `stad` = ? AND `safe` = 0 FOR UPDATE',
            [$user['login'], $user['stad']]
        );

        if ($wagens === []) {
            throw new SpelFout('Je hebt hier geen wagens die verkocht kunnen worden.');
        }

        $opbrengst = array_sum(array_map(static fn (array $w): int => (int) $w['waarde'], $wagens));

        bijschrijven((int) $user['id'], $opbrengst, 'zak');
        q("DELETE FROM `garage` WHERE `login` = ? AND `stad` = ? AND `safe` = 0",
            [$user['login'], $user['stad']]);

        return 'Je hebt ' . count($wagens) . ' ' . (count($wagens) === 1 ? 'wagen' : 'wagens')
             . ' verkocht voor ' . money($opbrengst) . '.';
    });
}

/** @throws SpelFout */
function alles_crushen(array $user): string
{
    return db_transaction(static function () use ($user): string {
        $wagens = q_all(
            'SELECT * FROM `garage` WHERE `login` = ? AND `stad` = ? AND `safe` = 0 FOR UPDATE',
            [$user['login'], $user['stad']]
        );

        if ($wagens === []) {
            throw new SpelFout('Je hebt hier geen wagens die gecrusht kunnen worden.');
        }

        // Deze controle ontbrak volledig in de oude versie, waardoor je met
        // "alles crushen" de familielimiet kon omzeilen.
        crusher_controle($user, count($wagens));

        $kogels = count($wagens) * CRUSH_KOGELS;

        bijschrijven((int) $user['id'], $kogels, 'kogels');
        q('UPDATE `famillie` SET `aantal` = `aantal` - ? WHERE `name` = ?',
            [count($wagens), $user['famillie']]);
        q("DELETE FROM `garage` WHERE `login` = ? AND `stad` = ? AND `safe` = 0",
            [$user['login'], $user['stad']]);

        return 'Je hebt ' . count($wagens) . ' wagens gecrusht voor ' . num($kogels) . ' kogels.';
    });
}

// ==========================================================================

function toon_wagens(array $wagens, array $user, ?array $familie): void
{
    echo '<div class="tabelwikkel"><table class="lijst">';
    echo '<thead><tr><th>Wagen</th><th class="getal">Waarde</th><th class="getal">Schade</th>'
       . '<th>Stad</th><th>Acties</th></tr></thead><tbody>';

    foreach ($wagens as $wagen) {
        $hier = $wagen['stad'] === $user['stad'];
        $veilig = (int) $wagen['safe'] === 1;

        echo '<tr>';
        echo '<td>' . e((string) $wagen['naam']) . ($veilig ? ' <small>(safehouse)</small>' : '') . '</td>';
        echo '<td class="getal">' . money((int) $wagen['waarde']) . '</td>';
        echo '<td class="getal">' . (int) $wagen['damage'] . '%</td>';
        echo '<td>' . e((string) $wagen['stad']) . '</td>';
        echo '<td>';

        if (!$hier) {
            echo '<small>Je bent hier niet</small>';
        } else {
            if (!$veilig) {
                actieknop('verkoop', (int) $wagen['id'], 'Verkoop');
            }
            if ((int) $wagen['damage'] > 0) {
                actieknop('repareer', (int) $wagen['id'], 'Repareer');
            }
            if (!$veilig) {
                actieknop('safehouse', (int) $wagen['id'], 'Safehouse');
                if ($familie !== null && (int) $familie['crusher'] > 0 && (int) $familie['aantal'] > 0) {
                    actieknop('crush', (int) $wagen['id'], 'Crush');
                }
            }
            verscheepformulier((int) $wagen['id'], (string) $wagen['stad']);
        }

        echo '</td></tr>';
    }

    echo '</tbody></table></div>';
}

function actieknop(string $actie, int $id, string $label): void
{
    echo '<form method="post" style="display:inline">' . csrf_field()
       . '<input type="hidden" name="actie" value="' . e($actie) . '">'
       . '<input type="hidden" name="id" value="' . $id . '">'
       . '<button type="submit">' . e($label) . '</button></form> ';
}

function verscheepformulier(int $id, string $huidig): void
{
    echo '<form method="post" style="display:inline">' . csrf_field()
       . '<input type="hidden" name="actie" value="verscheep">'
       . '<input type="hidden" name="id" value="' . $id . '">'
       . '<select name="stad" aria-label="Bestemming">';
    foreach (cities() as $stad) {
        if ($stad !== $huidig) {
            echo '<option value="' . e($stad) . '">' . e($stad) . '</option>';
        }
    }
    echo '</select><button type="submit">Verscheep (' . money(TRANSPORT_PRIJS) . ')</button></form>';
}

function toon_verzamelknoppen(array $hierNu, ?array $familie): void
{
    $verkoopbaar = array_filter($hierNu, static fn (array $w): bool => (int) $w['safe'] === 0);

    if ($verkoopbaar === []) {
        return;
    }

    $waarde = array_sum(array_map(static fn (array $w): int => (int) $w['waarde'], $verkoopbaar));

    echo '<h3>Alles in deze stad</h3>';
    echo '<p>' . count($verkoopbaar) . ' ' . (count($verkoopbaar) === 1 ? 'wagen' : 'wagens')
       . ', samen ' . money($waarde) . '.</p>';

    echo '<form method="post" style="display:inline">' . csrf_field()
       . '<input type="hidden" name="actie" value="verkoopall">'
       . '<button type="submit">Verkoop alles</button></form> ';

    if ($familie !== null && (int) $familie['crusher'] > 0) {
        echo '<form method="post" style="display:inline">' . csrf_field()
           . '<input type="hidden" name="actie" value="crushall">'
           . '<button type="submit">Crush alles</button></form>';
        echo '<p class="uitleg">Je familie kan vandaag nog ' . (int) $familie['aantal']
           . ' wagens crushen.</p>';
    }
}
