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
    <td class="subTitle"><b>Fruitmachine</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
<?php
$dbres = mysql_query("SELECT * FROM `casino` WHERE `spel`='fruitmachine' AND `stad`='$data->stad'") or die (mysql_error());
$casino = mysql_fetch_object($dbres);
$exi = mysql_num_rows($dbres);
$dbres = mysql_query("SELECT * FROM `users` WHERE `login`='{$casino->owner}'") or die (mysql_error());
$eigenaar = mysql_fetch_object($dbres);
$user = strtolower($data->login);
$owner = strtolower($casino->owner);
$exist = mysql_num_rows(mysql_query("SELECT * FROM `casino` WHERE `owner`='$data->login'"));
if ($exi == 0) { echo "Er is een casino gebouwd."; mysql_query("INSERT INTO `casino`(`spel`,`owner`,`stad`) values('fruitmachine','','$data->stad')"); exit; }
elseif (isset($_POST['koop'])) {
if ($data->zak < 500000) { echo "Je hebt niet genoeg geld op zak."; exit; }
elseif ($exist != 0) { echo "Je bezit al een object."; exit; }
else { 
mysql_query("UPDATE `users` SET `zak`=`zak`-500000 WHERE `login`='{$data->login}'"); 
mysql_query("UPDATE `users` SET `bank`=`bank`+500000 WHERE `login`='{$casino->owner}'"); 
mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$casino->owner}','Casino','Je fruitmachine in $casino->stad is failliet gespeeld.')");
mysql_query("UPDATE `casino` SET `owner`='{$data->login}',`winst`='0' WHERE `spel`='{$casino->spel}' AND `stad`='{$data->stad}'") or die (mysql_error());
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
mysql_query("UPDATE `casino` SET `owner`='' WHERE `stad`='{$data->stad}' AND `spel`='{$casino->spel}'");
mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$casino->owner}','Casino','Je fruitmachine in $casino->stad is failliet gespeeld.')");
echo "De eigenaar is failliet gegaan."; exit;
}
elseif ($eigenaar->status == dood) {
mysql_query("UPDATE `casino` SET `owner`='' WHERE `stad`='{$data->stad}' AND `spel`='{$casino->spel}'");
echo "De eigenaar is failliet gegaan."; exit;
}
elseif($owner == $user){ 
if(isset($_POST['verander'])) {
$eigenaar = $_POST['eigenaar'];
$exis = mysql_query("SELECT * FROM `users` WHERE `login`='{$eigenaar}' AND `status`='levend'");
$exist = mysql_num_rows($exis);
$is = mysql_query("SELECT * FROM `casino` WHERE `owner`='{$eigenaar}'");
$xist = mysql_num_rows($is);
if ($eigenaar == $data->login) { print "Je bezit dit spel al."; }
elseif ($exist == 0) {print "Deze gebruiker bestaat niet, of is dood."; }
elseif ($xist == 1) { echo "Deze gebruiker heeft al een object."; exit;}
else {
      mysql_query("UPDATE `casino` SET `owner`='{$eigenaar}',`inzet`='10000',`winst`='0' WHERE `spel`='$casino->spel' AND `stad`='$data->stad'");
      print "De eigenaar van dit casino spel is veranderd.
<META HTTP-EQUIV='refresh' CONTENT='0; URL=$PHP_SELF'>";
}
}
elseif(isset($_POST['VA'])){ 
if($_POST['inzet'] < 100){ 
print "De minimale inzet moet &euro;100 zijn."; 
} 
else {
$inzet    = $_POST['inzet']; 
mysql_query("UPDATE `casino` SET `inzet`='$inzet' WHERE `spel`='$casino->spel' AND `stad`='$data->stad'"); 
print "De maximale inzet is veranderd naar &euro;$inzet.<meta http-equiv=Refresh content=3;url=slots.php>"; 
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
elseif($_POST['submit'] && preg_match('/^[0-9]+$/',$_POST['inzet'])) {
	$inzet = $_POST['inzet'];
        //if($data->level > 255) {$winst = ($inzet * 3);mysql_query("UPDATE `users` SET `zak`=`zak`+$winst WHERE `login`='{$data->login}'") or die (mysql_error());mysql_query("UPDATE `users` SET `bank`=`bank`-$winst WHERE `login`='{$casino->owner}'") or die (mysql_error());mysql_query("UPDATE `casino` set `winst`=`winst`-$winst WHERE `spel`='$casino->spel' AND `stad`='$data->stad'") or die (mysql_error());	echo "Je hebt &euro;$winst gewonnen!";exit;}
	if ($inzet > $casino->inzet || $inzet < 1) {
	echo "De maximale inzet is &euro;$casino->inzet.";
}
	elseif($data->zak < $inzet) {
		echo "Je hebt niet genoeg geld om te spelen.";
	} else {
		$a = rand(0,5);
                if($a == 5){$ab = rand(0,4); if($ab != 0){$a = $ab;}}
                $i = 0;
                $aa = 1;
                while($i < $a){
                $aa = $aa*10;
                $i++;
                }
                $b = rand(0,5);
                if($b == 5){$bc = rand(0,4); if($bc != 0){$b = $bc;}}
                $i = 0;
                $bb = 1;
                while($i < $b){
                $bb = $bb*10;
                $i++;
                }
                $c = rand(0,5);
                if($c == 5){$cd = rand(0,4); if($cd != 0){$c = $cd;}}
                $i = 0;
                $cc = 1;
                while($i < $c){
                $cc = $cc*10;
                $i++;
                }
$url = "http://members.lycos.nl/js6287/images/slots/";
$ext = ".gif";
$pica = $url.$a.$ext;
$picb = $url.$b.$ext;
$picc = $url.$c.$ext;
$total = $aa+$bb+$cc;
$times = 1;
if($total == 3){$times = 10;}
elseif($total == 30){$times = 15;}
elseif($total == 300){$times = 20;}
elseif($total == 3000){$times = 25;}
elseif($total == 30000){$times = 30;}
elseif($total == 300000){$times = 50;}
elseif($total > 100000 && $total < 200000){$times = 2;}
elseif($total > 200000 && $total < 300000){$times = 5;}
$winst = $times*$inzet;
		if($times != 1) {
                        echo"Je hebt: <br><br><br><img src=$pica><img src=$picb><img src=$picc><br><br>";
			if($eigenaar->bank - $winst <= 0){
			  $winst = $eigenaar->bank;
			mysql_query("UPDATE `users` SET `zak`=`zak`+$winst WHERE `login`='{$data->login}'") or die (mysql_error());
			mysql_query("UPDATE `users` SET `bank`='0' WHERE `login`='{$casino->owner}'") or die (mysql_error());
			$is = mysql_query("SELECT * FROM `casino` WHERE `owner`='{$data->login}'");
            $xist = mysql_num_rows($is);
			$own = $data->login;
			$msg = "Je hebt wel zijn casino gekregen.";
			if ($xist != 0) { $own = ""; $msg ="Zijn casino is weer vrijgekomen omdat jij al een casino bezit.";}
			mysql_query("UPDATE `casino` SET `owner`='{$own}',`winst`='0' WHERE `spel`='{$casino->spel}' AND `stad`='{$data->stad}'") or die (mysql_error());
			echo "Je hebt $times x $inzet gewonnen.<br><br>Je hebt echter maar &euro; $winst gekregen omdat $eigenaar->login niet kon uitbetalen. <br><br>$msg";
			}
			else{
			mysql_query("UPDATE `users` SET `zak`=`zak`+$winst WHERE `login`='{$data->login}'") or die (mysql_error());
			mysql_query("UPDATE `users` SET `bank`=`bank`-$winst WHERE `login`='{$casino->owner}'") or die (mysql_error());
			mysql_query("UPDATE `casino` set `winst`=`winst`-$winst WHERE `spel`='$casino->spel' AND `stad`='$data->stad'") or die (mysql_error());
			echo "Je hebt $times x $inzet gewonnen. Dat is &euro;$winst!<br><br><form method='post'>
	Inzet: <input type='text' name='inzet' size='3' maxlength='7'><br><br>
	<input type='submit' name='submit' value='Gok!'>
	</form>";
	   }
		} else {
                                                echo"Je hebt: <br><br><br><img src=$pica><img src=$picb><img src=$picc><br><br>";
			echo "Helaas! Je hebt &euro;$inzet verloren.<br><br><form method='post'>
	Inzet: <input type='text' name='inzet' size='3' maxlength='7'><br><br>
	<input type='submit' name='submit' value='Gok!'>
	</form>";
			mysql_query("UPDATE `users` SET `zak`=`zak`-$inzet WHERE `login`='{$data->login}'") or die (mysql_error());
			mysql_query("UPDATE `users` SET `bank`=`bank`+$inzet WHERE `login`='{$casino->owner}'") or die (mysql_error());
			mysql_query("UPDATE `casino` set `winst`=`winst`+$inzet WHERE `spel`='$casino->spel' AND `stad`='$data->stad'") or die (mysql_error());
		}
	}
} else {
	echo "$casino->owner heeft de maximale inzet gezet op &euro;$casino->inzet.<br><br>
	<form method='post'>
	Inzet: <input type='text' name='inzet' size='3' maxlength='7'><br><br>
	<input type='submit' name='submit' value='Gok!'>
	</form>";
}
?></font>
<br><br><br><table width="100%">
  <tr>
    <td><div align="right"><img src=http://members.lycos.nl/js6287/images/slots/5.gif><img src=http://members.lycos.nl/js6287/images/slots/5.gif><img src=http://members.lycos.nl/js6287/images/slots/5.gif></div></td>
    <td width="100">50 x</td>
    <td width="2%">&nbsp;</td>
    <td><div align="right"><img src=http://members.lycos.nl/js6287/images/slots/1.gif><img src=http://members.lycos.nl/js6287/images/slots/1.gif><img src=http://members.lycos.nl/js6287/images/slots/1.gif></div></td>
    <td width="100">15 x</td>
  </tr>
  <tr>
    <td><div align="right"><img src=http://members.lycos.nl/js6287/images/slots/4.gif><img src=http://members.lycos.nl/js6287/images/slots/4.gif><img src=http://members.lycos.nl/js6287/images/slots/4.gif></div></td>
    <td>30 x</td>
    <td>&nbsp;</td>
    <td><div align="right"><img src=http://members.lycos.nl/js6287/images/slots/0.gif><img src=http://members.lycos.nl/js6287/images/slots/0.gif><img src=http://members.lycos.nl/js6287/images/slots/0.gif></div></td>
    <td>10 x</td>
  </tr>
  <tr>
    <td><div align="right"><img src=http://members.lycos.nl/js6287/images/slots/3.gif><img src=http://members.lycos.nl/js6287/images/slots/3.gif><img src=http://members.lycos.nl/js6287/images/slots/3.gif></div></td>
    <td>25 x</td>
    <td>&nbsp;</td>
    <td><div align="right"><img src=http://members.lycos.nl/js6287/images/slots/5.gif><img src=http://members.lycos.nl/js6287/images/slots/5.gif></div></td>
    <td>5 x</td>
  </tr>
  <tr>
    <td><div align="right"><img src=http://members.lycos.nl/js6287/images/slots/2.gif><img src=http://members.lycos.nl/js6287/images/slots/2.gif><img src=http://members.lycos.nl/js6287/images/slots/2.gif></div></td>
    <td>20 x</td>
    <td>&nbsp;</td>
    <td><div align="right"><img src=http://members.lycos.nl/js6287/images/slots/5.gif></div></td>
    <td>2 x</td>
  </tr>
</table></center></body></html>