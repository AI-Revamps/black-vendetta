<?php
/**
 * Routerscript voor de ingebouwde PHP-server.
 *
 *     php -S 127.0.0.1:8149 -t . tests/router.php
 *
 * De ingebouwde server leest geen .htaccess. Dit script bootst de enige regel
 * na die er voor het spel toe doet: bestaat /iets niet als bestand of map, maar
 * /iets.php wel, laad dan dat bestand. Daarmee is de instelling mooie_urls
 * lokaal te testen zonder Apache.
 *
 * Op de webhost doet Apache dit; daar wordt dit bestand nooit gebruikt.
 */

declare(strict_types=1);

$wortel = dirname(__DIR__);
$pad    = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$vol    = $wortel . $pad;

// De voorpagina, en alles wat gewoon bestaat: daar doet de server zelf goed aan.
if ($pad === '/' || is_file($vol) || is_dir($vol)) {
    return false;
}

$metPhp = $wortel . rtrim($pad, '/') . '.php';

if (is_file($metPhp)) {
    // Zonder deze twee denkt de pagina dat ze router.php heet, en dan wijst
    // current_page() het verkeerde menu-item aan.
    $_SERVER['SCRIPT_NAME']     = rtrim($pad, '/') . '.php';
    $_SERVER['SCRIPT_FILENAME'] = $metPhp;

    require $metPhp;

    return true;
}

// Hier wijkt de ingebouwde server af van Apache: hij valt voor een onbekend
// adres terug op index.php, waardoor een typefout in een link er als een
// werkende pagina uitziet. Apache geeft een 404, dus die geven wij ook.
http_response_code(404);
header('Content-Type: text/html; charset=utf-8');
echo '<!doctype html><html lang="nl"><meta charset="utf-8">',
     '<title>Niet gevonden</title><p>Deze pagina bestaat niet.';

return true;
