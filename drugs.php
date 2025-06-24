<?php
declare(strict_types=1);
require 'config.php';

$stmt = pdo_query(
    "SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`drugst`) AS `drugst`," .
    "UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`," .
    "UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`," .
    "UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`," .
    "UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login` = ?",
    [$_SESSION['login']]
);
$data = $stmt->fetch();
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
$stmt = pdo_query("SELECT * FROM `stad` WHERE `stad` = ?", [$data->stad]);
$drugs = $stmt->fetch();
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
    pdo_query(
        "INSERT INTO `jail`(`login`,`boete`,`stad`,`famillie`,`time`) VALUES(?, ?, ?, ?, FROM_UNIXTIME(?))",
        [$data->login, $boete, $data->stad, $famillie, $jailtime]
    );
    exit;
}
        elseif (!Empty($aantal)) {
            echo "Je hebt $aantal units gekocht voor &euro; $winst.";
            if ($data->drugst - time() > 0) {
                pdo_query(
                    "UPDATE `users` SET `drugs`=`drugs`+? WHERE `login` = ?",
                    [$aantal, $data->login]
                );
                pdo_query(
                    "UPDATE `users` SET `zak`=`zak`-? WHERE `login` = ?",
                    [$winst, $data->login]
                );
                pdo_query(
                    "UPDATE `stad` SET `drugs`=`drugs`-? WHERE `stad` = ?",
                    [$aantal, $data->stad]
                );
            } else {
                pdo_query(
                    "UPDATE `users` SET `xp`=`xp`+3 WHERE `login` = ?",
                    [$data->login]
                );
                pdo_query(
                    "UPDATE `users` SET `drugs`=`drugs`+?,`drugst`=FROM_UNIXTIME(?) WHERE `login` = ?",
                    [$aantal, $drugstijd, $data->login]
                );
                pdo_query(
                    "UPDATE `users` SET `zak`=`zak`-? WHERE `login` = ?",
                    [$winst, $data->login]
                );
                pdo_query(
                    "UPDATE `stad` SET `drugs`=`drugs`-? WHERE `stad` = ?",
                    [$aantal, $data->stad]
                );
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
    pdo_query(
        "INSERT INTO `jail`(`login`,`boete`,`stad`,`famillie`,`time`) VALUES(?, ?, ?, ?, FROM_UNIXTIME(?))",
        [$data->login, $boete, $data->stad, $famillie, $jailtime]
    );
    exit;
}
        elseif (!Empty($aantal)) {
            pdo_query(
                "UPDATE `stad` SET `drugs`=`drugs`+? WHERE `stad` = ?",
                [$aantal, $data->stad]
            );
            pdo_query(
                "UPDATE `users` SET `drugs`=`drugs`-? WHERE `login` = ?",
                [$aantal, $data->login]
            );
            pdo_query(
                "UPDATE `users` SET `zak`=`zak`+? WHERE `login` = ?",
                [$winst, $data->login]
            );
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