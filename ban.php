<?php
declare(strict_types=1);

$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
$host = gethostbyaddr($remoteAddr);
$parts = explode('.', $host);
$tld = strtolower(end($parts));

if (!in_array($tld, ['be', 'nl'], true)) {
    echo 'Proxys zijn niet toegelaten.';
    exit;
}

?>
