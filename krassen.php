<?php
  include("config.php");
$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");  
$data    = mysql_fetch_object($dbres);
  if(! check_login()) {
    header('Location: login.php');
    exit;
  }
if ($jisin == 1) { header('Location: jisin.php'); }
//if ($sisin == 1) { header('Location: sisin.php'); }
//if ($data->sl == 1) { header('Location: eisin.php'); }
//if ($spisin == 1) { header("Location: spisin.php"); }
?> 
<!DOCTYPE html>
<html lang="nl">
<head>
<title>Vendetta</title>
<link rel="stylesheet" type="text/css" href="style.css">
<meta name="keywords" content="Vendetta,Crimegame,crimegame,vendetta">
<meta name="language" content="english">
<META name="description" lang="nl" content="Vendetta crimegame met pit.">
</head>
<?php
$kras = mysql_fetch_object(mysql_query("SELECT * FROM `kras` WHERE `login`='$data->login'"));
if (!$kras) { mysql_query("INSERT INTO `kras`(`login`,`aantal`) values('$data->login','0')"); 
$kras = mysql_fetch_object(mysql_query("SELECT * FROM `kras` WHERE `login`='$data->login'")); }
print <<<ENDHTML
<table width=100%>
   <tr> 
    <td class="subTitle"><b>Krassen</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
ENDHTML;
if ($kras->aantal >= 10) { echo "Je kan elke dag maar 10 loten kopen."; exit; }
if ($_GET['scratch'] == yes) {
if ($data->zak < 10000) { echo "Je hebt niet genoeg geld op zak"; exit; }
else { 
mysql_query("UPDATE `kras` SET `aantal`=`aantal`+1 WHERE `login`='$data->login'");
$won = 0;
mysql_query("UPDATE `users` SET `zak`=`zak`-10000 WHERE `login`='$data->login'");
for ($i = 1; $i < 7; $i++) {
$prijzen = Array("","&euro;1.000.000","&euro;500.000","&euro;100.000","500 kogels","250 kogels","100 kogels","&euro;50.000","&euro;10.000","&euro;5000");
$r = rand(1,9);
$prijs[$i] = $prijzen[$r];
$p[$r] = ($p[$r] + 1);
}
if ($p[1] > 2) { echo "Je hebt $prijzen[1] gewonnen<br><br>"; mysql_query("UPDATE `users` SET `zak`=`zak`+1000000 WHERE `login`='$data->login'"); $won = 1; }
if ($p[2] > 2) { echo "Je hebt $prijzen[2] gewonnen<br><br>"; mysql_query("UPDATE `users` SET `zak`=`zak`+500000 WHERE `login`='$data->login'"); $won = 1; }
if ($p[3] > 2) { echo "Je hebt $prijzen[3] gewonnen<br><br>"; mysql_query("UPDATE `users` SET `zak`=`zak`+100000 WHERE `login`='$data->login'"); $won = 1; }
if ($p[4] > 2) { echo "Je hebt $prijzen[4] gewonnen<br><br>"; mysql_query("UPDATE `users` SET `kogels`=`kogels`+500 WHERE `login`='$data->login'"); $won = 1; }
if ($p[5] > 2) { echo "Je hebt $prijzen[5] gewonnen<br><br>"; mysql_query("UPDATE `users` SET `kogels`=`kogels`+250 WHERE `login`='$data->login'"); $won = 1; }
if ($p[6] > 2) { echo "Je hebt $prijzen[6] gewonnen<br><br>"; mysql_query("UPDATE `users` SET `kogels`=`kogels`+100 WHERE `login`='$data->login'"); $won = 1; }
if ($p[7] > 2) { echo "Je hebt $prijzen[7] gewonnen<br><br>"; mysql_query("UPDATE `users` SET `zak`=`zak`+50000 WHERE `login`='$data->login'"); $won = 1; }
if ($p[8] > 2) { echo "Je hebt $prijzen[8] gewonnen<br><br>"; mysql_query("UPDATE `users` SET `zak`=`zak`+10000 WHERE `login`='$data->login'"); $won = 1; }
if ($p[9] > 2) { echo "Je hebt $prijzen[9] gewonnen<br><br>"; mysql_query("UPDATE `users` SET `zak`=`zak`+5000 WHERE `login`='$data->login'"); $won = 1; }
if ($won == 0) { echo "Je hebt niks gewonnen<br><br>"; }
print <<<ENDHTML
<table border="1" width="25%" cellspacing="0" cellpadding="0" bordercolor="#000000" bgcolor="#339933" align=center>
	<tr>
		<td>
		<table border="0" width="75" height="45" cellspacing="0">
			<tr>
				<td>
				<p align="center">$prijs[1]</td>
			</tr>
		</table>
		</td>
		<td>
		<table border="0" width="75" height="45" cellspacing="0">
			<tr>
				<td>
				<p align="center">$prijs[2]</td>
			</tr>
		</table>
		</td>
		<td>
		<table border="0" width="75" height="45" cellspacing="0">
			<tr>
				<td>

				<p align="center">$prijs[3]</td>
			</tr>
		</table>
		</td>
	</tr>
	<tr>
		<td>
		<table border="0" width="75" height="45" cellspacing="0">
			<tr>
				<td>
				<p align="center">$prijs[4]</td>
			</tr>
		</table>
		</td>
		<td>
		<table border="0" width="75" height="45" cellspacing="0">
			<tr>
				<td>
				<p align="center">$prijs[5]</td>
			</tr>
		</table>
		</td>
		<td>
		<table border="0" width="75" height="45" cellspacing="0">
			<tr>
				<td>
				<p align="center">$prijs[6]</td>
			</tr>
		</table>
		</td>
	</tr>
</table>
ENDHTML;
echo "<br>Je hebt nog &euro;$data->zak <br><br><a href=krassen.php?scratch=yes>Speel nog een keer (&euro; 10.000)</a>";
exit;
}
}
else {
echo "<a href=krassen.php?scratch=yes>Klik hier om te krassen. (&euro; 10.000)</a>";
}
?>