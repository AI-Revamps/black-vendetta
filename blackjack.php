<?php
/**
 * Blackjack. De gedeelde casinologica staat in inc/casino.php.
 *
 * Wat hier gerepareerd is ten opzichte van de oude versie:
 *
 *  - GELDLEK 1: won de speler doordat de dealer kapot ging, dan kreeg hij
 *    `2 * inzet` uitbetaald terwijl de eigenaar maar `1 * inzet` kwijtraakte.
 *    Het verschil kwam uit het niets.
 *  - GELDLEK 2: ging de speler zelf kapot, dan werd `bank = bank + $inzet`
 *    uitgevoerd met een variabele die in dat verzoek nooit gezet was. De
 *    eigenaar kreeg dus niets, terwijl de speler zijn inzet al kwijt was. Dat
 *    geld verdween.
 *  - De eigenaarswissel werkte nooit. De controle "heeft deze gebruiker al een
 *    object" keek naar `owner = jouw eigen naam` in plaats van naar de
 *    ontvanger. Omdat je in die tak per definitie zelf eigenaar bent, was die
 *    controle altijd waar en brak hij altijd af.
 *  - De kaartafbeeldingen werden geladen van http://members.lycos.nl/js6287/,
 *    een host die niet meer bestaat, terwijl de plaatjes gewoon in
 *    images/kaarten/ staan.
 *  - Kant-en-klare HTML (<img src=...>) werd in de database opgeslagen. Nu
 *    worden alleen kaartcodes bewaard en wordt de HTML bij het tonen gemaakt.
 *  - Geen transactie rond de afrekening.
 *
 * Gewijzigde spelregels, bewust:
 *  - De aas telt automatisch als 11 zolang dat niet boven de 21 uitkomt, en
 *    anders als 1. De oude versie vroeg de speler zelf welke waarde hij wilde,
 *    wat geen blackjack is en alleen maar een extra scherm opleverde.
 *  - De dealer trekt tot 17, zoals gebruikelijk. De oude versie liet hem
 *    doortrekken tot hij de speler voorbij was, wat betekende dat de dealer
 *    nooit kon verliezen zonder kapot te gaan.
 *  - Uitbetaling: winst 1x je inzet, blackjack 1,5x, gelijkspel niets.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';
require BV_INC . '/casino.php';

const BJ_MIN_INZET = 100;
const BJ_DEALER_STOP = 17;

/** De vier kleuren, met de mapnaam van de afbeeldingen. */
function bj_kleuren(): array
{
    return ['h' => 'harten', 's' => 'schoppen', 'r' => 'ruiten', 'k' => 'klaveren'];
}

/** Naam van een kaartwaarde, zoals in de bestandsnamen. */
function bj_waardenaam(int $waarde): string
{
    return match ($waarde) {
        1       => 'aas',
        11      => 'boer',
        12      => 'vrouw',
        13      => 'koning',
        default => (string) $waarde,
    };
}

/** Trek een willekeurige kaart. Geeft een code terug als 'h1' of 's13'. */
function bj_trek(): string
{
    return array_rand(bj_kleuren()) . random_int(1, 13);
}

/** Splits een kaartcode in kleur en waarde. */
function bj_ontleed(string $code): array
{
    return ['kleur' => substr($code, 0, 1), 'waarde' => (int) substr($code, 1)];
}

/**
 * Tel een hand op. De aas telt als 11 zolang dat past, anders als 1.
 */
function bj_totaal(array $kaarten): int
{
    $totaal = 0;
    $azen   = 0;

    foreach ($kaarten as $code) {
        $waarde = bj_ontleed($code)['waarde'];

        if ($waarde === 1) {
            $azen++;
            $totaal += 11;
        } else {
            $totaal += min($waarde, 10);
        }
    }

    // Zolang we boven de 21 zitten en er nog een aas als 11 telt: verlaag hem.
    while ($totaal > 21 && $azen > 0) {
        $totaal -= 10;
        $azen--;
    }

    return $totaal;
}

/** Tekent een hand als plaatjes. */
function bj_hand_html(array $kaarten): string
{
    $html = '<div class="kaarthand">';

    foreach ($kaarten as $code) {
        $kaart    = bj_ontleed($code);
        $kleur    = bj_kleuren()[$kaart['kleur']] ?? 'harten';
        $bestand  = $kleur . bj_waardenaam($kaart['waarde']) . '.jpg';
        $omschrijving = $kleur . ' ' . bj_waardenaam($kaart['waarde']);

        $html .= '<img src="' . e(url('images/kaarten/' . $bestand)) . '" alt="'
               . e($omschrijving) . '" title="' . e($omschrijving) . '">';
    }

    return $html . '</div>';
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
        [$melding, $type] = verwerk($user);
        $user = current_user(true);
    } catch (SpelFout $e) {
        $melding = $e->getMessage();
        $type    = 'fout';
    }
}

$casino = casino_spel('blackjack', (string) $user['stad']);
$partij = q_row('SELECT * FROM `blackjack` WHERE `login` = ?', [$user['login']]);

layout_header('Blackjack');

if ($melding !== null) {
    notice(e($melding), $type);
}

if (casino_kop($user, $casino)) {
    if ($partij !== null) {
        toon_partij($partij);
    } else {
        toon_start($user, $casino);
    }
}

layout_footer();

// ==========================================================================

/**
 * @return array{0:string, 1:string}
 * @throws SpelFout
 */
function verwerk(array $user): array
{
    $casino = casino_spel('blackjack', (string) $user['stad']);

    return match (post('actie')) {
        'koop'  => [casino_kopen($user, 'blackjack', (string) $user['stad']), 'ok'],
        'inzet' => [casino_inzet_zetten($user, $casino, int_input('maxinzet')), 'ok'],
        'start' => starten($user, $casino, int_input('inzet', 0)),
        'kaart' => nog_een_kaart($user),
        'stop'  => passen($user),
        default => throw new SpelFout('Onbekende handeling.'),
    };
}

/**
 * @return array{0:string, 1:string}
 * @throws SpelFout
 */
function starten(array $user, array $casino, int $inzet): array
{
    if ($casino['owner'] === $user['login']) {
        throw new SpelFout('Je kunt niet in je eigen casino spelen.');
    }
    if ($inzet < BJ_MIN_INZET) {
        throw new SpelFout('De minimale inzet is ' . money(BJ_MIN_INZET) . '.');
    }
    if ($inzet > (int) $casino['inzet']) {
        throw new SpelFout('De maximale inzet is ' . money((int) $casino['inzet']) . '.');
    }

    return db_transaction(static function () use ($user, $inzet): array {
        $speler = lock_user((int) $user['id']);

        if (q_row('SELECT `id` FROM `blackjack` WHERE `login` = ? FOR UPDATE', [$speler['login']]) !== null) {
            throw new SpelFout('Je hebt al een partij lopen.');
        }
        if ((int) $speler['zak'] < $inzet) {
            throw new SpelFout('Je hebt niet genoeg geld op zak.');
        }

        $speler_kaarten = [bj_trek(), bj_trek()];
        $dealer_kaarten = [bj_trek()];

        $totaal = bj_totaal($speler_kaarten);

        // Blackjack meteen: 1,5 keer de inzet, partij is klaar.
        if ($totaal === 21) {
            $winst   = (int) round($inzet * 1.5);
            $uitslag = casino_afrekenen($user, 'blackjack', (string) $speler['stad'], 0, $winst);

            return ['Blackjack! ' . $uitslag['tekst'], $uitslag['type']];
        }

        q(
            'INSERT INTO `blackjack` (`login`, `inzet`, `kaart`, `kaartpic`, `aas`, `dealer`, `dealerpic`)
                  VALUES (?, ?, ?, ?, 0, ?, ?)',
            [
                $speler['login'], $inzet, $totaal, implode(',', $speler_kaarten),
                bj_totaal($dealer_kaarten), implode(',', $dealer_kaarten),
            ]
        );

        return ['Je hebt ' . $totaal . '. Nog een kaart of passen?', 'info'];
    });
}

/**
 * @return array{0:string, 1:string}
 * @throws SpelFout
 */
function nog_een_kaart(array $user): array
{
    return db_transaction(static function () use ($user): array {
        $partij = q_row('SELECT * FROM `blackjack` WHERE `login` = ? FOR UPDATE', [$user['login']]);

        if ($partij === null) {
            throw new SpelFout('Je hebt geen partij lopen.');
        }

        $kaarten   = explode(',', (string) $partij['kaartpic']);
        $kaarten[] = bj_trek();
        $totaal    = bj_totaal($kaarten);

        if ($totaal > 21) {
            // Kapot: de eigenaar krijgt de inzet. In de oude versie gebeurde
            // dat met een lege variabele, waardoor het geld verdween.
            $uitslag = casino_afrekenen($user, 'blackjack', (string) $user['stad'],
                (int) $partij['inzet'], 0);

            q('DELETE FROM `blackjack` WHERE `id` = ?', [$partij['id']]);

            return ['Je hebt ' . $totaal . ' en bent kapot. ' . $uitslag['tekst'], 'fout'];
        }

        q('UPDATE `blackjack` SET `kaart` = ?, `kaartpic` = ? WHERE `id` = ?',
            [$totaal, implode(',', $kaarten), $partij['id']]);

        return ['Je hebt nu ' . $totaal . '.', 'info'];
    });
}

/**
 * @return array{0:string, 1:string}
 * @throws SpelFout
 */
function passen(array $user): array
{
    return db_transaction(static function () use ($user): array {
        $partij = q_row('SELECT * FROM `blackjack` WHERE `login` = ? FOR UPDATE', [$user['login']]);

        if ($partij === null) {
            throw new SpelFout('Je hebt geen partij lopen.');
        }

        $speler = explode(',', (string) $partij['kaartpic']);
        $dealer = explode(',', (string) $partij['dealerpic']);
        $inzet  = (int) $partij['inzet'];

        $somSpeler = bj_totaal($speler);

        // De dealer trekt tot 17. In de oude versie trok hij tot hij de speler
        // voorbij was, waardoor hij alleen kon verliezen door kapot te gaan.
        while (bj_totaal($dealer) < BJ_DEALER_STOP) {
            $dealer[] = bj_trek();
        }

        $somDealer = bj_totaal($dealer);

        $verhaal = 'Jij hebt ' . $somSpeler . ', de dealer ' . $somDealer . '. ';

        if ($somDealer > 21 || $somSpeler > $somDealer) {
            $uitslag = casino_afrekenen($user, 'blackjack', (string) $user['stad'], 0, $inzet);
            $tekst   = $verhaal . ($somDealer > 21 ? 'De dealer is kapot. ' : '') . $uitslag['tekst'];
            $soort   = $uitslag['type'];
        } elseif ($somSpeler === $somDealer) {
            $tekst = $verhaal . 'Gelijkspel, je inzet blijft van jou.';
            $soort = 'info';
        } else {
            $uitslag = casino_afrekenen($user, 'blackjack', (string) $user['stad'], $inzet, 0);
            $tekst   = $verhaal . $uitslag['tekst'];
            $soort   = 'fout';
        }

        q('DELETE FROM `blackjack` WHERE `id` = ?', [$partij['id']]);

        return [$tekst, $soort];
    });
}

// ==========================================================================

function toon_start(array $user, array $casino): void
{
    panel_open('Blackjack');
    echo '<p>Kom zo dicht mogelijk bij 21 zonder eroverheen te gaan. Blackjack met de '
       . 'eerste twee kaarten betaalt anderhalf keer je inzet, een gewone winst een keer. '
       . 'De dealer trekt tot ' . BJ_DEALER_STOP . '.</p>';
    echo '<p>Je hebt ' . money((int) $user['zak']) . ' op zak. Inzet tussen '
       . money(BJ_MIN_INZET) . ' en ' . money((int) $casino['inzet']) . '.</p>';

    echo '<form method="post">' . csrf_field();
    echo '<input type="hidden" name="actie" value="start">';
    echo '<div class="veldenraster">';
    echo '<label for="inzet">Inzet</label>';
    echo '<input id="inzet" name="inzet" type="number" min="' . BJ_MIN_INZET . '" max="'
       . (int) $casino['inzet'] . '" step="1" required>';
    echo '<span></span><button type="submit">Delen</button>';
    echo '</div></form>';
    panel_close();
}

function toon_partij(array $partij): void
{
    $speler = explode(',', (string) $partij['kaartpic']);
    $dealer = explode(',', (string) $partij['dealerpic']);

    panel_open('Je partij');

    echo '<p>Inzet: ' . money((int) $partij['inzet']) . '</p>';

    echo '<h3>De dealer</h3>';
    echo bj_hand_html($dealer);
    echo '<p>Zichtbaar: ' . bj_totaal($dealer) . '</p>';

    echo '<h3>Jouw kaarten</h3>';
    echo bj_hand_html($speler);
    echo '<p>Totaal: <strong>' . bj_totaal($speler) . '</strong></p>';

    echo '<form method="post" style="display:inline">' . csrf_field()
       . '<input type="hidden" name="actie" value="kaart">'
       . '<button type="submit">Nog een kaart</button></form> ';
    echo '<form method="post" style="display:inline">' . csrf_field()
       . '<input type="hidden" name="actie" value="stop">'
       . '<button type="submit">Passen</button></form>';

    panel_close();
}
