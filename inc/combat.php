<?php
/**
 * Gevechtsberekening en de afhandeling van een sterfgeval.
 *
 * Staat apart van kill.php omdat oc.php en heist.php dezelfde afhandeling
 * nodig hebben: erfenis, familieopvolging, casino's en de premielijst.
 */

declare(strict_types=1);

defined('BV_INC') || exit;

// --- Instelbare balans -------------------------------------------------------
// Deze waarden bepalen hoe hard er geschoten wordt. Pas ze hier aan; de rest
// van de code hoeft er niet voor te veranderen.

const GEVECHT_BASIS        = 100;    // grondweerstand van elke speler
const GEVECHT_PER_GUARD    = 100;    // weerstand per bodyguard
const GEVECHT_HUIS_BONUS   = 5000;   // eigen huis in de stad van het gevecht
const GEVECHT_FAM_BONUS    = 2500;   // familie zetelt in deze stad
const GEVECHT_MIN_AIM      = 0.05;   // ook zonder moordervaring raak je iets
const BESCHERMING_UREN     = 24;     // nieuwe spelers zijn even onschendbaar
const MAX_RANGVERSCHIL     = 2;      // hoeveel rangen je omhoog mag moorden
const MOORDEN_PER_WEEK     = 10;
const OOGGETUIGE_GELDIG    = 172800; // twee dagen

/** Effect van een item, of 1.0 als het niet bestaat. */
function item_effect(string $type, int $nr): float
{
    if ($nr < 1) {
        return 1.0;
    }
    $effect = q_val('SELECT `effect` FROM `items` WHERE `type` = ? AND `nr` = ?', [$type, $nr]);
    return $effect === null || (float) $effect <= 0 ? 1.0 : (float) $effect;
}

/**
 * Hoe moeilijk deze speler neer te halen is, in de stad waar gevochten wordt.
 */
function weerstand(array $speler, string $stad): float
{
    $basis = GEVECHT_BASIS
           + (int) round((int) $speler['xp'] / 10)
           + (int) $speler['guard'] * GEVECHT_PER_GUARD;

    $weerstand = $basis * item_effect('def', (int) $speler['defence']);

    // Thuisvoordeel: een eigen huis in deze stad.
    if ((int) ($speler[$stad] ?? 0) > 0) {
        $weerstand += GEVECHT_HUIS_BONUS;
    }

    // De familie van deze speler zetelt in deze stad.
    if ($speler['famillie'] !== '') {
        $famStad = q_val('SELECT `stad` FROM `famillie` WHERE `name` = ?', [$speler['famillie']]);
        if ($famStad === $stad) {
            $weerstand += GEVECHT_FAM_BONUS;
        }
    }

    return max(1.0, $weerstand);
}

/**
 * Trefzekerheid van de schutter: moordervaring gedeeld door het wapen.
 *
 * LET OP - hier is bewust van het origineel afgeweken.
 *
 * De oude formule was `100 / (weerstand * rand * aim) * kogels`. Daarin zat de
 * trefzekerheid in de noemer, waardoor méér moordervaring juist mínder schade
 * gaf. En omdat nieuwe spelers `se = 0` hebben werd er door nul gedeeld: op
 * PHP 5 gaf dat oneindig veel schade (elke beginner doodde in één keer), op
 * PHP 8 is het een fatale fout.
 *
 * Hier telt trefzekerheid als vermenigvuldiger en zit er een ondergrens in.
 * Wil je de balans anders, pas dan de constanten bovenaan dit bestand aan.
 */
function trefzekerheid(array $speler): float
{
    $ervaring = max(GEVECHT_MIN_AIM, (float) $speler['se'] / 100);
    $wapen    = item_effect('att', (int) $speler['wapon']);

    return $ervaring / $wapen;
}

/**
 * Schade die $schutter toebrengt aan $doelwit met $kogels kogels.
 */
function schade(array $schutter, array $doelwit, int $kogels, string $stad): float
{
    if ($kogels < 1) {
        return 0.0;
    }

    $geluk = random_int(1, 5);

    return ($kogels * GEVECHT_BASIS * trefzekerheid($schutter) * $geluk)
         / weerstand($doelwit, $stad);
}

/**
 * Hoeveel kogels het slachtoffer terugschiet, op basis van zijn
 * backfire-instelling (`bf`).
 */
function backfire_kogels(array $doelwit, int $inkomend): int
{
    if ((int) $doelwit['bf'] === 0 || (int) $doelwit['wapon'] === 0) {
        return 0;
    }

    $terug = match (true) {
        (int) $doelwit['bf'] < 2 => (int) round($inkomend * 0.5),
        (int) $doelwit['bf'] < 3 => $inkomend,
        (int) $doelwit['bf'] < 4 => $inkomend * 2,
        default                  => (int) $doelwit['bf'],
    };

    return (int) min($terug, (int) $doelwit['kogels']);
}

/** Staat deze speler nog onder de bescherming voor nieuwe spelers? */
function is_beschermd(array $speler): bool
{
    $start = $speler['start_ts'] ?? null;
    if ($start === null) {
        $start = q_val('SELECT UNIX_TIMESTAMP(`start`) FROM `users` WHERE `id` = ?', [$speler['id']]);
    }
    return $start !== null && (time() - (int) $start) < BESCHERMING_UREN * 3600;
}

/** Zit deze speler ondergedoken? */
function is_ondergedoken(array $speler): bool
{
    $safe = q_val('SELECT UNIX_TIMESTAMP(`safe`) FROM `users` WHERE `id` = ?', [$speler['id']]);
    return $safe !== null && (int) $safe > time();
}

// ==========================================================================
// Sterfgeval afhandelen
// ==========================================================================

/**
 * Verwerk het overlijden van een speler: bezit, erfenis, familieopvolging,
 * casino's en de premielijst.
 *
 * Roep dit altijd binnen db_transaction() aan.
 *
 * @param array  $dode    De volledige, vergrendelde rij van het slachtoffer.
 * @param string $dader   Wie hem heeft omgelegd.
 * @param string $bericht Het bericht dat de dader achterlaat.
 * @return string[] Regels om aan de dader te tonen.
 */
function speler_sterft(array $dode, string $dader, string $bericht): array
{
    $regels = [];
    $naam   = (string) $dode['login'];

    q('INSERT INTO `vermoord` (`login`, `dader`, `date`, `msg`) VALUES (?, ?, NOW(), ?)',
        [$naam, $dader, $bericht]);

    // --- Premie uitbetalen ---
    $premie = q_row('SELECT * FROM `hitlist` WHERE `login` = ? FOR UPDATE', [$naam]);
    if ($premie !== null) {
        $bedrag = (int) $premie['prijs'];
        $winnaar = lock_user_by_login($dader);

        if ($winnaar !== null) {
            bijschrijven((int) $winnaar['id'], $bedrag, 'zak');
            notify($dader, 'Premielijst',
                'Je hebt ' . $naam . ' vermoord. Er stond een premie van '
                . money($bedrag) . ' op zijn hoofd. Dat geld is nu van jou.');
        }
        notify((string) $premie['suspect'], 'Premielijst',
            'Jij had een premie op ' . $naam . ' gezet, en die is uitbetaald.');

        q('DELETE FROM `hitlist` WHERE `login` = ?', [$naam]);
        $regels[] = 'De premie van ' . money($bedrag) . ' is aan jou uitbetaald.';
    }

    // --- Casino's komen weer vrij ---
    $casinos = q_all("SELECT * FROM `casino` WHERE `owner` = ?", [$naam]);
    foreach ($casinos as $casino) {
        q("UPDATE `casino` SET `owner` = '', `winst` = 0 WHERE `id` = ?", [$casino['id']]);
        $regels[] = $naam . ' was eigenaar van ' . $casino['spel'] . ' in '
                  . $casino['stad'] . '. Dat is nu weer te koop.';
    }

    // --- Familieopvolging ---
    if ((int) $dode['famrang'] > 4 && $dode['famillie'] !== '') {
        $regels = array_merge($regels, familie_opvolging((string) $dode['famillie'], $naam));
    }

    // --- Erfenis ---
    $erfgenaam = (string) $dode['testament'];
    if ($erfgenaam !== '') {
        $erfdeel = (int) round((int) $dode['bank'] * 0.5);
        $erver   = lock_user_by_login($erfgenaam);

        if ($erver !== null) {
            bijschrijven((int) $erver['id'], $erfdeel, 'bank');
            q('UPDATE `garage` SET `login` = ? WHERE `login` = ?', [$erfgenaam, $naam]);
            notify($erfgenaam, 'Testament',
                'Je stond in het testament van ' . $naam . '. Je erft ' . money($erfdeel)
                . ' en zijn wagens.');
        }
    }

    // Wat niet geërfd is, verdwijnt.
    q('DELETE FROM `garage` WHERE `login` = ?', [$naam]);
    q("UPDATE `users` SET `testament` = '' WHERE `testament` = ?", [$naam]);

    // --- De speler zelf ---
    // zak én bank op nul. In de oude versie bleef `zak` staan terwijl de
    // moordenaar datzelfde bedrag kreeg, waardoor elke moord geld bijdrukte.
    q(
        "UPDATE `users`
            SET `status` = 'dood', `zak` = 0, `bank` = 0,
                `famillie` = '', `famrang` = 0, `famcapo` = '',
                `kogels` = 0, `drugs` = 0, `drank` = 0
          WHERE `id` = ?",
        [$dode['id']]
    );

    // --- Rouwbericht op het forum ---
    q(
        "INSERT INTO `forum_topics` (`user`, `type`, `subject`, `message`, `date`)
              VALUES ('Notificatie', 'rip', ?, ?, NOW())",
        ['RIP ' . $naam, $naam . ' is omgelegd.']
    );

    return $regels;
}

/**
 * De don is dood. Zoek een opvolger, of hef de familie op.
 *
 * @return string[]
 */
function familie_opvolging(string $familie, string $dodeDon): array
{
    $opvolger = q_row(
        "SELECT * FROM `users`
          WHERE `famillie` = ? AND `login` <> ? AND `status` = 'levend' AND `famrang` IN (4, 3)
       ORDER BY `famrang` DESC, `xp` DESC LIMIT 1",
        [$familie, $dodeDon]
    );

    if ($opvolger !== null) {
        q("UPDATE `users` SET `famrang` = 5 WHERE `id` = ?", [$opvolger['id']]);
        notify((string) $opvolger['login'], 'Familie',
            'Je Don is vermoord. Jij bent nu de nieuwe Don van ' . $familie . '.');

        return ['De Don van ' . $familie . ' is dood. ' . $opvolger['login'] . ' neemt het over.'];
    }

    // Geen opvolger: de familie valt uiteen.
    $leden = q_all('SELECT `login` FROM `users` WHERE `famillie` = ?', [$familie]);
    foreach ($leden as $lid) {
        notify((string) $lid['login'], 'Familie',
            'De Don van ' . $familie . ' is vermoord en er was geen opvolger. De familie is opgeheven.');
    }

    q("UPDATE `users` SET `famillie` = '', `famrang` = 0, `famcapo` = '' WHERE `famillie` = ?", [$familie]);
    q('DELETE FROM `famillie` WHERE `name` = ?', [$familie]);
    q("UPDATE `casino` SET `owner` = '' WHERE `owner` IN (SELECT `login` FROM `users` WHERE `famillie` = ?)", [$familie]);

    return ['Je slachtoffer was de Don van ' . $familie . '. Er was geen opvolger: de familie is uitgemoord.'];
}

/**
 * Wijs een willekeurige ooggetuige aan die de moord gezien heeft.
 * Die kan de verklaring later op de zwarte markt verkopen.
 */
function ooggetuige_aanwijzen(string $dader, string $slachtoffer): void
{
    $getuige = q_row(
        "SELECT `login` FROM `users`
          WHERE `online` > DATE_SUB(NOW(), INTERVAL 12 HOUR)
            AND `status` = 'levend'
            AND `login` NOT IN (?, ?)
       ORDER BY RAND() LIMIT 1",
        [$dader, $slachtoffer]
    );

    if ($getuige === null) {
        return;
    }

    q(
        'INSERT INTO `ws` (`login`, `victim`, `suspect`, `prijs`, `status`, `time`)
              VALUES (?, ?, ?, 0, 0, DATE_ADD(NOW(), INTERVAL ? SECOND))',
        [$getuige['login'], $slachtoffer, $dader, OOGGETUIGE_GELDIG]
    );

    notify((string) $getuige['login'], 'Ooggetuige',
        'Je hebt gezien hoe ' . $slachtoffer . ' werd vermoord. Je kunt je verklaring '
        . 'te koop zetten op de zwarte markt.');
}
