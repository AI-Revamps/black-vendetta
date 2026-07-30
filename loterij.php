<?php
/**
 * Loterij: koop loten, win wekelijks een prijs.
 *
 * LET OP - in de oude versie werd er nooit getrokken.
 *
 * Spelers konden loten kopen van € 10.000 per stuk, tot honderd stuks, en de
 * pagina toonde een lijst met negen prijzen. Maar nergens in de hele codebase
 * stond code die een winnaar aanwees of iets uitbetaalde. De tabel `cron` had
 * wel een regel 'loterij', maar geen enkele taak deed er iets mee. De loterij
 * was dus een put waar geld in verdween en nooit iets uit kwam.
 *
 * De trekking staat nu als taak in inc/cron.php en draait wekelijks.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

const LOT_PRIJS = 10_000;
const LOT_MAX   = 100;

$user = require_login();

if (is_dead()) {
    redirect('rip.php');
}

$melding = null;
$type    = 'info';

if (is_post()) {
    csrf_check();
    try {
        $melding = loten_kopen($user, int_input('nroflot', 0));
        $type    = 'ok';
        $user    = current_user(true);
    } catch (SpelFout $e) {
        $melding = $e->getMessage();
        $type    = 'fout';
    }
}

$totaal   = (int) q_val('SELECT COUNT(*) FROM `loterij`', [], 0);
$eigen    = (int) q_val('SELECT COUNT(*) FROM `loterij` WHERE `login` = ?', [$user['login']], 0);
$jackpot  = $totaal * LOT_PRIJS;
$volgende = q_val("SELECT UNIX_TIMESTAMP(`time`) + 604800 FROM `cron` WHERE `name` = 'loterij'");
$betaalbaar = min(LOT_MAX - $eigen, intdiv((int) $user['zak'], LOT_PRIJS));

layout_header('Loterij');

if ($melding !== null) {
    notice(e($melding), $type);
}

panel_open('Loterij');

echo '<div class="tabelwikkel"><table class="lijst">';
echo '<tr><th scope="row">Jackpot</th><td>' . money($jackpot) . '</td></tr>';
echo '<tr><th scope="row">Loten verkocht</th><td>' . num($totaal) . '</td></tr>';
echo '<tr><th scope="row">Jouw loten</th><td>' . num($eigen) . ' van ' . LOT_MAX . '</td></tr>';
echo '<tr><th scope="row">Volgende trekking</th><td>'
   . ($volgende !== null ? e(date('d-m-Y H:i', (int) $volgende)) : 'onbekend') . '</td></tr>';
echo '</table></div>';

echo '<h3>Prijzen</h3>';
echo '<div class="tabelwikkel"><table class="lijst">';
echo '<thead><tr><th>Prijs</th><th>Wat je wint</th></tr></thead><tbody>';
foreach (loterij_prijzen() as $i => $prijs) {
    echo '<tr><td>' . ($i + 1) . 'e</td><td>' . e($prijs['omschrijving']) . '</td></tr>';
}
echo '</tbody></table></div>';
echo '<p class="uitleg">Elk lot maakt kans. Eén lot kan maar één prijs winnen, '
   . 'en er wordt alleen getrokken als er genoeg loten verkocht zijn.</p>';

panel_close();

panel_open('Loten kopen');

if ($eigen >= LOT_MAX) {
    echo '<p>Je hebt het maximum van ' . LOT_MAX . ' loten al bereikt.</p>';
} elseif ($betaalbaar < 1) {
    echo '<p>Een lot kost ' . money(LOT_PRIJS) . ' en zoveel heb je niet op zak.</p>';
} else {
    echo '<p>Een lot kost ' . money(LOT_PRIJS) . '. Je kunt er nu ' . $betaalbaar . ' kopen.</p>';
    echo '<form method="post">' . csrf_field();
    echo '<div class="veldenraster">';
    echo '<label for="nroflot">Aantal loten</label>';
    echo '<input id="nroflot" name="nroflot" type="number" min="1" max="' . $betaalbaar . '" step="1" value="1" required>';
    echo '<span></span><button type="submit">Kopen</button>';
    echo '</div></form>';
}

panel_close();
layout_footer();

// ==========================================================================

/** @throws SpelFout */
function loten_kopen(array $user, int $aantal): string
{
    if ($aantal < 1) {
        throw new SpelFout('Vul een aantal van minstens 1 in.');
    }
    if ($aantal > LOT_MAX) {
        throw new SpelFout('Je mag hoogstens ' . LOT_MAX . ' loten hebben.');
    }

    return db_transaction(static function () use ($user, $aantal): string {
        $speler = lock_user((int) $user['id']);
        $eigen  = (int) q_val('SELECT COUNT(*) FROM `loterij` WHERE `login` = ?', [$speler['login']], 0);

        if ($eigen + $aantal > LOT_MAX) {
            throw new SpelFout('Je hebt al ' . $eigen . ' loten; het maximum is ' . LOT_MAX . '.');
        }

        $kosten = $aantal * LOT_PRIJS;

        if (!afboeken((int) $speler['id'], $kosten, 'zak')) {
            throw new SpelFout($aantal . ' loten kosten ' . money($kosten) . '.');
        }

        // Eén INSERT met meerdere rijen, in plaats van honderd losse queries.
        $rijen = implode(',', array_fill(0, $aantal, '(?)'));
        q("INSERT INTO `loterij` (`login`) VALUES {$rijen}",
            array_fill(0, $aantal, $speler['login']));

        return 'Je hebt ' . $aantal . ' ' . ($aantal === 1 ? 'lot' : 'loten')
             . ' gekocht voor ' . money($kosten) . '.';
    });
}
