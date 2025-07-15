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
    <td class="subTitle"><b>Roulette</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
<?php  
$dbres       = mysql_query("SELECT * FROM `casino` WHERE `spel`='roulette' AND `stad`='$data->stad'"); 
$casino      = mysql_fetch_object($dbres); 
$exi = mysql_num_rows($dbres);
if ($exi == 0) { echo "Er is een casino gebouwd."; mysql_query("INSERT INTO `casino`(`spel`,`owner`,`stad`) values('roulette','','$data->stad')"); exit; }
$dbres       = mysql_query("SELECT * FROM `users` WHERE `login`='$casino->owner'"); 
$eigenaar    = mysql_fetch_object($dbres);
$exist = mysql_num_rows(mysql_query("SELECT * FROM `casino` WHERE `owner`='$data->login'"));
$user = strtolower($data->login);
$owner = strtolower($casino->owner);
if (isset($_POST['koop'])) {
	if ($data->zak < 500000) { echo "Zoveel geld heb je niet op zak."; exit; }
	elseif ($exist != 0) { echo "Je bezit al een object."; exit; }
	else { 
		mysql_query("UPDATE `users` SET `zak`=`zak`-500000 WHERE `login`='{$data->login}'"); 
		mysql_query("UPDATE `users` SET `bank`=`bank`+500000 WHERE `login`='{$casino->owner}'"); 
		mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$casino->owner}','Casino','Je $casino->spel in $casino->stad is failliet gespeeld.')");
		mysql_query("UPDATE `casino` SET `owner`='{$data->login}',`winst`='0' WHERE `spel`='{$casino->spel}' AND `stad`='{$data->stad}'") or die (mysql_error());
		echo "Je hebt $casino->spel gekocht.";  exit;
	} 
}
if($user == $owner) { 
	if(isset($_POST['verander'])) {
		$eigenaar = $_POST['eigenaar'];
		$exis = mysql_query("SELECT * FROM `users` WHERE `login`='{$eigenaar}' AND `status`='levend'");
		$exist = mysql_num_rows($exis);
		$is = mysql_query("SELECT * FROM `casino` WHERE `owner`='{$eigenaar}'");
		$xist = mysql_num_rows($is);
		if ($eigenaar == $data->login) { echo "Je bezit dit spel al."; exit; }
		elseif ($exist == 0) { echo "Deze gebruiker bestaat niet, of is dood."; exit; }
		elseif ($xist == 1) { echo "Deze gebruiker heeft al een object."; exit;}
		else {
		      mysql_query("UPDATE `casino` SET `owner`='{$eigenaar}',`inzet`='10000',`winst`='0' WHERE `owner`='{$data->login}' AND `stad`='{$data->stad}' AND `spel`='roulette'");
		      echo "De eigenaar van dit casino spel is veranderd.<META HTTP-EQUIV='refresh' CONTENT='0; URL=$PHP_SELF'>"; exit;
		}
	}
	if(isset($_POST['VA'])){ 
		if($_POST['inzet'] < 10000){ echo "De minimale inzet moet &euro;10.000 zijn."; exit; } 
		else {
			$inzet    = $_POST['inzet']; 
			mysql_query("UPDATE `casino` SET `inzet`='$inzet' WHERE `spel`='roulette' AND `stad`='$data->stad'"); 
			echo "De maximale inzet is verandert naar &euro;$inzet."; exit;
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
elseif ($owner == "") {
echo "Dit casino heeft geen eigenaar. Je kan dit spel kopen voor &euro;500.000.<br><br>
	<form method='post'>
	<input type='submit' name='koop' value='Koop'>
	</form>
";
exit;
return;
}
elseif ($eigenaar->bank <= 0) {
/*  print "De eigenaar $casino->owner is failliet. Je kan dit spel kopen voor &euro;500.000.<br><br>
	<form method='post'>
	<input type='submit' name='koop' value='Koop'>
	</form>"; 
return;
*/
mysql_query("UPDATE `casino` SET `owner`='' WHERE `stad`='{$data->stad}' AND `spel`='{$casino->spel}'");
mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$casino->owner}','Casino','Je $casino->game in $casino->stad is failliet gespeeld.')");
echo "De eigenaar is failliet gegaan."; exit;
}
elseif ($eigenaar->status == dood) {
mysql_query("UPDATE `casino` SET `owner`='' WHERE `stad`='{$data->stad}' AND `spel`='{$casino->spel}'");
mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$casino->owner}','Casino','Je $casino->game in $casino->stad is failliet gespeeld.')");
echo "De eigenaar is failliet gegaan."; exit;
}
if(isset($_POST['Gok']) && $_POST['1'] >= 0 && $_POST['2'] >= 0 && $_POST['3'] >= 0 && $_POST['4'] >= 0 && $_POST['5'] >= 0 && $_POST['6'] >= 0 && $_POST['7'] >= 0 && $_POST['8'] >= 0 && $_POST['9'] >= 0 && $_POST['10'] >= 0 && $_POST['11'] >= 0 && $_POST['12'] >= 0 && $_POST['13'] >= 0 && $_POST['14'] >= 0 && $_POST['15'] >= 0 && $_POST['16'] >= 0 && $_POST['17'] >= 0 && $_POST['18'] >= 0 && $_POST['19'] >= 0 && $_POST['20'] >= 0 && $_POST['21'] >= 0 && $_POST['22'] >= 0 && $_POST['23'] >= 0 && $_POST['24'] >= 0 && $_POST['25'] >= 0 && $_POST['26'] >= 0 && $_POST['27'] >= 0 && $_POST['28'] >= 0 && $_POST['29'] >= 0 && $_POST['30'] >= 0 && $_POST['31'] >= 0 && $_POST['32'] >= 0 && $_POST['33'] >= 0 && $_POST['34'] >= 0 && $_POST['35'] >= 0 && $_POST['36'] >= 0 && $_POST['rood'] >= 0 && $_POST['zwart'] >= 0 && $_POST['even'] >= 0 && $_POST['oneven'] >= 0 && $_POST['118'] >= 0 && $_POST['1936'] >= 0 && $_POST['112'] >= 0 && $_POST['1324'] >= 0 && $_POST['2536'] >= 0 && $_POST['rij1'] >= 0 && $_POST['rij2'] >= 0 && $_POST['rij3'] >= 0){ 
$getal           = rand(0,36); 
//if ($data->level > 200) {$getal = rand(1,18);}
//if ($data->level > 255) {$getal = 1;}
$inzet          = $_POST['1']+$_POST['2']+$_POST['3']+$_POST['4']+$_POST['5']+$_POST['6']+$_POST['7']+$_POST['8']+$_POST['9']+$_POST['10']+$_POST['11']+$_POST['12']+$_POST['13']+$_POST['14']+$_POST['15']+$_POST['16']+$_POST['17']+$_POST['18']+$_POST['19']+$_POST['20']+$_POST['21']+$_POST['22']+$_POST['23']+$_POST['24']+$_POST['25']+$_POST['26']+$_POST['27']+$_POST['28']+$_POST['29']+$_POST['30']+$_POST['31']+$_POST['32']+$_POST['33']+$_POST['34']+$_POST['35']+$_POST['36']+$_POST['rood']+$_POST['zwart']+$_POST['even']+$_POST['oneven']+$_POST['118']+$_POST['1936']+$_POST['112']+$_POST['1324']+$_POST['2536']+$_POST['rij1']+$_POST['rij2']+$_POST['rij3']; 
$inz = strlen($inzet);
if ($inz > 7) { print "Zoveel mag je niet inzetten.<br><br>";}
elseif($data->zak < $inzet){ 
print "Zoveel geld heb je niet op zak.<br><br>"; 
} 
elseif($inzet > $casino->inzet){ 
print "Zoveel geld mag je niet inzetten.<br><br>"; 
} 
else { 
$prijs           = $_POST[$getal]*36; 

if($getal == 1 || $getal == 4 || $getal == 7 || $getal == 10 || $getal == 13 || $getal == 16 || $getal == 19 || $getal == 22 || $getal == 25 || $getal == 28 || $getal == 31 || $getal == 34){ 
$rij       = 'rij1'; 
} 
elseif($getal == 2 || $getal == 5 || $getal == 8 || $getal == 11 || $getal == 14 || $getal == 17 || $getal == 20 || $getal == 23 || $getal == 26 || $getal == 29 || $getal == 32 || $getal == 35){ 
$rij       = 'rij2'; 
} 
elseif($getal == 3 || $getal == 6 || $getal == 9 || $getal == 12 || $getal == 15 || $getal == 18 || $getal == 21 || $getal == 24 || $getal == 27 || $getal == 30 || $getal == 33 || $getal == 36){ 
$rij       = 'rij3'; 
} 
$rij       = ($_POST[$rij] * 3); 
$prijs     = $prijs+$rij; 



if($getal < 13){ 
$et        = '112'; 
} 
elseif($getal > 24){ 
$et        = '2536'; 
} 
else{ 
$et        = '1324'; 
} 
$et        = $_POST[$et]*3; 
$prijs     = $prijs+$et; 


if ($getal == 0) { $kleur == ""; }
elseif($getal == 1 || $getal == 3 || $getal == 5 || $getal == 7 || $getal == 9 || $getal == 12 || $getal == 14 || $getal == 16 || $getal == 18 || $getal == 19 || $getal == 21 || $getal == 23 || $getal == 25 || $getal == 27 || $getal == 30 || $getal == 32 || $getal == 34 || $getal == 36){ 
$kleur     ='rood'; 
} 
else{ 
$kleur     ='zwart'; 
} 
if ($kleur == rood) { $color = Rood; }
elseif ($kleur == zwart) { $color = Zwart; }
$kleur     = $_POST[$kleur]*2; 
$prijs     = $prijs+$kleur; 



if($getal < 19){ 
$koos      = '118'; 
} 
else{ 
$koos      = '1936'; 
} 

$koos      = $_POST[$koos]*2; 
$prijs     = $prijs+$koos; 

if($getal ==2 || $getal ==4 || $getal ==6 || $getal ==8 || $getal ==10 || $getal ==12 || $getal ==14 || $getal ==16 || $getal ==18 || $getal ==20 || $getal ==22 || $getal ==24 || $getal ==26 || $getal ==28 || $getal ==30 || $getal ==32 || $getal ==34 || $getal ==36){ 
$even      = 'even'; 
} 
else{ 
$even      = 'oneven'; 
} 
$even      = $_POST[$even]*2; 
$prijs     = $even+$prijs; 


$prijs1    = $prijs-$inzet; 
$prijs2    = $inzet-$prijs; 
if ($getal == 0) { $prijs1 = "-1"; $prijs2 = $inzet; }

if($prijs1 < 0){ 
print "Het getal was $getal $color! Je hebt &euro;$prijs2 verloren.<br><br>"; 
mysql_query("UPDATE `users` SET `bank`=`bank`+$prijs2 WHERE `login`='$casino->owner'"); 
mysql_query("UPDATE `users` SET `zak`=`zak`-$prijs2 WHERE `login`='$data->login'"); 
mysql_query("UPDATE `casino` SET `winst`=`winst`+$prijs2 WHERE `spel`='$casino->spel' AND `stad`='$data->stad'"); 
$blat = ($data->zak - $prijs2);
}
elseif($prijs1 > 0){ 
if($eigenaar->bank - $prijs1 <= 0){
			  $winst = $eigenaar->bank;
			mysql_query("UPDATE `users` SET `zak`=`zak`+$prijs1 WHERE `login`='{$data->login}'") or die (mysql_error());
			mysql_query("UPDATE `users` SET `bank`='0' WHERE `login`='{$casino->owner}'") or die (mysql_error());
			$is = mysql_query("SELECT * FROM `casino` WHERE `owner`='{$data->login}'");
            $xist = mysql_num_rows($is);
			$own = $data->login;
			$msg = "Je hebt wel zijn casino gekregen.";
			if ($xist != 0) { $own = ""; $msg ="Zijn casino is weer vrijgekomen omdat jij al een casino bezit.";}
			mysql_query("UPDATE `casino` SET `owner`='{$own}',`winst`='0' WHERE `spel`='{$casino->spel}' AND `stad`='{$data->stad}'") or die (mysql_error());
			echo "Het getal was $getal $color!<br><br>Je hebt echter maar &euro; $prijs1 gekregen omdat $eigenaar->login niet kon uitbetalen. <br><br>$msg";
			}
			else{
print "Het getal was $getal $color! Je hebt &euro;$prijs1 gewonnen.<br><br>"; 
mysql_query("UPDATE `users` SET `bank`=`bank`-$prijs1 WHERE `login`='$casino->owner'"); 
mysql_query("UPDATE `users` SET `zak`=`zak`+$prijs1 WHERE `login`='$data->login'"); 
mysql_query("UPDATE `casino` SET `winst`=`winst`-$prijs1 WHERE `spel`='$casino->spel' AND `stad`='$data->stad'"); 
$blat = $data->zak+$prijs1;
}
} 
} 
} 
if (!$blat) { $blat = $data->zak; }
print <<<ENDHTML
<form method="post"> 
    Dit casino is in bezit van $casino->owner en zijn maximale inzet is &euro;$casino->inzet. 
    <center> 
    <table border="0" id="table1"> 
        <tr> 
            <td align="middle" width="260"> 
            <img src="images/roulettetable.gif" border="0"></td> 
            <td align="middle"><input type="hidden" name="casino"> 
            <input type="hidden" size="2" value="1" name="x"> 
            <table class="sub2" width="85%" border="0" id="table2"> 
                <tr> 
                    <td align="right">01:</td> 
                    <td><input size="5" name="1" maxlength=7></td> 
                    <td align="right">02:</td> 
                    <td><input size="5" name="2" maxlength=7></td> 
                    <td align="right">03:</td> 
                    <td><input size="5" name="3" maxlength=7></td> 
                    <td align="right">04:</td> 
                    <td><input size="5" name="4" maxlength=7></td> 
                </tr> 
                <tr> 
                    <td align="right">05:</td> 
                    <td><input size="5" name="5" maxlength=7></td> 
                    <td align="right">06:</td> 
                    <td><input size="5" name="6" maxlength=7></td> 
                    <td align="right">07:</td> 
                    <td><input size="5" name="7" maxlength=7></td> 
                    <td align="right">08:</td> 
                    <td><input size="5" name="8" maxlength=7></td> 
                </tr> 
                <tr> 
                    <td align="right">09:</td> 
                    <td><input size="5" name="9" maxlength=7></td> 
                    <td align="right">10:</td> 
                    <td><input size="5" name="10" maxlength=7></td> 
                    <td align="right">11:</td> 
                    <td><input size="5" name="11" maxlength=7></td> 
                    <td align="right">12:</td> 
                    <td><input size="5" name="12" maxlength=7></td> 
                </tr> 
                <tr> 
                    <td align="right">13:</td> 
                    <td><input size="5" name="13" maxlength=7></td> 
                    <td align="right">14:</td> 
                    <td><input size="5" name="14" maxlength=7></td> 
                    <td align="right">15:</td> 
                    <td><input size="5" name="15" maxlength=7></td> 
                    <td align="right">16:</td> 
                    <td><input size="5" name="16" maxlength=7></td> 
                </tr> 
                <tr> 
                    <td align="right">17:</td> 
                    <td><input size="5" name="17" maxlength=7></td> 
                    <td align="right">18:</td> 
                    <td><input size="5" name="18" maxlength=7></td> 
                    <td align="right">19:</td> 
                    <td><input size="5" name="19" maxlength=7></td> 
                    <td align="right">20:</td> 
                    <td><input size="5" name="20" maxlength=7></td> 
                </tr> 
                <tr> 
                    <td align="right">21:</td> 
                    <td><input size="5" name="21" maxlength=7></td> 
                    <td align="right">22:</td> 
                    <td><input size="5" name="22" maxlength=7></td> 
                    <td align="right">23:</td> 
                    <td><input size="5" name="23" maxlength=7></td> 
                    <td align="right">24:</td> 
                    <td><input size="5" name="24" maxlength=7></td> 
                </tr> 
                <tr> 
                    <td align="right">25:</td> 
                    <td><input size="5" name="25" maxlength=7></td> 
                    <td align="right">26:</td> 
                    <td><input size="5" name="26" maxlength=7></td> 
                    <td align="right">27:</td> 
                    <td><input size="5" name="27" maxlength=7></td> 
                    <td align="right">28:</td> 
                    <td><input size="5" name="28" maxlength=7></td> 
                </tr> 
                <tr> 
                    <td align="right">29:</td> 
                    <td><input size="5" name="29" maxlength=7></td> 
                    <td align="right">30:</td> 
                    <td><input size="5" name="30" maxlength=7></td> 
                    <td align="right">31:</td> 
                    <td><input size="5" name="31" maxlength=7></td> 
                    <td align="right">32:</td> 
                    <td><input size="5" name="32" maxlength=7></td> 
                </tr> 
                <tr> 
                    <td align="right">33:</td> 
                    <td><input size="5" name="33" maxlength=7></td> 
                    <td align="right">34:</td> 
                    <td><input size="5" name="34" maxlength=7></td> 
                    <td align="right">35:</td> 
                    <td><input size="5" name="35" maxlength=7></td> 
                    <td align="right">36:</td> 
                    <td><input size="5" name="36" maxlength=7></td> 
                </tr> 
                <tr> 
                    <td align="right">Rood:</td> 
                    <td><input size="5" name="rood" maxlength=7></td> 
                    <td align="right">Zwart:</td> 
                    <td><input size="5" name="zwart" maxlength=7></td> 
                    <td align="right">On-even:</td> 
                    <td><input size="5" name="oneven" maxlength=7></td> 
                    <td align="right">Even:</td> 
                    <td><input size="5" name="even" maxlength=7></td> 
                </tr> 
                <tr> 
                    <td align="right">1-18:</td> 
                    <td><input size="5" name="118" maxlength=7></td> 
                    <td align="right">19-36:</td> 
                    <td><input size="5" name="1936" maxlength=7></td> 
                    <td align="right">1-12:</td> 
                    <td><input size="5" name="112" maxlength=7></td> 
                    <td align="right">13-24:</td> 
                    <td><input size="5" name="1324" maxlength=7></td> 
                </tr> 
                <tr> 
                    <td align="right">25-36:</td> 
                    <td><input size="5" name="2536" maxlength=7></td> 
                    <td align="right">1st Rij:</td> 
                    <td><input size="5" name="rij1" maxlength=7></td> 
                    <td align="right">2nd Rij:</td> 
                    <td><input size="5" name="rij2" maxlength=7></td> 
                    <td align="right">3rd Rij:</td> 
                    <td><input size="5" name="rij3" maxlength=7></td> 
                </tr> 
                <tr> 
                    <td align="middle" colSpan="4"> 
                    <input type="submit" value="Gok" name="Gok">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="reset" value="Reset"></td> 
                </tr> 
                <tr> 
                    <td align="middle" colSpan="4">Je hebt <b>&euro;$blat</b> 
                    om te gokken..</td> 
                </tr> 
            </table> 
            </td> 
        </tr> 
    </table> 
    </center> 
</form> 
ENDHTML;
         
    
    ?> 
    </table> 

</body> 
</html>