<?php

declare(strict_types=1);
require 'config.php';

$stmt = pdo_query(
    "SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`," .
    "UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`," .
    "UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`," .
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
<table width=100%>
<tr>
    <td class="subTitle"><b>Bannen</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr>
    <td class="mainTxt">
<?php
if ($data->level < 255) { print "Je hebt niet voldoende rechten.";exit; }
$ip = $_POST['ip'] ?? '';
$reden = $_POST['reden'] ?? '';
if (!$reden) { $reden = 'Geen'; }
$stmt = pdo_query("SELECT * FROM `bans` WHERE `ip` = ?", [$ip]);
$exist = $stmt->fetch();
if (isset($_POST['submit'])) {
    if (!$ip) { echo "Je moet een ip invullen."; exit; }
    if ($_POST['optie'] == 'ban') {
        if ($exist && $exist->ip == $ip) { echo "Dit ip adres is al verbannen.";exit; }
        else {
            pdo_query("INSERT INTO `bans`(`ip`,`reden`,`door`) values(?,?,?)", [$ip,$reden,$data->login]);
            echo "Dit ip adres is verbannen.";exit;
        }
    } elseif ($_POST['optie'] == 'unban') {
        if (!$exist) { echo "Dit ip adres is niet verbannen."; exit; }
        else {
            pdo_query("DELETE FROM `bans` WHERE `ip` = ?", [$ip]);
            echo "De ban op dit ip is opgehoffen"; exit;
        }
    }
}
print "<form method=post><input type=text name=ip> IP<br><input type=text name=reden> Reden<br><br><input type=radio value=ban name=optie>Ban<br><input type=radio value=unban name=optie>Unban<br><br><input type=submit name=submit value=Ok></form>";
print <<<ENDHTML
<table width=100%><tr>    <td align=center><b>IP</b></td>
      <td align=center><b>Reden</b></td>
          <td align=center><b>Door</b></td>
            </tr>

ENDHTML;
$query = "SELECT `ip`,`reden`,`door` FROM `bans` ORDER BY `door` ASC";
$info = pdo_query($query);
$count = 0;
while ($gegeven = $info->fetch()) {
$ip = htmlspecialchars($gegeven->ip);
$reden = htmlspecialchars($gegeven->reden);
$door = htmlspecialchars($gegeven->door);
$count++;
print <<<ENDHTML

<tr>            <td align=center>{$ip}</td>
                <td align=center>{$reden}</td>
                        <td align=center>{$door}</td>
</tr>
ENDHTML;
}
?>
