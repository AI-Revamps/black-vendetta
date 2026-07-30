<?php
/**
 * Winkel: wapens, bescherming, vervoer, lijfwachten, een huis en een safehouse.
 *
 * Wat hier gerepareerd is ten opzichte van de oude versie:
 *
 *  - Verkopen controleerde eerst of je het item had en boekte daarna het geld
 *    bij, in twee losse queries. Twee gelijktijdige verzoeken zagen allebei dat
 *    je het wapen nog had en betaalden allebei uit. Nu zit de voorwaarde in de
 *    UPDATE zelf, binnen een transactie.
 *  - De stad waarin je een huis koopt kwam uit een globale variabele die in
 *    config.php gezet werd en rechtstreeks als kolomnaam in de query ging.
 *  - Geen CSRF-bescherming op de aankopen.
 *  - Kale woorden als 'huis' en 'guard1' in vergelijkingen, wat in PHP 8 een
 *    fatale fout geeft.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

const HUIS_KOOPPRIJS   = 850_000;
const HUIS_VERKOOPPRIJS = 750_000;

/** Lijfwachten: niveau => prijs. */
function lijfwachten(): array
{
    return [1 => 25_000, 2 => 50_000, 3 => 100_000, 4 => 250_000, 5 => 500_000];
}

/** Safehouse: minuten => prijs. */
function safehuizen(): array
{
    return [
        60   => 500_000,
        120  => 1_000_000,
        360  => 3_000_000,
        720  => 6_000_000,
        1440 => 12_000_000,
        2880 => 24_000_000,
    ];
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
        $melding = verwerk($user, post('actie'));
        $type    = 'ok';
        $user    = current_user(true);
    } catch (SpelFout $e) {
        $melding = $e->getMessage();
        $type    = 'fout';
    }
}

layout_header('Winkel');

if ($melding !== null) {
    notice(e($melding), $type);
}

echo '<p class="uitleg">Je hebt ' . money((int) $user['zak']) . ' op zak. '
   . 'Je bent in ' . e((string) $user['stad']) . '.</p>';

toon_items($user, 'att',   'Wapens', 'wapon');
toon_items($user, 'def',   'Bescherming', 'defence');
toon_items($user, 'trans', 'Vervoer', 'trans');
toon_lijfwachten($user);
toon_huis($user);
toon_safehouse($user);

layout_footer();

// ==========================================================================

/** @throws SpelFout */
function verwerk(array $user, string $actie): string
{
    return match ($actie) {
        'koop_item'   => item_kopen($user, post('soort'), int_input('nr')),
        'verkoop_item' => item_verkopen($user, post('soort')),
        'koop_guard'  => guard_kopen($user, int_input('niveau')),
        'koop_huis'   => huis_kopen($user),
        'verkoop_huis' => huis_verkopen($user),
        'safehouse'   => onderduiken($user, int_input('minuten')),
        default       => throw new SpelFout('Onbekende handeling.'),
    };
}

/** Welke kolom in `users` hoort bij welk itemsoort? */
function item_kolom(string $soort): string
{
    return match ($soort) {
        'att'   => 'wapon',
        'def'   => 'defence',
        'trans' => 'trans',
        default => throw new SpelFout('Onbekend soort item.'),
    };
}

/** @throws SpelFout */
function item_kopen(array $user, string $soort, int $nr): string
{
    $kolom = item_kolom($soort);

    return db_transaction(static function () use ($user, $soort, $nr, $kolom): string {
        $item = q_row('SELECT * FROM `items` WHERE `type` = ? AND `nr` = ?', [$soort, $nr]);

        if ($item === null) {
            throw new SpelFout('Dat item bestaat niet.');
        }

        $speler = lock_user((int) $user['id']);

        if ((int) $speler[$kolom] === $nr) {
            throw new SpelFout('Je hebt al een ' . $item['naam'] . '.');
        }

        $prijs = (int) $item['aprijs'];

        if (!afboeken((int) $speler['id'], $prijs, 'zak')) {
            throw new SpelFout('Dit kost ' . money($prijs) . ' en zoveel heb je niet op zak.');
        }

        // Had je al iets van dit soort, dan wordt dat ingeruild zonder vergoeding.
        $oud = (int) $speler[$kolom];
        $ruil = '';
        if ($oud > 0) {
            $oudeNaam = q_val('SELECT `naam` FROM `items` WHERE `type` = ? AND `nr` = ?', [$soort, $oud]);
            if ($oudeNaam !== null) {
                $ruil = ' Je oude ' . $oudeNaam . ' is weggedaan.';
            }
        }

        q("UPDATE `users` SET `{$kolom}` = ? WHERE `id` = ?", [$nr, $speler['id']]);

        return 'Je hebt een ' . $item['naam'] . ' gekocht voor ' . money($prijs) . '.' . $ruil;
    });
}

/** @throws SpelFout */
function item_verkopen(array $user, string $soort): string
{
    $kolom = item_kolom($soort);

    return db_transaction(static function () use ($user, $soort, $kolom): string {
        $speler = lock_user((int) $user['id']);
        $nr     = (int) $speler[$kolom];

        if ($nr < 1) {
            throw new SpelFout('Je hebt niets om te verkopen.');
        }

        $item = q_row('SELECT * FROM `items` WHERE `type` = ? AND `nr` = ?', [$soort, $nr]);

        if ($item === null) {
            throw new SpelFout('Dat item bestaat niet meer in de winkel.');
        }

        // De voorwaarde staat in de UPDATE: lukt dat niet, dan was iemand net
        // eerder. Zo kan hetzelfde item niet twee keer verkocht worden.
        $gelukt = q_count("UPDATE `users` SET `{$kolom}` = 0 WHERE `id` = ? AND `{$kolom}` = ?",
            [$speler['id'], $nr]) === 1;

        if (!$gelukt) {
            throw new SpelFout('Je hebt dit item niet meer.');
        }

        bijschrijven((int) $speler['id'], (int) $item['vprijs'], 'zak');

        return 'Je hebt je ' . $item['naam'] . ' verkocht voor ' . money((int) $item['vprijs']) . '.';
    });
}

/** @throws SpelFout */
function guard_kopen(array $user, int $niveau): string
{
    $prijzen = lijfwachten();

    if (!isset($prijzen[$niveau])) {
        throw new SpelFout('Dat aantal lijfwachten bestaat niet.');
    }

    return db_transaction(static function () use ($user, $niveau, $prijzen): string {
        $speler = lock_user((int) $user['id']);
        $nu     = (int) $speler['guard'];

        if ($nu >= $niveau) {
            throw new SpelFout('Je hebt al ' . $nu . ' lijfwachten. Kies een hoger aantal.');
        }

        // Je betaalt alleen het verschil met wat je al hebt.
        $prijs = $prijzen[$niveau] - ($nu > 0 ? $prijzen[$nu] : 0);

        if (!afboeken((int) $speler['id'], $prijs, 'zak')) {
            throw new SpelFout('Uitbreiden kost ' . money($prijs) . ' en zoveel heb je niet op zak.');
        }

        q('UPDATE `users` SET `guard` = ? WHERE `id` = ?', [$niveau, $speler['id']]);

        return 'Je hebt nu ' . $niveau . ' lijfwachten. Dat kostte ' . money($prijs) . '.';
    });
}

/** @throws SpelFout */
function huis_kopen(array $user): string
{
    return db_transaction(static function () use ($user): string {
        $speler = lock_user((int) $user['id']);
        $stad   = (string) $speler['stad'];

        // Kolomnaam alleen uit de vaste stedenlijst, nooit uit invoer.
        if (!is_city($stad)) {
            throw new SpelFout('Je verblijft in een onbekende stad.');
        }
        if ((int) $speler[$stad] > 0) {
            throw new SpelFout('Je hebt al een huis in ' . $stad . '.');
        }
        if (!afboeken((int) $speler['id'], HUIS_KOOPPRIJS, 'zak')) {
            throw new SpelFout('Een huis kost ' . money(HUIS_KOOPPRIJS) . '.');
        }

        q("UPDATE `users` SET `{$stad}` = 1 WHERE `id` = ?", [$speler['id']]);

        return 'Je hebt een huis gekocht in ' . $stad . ' voor ' . money(HUIS_KOOPPRIJS)
             . '. Dat geeft je thuisvoordeel bij gevechten in deze stad.';
    });
}

/** @throws SpelFout */
function huis_verkopen(array $user): string
{
    return db_transaction(static function () use ($user): string {
        $speler = lock_user((int) $user['id']);
        $stad   = (string) $speler['stad'];

        if (!is_city($stad)) {
            throw new SpelFout('Je verblijft in een onbekende stad.');
        }

        $gelukt = q_count("UPDATE `users` SET `{$stad}` = 0 WHERE `id` = ? AND `{$stad}` > 0",
            [$speler['id']]) === 1;

        if (!$gelukt) {
            throw new SpelFout('Je hebt geen huis in ' . $stad . '.');
        }

        bijschrijven((int) $speler['id'], HUIS_VERKOOPPRIJS, 'zak');

        return 'Je huis in ' . $stad . ' is verkocht voor ' . money(HUIS_VERKOOPPRIJS) . '.';
    });
}

/** @throws SpelFout */
function onderduiken(array $user, int $minuten): string
{
    $prijzen = safehuizen();

    if (!isset($prijzen[$minuten])) {
        throw new SpelFout('Die duur bestaat niet.');
    }

    return db_transaction(static function () use ($user, $minuten, $prijzen): string {
        $speler = lock_user((int) $user['id']);
        $safeTs = (int) q_val('SELECT UNIX_TIMESTAMP(`safe`) FROM `users` WHERE `id` = ?',
            [$speler['id']], 0);

        if ($safeTs > time()) {
            throw new SpelFout('Je zit al ondergedoken.');
        }

        $prijs = $prijzen[$minuten];

        if (!afboeken((int) $speler['id'], $prijs, 'zak')) {
            throw new SpelFout('Dit kost ' . money($prijs) . ' en zoveel heb je niet op zak.');
        }

        q('UPDATE `users` SET `safe` = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE `id` = ?',
            [$minuten, $speler['id']]);

        return 'Je bent ondergedoken voor ' . ($minuten / 60) . ' uur. '
             . 'Zolang kan niemand je vermoorden, maar jij ook niemand.';
    });
}

// ==========================================================================
// Weergave
// ==========================================================================

function toon_items(array $user, string $soort, string $titel, string $kolom): void
{
    $items  = q_all('SELECT * FROM `items` WHERE `type` = ? ORDER BY `aprijs`', [$soort]);
    $huidig = (int) $user[$kolom];

    panel_open($titel);

    if ($items === []) {
        echo '<p>Er is hier niets te koop.</p>';
        panel_close();
        return;
    }

    echo '<div class="tabelwikkel"><table class="lijst">';
    echo '<thead><tr><th>Item</th><th class="getal">Koop</th><th class="getal">Verkoop</th>'
       . '<th class="getal">Effect</th><th></th></tr></thead><tbody>';

    foreach ($items as $item) {
        $isVanJou = (int) $item['nr'] === $huidig;

        echo '<tr>';
        echo '<td>' . e((string) $item['naam']) . ($isVanJou ? ' <small>(in bezit)</small>' : '') . '</td>';
        echo '<td class="getal">' . money((int) $item['aprijs']) . '</td>';
        echo '<td class="getal">' . money((int) $item['vprijs']) . '</td>';
        echo '<td class="getal">' . e(effect_tekst($soort, (float) $item['effect'])) . '</td>';
        echo '<td>';

        if ($isVanJou) {
            echo '<form method="post" style="margin:0">' . csrf_field()
               . '<input type="hidden" name="actie" value="verkoop_item">'
               . '<input type="hidden" name="soort" value="' . e($soort) . '">'
               . '<button type="submit">Verkoop</button></form>';
        } elseif ((int) $item['aprijs'] > 0) {
            $tekort = (int) $user['zak'] < (int) $item['aprijs'];
            echo '<form method="post" style="margin:0">' . csrf_field()
               . '<input type="hidden" name="actie" value="koop_item">'
               . '<input type="hidden" name="soort" value="' . e($soort) . '">'
               . '<input type="hidden" name="nr" value="' . (int) $item['nr'] . '">'
               . '<button type="submit"' . ($tekort ? ' disabled' : '') . '>Koop</button></form>';
        }

        echo '</td></tr>';
    }

    echo '</tbody></table></div>';
    panel_close();
}

function effect_tekst(string $soort, float $effect): string
{
    return match ($soort) {
        'trans' => duration((int) round($effect)) . ' reistijd',
        default => num($effect, 2),
    };
}

function toon_lijfwachten(array $user): void
{
    $nu = (int) $user['guard'];

    panel_open('Lijfwachten');
    echo '<p>Lijfwachten maken je moeilijker te vermoorden. Je hebt er nu <strong>'
       . $nu . '</strong>.</p>';

    echo '<div class="tabelwikkel"><table class="lijst">';
    echo '<thead><tr><th>Aantal</th><th class="getal">Prijs</th><th></th></tr></thead><tbody>';

    foreach (lijfwachten() as $niveau => $prijs) {
        $bij     = $prijs - ($nu > 0 ? lijfwachten()[$nu] : 0);
        $tekort  = (int) $user['zak'] < $bij;

        echo '<tr><td>' . $niveau . '</td><td class="getal">' . money($prijs) . '</td><td>';

        if ($niveau > $nu) {
            echo '<form method="post" style="margin:0">' . csrf_field()
               . '<input type="hidden" name="actie" value="koop_guard">'
               . '<input type="hidden" name="niveau" value="' . $niveau . '">'
               . '<button type="submit"' . ($tekort ? ' disabled' : '') . '>Bijkopen voor '
               . money($bij) . '</button></form>';
        } else {
            echo '<small>' . ($niveau === $nu ? 'in bezit' : '-') . '</small>';
        }

        echo '</td></tr>';
    }

    echo '</tbody></table></div>';
    panel_close();
}

function toon_huis(array $user): void
{
    $stad   = (string) $user['stad'];
    $heeft  = is_city($stad) && (int) $user[$stad] > 0;

    panel_open('Huis in ' . $stad);
    echo '<p>Een eigen huis geeft je thuisvoordeel bij gevechten in deze stad.</p>';

    if ($heeft) {
        echo '<p>Je hebt hier een huis.</p>';
        echo '<form method="post">' . csrf_field()
           . '<input type="hidden" name="actie" value="verkoop_huis">'
           . '<button type="submit">Verkoop voor ' . money(HUIS_VERKOOPPRIJS) . '</button></form>';
    } else {
        $tekort = (int) $user['zak'] < HUIS_KOOPPRIJS;
        echo '<form method="post">' . csrf_field()
           . '<input type="hidden" name="actie" value="koop_huis">'
           . '<button type="submit"' . ($tekort ? ' disabled' : '') . '>Koop voor '
           . money(HUIS_KOOPPRIJS) . '</button></form>';
    }

    // Overzicht van je andere huizen.
    $elders = array_filter(cities(), static fn (string $s): bool => $s !== $stad && (int) ($user[$s] ?? 0) > 0);
    if ($elders !== []) {
        echo '<p class="uitleg">Je hebt ook een huis in: ' . e(implode(', ', $elders)) . '.</p>';
    }

    panel_close();
}

function toon_safehouse(array $user): void
{
    $safeTs = (int) q_val('SELECT UNIX_TIMESTAMP(`safe`) FROM `users` WHERE `id` = ?', [$user['id']], 0);

    panel_open('Safehouse');

    if ($safeTs > time()) {
        echo '<p>Je zit ondergedoken. Nog <strong data-tot="' . $safeTs . '">'
           . e(duration($safeTs - time())) . '</strong>.</p>';
        panel_close();
        return;
    }

    echo '<p>Ondergedoken kan niemand je vermoorden — maar jij kunt ook niemand aanvallen.</p>';
    echo '<form method="post">' . csrf_field();
    echo '<input type="hidden" name="actie" value="safehouse">';
    echo '<div class="veldenraster">';
    echo '<label for="minuten">Duur</label><select id="minuten" name="minuten">';
    foreach (safehuizen() as $minuten => $prijs) {
        echo '<option value="' . $minuten . '">' . ($minuten / 60) . ' uur - ' . money($prijs) . '</option>';
    }
    echo '</select>';
    echo '<span></span><button type="submit">Onderduiken</button>';
    echo '</div></form>';

    panel_close();
}
