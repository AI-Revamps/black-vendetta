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
<!DOCTYPE html>
<html lang="nl">
<head>
<title>Vendetta</title>
<link rel="stylesheet" type="text/css" href="style.css">
<meta name="keywords" content="Vendetta,Crimegame,crimegame,vendetta">
<meta name="language" content="english">
<META name="description" lang="nl" content="Vendetta crimegame met pit.">
</head>
<table width=100%>
  <tr> 
    <td class="subTitle"><b>Route 66</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
    <table width=100%>
<?php    
$geluk = rand(1,2);
$nr = rand(1,2);
$item = ($nr == 1) ? drugs : drank;
$antal = rand(2,11);
$time = time();
$msgnum = rand(0,8);
$message = Array("Een wagen stopte, maar de man die uitstapte was tweemaal zo groot als jullie. Jullie sloegen dan maar op de vlucht","Jullie kregen onderweg een ongeluk, en raakten gewond","Het was een zwart busje met twee mensen in. Een goede kans dachten jullie, totdat er uit de achterkant een groep politie mensen stapte. Jullie werden gearresteerd.","Jullie werden overvallen door twee gangsters die de weg hadden geblockeerd. Jullie raakten net op tijd weg.","Jullie wagen viel midden in de woestijn stil. Gelukkig konden jullie liften bij een dikke trucker.","Na vijf uur naar een lege weg te staren werden jullie het beu. Wie kiest er nu ook een verlaten stadweg?","Jullie kwamen een blonde stoot tegen. Net toen jullie je wapens wilden bovenhalen deed ze haar rokje uit... Jullie leven is weer gestegen naar 100%","Een wagen stopte... De walm van wiet was duidelijk te herkennen.","Er kwam een oude dame in een auto af. Ze reed jullie wagen in de berm en stak haar middelvinger uit...");
$msg = "$message[$msgnum]";
$dbres = mysql_query("SELECT * FROM `route66` WHERE `login`='{$data->login}' OR `driver`='{$data->login}'");
$rout = mysql_fetch_object($dbres);
$route66 = gmdate('i:s',($data->pc - time()));
$routetijd = (time() + 3600);
if ($data->xp < 150) { echo "Je moet minstens Pickpocket zijn."; exit; }
if($data->pc - $time > 0) { echo "Je bent nog $route66 aan het uitrusten."; echo "</table></table>"; exit; }
if ($_POST['submit']) {
if (!$_POST['verify']){echo"Je moet een code opgeven.";exit;}
elseif($_POST['verify'] != $_SESSION['verify']){echo"De code die je hebt ingevoerd komt niet overeen met het plaatje.";exit;}
	$zelf = strtolower($data->login);
	$ander = strtolower($_POST['driver']);
	$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc` FROM `users` WHERE `login`='{$_POST['driver']}'");
	$driver = mysql_fetch_object($dbres);
	$dbres = mysql_query("SELECT * FROM `route66` WHERE `login`='{$driver->login}' OR `driver`='{$driver->login}'");
	$hot = mysql_num_rows($dbres);
	$wapen = $_POST['wapon'];
	if ($wapen == "fake") { $costs = 0 ;}
	else {$costs = 50000;}
	if (!$driver->login) { echo "Deze gebruiker bestaat niet."; exit; }
	elseif ($zelf == $ander) { echo "Je kan geen driver en leider tegelijk zijn."; exit; }
	elseif ($driver->pc - time() > 0) { echo "$driver->login heeft dit uur al een route 66 gedaan."; exit; }
	elseif ($driver->xp < 150) { echo "$driver->login is nog geen pickpocket."; exit; }
	elseif ($driver->status == dood) { echo "$driver->login is dood."; exit; }
	elseif ($hot == 1) { echo "$driver->login is al bezig met een route 66."; exit; }
	elseif ($data->zak < $costs) { echo "Je hebt niet genoeg geld op zak."; exit; }
	else {
		mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$driver->login','Route 66','Je bent door $data->login uitgenodigd om samen een route 66 te doen in {$data->stad}. Ga naar Route 66 om een wagen te selecteren, te accepteren of te weigeren.','0','1')"); 
		mysql_query("INSERT INTO `route66`(`login`,`driver`,`ready1`,`ready2`,`stad`) VALUES('$data->login','$driver->login','0','0','$data->stad')");
		mysql_query("UPDATE `users` SET `zak`=`zak`-'{$costs}' WHERE `login`='$data->login'");
		echo "De uitnodiging is verzonden."; exit;
	}
}
elseif ($_POST['scar']) {
	$auto = mysql_fetch_object(mysql_query("SELECT * FROM `garage` WHERE `id`='{$_POST['car']}' AND `login`='{$data->login}'"));
	if (!$auto) { echo "Deze wagen is niet van jou."; exit; }
	elseif ($auto->damage > 90) { echo "Deze wagen is te hard beschadigd."; exit; }
	else {
		mysql_query("UPDATE `route66` SET `car`='{$_POST['car']}' WHERE `driver`='{$data->login}'");
		mysql_query("UPDATE `route66` SET `ready2`='1' WHERE `driver`='$data->login'"); 
		mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$route66->login','Route 66','$data->login heeft de uitnodiging geaccepteerd. Hij heeft een wagen ingezet met een waarde van &euro;{$auto->waarde}.','0','1')");
		mysql_query("DELETE FROM `garage` WHERE `login`='$data->login' AND `id`='{$_POST['car']}'");
		echo "Je wagen staat klaar. Het is nu aan je leider om de rest te doen."; exit;
	}
}
elseif (isset($_GET['accept'])) {
	$dbres = mysql_query("SELECT * FROM `route66` WHERE `login`='{$data->login}' OR `driver`='{$data->login}'");
	$route66 = mysql_fetch_object($dbres);
	if ($route66->ready2 == 1 || $route66->driver != $data->login) { echo "Je hebt al geaccepteerd, of je bent niet met een route 66 bezig."; }
	elseif ($_GET['accept'] == 0) { mysql_query("DELETE FROM `route66` WHERE `driver`='$data->login'"); echo "Je hebt geweigerd."; mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$route66->login','Route 66','$data->login heeft de uitnodiging geweigerd.','0','1')"); }
} 
elseif (isset($_GET['cancel'])) {
	$dbres = mysql_query("SELECT * FROM `route66` WHERE `login`='{$data->login}' OR `driver`='{$data->login}'");
	$route66 = mysql_fetch_object($dbres);
	if (!$route66) { echo "Je bent niet met een route 66 bezig."; }
	elseif ($_GET['cancel'] == 1 && $route66->login == $data->login) { echo "Je hebt de route 66 geannuleerd."; mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$route66->driver','Route 66','$data->login heeft de route 66 geannuleerd.','0','1')"); mysql_query("DELETE FROM `route66` WHERE `login`='$data->login'"); }
	elseif ($_GET['cancel'] == 1 && $route66->driver == $data->login) { echo "Je hebt de route 66 geannuleerd."; mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$route66->login','Route 66','$data->login heeft de route 66 geannuleerd.','0','1')"); mysql_query("DELETE FROM `route66` WHERE `driver`='$data->login'"); }
} 
elseif (isset($_GET['go'])) {
if ($rout->ready2 != 1 || $rout->login != $data->login || $rout->car == 0) {echo "Jullie zijn nog niet klaar.";exit;}
$jtime = (time() + 600);
	$dbres = mysql_query("SELECT * FROM `route66` WHERE `login`='{$data->login}'");
	$route66 = mysql_fetch_object($dbres);
		$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc` FROM `users` WHERE `login`='$route66->login'");
		$leider = mysql_fetch_object($dbres);
		$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc` FROM `users` WHERE `login`='$route66->driver'");
		$driver = mysql_fetch_object($dbres);

	if (!$route66) { echo "Je bent niet met een route 66 bezig, of je bent niet de leider."; }
	elseif ($leider->stad != $route66->stad || $driver->stad != $route66->stad) { echo "Een van jullie zit niet in $route66->stad."; exit; }
	else {
		$time = time();
		$min = (($leider->drugs + $leider->drank + $driver->drank + $driver->drugs) * 2500);
		$mini = (((100 - $leider->health) + (100 - $driver->health)) * 5000);
		$ammount = ((500000 - $min) - $mini);
		if ($ammount < 1000) { $ammount = 5000; }
$din = (($leider->drugs + $leider->drank + $driver->drank + $driver->drugs) * 2500);
$dini = (((100 - $leider->health) + (100 - $driver->health)) * 5000);
$ammountp = ((100000 - $din) - $dini);
if ($ammountp < 10000) { $ammountp = 10000; }
		if ($leider->level + $driver->level > 255) { $kans = rand(1,1); } //Admin
		elseif ($leider->xp + $driver->xp < 500) { $kans = rand(1,10); } // Pickpocket
		elseif ($leider->xp + $driver->xp < 1000) { $kans = rand(1,10); } // Thief
		elseif ($leider->xp + $driver->xp < 2000) { $kans = rand(1,9); } // Associate
		elseif ($leider->xp + $driver->xp < 3000) { $kans = rand(1,8); } // Mobster
		elseif ($leider->xp + $driver->xp < 4500) { $kans = rand(1,7); } // Soldier
		elseif ($leider->xp + $driver->xp < 6000) { $kans = rand(1,6); } // Swindler
		elseif ($leider->xp + $driver->xp < 8000) { $kans = rand(1,5); } // Assassin
		elseif ($leider->xp + $driver->xp < 11000) { $kans = rand(1,4); } // Local Chief
		elseif ($leider->xp + $driver->xp < 15000) { $kans = rand(1,3); } // Chief
		elseif ($leider->xp + $driver->xp < 20000) { $kans = rand(1,3); } // Bruglione
		elseif ($leider->xp + $driver->xp >= 20000) { $kans = rand(1,3); } //Godfather
		if ($kans == 1) { 
		$slaagmessage = Array("Jullie hielden een kleine vrachtwagen tegen. Jullie deden zijn laadruim open. Het lag vol met zakken geld van het plaatselijke casino. Jullie hebben &euro; {$ammount} gestolen.","Jullie lieten vrachtwagen stoppen die vol stond met radio's en tv's. Jullie konden het verkopen voor &euro; {$ammount}.","Na een lange tijd te wachten kwam er eindelijk een vrachtwagen langst... In zijn laagruim vonden jullie niets. Jullie besloten dan maar om de trucker te dwingen naar zijn transportbedrijf te rijden. Jullie lieten alle truckers op een rij staan en eisten alles van waarde op... Jullie hebben &euro; {$ammount} gestolen.");
		$slaagmsgnum = rand(0,2);
		$slaagmsg = "$slaagmessage[$slaagmsgnum]";	
			echo "$slaagmsg De leider heeft het geld gekregen. Dit moeten jullie zelf verdelen.";
			mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$driver->login','Route 66','$slaagmsg De leider heeft het geld gekregen. Dit moeten jullie zelf verdelen.','0','1')"); 
			mysql_query("UPDATE `users` SET `zak`=`zak`+$ammount,`xp`=`xp`+4,`pc`=FROM_UNIXTIME($routetijd),`nrofroute`=`nrofroute`+1 WHERE `login`='$data->login'");
			mysql_query("UPDATE `users` SET `xp`=`xp`+4,`pc`=FROM_UNIXTIME($routetijd),`nrofroute`=`nrofroute`+1 WHERE `login`='$driver->login'");
			mysql_query("DELETE FROM `route66` WHERE `login`='$data->login'");
		}
		else {
			echo "$msg";
			mysql_query("UPDATE `users` SET `xp`=`xp`+1,`pc`=FROM_UNIXTIME($routetijd),`nrofroute`=`nrofroute`+1 WHERE `login`='$leider->login'");
			mysql_query("UPDATE `users` SET `xp`=`xp`+1,`pc`=FROM_UNIXTIME($routetijd),`nrofroute`=`nrofroute`+1 WHERE `login`='$driver->login'");
			mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$driver->login','Route 66','$msg','0','1')"); 
			if ($msgnum == 1) { 
				mysql_query("UPDATE `users` SET `health`=`health`-2 WHERE `login`='$leider->login'");
				mysql_query("UPDATE `users` SET `health`=`health`-2 WHERE `login`='$driver->login'");
			}
			elseif ($msgnum == 2) { 
				mysql_query("INSERT INTO `jail`(`login`,`boete`,`stad`,`famillie`,`time`) VALUES('$leider->login','150000','{$data->stad}','{$data->famillie}',FROM_UNIXTIME($jtime))");
				mysql_query("INSERT INTO `jail`(`login`,`boete`,`stad`,`famillie`,`time`) VALUES('$driver->login','150000','{$data->stad}','{$driver->famillie}',FROM_UNIXTIME($jtime))");
			}
			elseif ($msgnum == 6) { 
				mysql_query("UPDATE `users` SET `health`='100' WHERE `login`='{$leider->login}'");
				mysql_query("UPDATE `users` SET `health`='100' WHERE `login`='{$driver->login}'");
			}
			elseif ($msgnum == 7) { 
				if ($geluk == 1) { echo "<br><br><br>Je kon de junkie overmeesteren."; $bericht = "Je kon de junkie overmeesteren.";
				if ($antal != 0) { echo "<br>Er lagen $antal units $item in de wagen."; mysql_query("UPDATE `users` SET `$item`=`$item`+$antal WHERE `login`='$leider->login'"); $bericht = "$bericht Er lagen $antal units $item in de wagen."; }
				echo "Jullie konden de wagen verkopen voor &euro; {$ammountp}. Jullie moeten zelf het geld verdelen"; mysql_query("UPDATE `users` SET `zak`=`zak`+$ammountp WHERE `login`='{$leider->login}'"); $bericht = "$bericht Jullie konden de wagen verkopen voor &euro; {$ammountp}.";
				}					
				else { echo "De junkie kon ontsnappen!"; $bericht = "De junkie kon ontsnappen!"; }
				mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','$driver->login','Route 66','$bericht')");
		}
			mysql_query("DELETE FROM `route66` WHERE `login`='$data->login'");
		}
	}
}
elseif (!$rout) { 
print <<<ENDHTML
<div align=left>
Maak met iemand de afspraak om een Route 66 te doen.<br>
<form method="POST">
<div align=left>
Leider : $data->login <br>
Driver : &nbsp;<input type="text" name="driver" size="16" maxlength=16><br>
  Wapen : 
  <select name="wapon">
  <option value="fake">Valse wapens (&euro; 0)</option>
  <option value="real">Echte wapens (&euro; 50.000)</option>
  </select>
  <br>
  <br>
  Je code is: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src=img.php><br>
  Typ hier de code in:    <input type=text name=verify><br><br>
<input type="submit" value="Verzenden" name="submit"></form>
ENDHTML;
}
elseif ($rout->driver == $data->login && $rout->car == 0) {
echo "Je moet nog een wagen opgeven.<br><br><form method=POST><input type=text name=car><input type=submit name=scar value=OK></form><br><br><a href=heist.php?accept=0>Weigeren</a>";
}
elseif ($rout->driver == $data->login && $rout->ready2 == 0) {
echo "Je bent door $rout->login uitgenodigd om een route 66 te doen in {$rout->stad}.<br><br><a href=heist.php?accept=1>Accepteren</a>&nbsp;<a href=heist.php?accept=0>Weigeren</a>";
}
elseif ($rout->driver == $data->login && $rout->ready2 == 1 && $rout->car != 0) { 
echo "Je bent er klaar voor, je moet alleen nog wachten op de leider. <br><br><a href=heist.php?cancel=1>Annuleer de route 66</a>";
}
elseif ($rout->ready2 == 0 && $rout->login == $data->login) { echo "Je driver <a href=user.php?x={$rout->driver}>$rout->driver</a> moet nog accepteren of een wagen kiezen.<br><br><a href=heist.php?cancel=1>Annuleer de route 66</a>"; }
elseif ($rout->ready2 == 1 && $rout->login == $data->login && $rout->car != 0) { echo "Jullie zijn er helemaal klaar voor.<br><br><a href=heist.php?go=1>GO!</a>&nbsp;&nbsp;&nbsp;<a href=heist.php?cancel=1>Annuleer</a>"; }
?> 
</table> 
</body> 
</html> 