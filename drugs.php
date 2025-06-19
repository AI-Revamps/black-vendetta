<?php
  include("config.php");
$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`drugst`) AS `drugst`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");  
$data    = mysql_fetch_object($dbres);
  if(! check_login()) {
    header("Location: login.php");
    exit;
  }
if ($jisin == 1) { header("Location: jisin.php"); }
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
    <td class="subTitle"><b>Drugs</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
<?php
$query = mysql_query("SELECT * FROM stad WHERE `stad`='{$data->stad}'") or die (mysql_error());
$drugs = mysql_fetch_object($query);
$aantal = $_POST['aantal'];
$time = time();
$drugstijd = (time() + 3600);
if ($data->xp < 10) { $units = 0; $kan = 1; }
elseif ($data->xp < 20) { $units = 0; $kan = 1; }
elseif ($data->xp < 50) { $units = 1; $kan = 2; }
elseif ($data->xp < 150) { $units = 2; $kan = 2; }
elseif ($data->xp < 500) { $units = 4; $kan = 2;  }
elseif ($data->xp < 1000) { $units = 5; $kan = 2; }
elseif ($data->xp < 2000) { $units = 7;  $kan = 2;}
elseif ($data->xp < 3000) { $units = 8; $kan = 3; }
elseif ($data->xp < 4500) { $units = 10; $kan = 3; }
elseif ($data->xp < 6000) { $units = 11; $kan = 3; }
elseif ($data->xp < 8000) { $units = 13; $kan = 4; }
elseif ($data->xp < 11000) { $units = 14; $kan = 4; }
elseif ($data->xp < 15000) { $units = 16; $kan = 4; }
elseif ($data->xp < 20000) { $units = 17; $kan = 5; }
elseif ($data->xp >= 20000) { $units = 20; $kan = 5; }
if ($data->level >=255) {$kan = 10 ;}
$kans = rand(1, $kan);
$prijs = $drugs->drugsp;
$totaal = $data->drugs+$aantal;
$dragen = $units-$data->drugs;
$winst = $aantal*$prijs;
$xp = $aantal;
if(isset($_POST['koop'])) {
if (!$_POST['verify']){echo"Je moet een code opgeven.";}
elseif($_POST['verify'] != $_SESSION['verify']){echo"De code die je hebt ingevoerd komt niet overeen met het plaatje.";}
elseif ($aantal < 1) { 
	    echo "Er zijn niet zoveel units."; 
}
elseif ($drugs->drugs < $aantal) {
	    echo "Er zijn niet zoveel units."; 
}
elseif ($winst > $data->zak) {
	    echo "Je hebt niet genoeg geld op zak.";
}
elseif ($totaal > $units) {
	    echo "Je mag maar $units units dragen.";
}
elseif ($kans == 1) { 
echo "Je bent gearresteerd.";
mysql_query("INSERT INTO `jail`(`login`,`boete`,`stad`,`famillie`,`time`) VALUES('$data->login','{$boete}','{$data->stad}','{$famillie}',FROM_UNIXTIME($jailtime))");
exit;
}
	elseif (!Empty($aantal)) {
		mysql_connect("localhost","vendettaga_root","wwWOnPk12"); 
		mysql_select_db("vendettaga_main"); 
	    echo "Je hebt $aantal units gekocht voor &euro; $winst."; 
if ($data->drugst - time() > 0) {
mysql_query("UPDATE `users` SET `drugs`=`drugs`+$aantal WHERE `login`='{$data->login}'") or die (mysql_error());
mysql_query("UPDATE `users` SET `zak`=`zak`-$winst WHERE `login`='{$data->login}'") or die (mysql_error());
mysql_query("UPDATE `stad` SET `drugs`=`drugs`-$aantal WHERE `stad`='{$data->stad}'") or die (mysql_error());
}
else{
mysql_query("UPDATE `users` SET `xp`=`xp`+3 WHERE `login`='{$data->login}'") or die (mysql_error());
mysql_query("UPDATE `users` SET `drugs`=`drugs`+$aantal,`drugst`=FROM_UNIXTIME($drugstijd) WHERE `login`='{$data->login}'") or die (mysql_error());
mysql_query("UPDATE `users` SET `zak`=`zak`-$winst WHERE `login`='{$data->login}'") or die (mysql_error());
mysql_query("UPDATE `stad` SET `drugs`=`drugs`-$aantal WHERE `stad`='{$data->stad}'") or die (mysql_error());
}
} 
	}
elseif(isset($_POST['verkoop'])) {
if (!$_POST['verify']){echo"Je moet een code opgeven.";}
elseif($_POST['verify'] != $_SESSION['verify']){echo"De code die je hebt ingevoerd komt niet overeen met het plaatje.";}
elseif ($aantal < 1) {
	    echo "Zoveel units heb je niet."; 
}
elseif ($data->drugs < $aantal) {
	    echo "Zoveel units heb je niet."; 
}
elseif ($kans == 0) { 
echo "Je bent gearresteerd";
mysql_query("INSERT INTO `jail`(`login`,`boete`,`stad`,`famillie`,`time`) VALUES('$data->login','{$boete}','{$data->stad}','{$famillie}',FROM_UNIXTIME($jailtime))");
exit;
}
	elseif (!Empty($aantal)) {
		mysql_connect("localhost","vendettaga_root","wwWOnPk12"); 
		mysql_select_db("vendettaga_main"); 
			mysql_query("UPDATE `stad` SET `drugs`=`drugs`+$aantal WHERE `stad`='{$data->stad}'") or die (mysql_error());
			mysql_query("UPDATE `users` SET `drugs`=`drugs`-$aantal WHERE `login`='{$data->login}'") or die (mysql_error());
			mysql_query("UPDATE `users` SET `zak`=`zak`+$winst WHERE `login`='{$data->login}'") or die (mysql_error());
	    echo "Je hebt $aantal units verkocht voor &euro;$winst.";
			} 
	}

print <<<ENDHTML
<form method='post'><table align="left">
<div align=left>
Je hebt $data->drugs units op zak.<br> 
	Je kan nog $dragen units dragen.<br>
	Er zijn nog {$drugs->drugs} units.<br>
	Je kan hier units (ver)kopen voor &euro; $prijs per unit.
	<tr><td><br>Je code is: </td><td><img src=img.php></td></tr>
    <tr><td>Typ hier de code in:   </td><td> <input type=text name=verify></td></tr>
	<tr><td width=100 align=left>Aantal</td><td><input type='text' name='aantal' value='' size='20' maxlength=2></td></tr>
<br>
<br>
	<tr><td></td><td align="left"><input type='submit' name='koop' value='Koop'></td></tr><tr><td></td><td align="left"><input type='submit' name='verkoop' value='Verkoop'></td></tr>
</table>
</form>
  </td></tr>
ENDHTML;
?>
</table></html></table>