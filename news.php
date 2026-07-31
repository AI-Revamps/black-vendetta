<?php
/**
 * Nieuwsarchief.
 *
 * Wat hier gerepareerd is: het totaal werd geteld met `SELECT id FROM news`
 * gevolgd door mysql_num_rows() — alle rijen ophalen om alleen het aantal te
 * weten. De pagineerlus liep bovendien over `$rows/$pp` zonder afronding,
 * waardoor de laatste pagina bij een niet-rond aantal ontbrak. De nieuwstekst
 * ging ongefilterd naar de browser.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';
require BV_INC . '/opmaak.php';

const PER_PAGINA = 10;

$user = require_login();

$totaal  = (int) q_val('SELECT COUNT(*) FROM `news`', [], 0);
$paginas = max(1, (int) ceil($totaal / PER_PAGINA));
$pagina  = min(int_input('p', 0, 0), $paginas - 1);

$berichten = q_all(
    'SELECT * FROM `news` ORDER BY `time` DESC LIMIT ' . PER_PAGINA
    . ' OFFSET ' . ($pagina * PER_PAGINA)
);

layout_header('Nieuws');

panel_open('Nieuws');

if ($berichten === []) {
    echo '<p>Er is nog geen nieuws.</p>';
}

foreach ($berichten as $bericht) {
    echo '<article class="nieuwsbericht">';
    echo '<h3>' . e((string) $bericht['title']) . '</h3>';
    echo '<p class="klein">' . e(timestamp_nl((int) $bericht['time'])) . '</p>';
    echo '<div>' . bericht_html((string) $bericht['text']) . '</div>';
    echo '</article>';
}

if ($paginas > 1) {
    echo '<p class="paginering">';
    for ($i = 0; $i < $paginas; $i++) {
        echo $i === $pagina
            ? '<strong>' . ($i + 1) . '</strong> '
            : '<a href="' . e(url('news.php?p=' . $i)) . '">' . ($i + 1) . '</a> ';
    }
    echo '</p>';
}

panel_close();
layout_footer();
