<?php 
include("config.php");
  $dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");
  $data	= mysql_fetch_object($dbres);
if(! check_login()) {
	header("Location: login.php");
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
<?php 
if(isset($_GET['x'])) {
	echo "<table align=center width=100%> 
	<tr> 
    <td class=subTitle><b>Gevangenis {$data->stad}</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
	$victim = mysql_query("SELECT * FROM `jail` WHERE `login`='{$_GET['x']}'");
	$vic = mysql_fetch_object($victim);
	$vict = mysql_query("SELECT * FROM `jail` WHERE `login`='{$_GET['x']}'");
	$isin = mysql_num_rows($vict);
	if ($vic->boete > $data->zak) {	echo "Je hebt niet genoeg geld op zak.";	}
	elseif ($isin == 0) { echo "Deze persoon zit niet meer in de gevangenis."; }
	else {
		mysql_query("UPDATE `users` SET `zak`=`zak`-{$vic->boete} WHERE `login`='{$data->login}'");
		echo "Je hebt deze persoon vrijgekocht.";
		mysql_query("DELETE FROM `jail` WHERE `login`='{$vic->login}'");
		if ($data->login != $vic->login) {
        mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'{$vic->login}','Vrijgekocht','Je bent door {$data->login} uit de gevangenis gekocht.')");
	   exit;
	   }
	}
}
elseif($_GET['bo'] == yes) {
	echo "<table align=center width=100%>  
	<tr> 
    <td class=subTitle><b>Gevangenis {$data->stad}</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
$victim = mysql_fetch_object(mysql_query("SELECT *,UNIX_TIMESTAMP(`time`) AS `time` FROM `jail` WHERE `login`='$data->login'"));
if (!$victim) { echo "Je zit niet in de gevangenis."; ;exit; }
elseif ($victim->bo >= 2) { echo "Je zit nu in een isoleercel, je kunt niet meer ontsnappen."; exit; }
else {
	$kans = rand(1,3);
	if ($kans == 2) { mysql_query("DELETE FROM `jail` WHERE `login`='$data->login'"); mysql_query("UPDATE `users` SET `xp`=`xp`+1,`bo`=`bo`+1 WHERE `login`='$data->login'"); echo "Je hebt jezelf uitgebroken."; exit; }
	elseif ($data->level >=255) { mysql_query("DELETE FROM `jail` WHERE `login`='$data->login'"); mysql_query("UPDATE `users` SET `xp`=`xp`+1,`bo`=`bo`+1 WHERE `login`='$data->login'"); echo "Je hebt jezelf uitgebroken."; exit; }
	else {
		$nb = ($victim->boete * 1.2);
		mysql_query("UPDATE `jail` SET `boete`='$nb',`bo`=`bo`+1,`time`=FROM_UNIXTIME($victim->time + 50) WHERE `login`='$data->login'");
		echo "Je uitbraak is mislukt.";
		exit;
		}
echo "</td></tr></table>";
}
}
elseif(isset($_GET['breakout'])) {
	echo "<table align=center width=100%> 
	<tr> 
    <td class=subTitle><b>Gevangenis {$data->stad}</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
$kans = rand(1,2);
$op = rand(1,3);
$victim = mysql_query("SELECT * FROM `jail` WHERE `login`='{$_GET['breakout']}'");
$vic = mysql_fetch_object($victim);
$vict = mysql_query("SELECT * FROM `jail` WHERE `login`='{$_GET['breakout']}'");
$isin = mysql_num_rows($vict);
$us = mysql_query("SELECT * FROM `users` WHERE `login`='{$_GET['breakout']}'");
$usr = mysql_fetch_object($us);
$gevangenis = mysql_query("SELECT * FROM `jail` WHERE `login`='{$data->login}'");
$zisin = mysql_num_rows($gevangenis);    
if ($isin == 0) {
echo "Deze persoon zit niet in de gevangenis.";
}
elseif ($vic->login == $data->login) {
echo "Je kan jezelf niet uitbreken.";
}
elseif ($zisin == 1) {
echo "Je kan niet iemand uitbreken als je zelf in de gevangenis zit.";
}
elseif ($kans == 1) {
echo "Je hebt deze persoon bevrijd.";
   mysql_query("DELETE FROM `jail` WHERE `login`='{$vic->login}'");
            mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$vic->login}','Bust-Out','Je bent door {$data->login} uit de gevangenis gebroken.')");
mysql_query("UPDATE `users` SET `xp`=`xp`+1,`bo`=`bo`+1 WHERE `login`='{$data->login}'");
}

elseif ($kans != 1) {
if ($op == 2) { echo "Je viel van de muur nadat je je gesneden had aan de prikkeldraad. Toen je wakker werd zat je in de cell.";
mysql_query("UPDATE `users` SET `health`=`health`-1 WHERE `login`='{$data->login}'"); 
mysql_query("INSERT INTO `jail`(`login`,`boete`,`stad`,`famillie`,`time`) VALUES('$data->login','{$boete}','{$data->stad}','{$famillie}',FROM_UNIXTIME($jailtime))");
}
else {
echo "Je bent gearresteerd in je poging tot uitbraak.";
mysql_query("INSERT INTO `jail`(`login`,`boete`,`stad`,`famillie`,`time`) VALUES('$data->login','{$boete}','{$data->stad}','{$famillie}',FROM_UNIXTIME($jailtime))");
exit;
}
}
}
else {
	echo "<table align=center width=100%>
	  <tr> 
    <td class=subTitle><b>Gevangenis {$data->stad}</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>
	  <table width=100%><tr>
	  <td width='100' align=center><b>Login</b></td> 
      <td width='100' align=center><b>Familie</b></td> 
      <td width='20%' align=center><b>Boete</b></td> 
      <td width='20%' align=center><b>Tijd</b></td> 
      <td width='20%' align=center><b>Koop Uit</b></td> 
      <td width='50' align=center><b>Breek Uit</b></td></tr>"; 

$query = "SELECT `bo`,`login`,`famillie`,`boete`,UNIX_TIMESTAMP(`time`) AS `time` FROM jail WHERE `stad`='{$data->stad}' ORDER BY `time` ASC"; 
$info = mysql_query($query) or die(mysql_error()); 
$count = 0; 
while ($gegeven = mysql_fetch_array($info)) { 
$name = $gegeven["login"]; 
$boete = $gegeven["boete"]; 
$bo = $gegeven["bo"];
$tim = ($gegeven['time'] - time());
$time = gmdate('i:s', $tim); 
$famillie = $gegeven['famillie'];
if (!$famillie) { $famillie = Geen; }
else { $famillie = "<a href=fam.php?x={$famillie}>$famillie</a>"; }
$count++; 
echo "<tr> 
        <td align=center><a href=\"user.php?x={$name}\">{$name}</a></td> 
                <td align=center>$famillie</td>
                <td align=center>&euro;{$boete}</td>
                <td align=center>{$time}</td>
                <td align=center><a href=\"jail.php?x={$name}\">[x]</a></td>";
if ($name == $data->login && $bo < 2) { echo "<td width='50' align=center><a href=\"jail.php?bo=yes\">[x]</a></td>"; }
elseif ($name == $data->login && $bo >= 2) { echo "<td width='50' align=center>[x]</td>"; }
else { echo "<td width='50' align=center><a href=\"jail.php?breakout={$name}\">[x]</a></td></tr>"; } 
echo "</tr>";
}
echo "</table></td></tr></table>";
}
?>
