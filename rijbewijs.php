<?php
/**
 * Rijschool: haal je rijbewijs, zodat je kunt reizen tussen steden.
 *
 * LET OP - dit onderdeel heeft in de oude versie nooit gewerkt.
 *
 * Het oude rijbewijs.php was letterlijk uit een ander spel geplakt; de
 * bestandskop noemde een andere auteur en een andere site. Het gebruikte
 * vijf kolommen die in sql.sql helemaal niet bestonden: rijbewijs, rijvord,
 * rijbewijstijd, lessen en geslaagd. Bovendien las het het saldo uit `cash`,
 * terwijl de geldkolom in dit spel `zak` heet. Elke query faalde dus, en de
 * controle op het saldo sloeg altijd aan omdat de waarde leeg was.
 *
 * Daar kwam bij dat de promotie naar een rijbewijs in het oude config.php op
 * regel 27 stond, terwijl de spelergegevens pas op regel 33 werden opgehaald.
 * De voorwaarde keek dus altijd naar een lege waarde en is nooit uitgekomen.
 *
 * Met beide fouten samen was het rijbewijs onhaalbaar. Dat viel niet op omdat
 * transport.php de controle erop ook niet uitvoerde: daar stond
 * `if ($data->rijbewijs = 0)` met een toewijzing in plaats van een
 * vergelijking. Nu die controle wel werkt, moet het rijbewijs te halen zijn.
 *
 * Deze versie is opnieuw opgebouwd op de kolommen van dit spel.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

const LES_PRIJS      = 5_000;
const LES_MAX        = 50;      // hoeveel lessen je tegelijk kunt kopen
const LES_WACHTTIJD  = 300;     // vijf minuten tussen twee lessen
const RIJVORD_KLAAR  = 100.0;   // bij dit percentage haal je je rijbewijs

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
        [$melding, $type] = verwerk($user, post('actie'));
        $user = current_user(true);
    } catch (SpelFout $e) {
        $melding = $e->getMessage();
        $type    = 'fout';
    }
}

$wacht = cooldown_left(rijbewijstijd($user));

layout_header('Rijschool');

if ($melding !== null) {
    notice(e($melding), $type);
}

panel_open('Rijschool');

if ((int) $user['rijbewijs'] === 1) {
    echo '<p>Je hebt je rijbewijs gehaald. Je kunt reizen via '
       . '<a href="' . e(url('transport.php')) . '">Transport</a>.</p>';
    panel_close();
    layout_footer();
    exit;
}

echo '<p>Zonder rijbewijs kun je niet tussen steden reizen. Koop lessen en ga rijden '
   . 'tot je vordering op ' . num(RIJVORD_KLAAR) . '% staat.</p>';

echo '<div class="tabelwikkel"><table class="lijst">';
echo '<tr><th scope="row">Vordering</th><td>' . num((float) $user['rijvord'], 1) . '%</td></tr>';
echo '<tr><th scope="row">Lessen op zak</th><td>' . num((int) $user['lessen']) . '</td></tr>';
echo '<tr><th scope="row">Geld op zak</th><td>' . money((int) $user['zak']) . '</td></tr>';
echo '</table></div>';

$vord = (float) $user['rijvord'];
echo '<div class="balk" role="img" aria-label="Vordering ' . num($vord, 1) . ' procent">'
   . '<span style="width:' . min(100, max(0, $vord)) . '%"></span></div>';

panel_close();

// --- Lessen kopen ---
panel_open('Lessen kopen');
echo '<p>Een rijles kost ' . money(LES_PRIJS) . '. Je kunt er hoogstens ' . LES_MAX
   . ' tegelijk kopen.</p>';
echo '<form method="post">' . csrf_field();
echo '<input type="hidden" name="actie" value="lessen">';
echo '<div class="veldenraster">';
echo '<label for="aantal">Aantal lessen</label>';
echo '<input id="aantal" name="aantal" type="number" min="1" max="' . LES_MAX . '" step="1" value="1" required>';
echo '<span></span><button type="submit">Kopen</button>';
echo '</div></form>';
panel_close();

// --- Rijden ---
panel_open('Rijles nemen');

if ((int) $user['lessen'] < 1) {
    echo '<p>Je hebt geen lessen meer. Koop er eerst een paar.</p>';
} elseif ($wacht > 0) {
    echo '<p>Je instructeur is bezig met een andere leerling. Nog '
       . '<strong data-tot="' . (time() + $wacht) . '">' . e(duration($wacht)) . '</strong>.</p>';
} else {
    echo '<p>Elke les kost je één van je ' . num((int) $user['lessen'])
       . ' lessen. Gaat het goed, dan stijgt je vordering.</p>';
    echo '<form method="post">' . csrf_field()
       . '<input type="hidden" name="actie" value="rijden">'
       . '<button type="submit">Rijden</button></form>';
}

panel_close();
layout_footer();

// ==========================================================================

/** Unix-tijd waarop de volgende les mag beginnen. */
function rijbewijstijd(array $user): int
{
    return (int) q_val('SELECT UNIX_TIMESTAMP(`rijbewijstijd`) FROM `users` WHERE `id` = ?',
        [$user['id']], 0);
}

/**
 * @return array{0:string, 1:string}
 * @throws SpelFout
 */
function verwerk(array $user, string $actie): array
{
    return match ($actie) {
        'lessen' => [lessen_kopen($user, int_input('aantal', 0)), 'ok'],
        'rijden' => rijden($user),
        default  => throw new SpelFout('Onbekende handeling.'),
    };
}

/** @throws SpelFout */
function lessen_kopen(array $user, int $aantal): string
{
    if ($aantal < 1 || $aantal > LES_MAX) {
        throw new SpelFout('Kies tussen 1 en ' . LES_MAX . ' lessen.');
    }

    return db_transaction(static function () use ($user, $aantal): string {
        $speler = lock_user((int) $user['id']);

        if ((int) $speler['rijbewijs'] === 1) {
            throw new SpelFout('Je hebt je rijbewijs al.');
        }

        $kosten = $aantal * LES_PRIJS;

        if (!afboeken((int) $speler['id'], $kosten, 'zak')) {
            throw new SpelFout('Dit kost ' . money($kosten) . ' en zoveel heb je niet op zak.');
        }

        q('UPDATE `users` SET `lessen` = `lessen` + ? WHERE `id` = ?', [$aantal, $speler['id']]);

        return 'Je hebt ' . $aantal . ' ' . ($aantal === 1 ? 'les' : 'lessen')
             . ' gekocht voor ' . money($kosten) . '.';
    });
}

/**
 * @return array{0:string, 1:string}
 * @throws SpelFout
 */
function rijden(array $user): array
{
    return db_transaction(static function () use ($user): array {
        $speler = lock_user((int) $user['id']);

        if ((int) $speler['rijbewijs'] === 1) {
            throw new SpelFout('Je hebt je rijbewijs al.');
        }
        if ((int) $speler['lessen'] < 1) {
            throw new SpelFout('Je hebt geen lessen meer.');
        }
        if (cooldown_left(rijbewijstijd($speler)) > 0) {
            throw new SpelFout('Je instructeur is nog bezig met een andere leerling.');
        }

        // Les verbruiken en de afkoeltijd starten, of het nu goed of fout gaat.
        q('UPDATE `users` SET `lessen` = `lessen` - 1,
                              `rijbewijstijd` = DATE_ADD(NOW(), INTERVAL ? SECOND)
            WHERE `id` = ?',
            [LES_WACHTTIJD, $speler['id']]);

        // Ongeveer twee op de drie lessen leveren vordering op.
        if (random_int(1, 3) === 1) {
            $pech = [
                'Je reed de stoeprand op. Daar leer je niets van.',
                'Je liet de motor drie keer afslaan bij het wegrijden.',
                'Je vergat te kijken bij het invoegen. De instructeur greep in.',
                'Je parkeerde de auto tegen een paaltje.',
            ];

            return [$pech[array_rand($pech)], 'fout'];
        }

        // Vordering in hele procenten.
        $winst = (float) random_int(10, 25);
        $nieuw = min(RIJVORD_KLAAR, (float) $speler['rijvord'] + $winst);

        q('UPDATE `users` SET `rijvord` = ? WHERE `id` = ?', [$nieuw, $speler['id']]);

        // De promotie hoort hier, niet in een globale controle die draaide
        // voordat de spelergegevens waren opgehaald.
        if ($nieuw >= RIJVORD_KLAAR) {
            q('UPDATE `users` SET `rijbewijs` = 1, `rijvord` = 0 WHERE `id` = ?', [$speler['id']]);

            log_action((string) $speler['login'], 'rijbewijs', 'Rijbewijs gehaald');

            return ['Geslaagd! Je hebt je rijbewijs. Je kunt nu reizen tussen steden.', 'ok'];
        }

        return ['Die ging goed. Je vordering steeg met ' . num($winst, 1)
              . '% naar ' . num($nieuw, 1) . '%.', 'ok'];
    });
}
