<?php
include("config.php");
  $dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");
  $data	= mysql_fetch_object($dbres);
if ($data->level < 200) {exit; }
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
$ip = mysql_fetch_object(mysql_query("SELECT * FROM `multiple` WHERE `ip`='{$_GET['del']}'"));
if ($ip->ip != $_GET['del']) { echo "Dit ip adres staat niet in de lijst."; }
else { mysql_query("UPDATE `iplog` SET `allo`='0' WHERE `ip`='{$_GET['del']}'"); mysql_query("DELETE FROM `multiple` WHERE `ip`='{$_GET['del']}'"); }
}
elseif ($_POST['submit']) {
$ip = mysql_fetch_object(mysql_query("SELECT * FROM `multiple` WHERE `ip`='{$_POST['ip']}'"));
if (!$_POST['ip']) { echo "Je moet een ip opgeven."; }
elseif ($ip->ip == $_POST['ip']) { echo "Dit ip adres staat al in de lijst."; }
else { mysql_query("INSERT INTO `multiple`(`ip`,`allo`) values('{$_POST['ip']}','1')"); mysql_query("DELETE FROM `iplog` WHERE `ip`='{$_POST['ip']}'"); echo "Toegevoegd"; }
}
print "<form method='post'>IP: <input type=text name=ip><br><br><input type=submit name=submit value='Voeg toe'></form>";
echo "De volgende ip addressen mogen meerdere accounts hebben:<br><br>";
$dbres = mysql_query("SELECT * FROM `multiple`");
while ($multi = mysql_fetch_object($dbres)) {
echo "<a href=adm-addmulti.php?del=$multi->ip>[Delete]</a> $multi->ip<br>";
}
?>
</td></tr></table>