<?php
/**
 * Poll: stem op de vraag van de beheerders en bekijk de uitslagen.
 *
 * Wat hier gerepareerd is ten opzichte van de oude versie:
 *
 *  - Het bestand gebruikte $HTTP_SERVER_VARS en $HTTP_GET_VARS. Die zijn in
 *    PHP 5.4 verwijderd, dus het IP-adres en het poll-nummer waren altijd leeg.
 *  - De klasse had een constructor in de oude stijl (een functie met dezelfde
 *    naam als de klasse). Dat is in PHP 8 geen constructor meer.
 *  - Wie gestemd had werd bijgehouden door de tekst "(ip,keuze)" aan een
 *    tekstveld te plakken. Dat veld groeide onbeperkt en de controle of iemand
 *    al gestemd had gebeurde met string-zoeken, wat op deelreeksen misgaat:
 *    wie vanaf 10.0.0.1 gestemd had, blokkeerde ook 10.0.0.11. Stemmen staan
 *    nu in een eigen tabel, met de speler als sleutel in plaats van het IP.
 *  - De stemwaarde werd wel op bereik gecontroleerd, maar rechtstreeks in de
 *    kolomnaam `antwoord{$vote}` gezet.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

const POLL_MAX_KEUZES = 10;

$user = require_login();

$melding = null;
$type    = 'info';

if (is_post()) {
    csrf_check();
    try {
        $melding = stemmen($user, int_input('poll'), int_input('keuze'));
        $type    = 'ok';
    } catch (SpelFout $e) {
        $melding = $e->getMessage();
        $type    = 'fout';
    }
}

$gevraagd = int_input('pollid');

$poll = $gevraagd > 0
    ? q_row('SELECT * FROM `poll` WHERE `id` = ?', [$gevraagd])
    : q_row('SELECT * FROM `poll` WHERE `actief` = 1 ORDER BY `id` DESC LIMIT 1');

layout_header('Poll');

if ($melding !== null) {
    notice(e($melding), $type);
}

if ($poll === null) {
    panel_open('Poll');
    echo '<p>Er is op dit moment geen poll.</p>';
    panel_close();
} else {
    toon_poll($user, $poll);
}

toon_archief($poll['id'] ?? 0);

layout_footer();

// ==========================================================================

/** De keuzes van een poll als [nummer => ['tekst' => ..., 'stemmen' => ...]]. */
function poll_keuzes(array $poll): array
{
    $keuzes = [];

    for ($i = 1; $i <= POLL_MAX_KEUZES; $i++) {
        $tekst = trim((string) ($poll['keuze' . $i] ?? ''));

        if ($tekst !== '') {
            $keuzes[$i] = [
                'tekst'   => $tekst,
                'stemmen' => (int) ($poll['antwoord' . $i] ?? 0),
            ];
        }
    }

    return $keuzes;
}

/** @throws SpelFout */
function stemmen(array $user, int $pollId, int $keuze): string
{
    if ($keuze < 1 || $keuze > POLL_MAX_KEUZES) {
        throw new SpelFout('Kies een geldig antwoord.');
    }

    return db_transaction(static function () use ($user, $pollId, $keuze): string {
        $poll = q_row('SELECT * FROM `poll` WHERE `id` = ? FOR UPDATE', [$pollId]);

        if ($poll === null) {
            throw new SpelFout('Die poll bestaat niet.');
        }
        if ((int) $poll['actief'] !== 1) {
            throw new SpelFout('Deze poll is gesloten.');
        }
        if (!isset(poll_keuzes($poll)[$keuze])) {
            throw new SpelFout('Dat antwoord staat niet bij deze poll.');
        }

        // De sleutel op (poll_id, login) laat maar één stem per speler toe.
        $nieuw = q_count(
            'INSERT IGNORE INTO `poll_stemmen` (`poll_id`, `login`, `keuze`) VALUES (?, ?, ?)',
            [$pollId, $user['login'], $keuze]
        );

        if ($nieuw === 0) {
            throw new SpelFout('Je hebt al gestemd op deze poll.');
        }

        // De kolomnaam komt uit een gecontroleerd getal, niet uit invoer.
        $kolom = 'antwoord' . $keuze;
        q("UPDATE `poll` SET `{$kolom}` = `{$kolom}` + 1 WHERE `id` = ?", [$pollId]);

        return 'Je stem is geteld.';
    });
}

// ==========================================================================

function toon_poll(array $user, array $poll): void
{
    $keuzes = poll_keuzes($poll);
    $totaal = array_sum(array_column($keuzes, 'stemmen'));
    $actief = (int) $poll['actief'] === 1;

    $eigenStem = q_val('SELECT `keuze` FROM `poll_stemmen` WHERE `poll_id` = ? AND `login` = ?',
        [$poll['id'], $user['login']]);

    panel_open($poll['vraag'] === '' ? 'Poll' : (string) $poll['vraag']);

    if ($keuzes === []) {
        echo '<p>Bij deze poll staan geen antwoorden.</p>';
        panel_close();
        return;
    }

    // Stemmen mag alleen als de poll open is en je nog niet gestemd hebt.
    if ($actief && $eigenStem === null) {
        echo '<form method="post">' . csrf_field();
        echo '<input type="hidden" name="poll" value="' . (int) $poll['id'] . '">';

        foreach ($keuzes as $nr => $keuze) {
            echo '<p><label><input type="radio" name="keuze" value="' . $nr . '" required> '
               . e($keuze['tekst']) . '</label></p>';
        }

        echo '<p><button type="submit">Stem</button></p></form>';
        echo '<p class="uitleg">Je kunt maar één keer stemmen.</p>';
    } else {
        toon_uitslag($keuzes, $totaal, $eigenStem === null ? null : (int) $eigenStem);

        if (!$actief) {
            echo '<p class="uitleg">Deze poll is gesloten.</p>';
        }
    }

    panel_close();
}

function toon_uitslag(array $keuzes, int $totaal, ?int $eigenStem): void
{
    echo '<div class="tabelwikkel"><table class="lijst">';
    echo '<thead><tr><th>Antwoord</th><th class="getal">Stemmen</th><th>Aandeel</th></tr></thead><tbody>';

    foreach ($keuzes as $nr => $keuze) {
        $deel = $totaal > 0 ? round($keuze['stemmen'] / $totaal * 100) : 0;

        echo '<tr>';
        echo '<td>' . e($keuze['tekst'])
           . ($nr === $eigenStem ? ' <small>(jouw stem)</small>' : '') . '</td>';
        echo '<td class="getal">' . num($keuze['stemmen']) . '</td>';
        echo '<td><div class="balk"><span style="width:' . $deel . '%"></span></div>'
           . '<small>' . $deel . '%</small></td>';
        echo '</tr>';
    }

    echo '</tbody></table></div>';
    echo '<p class="uitleg">' . num($totaal) . ' ' . ($totaal === 1 ? 'stem' : 'stemmen')
       . ' in totaal.</p>';
}

function toon_archief(int $huidig): void
{
    $oudere = q_all(
        'SELECT `id`, `vraag`, `actief` FROM `poll` WHERE `id` <> ? ORDER BY `id` DESC LIMIT 15',
        [$huidig]
    );

    if ($oudere === []) {
        return;
    }

    panel_open('Eerdere polls');
    echo '<ul>';
    foreach ($oudere as $rij) {
        echo '<li><a href="' . e(url('poll.php?pollid=' . (int) $rij['id'])) . '">'
           . e((string) $rij['vraag']) . '</a>'
           . ((int) $rij['actief'] === 1 ? ' <small>(loopt nog)</small>' : '') . '</li>';
    }
    echo '</ul>';
    panel_close();
}
