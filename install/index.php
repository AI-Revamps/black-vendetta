<?php
/**
 * Voordeur van de installatie.
 *
 * Dit bestand is met opzet in ouderwets PHP geschreven: geen match, geen
 * pijlfuncties, geen typering. Reden: PHP leest een heel bestand voordat het
 * ook maar een regel uitvoert. Staat er syntax in die de server niet kent, dan
 * krijg je een 500 en zie je nooit waarom.
 *
 * Juist als je host een te oude PHP-versie draait wil je dat wel weten. Dus
 * doet dit bestand alleen de versiecontrole, en pas als die slaagt wordt de
 * echte installer (setup.php) ingeladen.
 *
 * Verander hier dus niets naar moderne schrijfwijze.
 */

define('BV_INSTALLER', true);

if (version_compare(PHP_VERSION, '8.1.0', '<')) {
    header('Content-Type: text/html; charset=utf-8');
    http_response_code(500);

    $nu = htmlspecialchars(PHP_VERSION, ENT_QUOTES, 'UTF-8');

    echo '<!doctype html><html lang="nl"><head><meta charset="utf-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>PHP te oud</title><style>'
       . 'body{margin:0;padding:2rem 1rem;background:#0d0d0d;color:#d8d8d8;'
       . 'font:15px/1.6 "Segoe UI",Roboto,system-ui,sans-serif}'
       . '.kader{max-width:44rem;margin:0 auto;background:#1a1a1a;border:1px solid #383838;'
       . 'border-radius:6px;padding:1.5rem 2rem}'
       . 'h1{margin:0 0 .5rem;color:#ff9900;font-size:1.3rem}'
       . 'code{background:#000;padding:.1rem .35rem;border-radius:3px}'
       . 'ol{padding-left:1.2rem}li{margin:.4rem 0}'
       . '</style></head><body><div class="kader">'
       . '<h1>PHP is te oud</h1>'
       . '<p>Dit spel heeft <strong>PHP 8.1 of nieuwer</strong> nodig. '
       . 'Deze server draait <strong>' . $nu . '</strong>.</p>'
       . '<p>Op vrijwel elke webhost stel je dat zelf in:</p>'
       . '<ol>'
       . '<li>Log in op het configuratiescherm van je host (cPanel, DirectAdmin '
       . 'of het eigen scherm van je provider).</li>'
       . '<li>Zoek naar <em>PHP-versie</em>, <em>PHP Selector</em> of '
       . '<em>Select PHP Version</em>.</li>'
       . '<li>Kies 8.1 of hoger en sla op.</li>'
       . '<li>Laad deze pagina opnieuw.</li>'
       . '</ol>'
       . '<p>Zie je deze melding nog steeds? Dan geldt de instelling misschien '
       . 'alleen voor een ander domein of een andere map. Vraag je host er dan naar.</p>'
       . '</div></body></html>';
    exit;
}

require __DIR__ . '/setup.php';
