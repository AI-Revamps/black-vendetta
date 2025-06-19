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
<table width=100% align=center>
  <tr> 
    <td class="subTitle"><b>Misdaad</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
ENDHTML;
$time = time();
$crime = gmdate('i:s',($data->crime - time()));
if($data->crime - time() > 0) { echo "Je moet nog $crime wachten voor je volgende misdaad."; exit; }
$kind = round(100 / (200 / $data->xp));
if ($kind > 50) { $kind = 50; }
$kkind = rand(1,(100 / $kind));
if ($data->level > 254) { $kkind = rand(1,2);}
$puber = round(100 / (333 / $data->xp));
if ($puber > 50) { $puber = 50; }
$kpuber = rand(1,(100 / $puber));
if ($data->level > 254) { $kpuber = rand(1,2);}
$juwelier = round(100 / (666 / $data->xp));
if ($juwelier > 50) { $juwelier = 50; }
$kjuwelier = rand(1,(100 / $juwelier));
if ($data->level > 254) { $kjuwelier = rand(1,2);}
$bar = 25; 
$member = 10; 
$kmember = rand(0,5);
if ($data->level > 254) { $kmember = rand(1,2);}
if ($data->se < 15) { $schietmsg = "Je kogel schraapte langst de brievenbus, blijf oefenen."; }
elseif ($data->se < 25) { $schietmsg = "Je schoot de vogelstront van de brievenbus."; }
elseif ($data->se < 30) { $schietmsg = "Je kogel boorde een groot gat in het dak van de brievenbus."; }
elseif ($data->se < 50) { $schietmsg = "Je schoot recht in de gleuf van de brievenbus."; }
elseif ($data->se < 75) { $schietmsg = "Je schoot de krant uit de brievenbus."; }
elseif ($data->se > 74) { $schietmsg = "Je schoot de brievenbus van zijn staander."; }

if (isset($_POST['submit'])) { 
if (!$_POST['verify']){echo"Je moet een code opgeven.";}
elseif($_POST['verify'] != $_SESSION['verify']){echo"De code die je hebt ingevoerd komt niet overeen met het plaatje.";}
elseif ($_POST['crime'] == kind) {
$ammount = rand(1,10);
$msgnum = rand(0,4);
$message = Array("Het kind had niets bij.","Het kind begon te schreeuwen, je bent dan maar gaan lopen.","Het kind droeg een kort rokje, je kon er niet aan weerstaan... Gelukkig kon je gaan lopen voordat de moeder je sloeg.","Je wou de portefeuille van het kind grijpen, maar ze liep weg. Je greep haar bij haar haar, de pruik kwam er af, maar het meisje kon wegkomen.","Je bent gearresteerd toen je het kind haar hoofd tussen je knieën stak.");
$msg = $message[$msgnum];
if ($kkind == 1) { echo "Je hebt &euro;$ammount Gestolen."; mysql_query("UPDATE `users` SET `zak`=`zak`+$ammount WHERE `login`='{$data->login}'"); }
else { 
echo "$msg"; 
if ($msgnum == 4) { mysql_query("INSERT INTO `jail`(`login`,`boete`,`stad`,`famillie`,`time`) VALUES('$data->login','{$boete}','{$data->stad}','{$famillie}',FROM_UNIXTIME($jailtime))"); }
}
mysql_query("UPDATE `users` SET `xp`=`xp`+1,`crime`=FROM_UNIXTIME($crimetijd),`nrofcrime`=`nrofcrime`+1 WHERE `login`='{$data->login}'");
} 
elseif ($_POST['crime'] == puber) {
$ammount = rand(1,100);
$msgnum = rand(0,5);
$message = Array("Hij had niets bij.","De puber kende je... Je had die ochtend tegen zijn fiets gepist. Hij kwam woedend achter je aan, je moest vluchten.","Die pubermeisjes zijn zo verrukkelijk, je wou haar net naaien toen haar vader afkwam.","Hij liep snel weg.","Je kon zijn portefeuille stelen, maar toen kwam hij achter je aan. Je wou hem net in elkaar slaan toen de politie arriveerde. Je zit nu in de gevangenis.","Zijn vrienden kwamen van overal, ze sloegen je in het ziekenhuis.");
$msg = $message[$msgnum];
if ($kpuber == 1) { echo "Je hebt &euro;$ammount gestolen."; mysql_query("UPDATE `users` SET `zak`=`zak`+$ammount WHERE `login`='{$data->login}'"); }
else { 
echo "$msg"; 
if ($msgnum == 4) { mysql_query("INSERT INTO `jail`(`login`,`boete`,`stad`,`famillie`,`time`) VALUES('$data->login','{$boete}','{$data->stad}','{$famillie}',FROM_UNIXTIME($jailtime))"); }
if ($msgnum == 5) { mysql_query("UPDATE `users` SET `health`=`health`-2 WHERE `login`='{$data->login}'"); }
}
mysql_query("UPDATE `users` SET `xp`=`xp`+1,`crime`=FROM_UNIXTIME($crimetijd),`nrofcrime`=`nrofcrime`+1 WHERE `login`='{$data->login}'");
}
elseif ($_POST['crime'] == juwelier) {
$ammount = rand(500,1000);
$msgnum = rand(0,5);
$message = Array("Er liep net een andere gangster met een lading juwelen naar buiten.","De juwelier nam zijn magmun 2.1 half automatich van achter de toonbank en begon op je te schieten. Je kon maar net op tijd wegkomen.","De juwelier was dicht.","Je wou net binnengaan toen de politie voorbijreed.","Je besloot de juweliersvrouw te neuken. Domme zet, de politie kwam en pakte je op.","Je was de winkel uit, maar je viel over een oude zwerver die buiten zat. Hij nam je juwelen en liep weg.");
$msg = $message[$msgnum];
if ($kjuwelier == 1) { echo "Je hebt &euro;$ammount gestolen."; mysql_query("UPDATE `users` SET `zak`=`zak`+$ammount WHERE `login`='{$data->login}'"); }
else { 
echo "$msg"; 
if ($msgnum == 4) { mysql_query("INSERT INTO `jail`(`login`,`boete`,`stad`,`famillie`,`time`) VALUES('$data->login','{$boete}','{$data->stad}','$famillie',FROM_UNIXTIME($jailtime))"); }
                    mysql_query("UPDATE `users` SET `health`=`100` WHERE `login`='{$data->login}'");
}
mysql_query("UPDATE `users` SET `xp`=`xp`+1,`crime`=FROM_UNIXTIME($crimetijd),`nrofcrime`=`nrofcrime`+1 WHERE `login`='{$data->login}'");
}
elseif ($_POST['crime'] == bar) {
if ($data->level > 254) { $kbar = rand(1,2);}
elseif ($data->se < 10) { $kbar = rand(1,6); }
elseif ($data->se < 25) { $kbar = rand(1,5); }
elseif ($data->se < 50) { $kbar = rand(1,4); }
elseif ($data->se > 49) { $kbar = rand(1,3); }
if ($data->wapon < 1) { echo "Je hebt nog geen wapen.<br>Ga naar de shop om een wapen te kopen."; exit; }
if ($data->se > 99.9) { echo "Je kan niet meer dan 100% moordervaring hebben."; exit; }
$msgnum = rand(0,5);
$message = Array("Je schoot de hond dood die toevallig passeerde.","Je schoot in de grond. Zonde van de kogel.","Je kogel boorde zich in de band van een wagen die iets verder stond.","Je schoot een vogel dood die iets verder in een boom zat.","Je schoot tegen de pet van een politieman. Hij besloot je te arresteren.","Er vloog een stuk uit de brievenbus, recht in je wang. Je bent leven kwijtgeraakt.");
$msg = $message[$msgnum];
if ($kbar == 1) { echo "$schietmsg"; mysql_query("UPDATE `users` SET `se`=`se`+0.1 WHERE `login`='{$data->login}'"); }
else { 
echo "$msg"; 
if ($msgnum == 4) { mysql_query("INSERT INTO `jail`(`login`,`boete`,`stad`,`famillie`,`time`) VALUES('$data->login','{$boete}','{$data->stad}','{$famillie}',FROM_UNIXTIME($jailtime))"); }
if ($msgnum == 5) { mysql_query("UPDATE `users` SET `health`=`health`-1 WHERE `login`='{$data->login}'"); }
}
mysql_query("UPDATE `users` SET `xp`=`xp`+1,`crime`=FROM_UNIXTIME($crimetijd),`nrofcrime`=`nrofcrime`+1 WHERE `login`='{$data->login}'");
}
elseif ($_POST['crime'] == member) {
$msgnum = rand(0,6);
$message = Array("Hij had geen geld bij zich.","Je wou net zijn portefeuille pikken toen hij een wapen bovenhaalde. Je ging lopen.","Je bent gearresteerd.","Opeens begon hij NOOOOOB te roepen, je bent gaan lopen.","Hij had geen portefeuille bij.","Hij begon NOOOOOB te roepen, je bent dan maar wegggegaan.","Je kwam langst de hoeren en vergat dat je geld wou pikken.");
$msg = $message[$msgnum];
$me = mysql_query("SELECT * FROM `users` WHERE `login`!='{$data->login}' AND `stad`='{$data->stad}' AND `status`='levend' AND `level`!='255' AND `level`!='1000' ORDER BY `zak` DESC LIMIT 0,1");
$mem = mysql_fetch_object($me);
$ammount = round($mem->zak * 0.25);
if($ammount > 100000){$ammount = 100000;}
if ($mem->zak < 10) { echo "Er is geen member in je stad met geld op zak."; exit; }
elseif ($kmember == 1) { echo "Je hebt &euro;$ammount gestolen van $mem->login"; mysql_query("UPDATE `users` SET `zak`=`zak`-$ammount WHERE `login`='$mem->login'"); mysql_query("UPDATE `users` SET `zak`=`zak`+$ammount WHERE `login`='{$data->login}'"); mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$mem->login}','Zakkenroller','$data->login heeft &euro;$ammount uit je zak gestolen.')"); }
else { 
echo "$msg";
if ($msgnum == 2) { mysql_query("INSERT INTO `jail`(`login`,`boete`,`stad`,`famillie`,`time`) VALUES('$data->login','{$boete}','{$data->stad}','{$famillie}',FROM_UNIXTIME($jailtime))"); }
elseif ($msgnum == 6) { mysql_query("UPDATE `users` SET `health`=`health`-1 WHERE `login`='{$data->login}'"); }
}
 mysql_query("UPDATE `users` SET `crime`=FROM_UNIXTIME($crimetijd),`nrofcrime`=`nrofcrime`+1 WHERE `login`='{$data->login}'"); 
}
exit;
}
print "
	<form method='post'><table><tr><td width=100% align=left>
<tr><td width=100%><input type='radio' name='crime' value='kind' checked>Steel van een kind {$kind}%<br></td></tr>
<tr><td width=100%><input type='radio' name='crime' value='puber'>Steel van een puber {$puber}%<br></td></tr>
<tr><td width=100%><input type='radio' name='crime' value='juwelier'>Beroof een juwelier {$juwelier}%<br></td></tr>
<tr><td width=100%><input type='radio' name='crime' value='bar'>Schiet op brievenbussen {$bar}%<br></td></tr>
<tr><td width=100%><input type='radio' name='crime' value='member'>Steel van een member {$member}%<br></td></tr>
<tr><td><br>Je code is: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src=img.php></td></tr>
<tr><td>Typ hier de code in:    <input type=text name=verify></td></tr>";
?>


<tr><td width=100%><br><input type='submit' name='submit' value='Ok'></form></td></tr></html>
</td></tr></table></table>