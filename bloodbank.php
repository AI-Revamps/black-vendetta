<?php
/**
 * Bloedbank: koop bloedzakjes om je gezondheid aan te vullen.
 *
 * Wat hier gerepareerd is ten opzichte van de oude versie:
 *
 *  - Betalen en de gezondheid ophogen gebeurde in twee losse queries zonder
 *    transactie. Ging de tweede mis, dan was je je geld kwijt zonder resultaat.
 *  - De nieuwe gezondheid werd berekend uit de waarde die bij het laden van de
 *    pagina was opgehaald. Tussen laden en versturen kon die veranderd zijn,
 *    bijvoorbeeld door een aanslag, waardoor je boven de 100% uit kon komen.
 *  - Er stond een controle op een negatief aantal, maar die kon nooit vuren
 *    omdat de invoer daarvoor al op alleen cijfers gecontroleerd was.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

const BLOEDZAKJE_PRIJS = 1000;
const GEZONDHEID_MAX   = 100;

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
        $melding = kopen($user, int_input('aantal', 0));
        $type    = 'ok';
        $user    = current_user(true);
    } catch (SpelFout $e) {
        $melding = $e->getMessage();
        $type    = 'fout';
    }
}

$tekort    = GEZONDHEID_MAX - (int) $user['health'];
$betaalbaar = intdiv((int) $user['zak'], BLOEDZAKJE_PRIJS);

layout_header('Bloedbank');

if ($melding !== null) {
    notice(e($melding), $type);
}

panel_open('Bloedbank');

echo '<p>Je gezondheid is <strong>' . (int) $user['health'] . '%</strong>. '
   . 'Een bloedzakje kost ' . money(BLOEDZAKJE_PRIJS) . ' en geeft 1% terug.</p>';

if ($tekort < 1) {
    echo '<p>Je bent kerngezond. Je hebt geen bloed nodig.</p>';
} elseif ($betaalbaar < 1) {
    echo '<p>Je hebt niet genoeg geld voor een bloedzakje.</p>';
} else {
    $max = min($tekort, $betaalbaar);

    echo '<p>Je kunt er nu <strong>' . $max . '</strong> kopen: je mist ' . $tekort
       . '% en kunt er ' . $betaalbaar . ' betalen.</p>';

    echo '<form method="post">' . csrf_field();
    echo '<div class="veldenraster">';
    echo '<label for="aantal">Aantal zakjes</label>';
    echo '<input id="aantal" name="aantal" type="number" min="1" max="' . $max . '" step="1" required>';
    echo '<span></span><button type="submit">Kopen</button>';
    echo '</div></form>';
}

panel_close();
layout_footer();

// ==========================================================================

/** @throws SpelFout */
function kopen(array $user, int $aantal): string
{
    if ($aantal < 1) {
        throw new SpelFout('Vul een aantal van minstens 1 in.');
    }

    return db_transaction(static function () use ($user, $aantal): string {
        // Binnen de transactie opnieuw ophalen: de gezondheid kan sinds het
        // laden van de pagina veranderd zijn.
        $speler = lock_user((int) $user['id']);
        $ruimte = GEZONDHEID_MAX - (int) $speler['health'];

        if ($ruimte < 1) {
            throw new SpelFout('Je gezondheid staat al op ' . GEZONDHEID_MAX . '%.');
        }
        if ($aantal > $ruimte) {
            throw new SpelFout('Je mist maar ' . $ruimte . '%, dus meer dan '
                . $ruimte . ' zakjes hebben geen zin.');
        }

        $prijs = $aantal * BLOEDZAKJE_PRIJS;

        if (!afboeken((int) $speler['id'], $prijs, 'zak')) {
            throw new SpelFout('Dit kost ' . money($prijs) . ' en zoveel heb je niet op zak.');
        }

        q('UPDATE `users` SET `health` = LEAST(?, `health` + ?) WHERE `id` = ?',
            [GEZONDHEID_MAX, $aantal, $speler['id']]);

        $nieuw = min(GEZONDHEID_MAX, (int) $speler['health'] + $aantal);

        return 'Je hebt ' . $aantal . ' ' . ($aantal === 1 ? 'bloedzakje' : 'bloedzakjes')
             . ' gekocht voor ' . money($prijs) . '. Je gezondheid is nu ' . $nieuw . '%.';
    });
}
