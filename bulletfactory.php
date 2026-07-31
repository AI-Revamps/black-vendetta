<?php
/**
 * Kogelfabriek: koop kogels uit de voorraad van de stad.
 *
 * De voorraad en de prijs per stad worden elke paar minuten ververst door de
 * taak 'kogels' in inc/cron.php.
 *
 * Wat hier gerepareerd is ten opzichte van de oude versie:
 *
 *  - De donateursbonus werd gecontroleerd met `$data->paid == 1`, terwijl `paid`
 *    het aantal actieve donaties bijhield: met twee of drie donaties verloor je
 *    je bonus juist. Die telling is inmiddels vervangen door één premiumtermijn,
 *    dus de controle is nu simpelweg is_premium().
 *  - Betalen en de voorraad afboeken gebeurde in twee losse queries zonder
 *    transactie, zodat de stadsvoorraad negatief kon worden bij gelijktijdige
 *    aankopen.
 *  - Er stond een controle op een negatief aantal die nooit kon vuren, omdat
 *    de invoer al op alleen cijfers gecontroleerd was.
 *  - Het invoerveld stond op hoogstens drie tekens, wat de aankoop stilzwijgend
 *    op 999 begrensde zonder dat ergens te melden.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

/** Hoe lang je moet wachten tussen twee aankopen. */
const KOGELS_WACHTTIJD = 3600;

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

$stad      = q_row('SELECT * FROM `stad` WHERE `stad` = ?', [$user['stad']]);
$wacht     = cooldown_left((int) $user['slaap_ts']);
$donateur  = is_premium($user);
$voorraad  = beschikbaar($stad, $donateur);
$prijs     = (int) ($stad['prijs'] ?? 0);
$betaalbaar = $prijs > 0 ? intdiv((int) $user['zak'], $prijs) : 0;

layout_header('Kogelfabriek');

if ($melding !== null) {
    notice(e($melding), $type);
}

panel_open('Kogelfabriek in ' . ($stad['stad'] ?? '?'));

if ($wacht > 0) {
    echo '<p>Je moet nog <strong data-tot="' . (time() + $wacht) . '">' . e(duration($wacht))
       . '</strong> wachten voordat je weer kogels kunt kopen.</p>';
} else {
    echo '<div class="tabelwikkel"><table class="lijst">';
    echo '<tr><th scope="row">Voorraad</th><td>' . num($voorraad) . ' kogels'
       . ($donateur ? ' <small>(dubbel, als donateur)</small>' : '') . '</td></tr>';
    echo '<tr><th scope="row">Prijs per kogel</th><td>' . money($prijs) . '</td></tr>';
    echo '<tr><th scope="row">Je hebt op zak</th><td>' . money((int) $user['zak']) . '</td></tr>';
    echo '<tr><th scope="row">Je kunt er kopen</th><td>' . num(min($voorraad, $betaalbaar)) . '</td></tr>';
    echo '</table></div>';

    $max = min($voorraad, $betaalbaar);

    if ($max < 1) {
        echo '<p>Er is niets te koop, of je hebt niet genoeg geld.</p>';
    } else {
        echo '<form method="post">' . csrf_field();
        echo '<div class="veldenraster">';
        echo '<label for="aantal">Aantal kogels</label>';
        echo '<input id="aantal" name="aantal" type="number" min="1" max="' . $max . '" step="1" required>';
        echo '<span></span><button type="submit">Kopen</button>';
        echo '</div></form>';
        echo '<p class="uitleg">Na een aankoop moet je ' . e(duration(KOGELS_WACHTTIJD))
           . ' wachten voor de volgende.</p>';
    }
}

panel_close();
layout_footer();

// ==========================================================================

/** Hoeveel kogels deze speler hier kan kopen. Donateurs krijgen er twee keer zoveel. */
function beschikbaar(?array $stad, bool $donateur): int
{
    $kogels = (int) ($stad['kogels'] ?? 0);
    return $donateur ? $kogels * 2 : $kogels;
}

/** @throws SpelFout */
function kopen(array $user, int $aantal): string
{
    if ($aantal < 1) {
        throw new SpelFout('Vul een aantal van minstens 1 in.');
    }

    return db_transaction(static function () use ($user, $aantal): string {
        $speler = lock_user((int) $user['id']);

        if (cooldown_left((int) q_val('SELECT UNIX_TIMESTAMP(`slaap`) FROM `users` WHERE `id` = ?',
                [$speler['id']], 0)) > 0) {
            throw new SpelFout('Je moet nog wachten voordat je weer kogels kunt kopen.');
        }

        $stad = q_row('SELECT * FROM `stad` WHERE `stad` = ? FOR UPDATE', [$speler['stad']]);

        if ($stad === null) {
            throw new SpelFout('Er is hier geen kogelfabriek.');
        }

        $donateur = is_premium($speler);

        if ($aantal > beschikbaar($stad, $donateur)) {
            throw new SpelFout('Zoveel kogels zijn er niet in voorraad.');
        }

        $prijs = $aantal * (int) $stad['prijs'];

        if (!afboeken((int) $speler['id'], $prijs, 'zak')) {
            throw new SpelFout('Dit kost ' . money($prijs) . ' en zoveel heb je niet op zak.');
        }

        // Donateurs putten de stadsvoorraad half zo snel uit; dat is de bonus.
        $uitVoorraad = $donateur ? (int) ceil($aantal / 2) : $aantal;

        q('UPDATE `users` SET `kogels` = `kogels` + ?, `slaap` = DATE_ADD(NOW(), INTERVAL ? SECOND)
            WHERE `id` = ?',
            [$aantal, KOGELS_WACHTTIJD, $speler['id']]);

        q('UPDATE `stad` SET `kogels` = GREATEST(0, `kogels` - ?) WHERE `stad` = ?',
            [$uitVoorraad, $speler['stad']]);

        return 'Je hebt ' . num($aantal) . ' kogels gekocht voor ' . money($prijs) . '.';
    });
}
