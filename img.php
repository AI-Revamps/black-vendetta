<?php
/**
 * Tekent de beveiligingscode als plaatje.
 *
 * De code wordt door captcha_new() in de sessie gezet vóórdat er iets
 * verstuurd wordt. In de oude versie gebeurde dat ná imagepng(), waardoor de
 * volgorde niet gegarandeerd was.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';
require BV_INC . '/captcha.php';

if (!captcha_beschikbaar()) {
    http_response_code(404);
    exit;
}

$code = captcha_new();

$breedte = 150;
$hoogte  = 50;

$im = imagecreatetruecolor($breedte, $hoogte);

$achtergrond = imagecolorallocate($im, 20, 20, 20);
$rand        = imagecolorallocate($im, 90, 60, 10);
imagefilledrectangle($im, 0, 0, $breedte, $hoogte, $achtergrond);
imagerectangle($im, 0, 0, $breedte - 1, $hoogte - 1, $rand);

// Ruis: streepjes en stipjes maken het lastiger om de code automatisch te lezen.
for ($i = 0; $i < 6; $i++) {
    $ruis = imagecolorallocate($im, random_int(40, 80), random_int(30, 60), random_int(10, 30));
    imageline($im, random_int(0, $breedte), random_int(0, $hoogte),
                   random_int(0, $breedte), random_int(0, $hoogte), $ruis);
}
for ($i = 0; $i < 120; $i++) {
    $stip = imagecolorallocate($im, random_int(50, 110), random_int(40, 90), random_int(10, 40));
    imagesetpixel($im, random_int(0, $breedte - 1), random_int(0, $hoogte - 1), $stip);
}

// Tekens één voor één plaatsen, elk met een eigen kleur en hoogte.
$lengte  = strlen($code);
$stap    = (int) (($breedte - 20) / max(1, $lengte));
$xstart  = 12;

for ($i = 0; $i < $lengte; $i++) {
    $kleur = imagecolorallocate($im, random_int(200, 255), random_int(150, 210), random_int(40, 90));
    $x     = $xstart + $i * $stap;
    $y     = random_int(12, 26);

    imagestring($im, 5, $x, $y, $code[$i], $kleur);

    // Dubbele afdruk met een pixel verschuiving maakt de letters wat dikker.
    imagestring($im, 5, $x + 1, $y, $code[$i], $kleur);
}

// Golfje over het hele plaatje.
if (function_exists('imagefilter')) {
    imagefilter($im, IMG_FILTER_SMOOTH, 4);
}

header('Content-Type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

imagepng($im);
imagedestroy($im);
