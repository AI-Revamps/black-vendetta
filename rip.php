<?php
/**
 * Je bent vermoord. Hier eindigt het spel voor dit account.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

$user = require_login();

// Gezondheid op nul betekent alsnog het einde.
if (!is_dead() && (int) $user['health'] < 1) {
    q("UPDATE `users` SET `status` = 'dood' WHERE `id` = ?", [$user['id']]);
    $user = current_user(true);
}

if (!is_dead()) {
    redirect('home.php');
}

$moord = q_row(
    'SELECT * FROM `vermoord` WHERE `login` = ? ORDER BY `date` DESC LIMIT 1',
    [$user['login']]
);

layout_header('Rust in vrede');
panel_open('Rust in vrede');

echo '<p><img src="' . e(url('images/rip.gif')) . '" alt="" style="float:right;margin-left:1rem">';
echo '<strong>' . e((string) $user['login']) . '</strong> is niet meer. Je bent omgelegd.</p>';

if ($moord !== null) {
    echo '<p>Vermoord op ' . e(datetime_nl($moord['date']));
    if (($moord['dader'] ?? '') !== '') {
        echo ' door <strong>' . e((string) $moord['dader']) . '</strong>';
    }
    echo '.</p>';

    // Het bericht van de dader is tekst van een speler: eerst escapen, dan pas
    // de regelovergangen omzetten.
    if (($moord['msg'] ?? '') !== '') {
        echo '<p>Bericht van de dader:</p><blockquote>'
           . nl2br(e((string) $moord['msg'])) . '</blockquote>';
    }
}

if (($user['testament'] ?? '') !== '') {
    echo '<p>Je bezit is nagelaten aan ' . e((string) $user['testament']) . '.</p>';
}

echo '<p style="clear:both">Je kunt opnieuw beginnen met een nieuw account.</p>';
echo '<p><a class="knop" href="' . e(url('register.php')) . '">Nieuw account maken</a> '
   . '<a class="knop" href="' . e(url('wallofshame.php')) . '">Schandpaal</a></p>';

echo '<form method="post" action="' . e(url('logout.php')) . '" style="margin-top:1rem">'
   . csrf_field() . '<button type="submit">Uitloggen</button></form>';

panel_close();
layout_footer();
