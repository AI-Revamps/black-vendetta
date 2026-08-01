<?php
/**
 * Bank: geld storten en opnemen.
 *
 * De oude versie las het saldo uit, rekende in PHP en schreef het resultaat
 * terug. Twee gelijktijdige opnames zagen dan allebei hetzelfde saldo en
 * boekten allebei af — waarmee geld te verdubbelen was. Hier zit de
 * voorwaarde in de UPDATE zelf, binnen een transactie.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

$user = require_login();

if (is_dead()) {
    redirect('rip.php');
}
block_if_jailed();

$melding = null;
$type    = 'info';

if (is_post()) {
    csrf_check();

    $bedrag  = int_input('amount', 0, 0);
    $richting = isset($_POST['in']) ? 'in' : (isset($_POST['out']) ? 'out' : '');

    try {
        if ($bedrag < 1) {
            throw new SpelFout('Vul een bedrag van minstens 1 in.');
        }

        db_transaction(static function () use ($user, $bedrag, $richting) {
            lock_user((int) $user['id']);

            if ($richting === 'in') {
                if (!afboeken((int) $user['id'], $bedrag, 'zak')) {
                    throw new SpelFout('Zoveel geld heb je niet op zak.');
                }
                bijschrijven((int) $user['id'], $bedrag, 'bank');
            } elseif ($richting === 'out') {
                if (!afboeken((int) $user['id'], $bedrag, 'bank')) {
                    throw new SpelFout('Zoveel geld staat er niet op de bank.');
                }
                bijschrijven((int) $user['id'], $bedrag, 'zak');
            } else {
                throw new SpelFout('Kies of je geld wilt storten of opnemen.');
            }
        });

        $melding = $richting === 'in'
            ? 'Je hebt ' . money($bedrag) . ' gestort.'
            : 'Je hebt ' . money($bedrag) . ' opgenomen.';
        $type = 'ok';

        $user = current_user(true);
    } catch (SpelFout $e) {
        $melding = $e->getMessage();
        $type    = 'fout';
    }
}

layout_header('Bank');
panel_open('Bank');

if ($melding !== null) {
    notice(e($melding), $type);
}

echo '<div class="tabelwikkel"><table class="lijst">';
echo '<tr><th scope="row">Contant op zak</th><td class="getal">' . money((int) $user['zak']) . '</td></tr>';
echo '<tr><th scope="row">Op de bank</th><td class="getal">' . money((int) $user['bank']) . '</td></tr>';
echo '<tr><th scope="row">Totaal</th><td class="getal"><strong>'
   . money((int) $user['zak'] + (int) $user['bank']) . '</strong></td></tr>';
echo '</table></div>';

echo '<form method="post">' . csrf_field();
echo '<div class="veldenraster">';
echo '<label for="amount">Bedrag</label>';
echo '<input id="amount" name="amount" type="number" min="1" step="1" required inputmode="numeric">';
echo '<span></span><div><button type="submit" name="in" value="1">Storten</button> '
   . '<button type="submit" name="out" value="1">Opnemen</button></div>';
echo '</div></form>';

panel_close();
layout_footer();
