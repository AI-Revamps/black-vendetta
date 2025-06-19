<?php
  include("config.php");
  $dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");
  $data	= mysql_fetch_object($dbres);  
  if(! check_login()) {
    header("Location: login.php");
    exit;
  }
if ($jisin == 1) { header('Location: jisin.php'); }
?>
<html>
<head>
<title>Vendetta</title>
<link rel="stylesheet" type="text/css" href="style.css">
<meta name="keywords" content="Vendetta,Crimegame,crimegame,vendetta">
<meta name="language" content="english">
<META name="description" lang="nl" content="Vendetta crimegame met pit.">
</head>
<?php
print <<<ENDHTML
<table width="100%" align=center>
<tr> 
    <td class="subTitle"><b>Kogelfabriek</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
<html>
<center>
ENDHTML;
$time = time();
$bwtime = ($time + 3600);
$btime = gmdate('i:s',($data->slaap - $time));
if ($data->slaap - $time > 0) { echo "Je moet nog $btime wachten voordat je weer kogels kan kopen."; exit; } 
$fabriek = mysql_fetch_object(mysql_query("SELECT * FROM `stad` WHERE `stad`='{$data->stad}'"));
$koop = floor($data->zak / $fabriek->prijs);
$akogels = ($data->paid == 1) ? ($fabriek->kogels*2) : $fabriek->kogels;
if($_SERVER['REQUEST_METHOD']=='POST' && preg_match('/^[0-9]+$/',$_POST['aantal'])) {
$antal = $_POST['aantal'];
$aantal = ($data->paid == 1) ? ($_POST['aantal'] / 2) : $_POST['aantal'];
$prijs = $antal*$fabriek->prijs;
if ($antal < 0) {
echo "Ongeldig aantal."; exit;
}
if ($prijs > $data->zak) {
echo "Je hebt niet genoeg geld op zak."; exit;
}
if ($aantal > $akogels) {
echo "Zoveel kogels zijn er niet."; exit;
}
	if (!Empty($aantal)) {
			mysql_query("UPDATE `users` SET `zak`=`zak`-{$prijs},`kogels`=`kogels`+{$antal},`slaap`=FROM_UNIXTIME($bwtime) WHERE `login`= '{$data->login}'") or die (mysql_error());
			mysql_query("UPDATE `stad` SET `kogels`=`kogels`-{$aantal} WHERE `stad`='{$data->stad}'") or die (mysql_error());
			
			echo "Je hebt $antal kogels gekocht voor &euro; {$prijs}."; exit;
			} 
	else { echo "Ongeldig aantal."; exit; }
	}

echo "
<br><br><br>

<form method='post'>
Er zijn $akogels kogels in deze kogelfabriek.<br>Je kan kogels kopen voor &euro; {$fabriek->prijs} per kogel.<br>Je hebt &euro;{$data->zak} op zak.<br>Je kan $koop kogels kopen voor &euro; {$fabriek->prijs} met het geld dat je bij hebt.<br><br>
	<input type='text' name='aantal' size='20' maxlength=3>
<br>
<br>
	<p><input type='submit' value='Kopen'></form></p>";
?>
</table></table>
