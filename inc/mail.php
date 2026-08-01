<?php
/**
 * Systeemmail versturen via de standaard mail() van PHP.
 *
 * Op shared hosting is dat vrijwel altijd beschikbaar. Let op: gebruik als
 * afzender een adres op je eigen domein, anders weigeren Gmail en Outlook de
 * mail of zetten hem in de spam.
 */

declare(strict_types=1);

defined('BV_INC') || exit;

/**
 * Verstuur een bericht in platte tekst.
 *
 * @return bool False als de host de mail niet aannam. Log dat, maar laat het
 *              nooit de registratie blokkeren: het account bestaat dan al.
 */
function send_mail(string $naar, string $onderwerp, string $bericht): bool
{
    if (!filter_var($naar, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $van     = (string) config('site.mail_from', '');
    $vanNaam = (string) config('site.mail_from_name', config('site.name', ''));

    if ($van === '') {
        error_log('[mail] geen afzender ingesteld in inc/config.php');
        return false;
    }

    // Nieuweregels in de kop zouden een aanvaller extra headers laten injecteren.
    $onderwerp = str_replace(["\r", "\n"], '', $onderwerp);
    $vanNaam   = str_replace(['"', "\r", "\n"], '', $vanNaam);

    $headers = [
        'From: "' . $vanNaam . '" <' . $van . '>',
        'Reply-To: ' . $van,
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        'MIME-Version: 1.0',
        'X-Mailer: PHP/' . PHP_VERSION,
    ];

    // Regels afbreken op 70 tekens, zoals de mailstandaard voorschrijft.
    $bericht = wordwrap(str_replace("\r\n", "\n", $bericht), 70, "\r\n");

    $ok = @mail(
        $naar,
        '=?UTF-8?B?' . base64_encode($onderwerp) . '?=',
        $bericht,
        implode("\r\n", $headers),
        '-f' . $van
    );

    if (!$ok) {
        error_log("[mail] versturen naar {$naar} mislukt ({$onderwerp})");
    }
    return $ok;
}

/**
 * Maak een eenmalige code en zet hem klaar in de tabel `temp`.
 * Geeft de code terug; alleen de ontvanger van de mail kent hem.
 */
function make_token(string $login, string $area, string $forwardedFor = ''): array
{
    // 32 willekeurige bytes: niet te raden, in tegenstelling tot de rand()
    // van zes cijfers die de oude code gebruikte.
    $code = bin2hex(random_bytes(32));

    q(
        'INSERT INTO `temp` (`time`, `login`, `ip`, `forwardedFor`, `code`, `area`)
              VALUES (NOW(), ?, ?, ?, ?, ?)',
        [$login, client_ip(), $forwardedFor, $code, $area]
    );

    return ['id' => db_last_id(), 'code' => $code];
}

/**
 * Zoek een eenmalige code op en verwijder hem meteen, zodat hij niet twee keer
 * gebruikt kan worden. Codes ouder dan $geldigUren tellen niet meer mee.
 */
function take_token(int $id, string $code, string $area, int $geldigUren = 48): ?array
{
    $rij = q_row(
        'SELECT * FROM `temp`
          WHERE `id` = ? AND `area` = ? AND `time` > DATE_SUB(NOW(), INTERVAL ? HOUR)',
        [$id, $area, $geldigUren]
    );

    // hash_equals vergelijkt in constante tijd, zodat de code niet teken voor
    // teken te raden is aan de hand van de responstijd.
    if ($rij === null || !hash_equals((string) $rij['code'], $code)) {
        return null;
    }

    q('DELETE FROM `temp` WHERE `id` = ?', [$id]);
    return $rij;
}
