<?php /* ------------------------- */
  include("config.php");
  $dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");
  $data	= mysql_fetch_object($dbres);  
  if(! check_login()) {
    header("Location: login.php");
    exit;
  }

if ($jisin == 1) { header("Location: jisin.php"); }
?>
<html>
<head>
<title>Vendetta</title>
<link rel="stylesheet" type="text/css" href="style.css">
<meta name="keywords" content="Vendetta,Crimegame,crimegame,vendetta">
<meta name="language" content="english">
<META name="description" lang="nl" content="Vendetta crimegame met pit.">
</head>
<?PHP
print <<<ENDHTML
<table width="100%" align=center>
<tr> 
    <td class="subTitle"><b>Bloedbank</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
<center>
<html>
ENDHTML;
if($_POST['submit'] && preg_match('/^[0-9]+$/',$_POST['aantal'])) {
$aantal = $_POST['aantal'];
$prijs = $aantal*1000;
$nhealth = $data->health+$aantal;

if ($nhealth > 100) {
echo "Je kan niet meer dan 100% health hebben.";
return;
}
if ($aantal < 0) {
echo "Je kan niet meer dan 100% health hebben.";
return;
}
if ($prijs > $data->zak) {
echo "Je hebt niet genoeg geld op zak.";
return;
}
	if (!Empty($aantal)) {
			mysql_query("UPDATE `users` SET `zak`=`zak`-{$prijs} WHERE `login`= '{$data->login}'") or die (mysql_error());
			mysql_query("UPDATE `users` SET `health`=`health` +{$aantal} WHERE `login` = '{$data->login}'") or die (mysql_error());
			echo "Je hebt $aantal bloedzakjes gekocht voor &euro; {$prijs}. Je hebt nu {$nhealth}% health"; exit;
			} 
	else { echo "Je moet een aantal bloedzakjes invullen."; exit; }
	}
print <<<ENDHTML
<br><br><br>
Je hebt momenteel $data->health health.
<form method='post'>
	Koop hier bloedzakjes voor &euro; 1000 per stuk.<br><br>
	<input type='text' name='aantal' value='' size='20' maxlength=3>
<br>
<br>
	<p><input type='submit' value='Koop' name=submit></p>
ENDHTML;
?>
</table></table>