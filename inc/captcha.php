<?php
/**
 * Beveiligingscode ("captcha") bij handelingen die anders makkelijk te
 * automatiseren zijn, zoals het plegen van misdaden.
 *
 * De oude versie was één teken lang en bleef in de sessie staan tot een nieuw
 * plaatje opgevraagd werd — dezelfde code werkte dus eindeloos. Deze versie:
 *
 *   - vijf tekens, zonder makkelijk te verwarren letters (0/O, 1/I/L)
 *   - wordt bij controle meteen verbruikt, dus elke code werkt één keer
 *   - verloopt na tien minuten
 *   - wordt in de sessie gezet vóórdat het plaatje verstuurd wordt
 */

declare(strict_types=1);

defined('BV_INC') || exit;

const CAPTCHA_TEKENS  = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
const CAPTCHA_LENGTE  = 5;
const CAPTCHA_GELDIG  = 600;   // seconden

/** Maak een nieuwe code, sla hem op in de sessie en geef hem terug. */
function captcha_new(): string
{
    $code = '';
    $max  = strlen(CAPTCHA_TEKENS) - 1;

    for ($i = 0; $i < CAPTCHA_LENGTE; $i++) {
        $code .= CAPTCHA_TEKENS[random_int(0, $max)];
    }

    $_SESSION['_captcha'] = ['code' => $code, 'tijd' => time()];
    return $code;
}

/**
 * Controleer wat de speler intypte. De code wordt hoe dan ook verbruikt, ook
 * bij een fout antwoord — anders kan er eindeloos geraden worden.
 */
function captcha_check(string $invoer): bool
{
    $bewaard = $_SESSION['_captcha'] ?? null;
    unset($_SESSION['_captcha']);

    if (!is_array($bewaard) || time() - (int) $bewaard['tijd'] > CAPTCHA_GELDIG) {
        return false;
    }

    return hash_equals(
        strtoupper((string) $bewaard['code']),
        strtoupper(trim($invoer))
    );
}

/** Is er beeldondersteuning? Zo niet, dan tonen we een rekensom in plaats van een plaatje. */
function captcha_beschikbaar(): bool
{
    return function_exists('imagecreatetruecolor') && function_exists('imagepng');
}

/**
 * Invoerveld met plaatje, klaar om in een formulier te zetten.
 * De `t`-parameter zorgt dat de browser het plaatje niet uit zijn cache haalt.
 */
function captcha_field(): string
{
    if (!captcha_beschikbaar()) {
        return captcha_field_tekst();
    }

    $bron = e(url('img.php')) . '?t=' . time() . '.' . random_int(1000, 9999);

    return '<div class="captcha">'
         . '<img src="' . $bron . '" width="150" height="50" alt="Beveiligingscode">'
         . '<label for="verify">Typ de code over</label>'
         . '<input id="verify" name="verify" maxlength="' . CAPTCHA_LENGTE . '" size="8" '
         . 'required autocomplete="off" spellcheck="false" inputmode="text">'
         . '</div>';
}

/**
 * Terugvaloptie zonder de gd-uitbreiding: een eenvoudige rekensom.
 * Minder sterk dan een plaatje, maar houdt simpele scripts wel tegen.
 */
function captcha_field_tekst(): string
{
    $a = random_int(2, 9);
    $b = random_int(2, 9);

    $_SESSION['_captcha'] = ['code' => (string) ($a + $b), 'tijd' => time()];

    return '<div class="captcha">'
         . '<label for="verify">Hoeveel is ' . $a . ' + ' . $b . '?</label>'
         . '<input id="verify" name="verify" maxlength="3" size="5" required autocomplete="off" inputmode="numeric">'
         . '</div>';
}
