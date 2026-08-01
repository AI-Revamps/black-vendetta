<?php
/**
 * Handel in drugs en drank.
 *
 * De oude drank.php en drugs.php waren op de kolomnamen na identiek: dezelfde
 * 200 regels, twee keer. Hier staat de logica één keer; de twee pagina's zijn
 * nog maar een paar regels lang.
 *
 * Wat hier gerepareerd is:
 *
 *  - Beide bestanden openden halverwege een tweede databaseverbinding met een
 *    andere hardcoded gebruikersnaam en wachtwoord. Die database bestaat niet
 *    meer en de gegevens stonden gewoon in de broncode.
 *  - Bij verkopen werd gecontroleerd op `kans == 0`, terwijl de worp
 *    `rand(1, n)` is en dus nooit nul oplevert. Je kon bij het verkopen dus
 *    nooit gearresteerd worden.
 *  - Het aantal kwam ongefilterd uit het formulier en er zat geen transactie
 *    omheen, dus voorraad en geld konden uit de pas lopen.
 */

declare(strict_types=1);

defined('BV_INC') || exit;

/**
 * Kolomnamen per handelswaar. De sleutels komen overeen met de bestandsnamen.
 */
function handel_soorten(): array
{
    return [
        'drugs' => [
            'titel'      => 'Drugs',
            'eenheid'    => 'units',
            'bezit'      => 'drugs',    // users
            'timer'      => 'drugst',   // users
            'voorraad'   => 'drugs',    // stad
            'prijs'      => 'drugsp',   // stad
        ],
        'drank' => [
            'titel'      => 'Drank',
            'eenheid'    => 'flessen',
            'bezit'      => 'drank',
            'timer'      => 'drankt',
            'voorraad'   => 'drank',
            'prijs'      => 'drankp',
        ],
    ];
}

/**
 * Hoeveel je mag dragen en hoe groot de pakkans is, op basis van ervaring.
 *
 * @return array{max:int, kans:int} kans = 1 op N
 */
function handel_capaciteit(array $user): array
{
    $xp = (int) $user['xp'];

    $ladder = [
        [20,    0,  1],
        [50,    1,  2],
        [150,   2,  2],
        [500,   4,  2],
        [1000,  5,  2],
        [2000,  7,  2],
        [3000,  8,  3],
        [4500,  10, 3],
        [6000,  11, 3],
        [8000,  13, 4],
        [11000, 14, 4],
        [15000, 16, 4],
        [20000, 17, 5],
    ];

    foreach ($ladder as [$grens, $max, $kans]) {
        if ($xp < $grens) {
            return ['max' => $max, 'kans' => handel_stafbonus($user, $kans)];
        }
    }
    return ['max' => 20, 'kans' => handel_stafbonus($user, 5)];
}

/** Stafleden lopen nauwelijks risico; dat was in de oude versie ook zo. */
function handel_stafbonus(array $user, int $kans): int
{
    return (int) $user['level'] >= LEVEL_ADMIN ? 10 : $kans;
}

/**
 * Toon en verwerk de handelspagina.
 *
 * @return never
 */
function handel_pagina(string $soort): void
{
    $soorten = handel_soorten();
    if (!isset($soorten[$soort])) {
        fail_page('Onbekend', 'Deze handelswaar bestaat niet.', 404);
    }

    $def  = $soorten[$soort];
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
            $uitkomst = match (post('actie')) {
                'koop'    => handel_kopen($user, $def, int_input('aantal', 0)),
                'verkoop' => handel_verkopen($user, $def, int_input('aantal', 0)),
                default   => throw new SpelFout('Kies kopen of verkopen.'),
            };
            $melding = $uitkomst['tekst'];
            $type    = $uitkomst['type'];
            $user    = current_user(true);
        } catch (SpelFout $e) {
            $melding = $e->getMessage();
            $type    = 'fout';
        }
    }

    $stad = q_row('SELECT * FROM `stad` WHERE `stad` = ?', [$user['stad']]);
    $cap  = handel_capaciteit($user);

    $bezit    = (int) $user[$def['bezit']];
    $voorraad = (int) ($stad[$def['voorraad']] ?? 0);
    $prijs    = (int) ($stad[$def['prijs']] ?? 0);
    $ruimte   = max(0, $cap['max'] - $bezit);

    layout_header($def['titel']);

    if ($melding !== null) {
        notice(e($melding), $type);
    }

    panel_open($def['titel'] . ' in ' . $user['stad']);

    if ($cap['max'] < 1) {
        echo '<p>Je bent nog te onervaren om in ' . e(strtolower($def['titel']))
           . ' te handelen. Pleeg eerst wat misdaden.</p>';
        panel_close();
        layout_footer();
        exit;
    }

    echo '<div class="tabelwikkel"><table class="lijst">';
    echo '<tr><th scope="row">Je hebt</th><td>' . num($bezit) . ' ' . e($def['eenheid']) . '</td></tr>';
    echo '<tr><th scope="row">Je kunt nog dragen</th><td>' . num($ruimte) . '</td></tr>';
    echo '<tr><th scope="row">Voorraad in deze stad</th><td>' . num($voorraad) . '</td></tr>';
    echo '<tr><th scope="row">Prijs per stuk</th><td>' . money($prijs) . '</td></tr>';
    echo '<tr><th scope="row">Pakkans</th><td>1 op ' . $cap['kans'] . '</td></tr>';
    echo '</table></div>';

    echo '<p class="uitleg">De prijs verschilt per stad en wisselt dagelijks. '
       . 'Koop goedkoop in, reis naar een dure stad en verkoop daar.</p>';

    echo '<form method="post">' . csrf_field();
    echo '<div class="veldenraster">';
    echo '<label for="aantal">Aantal</label>';
    echo '<input id="aantal" name="aantal" type="number" min="1" step="1" required inputmode="numeric">';
    echo '<span></span><div>'
       . '<button type="submit" name="actie" value="koop">Kopen</button> '
       . '<button type="submit" name="actie" value="verkoop">Verkopen</button></div>';
    echo '</div></form>';

    panel_close();
    layout_footer();
    exit;
}

/**
 * @return array{tekst:string, type:string}
 * @throws SpelFout
 */
function handel_kopen(array $user, array $def, int $aantal): array
{
    if ($aantal < 1) {
        throw new SpelFout('Vul een aantal van minstens 1 in.');
    }

    return db_transaction(static function () use ($user, $def, $aantal): array {
        $speler = lock_user((int) $user['id']);
        $stad   = q_row('SELECT * FROM `stad` WHERE `stad` = ? FOR UPDATE', [$speler['stad']]);

        if ($stad === null) {
            throw new SpelFout('Deze stad bestaat niet.');
        }

        $cap = handel_capaciteit($speler);

        if ($cap['max'] < 1) {
            throw new SpelFout('Je bent nog te onervaren om hierin te handelen.');
        }
        if ((int) $stad[$def['voorraad']] < $aantal) {
            throw new SpelFout('Er zijn hier maar ' . num((int) $stad[$def['voorraad']]) . ' ' . $def['eenheid'] . '.');
        }
        if ((int) $speler[$def['bezit']] + $aantal > $cap['max']) {
            throw new SpelFout('Je mag hoogstens ' . $cap['max'] . ' ' . $def['eenheid'] . ' dragen.');
        }

        $kosten = $aantal * (int) $stad[$def['prijs']];

        if (!afboeken((int) $speler['id'], $kosten, 'zak')) {
            throw new SpelFout('Dit kost ' . money($kosten) . ' en zoveel heb je niet op zak.');
        }

        // Pakkans. Let op: hier wordt bewust géén SpelFout gegooid. Die zou de
        // transactie terugdraaien en dan zou je je geld terugkrijgen, terwijl
        // de melding zegt dat je het kwijt bent.
        if (random_int(1, $cap['kans']) === 1) {
            jail_put((string) $speler['login'], (int) $speler['xp'],
                (string) $speler['stad'], (string) $speler['famillie']);

            return [
                'tekst' => 'Je bent gearresteerd tijdens de aankoop. Je bent '
                         . money($kosten) . ' kwijt en zit nu vast.',
                'type'  => 'fout',
            ];
        }

        q("UPDATE `users` SET `{$def['bezit']}` = `{$def['bezit']}` + ? WHERE `id` = ?",
            [$aantal, $speler['id']]);
        q("UPDATE `stad` SET `{$def['voorraad']}` = `{$def['voorraad']}` - ? WHERE `stad` = ?",
            [$aantal, $speler['stad']]);

        // Ervaring hoogstens één keer per uur, zoals in de oude versie.
        $timerTs = (int) q_val("SELECT UNIX_TIMESTAMP(`{$def['timer']}`) FROM `users` WHERE `id` = ?",
            [$speler['id']], 0);

        if ($timerTs <= time()) {
            q("UPDATE `users` SET `xp` = `xp` + 3, `{$def['timer']}` = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE `id` = ?",
                [$speler['id']]);
        }

        return [
            'tekst' => 'Je hebt ' . num($aantal) . ' ' . $def['eenheid']
                     . ' gekocht voor ' . money($kosten) . '.',
            'type'  => 'ok',
        ];
    });
}

/**
 * @return array{tekst:string, type:string}
 * @throws SpelFout
 */
function handel_verkopen(array $user, array $def, int $aantal): array
{
    if ($aantal < 1) {
        throw new SpelFout('Vul een aantal van minstens 1 in.');
    }

    return db_transaction(static function () use ($user, $def, $aantal): array {
        $speler = lock_user((int) $user['id']);
        $stad   = q_row('SELECT * FROM `stad` WHERE `stad` = ? FOR UPDATE', [$speler['stad']]);

        if ($stad === null) {
            throw new SpelFout('Deze stad bestaat niet.');
        }
        if ((int) $speler[$def['bezit']] < $aantal) {
            throw new SpelFout('Zoveel ' . $def['eenheid'] . ' heb je niet.');
        }

        $cap        = handel_capaciteit($speler);
        $opbrengst  = $aantal * (int) $stad[$def['prijs']];

        // De voorraad gaat er hoe dan ook af; word je gepakt, dan ben je hem kwijt.
        q("UPDATE `users` SET `{$def['bezit']}` = `{$def['bezit']}` - ? WHERE `id` = ?",
            [$aantal, $speler['id']]);
        q("UPDATE `stad` SET `{$def['voorraad']}` = `{$def['voorraad']}` + ? WHERE `stad` = ?",
            [$aantal, $speler['stad']]);

        // Deze controle deed in de oude versie niets: er werd op nul getest
        // terwijl de worp bij één begint. Ook hier geen SpelFout, anders zou de
        // handelswaar door de terugdraaiing gewoon weer op zak staan.
        if (random_int(1, $cap['kans']) === 1) {
            jail_put((string) $speler['login'], (int) $speler['xp'],
                (string) $speler['stad'], (string) $speler['famillie']);

            return [
                'tekst' => 'Je bent gearresteerd tijdens de verkoop. Je '
                         . $def['eenheid'] . ' ben je kwijt en je zit nu vast.',
                'type'  => 'fout',
            ];
        }

        bijschrijven((int) $speler['id'], $opbrengst, 'zak');

        return [
            'tekst' => 'Je hebt ' . num($aantal) . ' ' . $def['eenheid']
                     . ' verkocht voor ' . money($opbrengst) . '.',
            'type'  => 'ok',
        ];
    });
}
