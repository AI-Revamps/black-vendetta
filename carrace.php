<?php
  include("config.php");
  $dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");
  $data	= mysql_fetch_object($dbres);  
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
<?php
print <<<ENDHTML
<table width=100%> 
  <tr> 
    <td class="subTitle"><b>Race</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
    <table align=left width=100%>
ENDHTML;
$dres = mysql_query("SELECT * FROM `autorace` WHERE `login`='{$data->login}' OR `enemy`='{$data->login}'");
$race = mysql_fetch_object($dres);
$auto1 = mysql_fetch_object(mysql_query("SELECT * FROM `garage` WHERE `login`='$race->login' AND `id`='$race->id1'"));
$auto2 = mysql_fetch_object(mysql_query("SELECT * FROM `garage` WHERE `login`='$race->enemy' AND `id`='$race->id2'"));
if ($data->xp < 150) { echo "Je moet minstens Pickpocket zijn voordat je mag racen."; exit; }
if ($_POST['submit']) {
if (!$_POST['verify']){echo"Je moet een code opgeven.";exit;}
elseif($_POST['verify'] != $_SESSION['verify']){echo"De code die je hebt ingevoerd komt niet overeen met het plaatje.";exit;}
	$zelf = strtolower($data->login);
	$ander = strtolower($_POST['piloot']);
	$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc` FROM `users` WHERE `login`='{$_POST['enemy']}'");
	$enemy = mysql_fetch_object($dbres);
	$dbres = mysql_query("SELECT * FROM `autorace` WHERE `login`='{$enemy->login}' OR `piloot`='{$piloot->login}'");
	$hot = mysql_num_rows($dbres);
	$auto = mysql_fetch_object(mysql_query("SELECT * FROM `garage` WHERE `id`='{$_POST['auto']}' AND `login`='{$data->login}'"));
	if (!$enemy->login) { echo "Deze gebruiker bestaat niet."; exit; }
	elseif ($zelf == $ander) { echo "Je kan niet tegen jezelf racen."; exit; }
	elseif ($enemy->xp < 150) { echo "$enemy->login is nog geen Pickpocket."; exit; }
	elseif ($enemy->status == dood) { echo "$enemy->login is dood."; exit; }
	elseif ($hot == 1) { echo "$enemy->login is al bezig met een race."; exit; }
	elseif (!$auto) { echo "Deze wagen is niet van jou."; exit; }
	elseif ($auto->damage > 75) { echo "Deze wagen is te hard beschadigd."; exit; }
	else {
		mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$enemy->login','Auto Race','Je bent uitgenodigd door $data->login om te racen in $data->stad. Ga naar race om te accepteren of te weigeren.','0','1')"); 
		mysql_query("INSERT INTO `autorace`(`login`,`enemy`,`ready1`,`ready2`,`stad`,`id1`,`id2`) VALUES('$data->login','$enemy->login','0','0','$data->stad','{$_POST['auto']}','')");
		echo "De uitnodiging is verzonden."; exit;
	}
}
elseif ($_POST['sauto1']) {
		$auto = mysql_fetch_object(mysql_query("SELECT * FROM `garage` WHERE `id`='{$_POST['auto1']}' AND `login`='{$data->login}'"));
	if (!$auto) { echo "Deze wagen is niet van jou."; exit; }
	elseif ($auto->damage > 75) { echo "Deze wagen is te hard beschadigd."; exit; }
	else {
		mysql_query("UPDATE `autorace` SET `id1`='{$_POST['auto1']}' WHERE `login`='{$data->login}'");
		echo "Je wagen staat klaar."; pexit;
		}
	}
elseif ($_POST['sauto2']) {
		$auto = mysql_fetch_object(mysql_query("SELECT * FROM `garage` WHERE `id`='{$_POST['auto2']}' AND `login`='{$data->login}'"));
	if (!$auto) { echo "Deze wagen is niet van jou."; exit; }
	elseif ($auto->damage > 75) { echo "Deze wagen is te hard beschadigd."; exit; }
	else {
		mysql_query("UPDATE `autorace` SET `id2`='{$_POST['auto2']}' WHERE `enemy`='{$data->login}'");
		echo "Je wagen staat klaar."; exit;
		}
	}
	elseif (isset($_GET['accept'])) {
	$dbres = mysql_query("SELECT * FROM `autorace` WHERE `login`='{$data->login}' OR `enemy`='{$data->login}'");
	$race = mysql_fetch_object($dbres);
	if ($race->ready2 == 1 || $race->enemy != $data->login) { echo "Je hebt al aanvaard."; }
	elseif ($_GET['accept'] == 0) { mysql_query("DELETE FROM `autorace` WHERE `enemy`='$data->login'"); echo "Je hebt de race geannuleerd."; mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$race->login','Auto race','$data->login heeft de uitnodiging afgewezen.','0','1')"); }
	elseif ($_GET['accept'] == 1) { mysql_query("UPDATE `autorace` SET `ready2`='1' WHERE `enemy`='$data->login'"); echo "Je hebt geaccepteerd."; mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$race->login','Auto race','$data->login heeft de uitnodiging aanvaard.','0','1')"); }
} 
elseif (isset($_GET['cancel'])) {
	$dbres = mysql_query("SELECT * FROM `autorace` WHERE `login`='{$data->login}' OR `enemy`='{$data->login}'");
	$race = mysql_fetch_object($dbres);
	if (!$race) { echo "Je bent niet met een race bezig."; }
	elseif ($_GET['cancel'] == 1 && $race->login == $data->login) { echo "Je hebt de race geannuleerd."; mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$race->piloot','Auto race','Je tegenstander heeft de race geannunleerd.','0','1')"); mysql_query("DELETE FROM `autorace` WHERE `login`='$data->login'"); }
	elseif ($_GET['cancel'] == 1 && $race->piloot == $data->login) { echo "Je hebt de race geannuleerd."; mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$race->login','Auto race','Je tegenstander heeft de race geannuleerd.','0','1')"); mysql_query("DELETE FROM `autorace` WHERE `enemy`='$data->login'"); }
} 
elseif (isset($_GET['go'])) {
	$dbres = mysql_query("SELECT * FROM `autorace` WHERE `login`='{$data->login}'");
	$race = mysql_fetch_object($dbres);
	$dbres = mysql_query("SELECT * FROM `users` WHERE `login`='$race->login'");
	$leider = mysql_fetch_object($dbres);
	$dbres = mysql_query("SELECT * FROM `users` WHERE `login`='$race->enemy'");
	$enemy = mysql_fetch_object($dbres);
	$auto1 = mysql_fetch_object(mysql_query("SELECT * FROM `garage` WHERE `id`='{$race->id1}'"));
	$auto2 = mysql_fetch_object(mysql_query("SELECT * FROM `garage` WHERE `id`='{$race->id2}'"));
	if (!$race) { echo "Je bent niet met een race bezig."; }
	elseif ($leider->stad != $race->stad || $enemy->stad != $race->stad) { echo "Iemand is niet in $race->stad"; exit; }
	elseif ($auto1->stad != $race->stad || $auto2->stad != $race->stad) { echo "Je wagen staat niet in $race->stad, of bestaat niet."; exit; }
	else {
		$a1 = mysql_fetch_object(mysql_query("SELECT `waarde`,`naam`,`auto` FROM `cars` WHERE `naam`='{$auto1->naam}'"));
		$a2 = mysql_fetch_object(mysql_query("SELECT `waarde`,`naam`,`auto` FROM `cars` WHERE `naam`='{$auto2->naam}'"));
		$vmsg = "Voor de race:<br><br>$data->login's Auto: $a1->auto Schade: $auto1->damage% Waarde: &#164;$auto1->waarde <br>$enemy->login's Auto: $a2->auto Schade: $auto2->damage% Waarde: &#164;$auto2->waarde";
		$rand1 = rand(1,10);
		$rand2 = rand(1,10);
		$schade1 = (rand(1,25) + $auto1->damage);
		$schade2 = (rand(1,25) + $auto2->damage);
		$s1 = floor($a1->waarde - ($a1->waarde / 75) * $schade1);
		$s2 = floor($a2->waarde - ($a2->waarde / 75) * $schade2);
		if ($s1 < 500) { $s1 = 500; }
		if ($s2 < 500) { $s2 = 500; }
		mysql_query("UPDATE `garage` SET `waarde`='$s1',`damage`='$schade1' WHERE `id`='{$auto1->id}'");
		mysql_query("UPDATE `garage` SET `waarde`='$s2',`damage`='$schade2' WHERE `id`='{$auto2->id}'");
		$p1 = floor(($auto1->waarde / 1000) + $rand1 + (100 - $auto1->damage));
		$p2 = floor(($auto2->waarde / 1000) + $rand2 + (100 - $auto2->damage));
		$p1 = ($p1 + $leider->health);
		$p2 = ($p2 + $enemy->health);
		$nmsg = "Na de race:<br><br>$data->login's Auto: $a1->auto Schade: $schade1% Waarde: &#164;$s1 <br>$enemy->login's Auto: $a2->auto Schade: $schade2% Waarde: &#164;$s2";
		if ($p1 > $p2) {
			mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','enemy->login','Auto race','Je hebt de race verloren van $leider->login.','0','1')"); 
			mysql_query("UPDATE `users` SET `xp`=`xp`+1,`nrofrace`=`nrofrace`+1 WHERE `login`='$leider->login'");
			mysql_query("UPDATE `users` SET `xp`=`xp`+1,`nrofrace`=`nrofrace`+1 WHERE `login`='$enemy->login'");
			mysql_query("UPDATE `garage` SET `login`='$leider->login' WHERE `id`='{$auto2->id}'");			
			mysql_query("DELETE FROM `autorace` WHERE `login`='$data->login'");
			echo "Je hebt de autorace gewonnen, je won een $a2->auto. <br><br>$vmsg<br><br>$nmsg";
			}
		else {
			mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','enemy->login','Auto race','Je hebt de race gewonnen, je won een $a1->auto.','0','1')"); 
			mysql_query("UPDATE `users` SET `xp`=`xp`+1,`nrofrace`=`nrofrace`+1 WHERE `login`='$leider->login'");
			mysql_query("UPDATE `users` SET `xp`=`xp`+1,`nrofrace`=`nrofrace`+1 WHERE `login`='$enemy->login'");
			mysql_query("UPDATE `garage` SET `login`='$enemy->login' WHERE `id`='{$auto1->id}'");			
			mysql_query("DELETE FROM `autorace` WHERE `login`='$data->login'");
			echo "Je hebt de autorace verloren van $enemy->login<br><br>$vmsg<br><br>$nmsg";
			}			
	}
}
elseif (!$race) { 
print <<<ENDHTML
<div align=left>
Kies hier een tegenstander om te racen in een illegale straatrace.<br>
<form method="POST">
<div align=left>
Uitdager: $data->login <br>
AutoID: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=text name=auto><br>
Tegenstander: <input type="text" name="enemy" maxlength=16><br><br>
Je code is: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src=img.php><br>
Typ hier de code in:    <input type=text name=verify><br>
<input type="submit" value="Ok" name="submit"></form>
ENDHTML;
}
elseif ($race->enemy == $data->login && $race->ready2 == 0) { echo "Je bent uitgenodigd door $race->login om te racen in {$race->stad}.<br><br><a href=carrace.php?accept=1>Accepteer</a>&nbsp;<a href=carrace.php?accept=0>Weiger</a>"; }
elseif ($race->login == $data->login && !$auto1) { echo "AutoID: <form method=POST><input type=text name=auto1><br><br><input type=submit name=sauto1 value=OK></form>"; }
elseif ($race->enemy == $data->login && !$auto2) { echo "AutoID: <form method=POST><input type=text name=auto2><br><br><input type=submit name=sauto2 value=OK></form>"; }
elseif ($race->enemy == $data->login && $race->ready2 != 0) { 
echo "Je bent klaar, nu moet je wachten op je tegenstander.<br><br><a href=carrace.php?cancel=1>Annuleer</a>";
}
elseif ($race->ready2 == 0 && $race->login == $data->login) { echo "Je tegenstander <a href=user.php?x={$race->enemy}>$race->enemy</a> moet nog een auto kiezen.<br><br><a href=carrace.php?cancel=1>Annuleer</a>"; }
elseif ($race->ready2 == 1 && $race->login == $data->login) { echo "Jullie zijn er klaar voor.<br><br><a href=carrace.php?go=1>Race!</a>&nbsp;<a href=carrace.php?cancel=1>Annuleer</a>"; }
?> 
</table> 
</body> 
</html> </table>
