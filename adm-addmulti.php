<?php

declare(strict_types=1);
require 'config.php';

$stmt = pdo_query(
    "SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,".
    "UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,".
    "UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,".
    "UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` " .
    "FROM `users` WHERE `login` = ?",
    [$_SESSION['login']]
);
$data = $stmt->fetch();
if (!$data || $data->level < 200) {
    exit;
}
?>
<html>
<head>
<title>Vendetta</title>
<link rel="stylesheet" type="text/css" href="style.css">
<meta name="keywords" content="Vendetta,Crimegame,crimegame,vendetta">
<meta name="language" content="english">
<META name="description" lang="nl" content="Vendetta crimegame met pit.">
</head>
<table width=100% border="0">
<tr>
    <td class="subTitle"><b>Multiple Accounts</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr>
    <td class="mainTxt"><a href=adm-search.php?p=multi>Multi Account Scan</a><br><br>
<?php
if (isset($_GET['del'])) {
    $delIp = $_GET['del'];
    $stmt = pdo_query("SELECT * FROM `multiple` WHERE `ip` = ?", [$delIp]);
    $ip = $stmt->fetch();
    if (!$ip) {
        echo "Dit ip adres staat niet in de lijst.";
    } else {
        pdo_query("UPDATE `iplog` SET `allo`='0' WHERE `ip` = ?", [$delIp]);
        pdo_query("DELETE FROM `multiple` WHERE `ip` = ?", [$delIp]);
    }
} elseif (isset($_POST['submit'])) {
    $newIp = $_POST['ip'] ?? '';
    $stmt = pdo_query("SELECT * FROM `multiple` WHERE `ip` = ?", [$newIp]);
    $ip = $stmt->fetch();
    if (!$newIp) {
        echo "Je moet een ip opgeven.";
    } elseif ($ip) {
        echo "Dit ip adres staat al in de lijst.";
    } else {
        pdo_query("INSERT INTO `multiple`(`ip`,`allo`) values(?, '1')", [$newIp]);
        pdo_query("DELETE FROM `iplog` WHERE `ip` = ?", [$newIp]);
        echo "Toegevoegd";
    }
}
print "<form method='post'>IP: <input type=text name=ip><br><br><input type=submit name=submit value='Voeg toe'></form>";
echo "De volgende ip addressen mogen meerdere accounts hebben:<br><br>";
$stmta = pdo_query("SELECT * FROM `multiple`");
while ($multi = $stmta->fetch()) {
    $ip = htmlspecialchars($multi->ip);
    echo "<a href=adm-addmulti.php?del=$ip>[Delete]</a> $ip<br>";
}
?>
</td></tr></table>
