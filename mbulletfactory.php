<?php
  include("config.php");
$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");  
$data    = mysql_fetch_object($dbres);
  if(!check_login()) {
    header('Location: login.php');
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
<table width=100% align=center>
  <tr> 
    <td class="subTitle"><b>Lokale kogelfabriek</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
<?php
$time = time();
$bwtime = ($time + 3600);
$dbres = mysql_query("SELECT * FROM `casino` WHERE `spel`='kogelfabriek' AND `stad`='$data->stad'") or die (mysql_error());
$casino = mysql_fetch_object($dbres);
$exi = mysql_num_rows($dbres);
$dbres = mysql_query("SELECT * FROM `users` WHERE `login`='{$casino->owner}'") or die (mysql_error());
$eigenaar = mysql_fetch_object($dbres);
$user = strtolower($data->login);
$owner = strtolower($casino->owner);
$exist = mysql_num_rows(mysql_query("SELECT * FROM `casino` WHERE `owner`='$data->login'"));
if ($exi == 0) { echo "Er is een kogelfabriek gebouwd."; mysql_query("INSERT INTO `casino`(`spel`,`owner`,`stad`,`winst`) values('kogelfabriek','','$data->stad','')"); exit; }
elseif (isset($_POST['koop'])) {
if ($data->zak < 1000000) { echo "Je hebt niet genoeg geld op zak."; exit; }
elseif ($exist != 0) { echo "Je bezit al een object."; exit; }
else { 
mysql_query("UPDATE `users` SET `zak`=`zak`-1000000 WHERE `login`='{$data->login}'"); 
mysql_query("UPDATE `users` SET `bank`=`bank`+1000000 WHERE `login`='{$casino->owner}'"); 
mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$casino->owner}','Kogelfabriek','Je $casino->game in $casino->stad is verkocht.')");
mysql_query("UPDATE `casino` SET `owner`='{$data->login}',`winst`='100' WHERE `spel`='{$casino->spel}' AND `stad`='{$data->stad}'") or die (mysql_error());
echo "Je hebt $casino->spel gekocht."; exit;
} 
}
if (!$casino->owner) {
echo "Deze kogelfabriek heeft geen eigenaar. Je kan de fabriek kopen voor &euro;1.000.000.<br><br>
	<form method='post'>
	<input type='submit' name='koop' value='Koop'>
	</form>";
exit;
return;
}
elseif ($eigenaar->status == dood) {
mysql_query("UPDATE `casino` SET `owner`='' WHERE `stad`='{$data->stad}' AND `spel`='{$casino->spel}'");
echo "De eigenaar is overleden."; exit;
}
elseif($owner == $user){ 
if(isset($_POST['verander'])) {
$eigenaar = $_POST['eigenaar'];
$exis = mysql_query("SELECT * FROM `users` WHERE `login`='{$eigenaar}' AND `status`='levend'");
$exist = mysql_num_rows($exis);
$is = mysql_query("SELECT * FROM `casino` WHERE `owner`='{$eigenaar}' AND `stad`='$data->stad'");
$xist = mysql_num_rows($is);
if ($eigenaar == $data->login) { print "Je bezit deze fabriek al."; }
elseif ($exist == 0) {print "Deze gebruiker bestaat niet, of is dood."; }
elseif ($xist == 1) { echo "Deze gebruiker heeft al een object."; exit;}
else {
      mysql_query("UPDATE `casino` SET `owner`='{$eigenaar}',`winst`='100' WHERE `spel`='$casino->spel' AND `stad`='$data->stad'");
      print "De eigenaar van deze kogelfabriek is veranderd.
<META HTTP-EQUIV='refresh' CONTENT='0; URL=$PHP_SELF'>";
}
}
elseif(isset($_POST['VA'])){ 
if($_POST['inzet'] < 50){ 
print "De kogelprijs moet &euro;50 zijn."; 
} 
else {
$inzet    = $_POST['inzet']; 
mysql_query("UPDATE `casino` SET `inzet`='$inzet' WHERE `spel`='$casino->spel' AND `stad`='$data->stad'"); 
print "De kogelprijs is veranderd naar &euro;$inzet."; 
} 
}
elseif(isset($_POST['buybullets'])){ 
if(preg_match('/^[0-9]+$/', $_POST['nrofbullets']) == 0) { print "Ongeldig aantal kogels.\n";exit; }
$aantal = $_POST['nrofbullets'];
$prijs = $aantal*100;
if($prijs > $data->zak){ 
print "Je hebt niet genoeg geld op zak."; 
} 
else {
$bijgekocht   = $aantal; 
mysql_query("UPDATE `users` SET `zak`=`zak`-{$prijs} WHERE `login`='{$data->login}'");
mysql_query("UPDATE `casino` SET `winst`=`winst`+{$aantal} WHERE `spel`='$casino->spel' AND `stad`='$data->stad'"); 
print "Je hebt $aantal kogels bijgekocht voor $prijs."; 
} 
}
print "<form method='post'>
	  <width=100>Nieuwe eigenaar:		<input type='text' name='eigenaar' maxlength=16>
  				<align='right'><input type='submit' name='verander' value='Verander'>
</form><br><br><form method='POST'> 
    <p>De kogelprijs is nu &euro;$casino->inzet.</p> 
    <p>&nbsp;Kogelprijs:<input type='text' name='inzet' size='20' maxlength=7></p> 
    <p><input type='submit' value='Verander' name='VA'></p> 
</form><br><br><form method='POST'> 
	<p>Er zijn momenteel $casino->winst kogels.</p>
    <p>&nbsp;Koop kogels bij (&euro;100 per stuk):<input type='text' name='nrofbullets' size='20' maxlength=7></p> 
    <p><input type='submit' value='Verander' name='buybullets'></p> 
</form>";
exit;
} 
elseif($_POST['submit']) {
	$inzet = $_POST['inzet'];
	$prijs = $casino->inzet;
	$winst = ($inzet * $prijs);
	$win = round($winst/2);
	if ($inzet > $casino->winst || $inzet < 1) {
	echo "Zoveel kogels zijn er niet.";
	}
	elseif($data->zak < $winst) {
		echo "Je hebt niet genoeg geld op zak.";
	} else {
			echo "Je hebt $inzet kogels gekocht voor &euro; $winst.";
			mysql_query("UPDATE `users` SET `zak`=`zak`-$winst,`kogels`=`kogels`+$inzet,`slaap`=FROM_UNIXTIME($bwtime) WHERE `login`='{$data->login}'") or die (mysql_error());
			mysql_query("UPDATE `users` SET `bank`=`bank`+$win WHERE `login`='{$casino->owner}'") or die (mysql_error());
			mysql_query("UPDATE `casino` set `winst`=`winst`-$inzet WHERE `spel`='$casino->spel' AND `stad`='$data->stad'") or die (mysql_error());
		
		 }
} else {
$btime = gmdate('i:s',($data->slaap - $time));
if ($data->slaap - $time > 0) { echo "Je moet nog $btime wachten voordat je weer kogels kan kopen."; exit; }
	$koop = floor($data->zak / $casino->inzet);
echo "$casino->owner heeft de kogelprijs gezet op &euro;$casino->inzet.<br><br>Er zijn in deze kogelfabriek {$casino->winst} kogels<br>Je kan $koop kogels kopen voor &euro; {$casino->inzet} met het geld dat je bij hebt.<br><BR>
	<form method='post'>
	Aantal kogels: <input type='text' name='inzet' size='3' maxlength='7'><br><br>
	<input type='submit' name='submit' value='Koop'>
	</form>";
}
?></font></center></body></html>