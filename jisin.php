<?php
/**
 * Je zit vast: wat je nog kunt doen vanuit de cel.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

$user = require_login();

if (is_dead()) {
    redirect('rip.php');
}

$cel = jail_status($user['login']);

layout_header('Gevangenis');
panel_open('Gevangenis');

if ($cel === null) {
    notice('Je bent vrij.', 'ok');
    echo '<p><a class="knop" href="' . e(url('home.php')) . '">Terug naar het spel</a></p>';
} else {
    $boete = (int) $cel['boete'];

    echo '<p>Je zit vast in ' . e((string) $cel['stad']) . '. Nog '
       . '<strong data-tot="' . (time() + $cel['resterend']) . '">'
       . e(duration($cel['resterend'])) . '</strong> te gaan.</p>';

    echo '<ul>';
    if ((int) $cel['bo'] < 2) {
        echo '<li><a href="' . e(url('jail.php?bo=yes')) . '">Probeer te ontsnappen</a></li>';
    }
    echo '<li><a href="' . e(url('jail.php?x=' . rawurlencode((string) $user['login']))) . '">'
       . 'Koop jezelf vrij voor ' . money($boete) . '</a></li>';
    echo '<li><a href="' . e(url('jail.php')) . '">Bekijk wie er nog meer vastzit</a></li>';
    echo '</ul>';
}

panel_close();
layout_footer();
