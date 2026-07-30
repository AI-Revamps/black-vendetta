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
<table width=100%>
  <tr> 
    <td class="subTitle"><b>Organised Crime</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
    <table width=100%>
<?php    
$geluk = rand(1,2);
$nr = rand(1,2);
$item = ($nr == 1) ? drugs : drank;
$antal = rand(5,20);
$time = time();
$msgnum = rand(0,8);
$message = Array("Jullie konden de kluis opblazen, maar jullie hadden de explosieven slecht aangebracht! Al het geld ging in vlammen op. Gelukkig konden jullie wegrijden voordat de politie kwam.","Jullie stormden de bank binnen en dreven iedereen in een hoekje. Plots kwam er een bewaker binnen die op jullie begon te schieten. Jullie moesten vluchten en zijn gewond geraakt.","De kluis opblazen was een makkie. Jullie schepten het geld in een zak en liepen de bank uit... Daar stond een batalion politieagenten jullie op te wachten. Jullie zijn gearresteerd.","Iemand liet het alarm afgaan. De Wapen Expert begon in het wilde weg te schieten. Gelukkig konden jullie nog op tijd weg komen.","Een eenheid politieagenten stond buiten. Jullie besloten de plannen dan maar te annuleren.","Jullie wagen geraakte niet eens ter plaatse, hij werd tegengehouden voor een drugscontrole. Gelukkig werd er niets gevonden.","De bediende smeekte jullie: 'Doe ons geen pijn, geen geweld, ik geef alles...' Ze nam jullie &eacute;&eacute;n voor &eacute;&eacute;n mee binnen in een klein kantoortje. Jullie leven is weer gestegen naar 100%","Jullie probeerden langst het ventilatiesysteem bij de kluis te komen.","'Steek je handen in de lucht, dit is een overval!' De bomma die net haar geld aan het tellen was, kreeg plots een hartaanval en viel tegen de leider zijn been. Hij schrok zo hard dat hij niet zag hoe iemand het alarm af liet gaan.");
$msg = "$message[$msgnum]";
$dbres = mysql_query("SELECT * FROM `oc` WHERE `login`='{$data->login}' OR `dr`='{$data->login}' OR `be`='{$data->login}' OR `we`='{$data->login}'");
$oc = mysql_fetch_object($dbres);
$bc = gmdate('H:i:s',($data->bc - time()));
$octime = (time() + 43200);
if ($data->xp < 500) { echo "Je moet minstens Thief zijn."; exit; }
if($data->bc - $time > 0) { echo "Je bent nog $bc aan het uitrusten."; echo "</table></table>"; exit; }
if ($_POST['submit']) {
if (!$_POST['verify']){echo"Je moet een code opgeven.";exit;}
elseif($_POST['verify'] != $_SESSION['verify']){echo"De code die je hebt ingevoerd komt niet overeen met het plaatje.";exit;}
	$zelf = strtolower($data->login);
	$dr = strtolower($_POST['dr']);
	$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`bc`) AS `bc` FROM `users` WHERE `login`='{$_POST['dr']}'");
	$dr = mysql_fetch_object($dbres);
	$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`bc`) AS `bc` FROM `users` WHERE `login`='{$_POST['we']}'");
	$we = mysql_fetch_object($dbres);
	$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`bc`) AS `bc` FROM `users` WHERE `login`='{$_POST['be']}'");
	$be = mysql_fetch_object($dbres);
	$dbres = mysql_query("SELECT * FROM `oc` WHERE `login`='{$we->login}' OR `dr`='{$we->login}' OR `be`='{$we->login}' OR `we`='{$we->login}'");
	$hot1 = mysql_num_rows($dbres);
	$dbres = mysql_query("SELECT * FROM `oc` WHERE `login`='{$be->login}' OR `dr`='{$be->login}' OR `be`='{$be->login}' OR `we`='{$be->login}'");
	$hot2 = mysql_num_rows($dbres);
	$dbres = mysql_query("SELECT * FROM `oc` WHERE `login`='{$dr->login}' OR `dr`='{$dr->login}' OR `be`='{$dr->login}' OR `we`='{$dr->login}'");
	$hot3 = mysql_num_rows($dbres);
    if (!$we->login) { echo "Deze gebruiker bestaat niet."; exit; }
	elseif ($we->bc - time() > 0) { echo "$we->login heeft al een oc gedaan."; exit; }
	elseif ($we->xp < 500) { echo "$we->login is nog geen Thief."; exit; }
	elseif ($we->status == dood) { echo "$we->login is dood."; exit; }
	if (!$be->login) { echo "Deze gebruiker bestaat niet."; exit; }
	elseif ($be->bc - time() > 0) { echo "$be->login heeft al een oc gedaan."; exit; }
	elseif ($be->xp < 500) { echo "$be->login is nog geen Thief."; exit; }
	elseif ($be->status == dood) { echo "$be->login is dood."; exit; }
	if (!$dr->login) { echo "Deze gebruiker bestaat niet."; exit; }
	elseif ($zelf == $dr || $zelf == $we || $zelf == $be || $dr == $we || $dr == $be || $we == $be) { echo "Je moet voor iedere taak iemand anders hebben."; exit; }
	elseif ($dr->bc - time() > 0) { echo "$dr->login heeft al een oc gedaan."; exit; }
	elseif ($dr->xp < 500) { echo "$dr->login is nog geen Thief."; exit; }
	elseif ($dr->status == dood) { echo "$dr->login is dood."; exit; }
	elseif ($hot1 == 1) { echo "$we->login is al bezig met een oc."; ;exit; }
	elseif ($hot2 == 1) { echo "$be->login is al bezig met een oc."; exit; }
	elseif ($hot3 == 1) { echo "$dr->login is al bezig met een oc."; exit; }
	elseif ($data->zak < 50000) { echo "Je hebt niet genoeg geld op zak."; exit; }
	else {
		mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$we->login','OC','Je bent door $data->login uitgenodigd om samen een oc te doen in {$data->stad} als WE. Ga naar oc om wapens te selecteren, te accepteren of te weigeren','0','1')"); 
		mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$be->login','OC','Je bent door $data->login uitgenodigd om samen een oc te doen in {$data->stad} als BE. Ga naar oc om explosieven te selecteren, te accepteren of te weigeren','0','1')"); 
		mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$dr->login','OC','Je bent door $data->login uitgenodigd om samen een oc te doen in {$data->stad} als driver. Ga naar oc om een wagen te selecteren, te accepteren of te weigeren','0','1')"); 
		mysql_query("INSERT INTO `oc`(`login`,`dr`,`we`,`be`,`wapens`,`kogels`,`ready1`,`ready2`,`ready3`,`stad`) VALUES('$data->login','$dr->login','$we->login','$be->login','0','0','0','0','0','$data->stad')");
		mysql_query("UPDATE `users` SET `zak`=`zak`-50000 WHERE `login`='$data->login'");
		echo "De uitnodiging is verzonden."; exit;
	}
}
elseif ($_POST['submitdr']) {
	$zelf = strtolower($data->login);
	$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`bc`) AS `bc` FROM `users` WHERE `login`='{$_POST['dr']}'");
	$dr = mysql_fetch_object($dbres);
	$dbres = mysql_query("SELECT * FROM `oc` WHERE `login`='{$dr->login}' OR `dr`='{$dr->login}' OR `be`='{$dr->login}' OR `we`='{$dr->login}'");
	$hot3 = mysql_num_rows($dbres);
	if (!$dr->login) { echo "Deze gebruiker bestaat niet."; exit; }
	elseif ($zelf == $dr || $dr == $oc->be || $oc->we == $dr) { echo "Je moet voor iedere taak iemand anders hebben."; exit; }
	elseif ($dr->bc - time() > 0) { echo "$dr->login heeft al een oc gedaan."; exit; }
	elseif ($dr->xp < 500) { echo "$dr->login is nog geen Thief."; exit; }
	elseif ($dr->status == dood) { echo "$dr->login is dood."; exit; }
	elseif ($hot3 == 1) { echo "$dr->login is al bezig met een oc."; exit; }
	else {
		mysql_query("UPDATE `oc` SET `dr`='{$_POST['dr']}',`ready3`='0',`auto`='',`damage`='',`autoid`='' WHERE `login`='{$data->login}'");
		mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$dr->login','OC','Je bent door $data->login uitgenodigd om samen een oc te doen in {$data->stad} als driver. Ga naar oc om een wagen te selecteren, te accepteren of te weigeren','0','1')"); 
		echo "De nieuwe driver is verwittigd."; exit;
	}
}
elseif ($_POST['submitwe']) {
	$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`bc`) AS `bc` FROM `users` WHERE `login`='{$_POST['we']}'");
	$we = mysql_fetch_object($dbres);
	$dbres = mysql_query("SELECT * FROM `oc` WHERE `login`='{$we->login}' OR `dr`='{$we->login}' OR `be`='{$we->login}' OR `we`='{$we->login}'");
	$hot1 = mysql_num_rows($dbres);
	if (!$we->login) { echo "Deze gebruiker bestaat niet."; exit; }
	elseif ($zelf == $we || $oc->dr == $we || $we == $oc->be) { echo "Je moet voor iedere taak iemand anders hebben.";exit; }
	elseif ($we->bc - time() > 0) { echo "$we->login heeft al een oc gedaan."; exit; }
	elseif ($we->xp < 500) { echo "$we->login is nog geen Thief."; exit; }
	elseif ($we->status == dood) { echo "$we->login is dood."; exit; }
	elseif ($hot1 == 1) { echo "$we->login is al bezig met een oc."; exit; }
	else {
		mysql_query("UPDATE `oc` SET `we`='{$_POST['we']}',`ready1`='0',`wapens`='0',`kogels`='0' WHERE `login`='{$data->login}'");
		mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$we->login','OC','Je bent door $data->login uitgenodigd om samen een oc te doen in {$data->stad} als WE. Ga naar oc om wapens te selecteren, te accepteren of te weigeren','0','1')"); 
		echo "De nieuwe WE is verwittigd."; exit;
	}
}
elseif ($_POST['submitbe']) {
	$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`bc`) AS `bc` FROM `users` WHERE `login`='{$_POST['be']}'");
	$be = mysql_fetch_object($dbres);
	$dbres = mysql_query("SELECT * FROM `oc` WHERE `login`='{$be->login}' OR `dr`='{$be->login}' OR `be`='{$be->login}' OR `we`='{$be->login}'");
	$hot2 = mysql_num_rows($dbres);
	if (!$be->login) { echo "Deze gebruiker bestaat niet."; exit; }
	elseif ($zelf == $be || $oc->dr == $be || $oc->we == $be) { echo "Je moet voor iedere taak iemand anders hebben."; exit; }
	elseif ($be->bc - time() > 0) { echo "$be->login heeft al een oc gedaan."; exit; }
	elseif ($be->xp < 500) { echo "$be->login is nog geen Thief."; exit; }
	elseif ($be->status == dood) { echo "$be->login is dood."; exit; }
	elseif ($hot2 == 1) { echo "$be->login is al bezig met een oc."; exit; }
	else {
		mysql_query("UPDATE `oc` SET `be`='{$_POST['be']}',`ready2`='0',`bommen`='0',`aantal`='0' WHERE `login`='{$data->login}'");
		mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$be->login','OC','Je bent door $data->login uitgenodigd om samen een oc te doen in {$data->stad} als BE. Ga naar oc om explosieven te selecteren, te accepteren of te weigeren','0','1')"); 
		echo "De nieuwe BE is verwittigd."; exit;
	}
}
elseif ($_POST['swe']) {
	$dbres = mysql_query("SELECT * FROM `oc` WHERE `we`='{$data->login}'");
	$orgcrime = mysql_fetch_object($dbres);
	$wapen = $_POST['wapon'];
	$kogels = $_POST['kogels'];
	if ($wapen == "1") { $costs = (50000+($kogels*500)) ;}
	else {$costs = (100000+($kogels*500));}
	if ($data->zak < $costs) { echo "Je hebt niet genoeg geld op zak."; exit; }
	else {
		mysql_query("UPDATE `oc` SET `wapens`='{$_POST['wapon']}',`kogels`='{$_POST['kogels']}' WHERE `we`='{$data->login}'");
		mysql_query("UPDATE `users` SET `zak`=`zak`-$costs WHERE `login`='$data->login'");
		mysql_query("UPDATE `oc` SET `ready1`='1' WHERE `we`='$data->login'"); 
        mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$orgcrime->login','WE Accept','$data->login heeft de uitnodiging geaccepteerd. Hij heeft voor &euro;{$costs} wapens gekocht.','0','1')"); 
        echo "Je bent er klaar voor."; exit;
	}
}
elseif ($_POST['sbe']) {
    $dbres = mysql_query("SELECT * FROM `oc` WHERE `be`='{$data->login}'");
	$orgcrime = mysql_fetch_object($dbres);
	$bommen = $_POST['bommen'];
	$aantal = $_POST['aantal'];
	if ($bommen == "1") { $costs = (50000*$aantal) ;}
	else {$costs = (100000*$aantal);}
	if ($data->zak < $costs) { echo "Je hebt niet genoeg geld op zak."; exit; }
	else {
		mysql_query("UPDATE `oc` SET `bommen`='{$_POST['bommen']}',`aantal`='{$_POST['aantal']}' WHERE `be`='{$data->login}'");
		mysql_query("UPDATE `users` SET `zak`=`zak`-$costs WHERE `login`='$data->login'");
		mysql_query("UPDATE `oc` SET `ready2`='1' WHERE `be`='$data->login'"); 
     	mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$orgcrime->login','BE Accept','$data->login heeft de uitnodiging geaccepteerd. Hij heeft voor &euro;{$costs} bommen gekocht.','0','1')"); 
	    echo "Je bent er klaar voor."; exit;
	}
}
elseif ($_POST['scar']) {
	$dbres = mysql_query("SELECT * FROM `oc` WHERE `dr`='{$data->login}'");
	$orgcrime = mysql_fetch_object($dbres);
	$dbres = mysql_query("SELECT stad FROM `oc` WHERE `dr`='{$data->login}'");
	$oc = mysql_fetch_object($dbres);
	$auto = mysql_fetch_object(mysql_query("SELECT * FROM `garage` WHERE `id`='{$_POST['car']}' AND `login`='{$data->login}'"));
	if (!$auto) { echo "Deze wagen is niet van jou."; exit; }
	elseif ($auto->damage > 90) { echo "Deze wagen is te hard beschadigd."; exit; }
	elseif ($auto->stad != $oc->stad) { echo "Deze wagen staat niet in $oc-stad."; exit; }
	else {
		mysql_query("UPDATE `oc` SET `autoid`='{$_POST['car']}',`auto`='{$auto->naam}',`damage`='{$auto->damage}' WHERE `dr`='{$data->login}'");
		mysql_query("DELETE FROM `garage` WHERE `login`='$data->login' AND `id`='{$_POST['car']}'");
		mysql_query("UPDATE `oc` SET `ready3`='1' WHERE `dr`='$data->login'"); 
     	mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$orgcrime->login','DR Accept','$data->login heeft de uitnodiging geaccepteerd. Hij gebruikt een wagen met een waarde van &euro;{$auto->waarde}','0','1')"); 
	    echo "Je bent er klaar voor."; exit;
	}
}
elseif (isset($_GET['accept3'])) {
	$dbres = mysql_query("SELECT * FROM `oc` WHERE `dr`='{$data->login}'");
	$orgcrime = mysql_fetch_object($dbres);
	if ($orgcrime->ready3 == 1 || $orgcrime->dr != $data->login) { echo "Je hebt al geaccepteerd, of je bent niet met een oc bezig, of je bent niet de driver."; }
	elseif ($_GET['accept3'] == 0) { 
	mysql_query("UPDATE `oc` SET `dr`='' WHERE `dr`='$data->login'"); 
	echo "Je hebt geweigerd."; 
	mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$orgcrime->login','DR Weiger','$data->login heeft de uitnodiging geweigerd.','0','1')"); }
	/*elseif ($_GET['accept3'] == 1) { 
	mysql_query("UPDATE `oc` SET `ready3`='1' WHERE `dr`='$data->login'"); 
	echo "Je hebt geaccepteerd."; 
	mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$orgcrime->login','DR Accept','$data->login heeft de uitnodiging geaccepteerd.','0','1')"); }
    */
} 
elseif (isset($_GET['accept2'])) {
	$dbres = mysql_query("SELECT * FROM `oc` WHERE `be`='{$data->login}'");
	$orgcrime = mysql_fetch_object($dbres);
	if ($orgcrime->ready2 == 1 || $orgcrime->be != $data->login) { echo "Je hebt al geaccepteerd, of je bent niet met een oc bezig, of je bent niet de be."; }
	elseif ($_GET['accept2'] == 0) { 
	mysql_query("UPDATE `oc` SET `be`='' WHERE `be`='$data->login'"); 
	echo "Je hebt geweigerd."; 
	mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$orgcrime->login','BE Weiger','$data->login heeft de uitnodiging geweigerd.','0','1')"); }
	/*elseif ($_GET['accept2'] == 1) { 
	mysql_query("UPDATE `oc` SET `ready2`='1' WHERE `be`='$data->login'"); 
	echo "Je hebt geaccepteerd."; 
	mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$orgcrime->login','BE Accept','$data->login heeft de uitnodiging geaccepteerd.','0','1')"); }
    */
} 
elseif (isset($_GET['accept1'])) {
	$dbres = mysql_query("SELECT * FROM `oc` WHERE `we`='{$data->login}'");
	$orgcrime = mysql_fetch_object($dbres);
	if ($orgcrime->ready1 == 1 || $orgcrime->we != $data->login) { echo "Je hebt al geaccepteerd, of je bent niet met een oc bezig, of je bent niet de we."; }
	elseif ($_GET['accept1'] == 0) { 
	mysql_query("UPDATE `oc` SET `we`='' WHERE `we`='$data->login'"); 
	echo "Je hebt geweigerd."; 
	mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$orgcrime->login','WE Weiger','$data->login heeft de uitnodiging geweigerd.','0','1')"); }
	/*elseif ($_GET['accept1'] == 1) { 
	mysql_query("UPDATE `oc` SET `ready1`='1' WHERE `we`='$data->login'"); 
	echo "Je hebt geaccepteerd."; 
	mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$orgcrime->login','WE Accept','$data->login heeft de uitnodiging geaccepteerd.','0','1')"); }
    */
} 
elseif (isset($_GET['cancel'])) {
	$dbres = mysql_query("SELECT * FROM `oc` WHERE `login`='{$data->login}' OR `dr`='{$data->login}' OR `be`='{$data->login}' OR `we`='{$data->login}'");
	$orgcrime = mysql_fetch_object($dbres);
	if (!$orgcrime) { echo "Je bent niet met een oc bezig."; }
	elseif ($_GET['cancel'] == 1 && $orgcrime->login == $data->login) { 
	echo "Je hebt de oc geannuleerd. Nu is iedereen zijn spullen kwijt!"; 
	mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$orgcrime->dr','OC','$data->login heeft de oc geannuleerd. Je bent nu al je spullen kwijt.','0','1')"); 
	mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$orgcrime->be','OC','$data->login heeft de oc geannuleerd. Je bent nu al je spullen kwijt.','0','1')");
	mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$orgcrime->we','OC','$data->login heeft de oc geannuleerd. Je bent nu al je spullen kwijt.','0','1')");
	mysql_query("DELETE FROM `oc` WHERE `login`='$data->login'"); 
	}
	elseif ($_GET['cancel'] == 1 && $orgcrime->dr == $data->login) { 
	echo "Je hebt de oc geannuleerd, nu moet de leider op zoek naar iemand anders."; 
	mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$orgcrime->login','DR cancel','$data->login heeft de oc geannuleerd. Nu moet je een nieuwe driver zoeken.','0','1')"); 
	mysql_query("UPDATE `oc` SET `dr`='' WHERE `dr`='$data->login'"); 
	}
	elseif ($_GET['cancel'] == 1 && $orgcrime->we == $data->login) { 
	echo "Je hebt de oc geannuleerd, nu moet de leider op zoek naar iemand anders."; 
	mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$orgcrime->login','WE cancel','$data->login heeft de oc geannuleerd. Nu moet je een nieuwe wapenexpert zoeken.','0','1')"); 
	mysql_query("UPDATE `oc` SET `we`='' WHERE `we`='$data->login'"); 
	}
	elseif ($_GET['cancel'] == 1 && $orgcrime->be == $data->login) { 
	echo "Je hebt de oc geannuleerd, nu moet de leider op zoek naar iemand anders."; 
	mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$orgcrime->login','BE cancel','$data->login heeft de oc geannuleerd. Nu moet je een nieuwe bommenexpert zoeken.','0','1')"); 
	mysql_query("UPDATE `oc` SET `be`='' WHERE `be`='$data->login'"); 
	}
} 
 elseif (isset($_GET['go'])) {
if ($oc->ready1 == 0 || $oc->ready2 == 0 || $oc->ready3 == 0 || $oc->bommen == 0 || $oc->wapens == 0 || $oc->autoid == 0 && $oc->login == $data->login) {echo"Jullie zijn nog niet klaar.";exit;}
$jtime = (time() + 600);
	$dbres = mysql_query("SELECT * FROM `oc` WHERE `login`='{$data->login}'");
	$orgcrime = mysql_fetch_object($dbres);
		$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`bc`) AS `bc` FROM `users` WHERE `login`='$orgcrime->login'");
		$leider = mysql_fetch_object($dbres);
		$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`bc`) AS `bc` FROM `users` WHERE `login`='$orgcrime->dr'");
		$dr = mysql_fetch_object($dbres);
		$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`bc`) AS `bc` FROM `users` WHERE `login`='$orgcrime->we'");
		$we = mysql_fetch_object($dbres);
		$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`bc`) AS `bc` FROM `users` WHERE `login`='$orgcrime->be'");
		$be = mysql_fetch_object($dbres);
	if (!$orgcrime) { echo "Je bent niet met een oc bezig, of je bent niet de leider."; }
	elseif ($leider->stad != $orgcrime->stad || $dr->stad != $orgcrime->stad || $we->stad != $orgcrime->stad || $be->stad != $orgcrime->stad) { echo "Niet iedereen is in $orgcrime->stad"; exit; }
	else {
		if ($leider->level + $dr->level + $we->level + $be->level > 255) { $kans = rand(1,2); } //Admin
		elseif ($leider->xp + $dr->xp + $we->xp + $be->xp < 4000) { $kans = rand(1,10); }
		elseif ($leider->xp + $dr->xp + $we->xp + $be->xp< 6000) { $kans = rand(1,10); }
		elseif ($leider->xp + $dr->xp + $we->xp + $be->xp < 9000) { $kans = rand(1,9); }
		elseif ($leider->xp + $dr->xp + $we->xp + $be->xp < 12000) { $kans = rand(1,9); }
		elseif ($leider->xp + $dr->xp + $we->xp + $be->xp < 16000) { $kans = rand(1,8); }
		elseif ($leider->xp + $dr->xp + $we->xp + $be->xp < 22000) { $kans = rand(1,8); }
		elseif ($leider->xp + $dr->xp + $we->xp + $be->xp < 30000) { $kans = rand(1,7); }
		elseif ($leider->xp + $dr->xp + $we->xp + $be->xp < 40000) { $kans = rand(1,7); } 
		elseif ($leider->xp + $dr->xp + $we->xp + $be->xp >= 40000) { $kans = rand(1,6); }
		$time = time();
		$ammount = rand(500000,4000000);
        $ammountp = rand(100000,1000000);
		if ($orgcrime->wapens == 2) {$kans = $kans-1;}
		if ($orgcrime->kogels >=25) {$kans = $kans-1;}
		if ($orgcrime->bommen == 2) {$kans = $kans-1;}
		if ($orgcrime->aantal >= 3) {$kans = $kans-1;}
		if ($orgcrime->damage >= 50) {$kans = $kans-1;}
		if ($orgcrime->damage == 100) {$kans = $kans-1;} 
		if ($kans <= 1) { 
		$slaagmessage = Array("Jullie gingen de bank binnen en maakten het alarm onschadelijk. Een bediende was net geld uit de kluis aan het halen en met een wapen onder zijn neus vulde hij 3 grote zakken geld. Jullie hebben &euro; {$ammount} gestolen.","Jullie kwamen op het geniale idee om het geld uit de portefeuilles van de aanwezigen te vragen. Jullie hebben &euro; {$ammount} verdient.","De bankdirecteur wou net het alarm laten afgaan toen {$orgcrime->we} hem neerschoot. De politie kwam, maar al schietend konden jullie toch nog wegkomen met &euro; {$ammount}.","Het was echt Fucked-Up, een van de bankbedienden begon te schreeuwen waardoor een groep security mensen afkwamen. Na een hels vuurgevecht wisten jullie toch nog te ontsnappen met &euro; {$ammount}.");
		$slaagmsgnum = rand(0,3);
		$slaagmsg = "$slaagmessage[$slaagmsgnum]";	
			echo "$slaagmsg De leider heeft het geld gekregen. Dit moeten jullie zelf verdelen";
			mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$dr->login','OC','$slaagmsg De leider heeft het geld gekregen. Dit moeten jullie zelf verdelen.','0','1')"); 
			mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$we->login','OC','$slaagmsg De leider heeft het geld gekregen. Dit moeten jullie zelf verdelen.','0','1')"); 
			mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$be->login','OC','$slaagmsg De leider heeft het geld gekregen. Dit moeten jullie zelf verdelen.','0','1')"); 
			mysql_query("UPDATE `users` SET `zak`=`zak`+$ammount,`xp`=`xp`+6,`bc`=FROM_UNIXTIME($octime),`nrofoc`=`nrofoc`+1 WHERE `login`='$data->login'");
			mysql_query("UPDATE `users` SET `xp`=`xp`+6,`bc`=FROM_UNIXTIME($octime),`nrofoc`=`nrofoc`+1 WHERE `login`='$dr->login'");
			mysql_query("UPDATE `users` SET `xp`=`xp`+6,`bc`=FROM_UNIXTIME($octime),`nrofoc`=`nrofoc`+1 WHERE `login`='$we->login'");
			mysql_query("UPDATE `users` SET `xp`=`xp`+6,`bc`=FROM_UNIXTIME($octime),`nrofoc`=`nrofoc`+1 WHERE `login`='$be->login'");

			mysql_query("DELETE FROM `oc` WHERE `login`='$data->login'");
		}
		else {
			echo "$msg";
			mysql_query("UPDATE `users` SET `xp`=`xp`+6,`bc`=FROM_UNIXTIME($octime),`nrofoc`=`nrofoc`+1 WHERE `login`='$leider->login'");
			mysql_query("UPDATE `users` SET `xp`=`xp`+6,`bc`=FROM_UNIXTIME($octime),`nrofoc`=`nrofoc`+1 WHERE `login`='$dr->login'");
			mysql_query("UPDATE `users` SET `xp`=`xp`+6,`bc`=FROM_UNIXTIME($octime),`nrofoc`=`nrofoc`+1 WHERE `login`='$we->login'");
			mysql_query("UPDATE `users` SET `xp`=`xp`+6,`bc`=FROM_UNIXTIME($octime),`nrofoc`=`nrofoc`+1 WHERE `login`='$be->login'");
			if ($msgnum == 1) { 
				mysql_query("UPDATE `users` SET `health`=`health`-2 WHERE `login`='$leider->login'");
				mysql_query("UPDATE `users` SET `health`=`health`-2 WHERE `login`='$dr->login'");
				mysql_query("UPDATE `users` SET `health`=`health`-2 WHERE `login`='$we->login'");
				mysql_query("UPDATE `users` SET `health`=`health`-2 WHERE `login`='$be->login'");
			}
			elseif ($msgnum == 2) { 
				mysql_query("INSERT INTO `jail`(`login`,`boete`,`stad`,`famillie`,`time`) VALUES('$leider->login','1000000','{$data->stad}','{$data->famillie}',FROM_UNIXTIME($jtime))");
				mysql_query("INSERT INTO `jail`(`login`,`boete`,`stad`,`famillie`,`time`) VALUES('$dr->login','1000000','{$data->stad}','{$dr->famillie}',FROM_UNIXTIME($jtime))");
				mysql_query("INSERT INTO `jail`(`login`,`boete`,`stad`,`famillie`,`time`) VALUES('$we->login','1000000','{$data->stad}','{$we->famillie}',FROM_UNIXTIME($jtime))");
				mysql_query("INSERT INTO `jail`(`login`,`boete`,`stad`,`famillie`,`time`) VALUES('$be->login','1000000','{$data->stad}','{$be->famillie}',FROM_UNIXTIME($jtime))");
			}
			elseif ($msgnum == 6) { 
				mysql_query("UPDATE `users` SET `health`=`100` WHERE `login`='$leider->login'");
				mysql_query("UPDATE `users` SET `health`=`100` WHERE `login`='$dr->login'");
				mysql_query("UPDATE `users` SET `health`=`100` WHERE `login`='$we->login'");
				mysql_query("UPDATE `users` SET `health`=`100` WHERE `login`='$be->login'");
			}
			elseif ($msgnum == 7) { 
				if ($geluk == 1) { echo "<br><br>Jullie kwamen terecht in de directeur zijn kantoor. Er was een kleine privékluis..."; $msg = "$msg Jullie kwamen terecht in de directeur zijn kantoor. Er was een kleine privékluis...";
				if ($antal != 0) { echo "<br>Er lagen $antal units $item in de kluis."; mysql_query("UPDATE `users` SET `$item`=`$item`+$antal WHERE `login`='$leider->login'"); $msg = "$msg Er lagen $antal units $item in de kluis."; }
				echo "Er lag ook nog &euro; {$ammountp} cash in. Jullie moeten zelf het geld verdelen"; mysql_query("UPDATE `users` SET `zak`=`zak`+$ammountp WHERE `login`='{$leider->login}'"); $msg = "$msg Er lag ook nog &euro; {$ammountp} cash in.";
								}					
				else { 
				echo "Jullie kwamen vast te zitten en zijn dan maar terug naar buiten gepkropen."; $msg = "$msg Jullie kwamen vast te zitten en zijn dan maar terug naar buiten gepkropen."; }
					}
			mysql_query("DELETE FROM `oc` WHERE `login`='$data->login'");
			mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$dr->login','OC','$msg','0','1')"); 
			mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$we->login','OC','$msg','0','1')"); 
			mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`,`read`,`inbox`) values(NOW(),'Notificatie','$be->login','OC','$msg','0','1')"); 
		}
	}
} 
elseif (!$oc) { 
print <<<ENDHTML
<div align=left>
Maak hier de plannen om een bank te beroven.<br>Om de plannen te maken moet je &euro; 50.000 betalen.<br>
<form method="POST">
<div align=left>
Leider : $data->login <br>
Wapen Expert : &nbsp;&nbsp;&nbsp;<input type=text name=we size=16 maxlength=16><br>
Bommen Expert : <input type=text name=be size="16" maxlength=16><br>
Driver : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=text name=dr size=16 maxlength=16><br><br>
Je code is: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src=img.php><br>
Typ hier de code in:    <input type=text name=verify><br>
<input type="submit" value="Verzenden" name="submit"></form>
ENDHTML;
}
elseif ($oc->dr == $data->login && $oc->autoid == 0) {
echo "Je moet nog een wagen opgeven.<br><br><form method=POST><input type=text name=car><input type=submit name=scar value=OK></form><br><br><a href=oc.php?accept3=0>Weigeren</a>";
}
elseif ($oc->dr == $data->login && $oc->ready3 == 0) {
echo "Je bent door $oc->login uitgenodigd om een oc te doen in {$oc->stad}.<br><br><a href=oc.php?accept3=1>Accepteren</a>&nbsp;<a href=oc.php?accept3=0>Weigeren</a>";
}
elseif ($oc->dr == $data->login && $oc->ready3 == 1 && $oc->autoid != 0) { 
echo "Je bent er klaar voor, je moet alleen nog wachten op de rest <br><br><a href=oc.php?cancel=1>Annuleer de oc</a>";
}
elseif ($oc->we == $data->login && $oc->wapens == 0) {
echo "Je moet nog wapens en een aantal kogels kiezen.<br><br><form method=POST>
  Wapen : 
  <select name=wapon>
  <option value=1>Uzi (&euro; 50.000)</option>
  <option value=2>M16 (&euro; 100.000)</option>
  </select><br>
Kogels : <input type=text name=kogels size=2 maxlength=2>
  (max 50)<br>
  <br>
<input type=submit value=Verzenden name=swe></form><br><br><a href=oc.php?accept1=0>Weigeren</a>";
}
elseif ($oc->we == $data->login && $oc->ready1 == 0) {
echo "Je bent door $oc->login uitgenodigd om een oc te doen in {$oc->stad}.<br><br><a href=oc.php?accept1=1>Accepteren</a>&nbsp;<a href=oc.php?accept1=0>Weigeren</a>";
}
elseif ($oc->we == $data->login && $oc->ready1 == 1 && $oc->wapens != 0) { 
echo "Je bent er klaar voor, je moet alleen nog wachten op de rest <br><br><a href=oc.php?cancel=1>Annuleer de oc</a>";
}
elseif ($oc->be == $data->login && $oc->bommen == 0) {
echo "Je moet nog explosieven kiezen.<br><br><form method=POST>
  Explosieven : 
  <select name=bommen>
  <option value=1>TNT (&euro; 50.000)</option>
  <option value=2>C4 (&euro; 100.000)</option>
  </select><br>
Aantal :<select name=aantal>
  <option value=1>1</option>
  <option value=2>2 (Prijs x 2)</option>
  </select><br>
  <br>
<input type=submit value=Verzenden name=sbe></form><br><br><a href=oc.php?accept2=0>Weigeren</a>";
}
elseif ($oc->be == $data->login && $oc->ready2 == 0) {
echo "Je bent door $oc->login uitgenodigd om een oc te doen in {$oc->stad}.<br><br><a href=oc.php?accept2=1>Accepteren</a>&nbsp;<a href=oc.php?accept2=0>Weigeren</a>";
}
elseif ($oc->be == $data->login && $oc->ready2 == 1 && $oc->bommen != 0) { 
echo "Je bent er klaar voor, je moet alleen nog wachten op de rest <br><br><a href=oc.php?cancel=1>Annuleer de oc</a>";
}
elseif ($oc->dr == '' && $oc->login == $data->login) { echo "Je driver heeft geannuleerd, selecteer een nieuwe.<br><form method=POST>Driver : <input type=text name=dr size=16 maxlength=16><br><br><input type=submit value=Verzenden name=submitdr></form><br><br><a href=oc.php?cancel=1>Annuleer de oc</a>"; }
elseif ($oc->we == '' && $oc->login == $data->login) { echo "Je WE heeft geannuleerd, selecteer een nieuwe.<br><form method=POST>WE : <input type=text name=we size=16 maxlength=16><br><br><input type=submit value=Verzenden name=submitwe></form><br><br><a href=oc.php?cancel=1>Annuleer de oc</a>"; }
elseif ($oc->be == '' && $oc->login == $data->login) { echo "Je BE heeft geannuleerd, selecteer een nieuwe.<br><form method=POST>BE : <input type=text name=be size=16 maxlength=16><br><br><input type=submit value=Verzenden name=submitbe></form><br><br><a href=oc.php?cancel=1>Annuleer de oc</a>"; }

elseif ($oc->ready1 == 0 || $oc->ready2 == 0 || $oc->ready3 == 0 || $oc->bommen == 0 || $oc->wapens == 0 || $oc->autoid == 0 && $oc->login == $data->login) { echo "Nog niet iedereen is klaar.<br><br><a href=oc.php?cancel=1>Annuleer de oc</a>"; }
elseif ($oc->ready1 == 1 && $oc->ready2 == 1 && $oc->ready3 == 1 && $oc->login == $data->login && $oc->bommen != 0 && $oc->wapens != 0 && $oc->autoid != 0) { echo "Jullie zijn er helemaal klaar voor.<br><br><a href=oc.php?go=1>GO!</a>&nbsp;&nbsp;&nbsp;<a href=oc.php?cancel=1>Annuleer</a>"; }
?> 
</table> 
</body> 
</html> 
