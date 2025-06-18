<?php
  include("config.php");
$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");  
$data    = mysql_fetch_object($dbres);
  if(! check_login()) {
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
<table width=100%> 
  <tr> 
    <td class="subTitle"><b>Trouwen</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
    <table align=center width=100%>
<?php 
if ($_GET['divorce'] == 1) { mysql_query("UPDATE `users` SET `huwelijk`='' WHERE `login`='{$data->huwelijk}'"); mysql_query("UPDATE `users` SET `huwelijk`='' WHERE `login`='{$data->login}'"); mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$data->huwelijk','Scheiding','$data->login heeft een scheiding aangevraagd. Jullie zijn gescheiden...','0','1')"); echo "Jullie zijn nu gescheiden"; ;exit; }
if ($data->huwelijk) { echo "Je bent al getrouwd.<br><br>Wil je scheiden? <a href=getmarried.php?divorce=1>Klik hier</a>"; exit; }
$huwprijs = "250000";  
$dbres = mysql_query("SELECT * FROM `trouwen` WHERE `login`='{$data->login}' OR `partner`='{$data->login}'");
$trouw = mysql_fetch_object($dbres);
$trouwen = gmdate('i:s',($data->pc - time()));
if ($data->xp < 500) { echo "Je moet minstens Thief zijn om te kunnen trouwen."; exit; }
if ($_POST['submit']) {
	$zelf = strtolower($data->login);
	$ander = strtolower($_POST['partner']);
	$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc` FROM `users` WHERE `login`='{$_POST['partner']}'");
	$partner = mysql_fetch_object($dbres);
	$dbres = mysql_query("SELECT * FROM `trouwen` WHERE `login`='{$partner->login}' OR `partner`='{$partner->login}'");
	$hot = mysql_num_rows($dbres);
	if (!$partner->login) { echo "Deze gebruiker bestaat niet."; exit; }
	elseif ($zelf == $ander) { echo "Je kan niet met jezelf trouwen."; exit; }
	elseif ($partner->huwelijk) { echo "$partner->login is al getrouwd."; exit; }
	elseif ($partner->xp < 500) { echo "$partner->login is nog geen Thief."; exit; }
	elseif ($partner->status == dood) { echo "$partner->login is dood."; exit; }
	elseif ($hot == 1) { echo "$partner->login heeft al een huwelijksverzoek gekregen."; exit; }
	elseif ($data->zak < $huwprijs) { echo "Je hebt niet genoeg geld om te trouwen. Het kost &euro;{$huwprijs}."; exit; }
	else {
		mysql_query("UPDATE `users` SET `zak`=`zak`-$huwprijs WHERE `login`='{$data->login}'");
		mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$partner->login','Huwelijksaanzoek','$data->login vraagt je ten huwelijk in {$data->stad}. Ga naar trouwen om je antwoord te geven.','0','1')"); 
		mysql_query("INSERT INTO `trouwen`(`login`,`partner`,`ready1`,`ready2`,`stad`) VALUES('$data->login','$partner->login','0','0','$data->stad')");
		echo "Je aanzoek is verzonden."; exit;
	}
}
elseif (isset($_GET['accept'])) {
	$dbres = mysql_query("SELECT * FROM `trouwen` WHERE `login`='{$data->login}' OR `partner`='{$data->login}'");
	$trouwen = mysql_fetch_object($dbres);
	if ($trouwen->ready2 == 1 || $trouwen->partner != $data->login) { echo "Je hebt al ja gezegd, of je hebt geen uitnodiging gekregen om te trouwen."; }
	elseif ($_GET['accept'] == 0) { mysql_query("DELETE FROM `trouwen` WHERE `partner`='$data->login'"); echo "Je hebt nee gezegd."; mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$trouwen->login','Huwelijksaanzoek','Het antwoord van $data->login is... Nee.','0','1')"); }
	elseif ($_GET['accept'] == 1) { mysql_query("UPDATE `trouwen` SET `ready2`='1' WHERE `partner`='$data->login'"); echo "Je hebt ja gezegd."; mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$trouwen->login','Huwelijksaanzoek','Het antwoord van $data->login is... Ja.','0','1')"); }
} 
elseif (isset($_GET['cancel'])) {
	$dbres = mysql_query("SELECT * FROM `trouwen` WHERE `login`='{$data->login}' OR `partner`='{$data->login}'");
	$trouwen = mysql_fetch_object($dbres);
	if (!$trouwen) { echo "Je hebt geen huwelijksaanzoek gekregen."; }
	elseif ($_GET['cancel'] == 1 && $trouwen->login == $data->login) { echo "Je hebt het huwelijksaanzoek geannuleerd."; mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$trouwen->partner','Huwelijksaanzoek','$data->login heeft het huwelijksaanzoek geannuleerd.','0','1')"); mysql_query("DELETE FROM `trouwen` WHERE `login`='$data->login'"); }
	elseif ($_GET['cancel'] == 1 && $trouwen->partner == $data->login) { echo "Je hebt het huwelijksaanzoek geannuleerd."; mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$trouwen->login','Huwelijksaanzoek','$data->login heeft het huwelijksaanzoek geannuleerd.','0','1')"); mysql_query("DELETE FROM `trouwen` WHERE `partner`='$data->login'"); }
} 
elseif (isset($_GET['go'])) {
$jtime = (time() + 600);
	$dbres = mysql_query("SELECT * FROM `trouwen` WHERE `login`='{$data->login}'");
	$trouwen = mysql_fetch_object($dbres);
		$dbres = mysql_query("SELECT * FROM `users` WHERE `login`='$trouwen->login'");
		$aanvrager = mysql_fetch_object($dbres);
		$dbres = mysql_query("SELECT * FROM `users` WHERE `login`='$trouwen->partner'");
		$partner = mysql_fetch_object($dbres);

	if (!$trouwen) { echo "Je hebt geen huwelijksaanzoek gekregen of verstuurd."; }
	elseif ($aanvrager->stad != $trouwen->stad|| $partner->stad != $trouwen->stad) { echo "1 van jullie zit niet in $trouwen->stad."; exit; }
	else {
			echo "Proficiat, julie zijn getrouwd, het huwelijk kostte &#164;{$huwprijs}.";
			mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$partner->login','Huwelijksaanzoek','Proficiat, julie zijn getrouwd, het huwelijk kostte &euro;{$huwprijs}.','0','1')"); 
			mysql_query("UPDATE `users` SET `huwelijk`='{$data->login}' WHERE `login`='$partner->login'");
			mysql_query("UPDATE `users` SET `huwelijk`='{$partner->login}' WHERE `login`='$data->login'");
			mysql_query("DELETE FROM `trouwen` WHERE `login`='$data->login'");
		}
	}
elseif (!$trouw) { 
print <<<ENDHTML
Heb jij de man/vrouw van je leven gevonden? Wacht niet langer en trouw.<br>Trouwen kost &euro;$huwprijs. Dit bedrag moet jij betalen.<br><br>
<form method="POST">
Huwelijksaanvrager:$data->login <br>
Partner:<input type="text" name="partner" size="16" maxlength=16><br><br>
<input type="submit" value="Verzenden" name="submit"></form>
ENDHTML;
}
elseif ($trouw->partner == $data->login && $trouw->ready2 == 0) {
echo "Je hebt een verzoek van $trouw->login om te trouwen in {$trouw->stad} aangekregen.<br><br>Wil je met $trouw->login trouwen?  <a href=getmarried.php?accept=1>Ja</a>&nbsp;&nbsp;<a href=getmarried.php?accept=0>Nee</a>";
}
elseif ($trouw->partner == $data->login && $trouw->ready2 == 1) { 
echo "Je hebt het huwelijksaanzoek geaccepteerd, je moet nu nog wachten op de andere speler. <br><br><a href=getmarried.php?cancel=1>Zeg de bruilhoft af</a>";
}
elseif ($trouw->ready2 == 0 && $trouw->login == $data->login) { echo "Je partner <a href=user.php?x={$trouw->partner}>$trouw->partner</a> moet nog antwoord geven. <br><br><a href=getmarried.php?cancel=1>Zeg de bruilhoft af</a>"; }
elseif ($trouw->ready2 == 1 && $trouw->login == $data->login) { echo "Jullie zijn helemaal klaar voor het huwelijk.<br><br><a href=getmarried.php?go=1>Trouw!</a>&nbsp;<a href=getmarried.php?cancel=1>Zeg de bruilhoft af</a>"; }
?> 
</table> 
</body> 
</html> 
</table>