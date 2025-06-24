<?php
declare(strict_types=1);
require 'config.php';
$stmt = pdo_query(
    "SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`," .
    "UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`," .
    "UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`," .
    "UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` " .
    "FROM `users` WHERE `login` = ?",
    [$_SESSION['login']]
);
$data = $stmt->fetch();
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
    <td class="subTitle"><b>Blackjack</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
<?php
$picurl = "http://members.lycos.nl/js6287/images/kaarten/";
$ext = ".jpg";
$stmt = pdo_query("SELECT * FROM `casino` WHERE `spel`='blackjack' AND `stad` = ?", [$data->stad]);
$casino = $stmt->fetch();
$exi = $stmt->rowCount();
$stmt = pdo_query("SELECT * FROM `users` WHERE `login` = ?", [$casino->owner]);
$eigenaar = $stmt->fetch();
$user = strtolower($data->login);
$owner = strtolower($casino->owner);
$stmt = pdo_query("SELECT * FROM `casino` WHERE `owner` = ?", [$data->login]);
$exist = $stmt->rowCount();
if ($exi == 0) { echo "Er is een casino gebouwd."; pdo_query("INSERT INTO `casino`(`spel`,`owner`,`stad`) values('blackjack','',?)", [$data->stad]); exit; }
elseif (isset($_POST['koop'])) {
if ($data->zak < 500000) { echo "Je hebt niet genoeg geld op zak."; exit; }
elseif ($exist != 0) { echo "Je bezit al een object."; exit; }
else {
pdo_query("UPDATE `users` SET `zak`=`zak`-500000 WHERE `login` = ?", [$data->login]);
pdo_query("UPDATE `users` SET `bank`=`bank`+500000 WHERE `login` = ?", [$casino->owner]);
pdo_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie',?,?,?)", [$casino->owner,'Casino','Je '.$casino->game.' in '.$casino->stad.' is failliet gespeeld.']);
pdo_query("UPDATE `casino` SET `owner`=?,`winst`='0' WHERE `spel`=? AND `stad`=?", [$data->login,$casino->spel,$data->stad]);
echo "Je hebt $casino->spel gekocht."; exit;
}
}
if (!$casino->owner) {
echo "Dit casino heeft geen eigenaar. Je kan dit spel kopen voor &euro;500.000.<br><br>
	<form method='post'>
	<input type='submit' name='koop' value='Koop'>
	</form>
";
exit;
return;
}
elseif ($eigenaar->bank < 50000) {
pdo_query("UPDATE `casino` SET `owner`='' WHERE `stad`=? AND `spel`=?", [$data->stad, $casino->spel]);
pdo_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie',?,?,?)", [$casino->owner,'Casino','Je '.$casino->game.' in '.$casino->stad.' is failliet gespeeld.']);
echo "De eigenaar is failliet gegaan."; exit;
}
elseif ($eigenaar->status == dood) {
pdo_query("UPDATE `casino` SET `owner`='' WHERE `stad`=? AND `spel`=?", [$data->stad, $casino->spel]);
echo "De eigenaar is failliet gegaan."; exit;
}
elseif($owner == $user){ 
if(isset($_POST['verander'])) {
$eigenaar = $_POST['eigenaar'];
$stmt = pdo_query("SELECT * FROM `users` WHERE `login` = ? AND `status` = 'levend'", [$eigenaar]);
$exist = $stmt->rowCount();
$stmt = pdo_query("SELECT * FROM `casino` WHERE `owner` = ?", [$data->login]);
$xist = $stmt->rowCount();
if ($eigenaar == $data->login) { print "Je bezit dit spel al."; }
elseif ($exist == 0) { print "Deze gebruiker bestaat niet, of is dood."; }
elseif ($xist == 1) { echo "Deze gebruiker heeft al een object."; exit;}
else {
      pdo_query("UPDATE `casino` SET `owner`=?,`inzet`='10000',`winst`='0' WHERE `spel`=? AND `stad`=?", [$eigenaar,$casino->spel,$data->stad]);
      print "De eigenaar van dit casino spel is veranderd.
<META HTTP-EQUIV='refresh' CONTENT='0; URL=$PHP_SELF'>";
}
}
elseif(isset($_POST['VA'])){ 
$_POST['inzet']= str_replace(",","",$_POST['inzet']);
if(!preg_match('/^[0-9]+$/',$_POST['inzet'])){echo"Ongeldig bedrag.";}
elseif($_POST['inzet'] < 100){ 
print "De minimale inzet moet &euro;100 zijn."; 
} 
else {

$inzet    = $_POST['inzet'];
pdo_query("UPDATE `casino` SET `inzet`=? WHERE `spel`=? AND `stad`=?", [$inzet,$casino->spel,$data->stad]);
print "De maximale inzet is veranderd naar &euro;$inzet.<meta http-equiv=Refresh content=3;url=blackjack.php>"; 
} 
}
print "<form method='post'>
	  <width=100>Nieuwe eigenaar:		<input type='text' name='eigenaar' maxlength=16>
  				<align='right'><input type='submit' name='verander' value='Verander'>
</form><br><br><form method='POST'> 
    <p>De maximale inzet is nu &euro;$casino->inzet.</p> 
    <p>&nbsp;Maximale inzet:<input type='text' name='inzet' size='20' maxlength=7></p> 
    <p><input type='submit' value='Verander' name='VA'></p> 
</form>";
exit;
} 
elseif($_POST['start']) {
$_POST['inzet']= str_replace(",","",$_POST['inzet']);
if(!preg_match('/^[0-9]+$/',$_POST['inzet'])){echo"Ongeldig bedrag.";}
	$inzet = $_POST['inzet'];
        //if($data->level > 255){mysql_query("UPDATE `users` SET `zak`=`zak`+$inzet WHERE `login`='{$data->login}'") or die (mysql_error());mysql_query("UPDATE `users` SET `bank`=`bank`-$inzet WHERE `login`='{$casino->owner}'") or die (mysql_error());mysql_query("UPDATE `casino` set `winst`=`winst`-$inzet WHERE `spel`='$casino->spel' AND `stad`='$data->stad'") or die (mysql_error());echo"Je hebt $inzet gewonnen";exit;}
	if ($inzet > $casino->inzet || $inzet < 1) {echo "De maximale inzet is &euro;$casino->inzet.";}
	elseif($data->zak < $inzet) {echo "Je hebt niet genoeg geld om te spelen.";} 
	else {
                pdo_query("DELETE FROM `blackjack` WHERE `login` = ?", [$data->login]);
		$winst = ($inzet * 3);
		$getal = rand(1,13);
		$soort = rand(1,4);
		$getal2 = rand(1,13);
		$soort2 = rand(1,4);
		if($soort == 1) {$soort = "harten";}
		if($soort == 2) {$soort = "schoppen";}
		if($soort == 3) {$soort = "ruiten";}
		if($soort == 4) {$soort = "klaveren";}
		if($soort2 == 1) {$soort2 = "harten";}
		if($soort2 == 2) {$soort2 = "schoppen";}
		if($soort2 == 3) {$soort2 = "ruiten";}
		if($soort2 == 4) {$soort2 = "klaveren";}
		$geta = $getal;
		$geta2 = $getal2;
		if($getal == 11) {$geta = "boer"; $getal = 10;}
		if($getal == 12) {$geta = "vrouw"; $getal = 10;}
		if($getal == 13) {$geta = "koning"; $getal = 10;}
		if($getal == 1) {$geta = "aas"; $getal = 11; $aas = 1;}
		if($getal2 == 11) {$geta2 = "boer"; $getal2 = 10;}
		if($getal2 == 12) {$geta2 = "vrouw"; $getal2 = 10;}
		if($getal2 == 13) {$geta2 = "koning"; $getal2 = 10;}
		if($getal2 == 1) {$geta2 = "aas"; $getal2 = 11; $aas = ($aas + 1);}
		$picture = "<img src=".$picurl.$soort.$geta.$ext.">";
		$picture2 = "<img src=".$picurl.$soort2.$geta2.$ext.">";
		$pictures = $picture." &nbsp;&nbsp; ".$picture2;
		if(($getal + $getal2) == 21) {
		if($eigenaar->bank - $winst <= 0){
			  $winst = $eigenaar->bank;
                        pdo_query("UPDATE `users` SET `zak`=`zak`+? WHERE `login` = ?", [$winst,$data->login]);
                        pdo_query("UPDATE `users` SET `bank`='0' WHERE `login` = ?", [$casino->owner]);
                        $stmt = pdo_query("SELECT * FROM `casino` WHERE `owner` = ?", [$data->login]);
            $xist = $stmt->rowCount();
			$own = $data->login;
			$msg = "Je hebt wel zijn casino gekregen.";
			if ($xist != 0) { $own = ""; $msg ="Zijn casino is weer vrijgekomen omdat jij al een casino bezit.";}
                        pdo_query("UPDATE `casino` SET `owner`=?,`winst`='0' WHERE `spel`=? AND `stad`=?", [$own,$casino->spel,$data->stad]);
			echo "<center>$pictures </center><br><br>";echo"Je hebt een $soort $geta en een $soort2 $geta2. Blackjack!<br><br>Je hebt echter maar &euro; $winst gekregen omdat $eigenaar->login niet kon uitbetalen. <br><br>$msg";
			}
			else{
			echo"<center>$pictures </center><br><br>";echo"Je hebt een $soort $geta en een $soort2 $geta2. Blackjack! Je hebt &euro;$winst gewonnen.<br><br><a href=blackjack.php>Speel opnieuw</a>";
                        pdo_query("UPDATE `users` SET `zak`=`zak`+? WHERE `login` = ?", [$winst,$data->login]);
                        pdo_query("UPDATE `users` SET `bank`=`bank`-? WHERE `login` = ?", [$winst,$casino->owner]);
                        pdo_query("UPDATE `casino` set `winst`=`winst`-? WHERE `spel`=? AND `stad`=?", [$winst,$casino->spel,$data->stad]);
		    }
		}
		else {
		$som = ($getal + $getal2);
		if($aas > 0) {$som2 = $som-10;}
		$getald = rand(1,13);
		$soortd = rand(1,4);
		if($soortd == 1) {$soortd = "harten";}
		if($soortd == 2) {$soortd = "schoppen";}
		if($soortd == 3) {$soortd = "ruiten";}
		if($soortd == 4) {$soortd = "klaveren";}
		$getad = $getald;
		if($getald == 11) {$getad = "boer"; $getald = 10;}
		if($getald == 12) {$getad = "vrouw"; $getald = 10;}
		if($getald == 13) {$getad = "koning"; $getald = 10;}
		if($getald == 1) {$getad = "aas"; $getald = 11;}
		$pictured = "<img src=".$picurl.$soortd.$getad.$ext.">";
                pdo_query("UPDATE `users` SET `zak`=`zak`-? WHERE `login` = ?", [$inzet,$data->login]);
                pdo_query("INSERT INTO `blackjack`(`login`,`inzet`,`kaart`,`kaartpic`,`aas`,`dealer`,`dealerpic`) values(?,?,?,?,?, ?, ?)", [$data->login,$inzet,$som,$pictures,$aas,$getald,$pictured]);
		if($aas > 0) {echo"<center>$pictures &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; $pictured </center><br><br>";echo"Je hebt een $soort $geta en een $soort2 $geta2.<br>Dat geeft $som of $som2. De deler heeft een $soortd $getad, welke waarde geef je aan je aas?<br><br><form method='post'><input type='submit' name='aas' value='1'> &nbsp;&nbsp; <input type='submit' name='aas2' value='11'></form>"; exit;}
		echo"<center>$pictures &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; $pictured </center><br><br>";
		echo"Je hebt een $soort $geta en een $soort2 $geta2.<br>Dat geeft $som. De deler heeft een $soortd $getad, wil je nog een kaart of wil je stoppen?<br><br><form method='post'><input type='submit' name='nieuw' value='Nog een kaart'> &nbsp;&nbsp; <input type='submit' name='stop' value='Stop'></form>";
		}
	}
} 
elseif($_POST['nieuw']) {
        $stmt = pdo_query("SELECT * FROM `blackjack` WHERE `login` = ?", [$data->login]);
        $vorige = $stmt->fetch();
	$winst = (2*$vorige->inzet);
	$inzet = $vorige->inzet;
	$getal = rand(1,13);
	$soort = rand(1,4);
	if($soort == 1) {$soort = "harten";}
	if($soort == 2) {$soort = "schoppen";}
	if($soort == 3) {$soort = "ruiten";}
	if($soort == 4) {$soort = "klaveren";}
	$geta = $getal;
	if($getal == 11) {$geta = "boer"; $getal = 10;}
	if($getal == 12) {$geta = "vrouw"; $getal = 10;}
	if($getal == 13) {$geta = "koning"; $getal = 10;}
	if($getal == 1) {$geta = "aas"; $getal = 11; $aas = 1;}
	$som = ($vorige->kaart + $getal);
	$so = ($vorige->kaart + 1);
	$picture = "<img src=".$picurl.$soort.$geta.$ext.">";
	$pictures = $vorige->kaartpic." &nbsp;&nbsp; ".$picture;
	$pictured = $vorige->dealerpic;
	if($getal == 11) {$som2 = "of $so";}
        pdo_query("UPDATE `blackjack` SET `aas`=?,`kaart`=?,`kaartpic`=? WHERE `login` = ?", [$aas,$som,$pictures,$data->login]);
	if($aas > 0) {echo"<center>$pictures &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; $pictured </center><br><br>";echo"Je hebt een $soort $geta.<br>Dat geeft $som $som2. De deler heeft {$vorige->dealer}, welke waarde geef je aan je aas?<br><br><form method='post'><input type='submit' name='aas' value='1'> &nbsp;&nbsp; <input type='submit' name='aas2' value='11'></form>";exit;}
	if($som > 21) {
        pdo_query("UPDATE `users` SET `bank`=`bank`+? WHERE `login` = ?", [$inzet,$casino->owner]);
        pdo_query("UPDATE `casino` set `winst`=`winst`+? WHERE `spel`=? AND `stad`=?", [$inzet,$casino->spel,$data->stad]);
    echo"<center>$pictures &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; $pictured </center><br><br>";
	echo"Je trok een $soort $geta. Nu heb je dus $som. Je bent kapot.<br><br><a href=blackjack.php>Speel opnieuw</a>";
	exit;
	}
	echo"<center>$pictures &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; $pictured </center><br><br>";
	echo"Je hebt een $soort $geta.<br>Dat geeft $som. De deler heeft {$vorige->dealer}, wil je nog een kaart of wil je stoppen?<br><br><form method='post'><input type='submit' name='nieuw' value='Nog een kaart'> &nbsp;&nbsp; <input type='submit' name='stop' value='Stop'></form>";
}
elseif($_POST['aas']) {
        $stmt = pdo_query("SELECT * FROM `blackjack` WHERE `login` = ?", [$data->login]);
        $vorige = $stmt->fetch();
    $pictures = $vorige->kaartpic;
	$pictured = $vorige->dealerpic;
	$i=0;
	while ($i < $vorige->aas){
	$som = ($vorige->kaart - 10);
	$i++;
	}
        pdo_query("UPDATE `blackjack` SET `aas`='0',`kaart`=? WHERE `login` = ?", [$som,$data->login]);
	echo"<center>$pictures &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; $pictured </center><br><br>";
	echo"Je hebt nu $som, wilt u nog een kaart, of stoppen?<br><br><form method='post'><input type='submit' name='nieuw' value='Nog een kaart'> &nbsp;&nbsp; <input type='submit' name='stop' value='Stop'></form>";
}
elseif($_POST['aas2']) {
        $stmt = pdo_query("SELECT * FROM `blackjack` WHERE `login` = ?", [$data->login]);
        $vorige = $stmt->fetch();
	$som = $vorige->kaart;
    $pictures = $vorige->kaartpic;
	$pictured = $vorige->dealerpic;
        pdo_query("UPDATE `blackjack` SET `aas`='0' WHERE `login` = ?", [$data->login]);
	echo"<center>$pictures &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; $pictured </center><br><br>";
	echo"Je hebt nu $som, wilt u nog een kaart, of stoppen?<br><br><form method='post'><input type='submit' name='nieuw' value='Nog een kaart'> &nbsp;&nbsp; <input type='submit' name='stop' value='Stop'></form>";
}
elseif($_POST['stop']) {
        $stmt = pdo_query("SELECT * FROM `blackjack` WHERE `login` = ?", [$data->login]);
        $vorige = $stmt->fetch();
	$som = $vorige->kaart;
    $pictures = $vorige->kaartpic;
	$pictured = $vorige->dealerpic;
	if($som > 21) {
        pdo_query("UPDATE `users` SET `bank`=`bank`+? WHERE `login` = ?", [$inzet,$casino->owner]);
        pdo_query("UPDATE `casino` set `winst`=`winst`+? WHERE `spel`=? AND `stad`=?", [$inzet,$casino->spel,$data->stad]);
	echo"<center>$pictures &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; $pictured </center><br><br>";
	echo"Je hebt $som. Je bent kapot. Je hebt &euro; $vorige->inzet verloren.<br><br><a href=blackjack.php>Speel opnieuw</a>";
	exit;}
	$dealer = $vorige->dealer;
	echo"<center>$pictures </center><br><br>";
	echo"Je hebt $som. De deler krijgt nu kaarten:";
	$getal = rand(1,13);
	$soort = rand(1,4);
	if($soort == 1) {$soort = "harten";}
	if($soort == 2) {$soort = "schoppen";}
	if($soort == 3) {$soort = "ruiten";}
	if($soort == 4) {$soort = "klaveren";}
	$geta = $getal;
	if($getal == 11) {$geta = "boer"; $getal = 10;}
	if($getal == 12) {$geta = "vrouw"; $getal = 10;}
	if($getal == 13) {$geta = "koning"; $getal = 10;}
	if($getal == 1) {$geta = "aas"; $getal = 11; $aas = 1;}
	echo"<br>Hij trekt een $soort $geta";
	$picture = "<img src=".$picurl.$soort.$geta.$ext.">";
	$pictured = $pictured." &nbsp;&nbsp; ".$picture;
	$somd = ($dealer + $getal);
	if($somd > 21 && $aas == 1) {$dealer = ($dealer + 1);echo", hij heeft de aas de waarde 1 gegeven"; $aas = 0;}
	else {$dealer = $somd;}
        echo" dus heeft hij $dealer";
	while ($dealer < $som){
	$getal = rand(1,13);
	$soort = rand(1,4);
	if($soort == 1) {$soort = "harten";}
	if($soort == 2) {$soort = "schoppen";}
	if($soort == 3) {$soort = "ruiten";}
	if($soort == 4) {$soort = "klaveren";}
	$geta = $getal;
	if($getal == 11) {$geta = "boer"; $getal = 10;}
	if($getal == 12) {$geta = "vrouw"; $getal = 10;}
	if($getal == 13) {$geta = "koning"; $getal = 10;}
	if($getal == 1) {$geta = "aas"; $getal = 11; $aas = 1;}
	echo"<br>Hij trekt een $soort $geta";
	$picture = "<img src=".$picurl.$soort.$geta.$ext.">";
	$pictured = $pictured." &nbsp;&nbsp; ".$picture;
	$somd = ($dealer + $getal);
	if($somd > 21 && $aas == 1) {$dealer = ($dealer + 1);echo", hij heeft de aas de waarde 1 gegeven"; $aas = 0;}
	else {$dealer = $somd;}
        echo" dus heeft hij $dealer.";
	}
	echo"<br><br><center>$pictured </center>";
	$winst = (2*$vorige->inzet);
	$inzet = ($vorige->inzet);
	if($dealer > 21){echo"<br><br>De deler is kapot, je hebt &euro;$inzet gewonnen.<br><br><a href=blackjack.php>Speel opnieuw</a>";
                        pdo_query("UPDATE `users` SET `zak`=`zak`+? WHERE `login` = ?", [$winst,$data->login]);
                        pdo_query("UPDATE `users` SET `bank`=`bank`-? WHERE `login` = ?", [$inzet,$casino->owner]);
                        pdo_query("UPDATE `casino` set `winst`=`winst`-? WHERE `spel`=? AND `stad`=?", [$inzet,$casino->spel,$data->stad]);
	}
	elseif($dealer < $som) {
	if($eigenaar->bank - $winst <= 0){
			  $winst = $eigenaar->bank;
                        pdo_query("UPDATE `users` SET `zak`=`zak`+? WHERE `login` = ?", [$winst,$data->login]);
                        pdo_query("UPDATE `users` SET `bank`='0' WHERE `login` = ?", [$casino->owner]);
                        $stmt = pdo_query("SELECT * FROM `casino` WHERE `owner` = ?", [$data->login]);
            $xist = $stmt->rowCount();
			$own = $data->login;
			$msg = "Je hebt wel zijn casino gekregen.";
			if ($xist != 0) { $own = ""; $msg ="Zijn casino is weer vrijgekomen omdat jij al een casino bezit.";}
                        pdo_query("UPDATE `casino` SET `owner`=?,`winst`='0' WHERE `spel`=? AND `stad`=?", [$own,$casino->spel,$data->stad]);
			echo "<br><br>Je hebt meer dan de dealer.<br><br>Je hebt echter maar &euro; $winst gekregen omdat $eigenaar->login niet kon uitbetalen. <br><br>$msg";
			}
			else{
	echo"<br><br>Je hebt meer dan de dealer, je hebt &euro;$inzet gewonnen.<br><br><a href=blackjack.php>Speel opnieuw</a>";
        pdo_query("UPDATE `users` SET `zak`=`zak`+? WHERE `login` = ?", [$winst,$data->login]);
        pdo_query("UPDATE `users` SET `bank`=`bank`-? WHERE `login` = ?", [$inzet,$casino->owner]);
        pdo_query("UPDATE `casino` set `winst`=`winst`-? WHERE `spel`=? AND `stad`=?", [$inzet,$casino->spel,$data->stad]);
	}
	}
	elseif($dealer > $som){
	echo"<br><br>Je hebt minder dan de dealer, je hebt &euro;$inzet verloren.<br><br><a href=blackjack.php>Speel opnieuw</a>";
        pdo_query("UPDATE `users` SET `bank`=`bank`+? WHERE `login` = ?", [$inzet,$casino->owner]);
        pdo_query("UPDATE `casino` set `winst`=`winst`+? WHERE `spel`=? AND `stad`=?", [$inzet,$casino->spel,$data->stad]);
	}
	elseif($dealer == $som){
	echo"<br><br>Je hebt evenveel als de dealer. Zijn kaarten tellen echter hoger, je hebt &euro;$inzet verloren.<br><br><a href=blackjack.php>Speel opnieuw</a>";
        pdo_query("UPDATE `users` SET `bank`=`bank`+? WHERE `login` = ?", [$inzet,$casino->owner]);
        pdo_query("UPDATE `casino` set `winst`=`winst`+? WHERE `spel`=? AND `stad`=?", [$inzet,$casino->spel,$data->stad]);
	}
        pdo_query("DELETE FROM `blackjack` WHERE `login` = ?", [$data->login]);
}
else {
	echo "$casino->owner heeft de maximale inzet gezet op &euro;$casino->inzet.<br><br>
	<form method='post'>
	Inzet: <input type='text' name='inzet' size='3' maxlength='7'><br><br>
	<input type='submit' name='start' value='Start!'>
	</form>";
}
?></font></center></body></html>