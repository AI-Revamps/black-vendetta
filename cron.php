<?php
/**
 * Periodieke spelupdates aanroepen.
 *
 * Twee manieren om dit te draaien:
 *
 * 1. Als cronjob bij je webhost (aanbevolen als je host dat aanbiedt).
 *    Zet in het configuratiescherm een taak die elke minuut draait:
 *
 *        php /home/JOUW_ACCOUNT/public_html/cron.php
 *
 *    Zet daarna 'cron_mode' in inc/config.php op 'cron'.
 *
 * 2. Via een externe cron-dienst die een URL opvraagt:
 *
 *        https://jouwdomein.nl/cron.php?key=JOUW_CRON_KEY
 *
 *    De sleutel staat als 'cron_key' in inc/config.php.
 *
 * Doe je niets, dan blijft 'cron_mode' op 'request' staan en liften de taken
 * mee op gewone paginabezoeken. Dat werkt op elke host.
 */

declare(strict_types=1);

// Zegt tegen de bootstrap dat hij de taken niet zelf moet starten: dat doen
// we hieronder, zodat de melding klopt met wat er werkelijk gedraaid heeft.
define('BV_CRON_ENDPOINT', true);

require __DIR__ . '/inc/bootstrap.php';

$viaCli = PHP_SAPI === 'cli';

// Via de browser is een geldige sleutel verplicht.
if (!$viaCli) {
    $sleutel = (string) config('cron_key', '');
    if ($sleutel === '' || $sleutel === 'verander-dit-in-iets-willekeurigs') {
        http_response_code(403);
        exit("Stel eerst een eigen 'cron_key' in in inc/config.php.\n");
    }
    if (!hash_equals($sleutel, get('key'))) {
        http_response_code(403);
        exit("Ongeldige sleutel.\n");
    }
    header('Content-Type: text/plain; charset=utf-8');
}

$gedraaid = cron_run(true);

echo $gedraaid === []
    ? "Geen taken aan de beurt.\n"
    : 'Uitgevoerd: ' . implode(', ', $gedraaid) . "\n";
