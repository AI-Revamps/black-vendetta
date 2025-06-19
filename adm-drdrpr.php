<?php
declare(strict_types=1);
require 'config.php';

$stm = pdo_query(
    "SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login` = ?",
    [$_SESSION['login']]
);
$data = $stm->fetch();
if (!$data || $data->level < 200) { exit; }
?>
<html>
<head>
<title>Vendetta</title>
<link rel="stylesheet" type="text/css" href="style.css">
<meta name="keywords" content="Vendetta,Crimegame,crimegame,vendetta">
<meta name="language" content="english">
<META name="description" lang="nl" content="Vendetta crimegame met pit.">
</head>
<table width=100% border="0">
<tr> 
    <td class="subTitle"><b>Drank & Drugs prijzen</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
<?php
if (isset($_POST['submit'])) {
$abrussel = rand(1000,6000);
$aleuven = rand(1000,6000);
$agent = rand(1000,6000);
$abrugge = rand(1000,6000);
$aantwerpen = rand(1000,6000);
$ahasselt = rand(1000,6000);
$aamsterdam = rand(1000,6000);
$ubrussel = rand(6000,15000);
$uleuven = rand(6000,15000);
$ugent = rand(6000,15000);
$ubrugge = rand(6000,15000);
$uantwerpen = rand(6000,15000);
$uhasselt = rand(6000,15000);
$uamsterdam = rand(6000,15000);
if (isset($_POST['brussel']) && $_POST['brussel'] == 1) {
    if ($_POST['brusseltype'] == 1) {
        pdo_query("UPDATE `stad` SET `drugsp`=? WHERE `stad`='Brussel'", [$ubrussel]);
        $msg = "Drugs Brussel<br>";
    } elseif ($_POST['brusseltype'] == 2) {
        pdo_query("UPDATE `stad` SET `drankp`=? WHERE `stad`='Brussel'", [$abrussel]);
        $msg = "Drank Brussel<br>";
    } else {
        pdo_query("UPDATE `stad` SET `drugsp`=?,`drankp`=? WHERE `stad`='Brussel'", [$ubrussel, $abrussel]);
        $msg = "Drank & Drugs Brussel<br>";
    }
}
if (isset($_POST['leuven']) && $_POST['leuven'] == 1) {
    if ($_POST['leuventype'] == 1) {
        pdo_query("UPDATE `stad` SET `drugsp`=? WHERE `stad`='Leuven'", [$uleuven]);
        $msg = "$msg Drugs Leuven<br>";
    } elseif ($_POST['leuventype'] == 2) {
        pdo_query("UPDATE `stad` SET `drankp`=? WHERE `stad`='Leuven'", [$aleuven]);
        $msg = "$msg Drank Leuven<br>";
    } else {
        pdo_query("UPDATE `stad` SET `drugsp`=?,`drankp`=? WHERE `stad`='Leuven'", [$uleuven, $aleuven]);
        $msg = "$msg Drank & Drugs Leuven<br>";
    }
}
if (isset($_POST['gent']) && $_POST['gent'] == 1) {
    if ($_POST['genttype'] == 1) {
        pdo_query("UPDATE `stad` SET `drugsp`=? WHERE `stad`='Gent'", [$ugent]);
        $msg = "$msg Drugs Gent<br>";
    } elseif ($_POST['genttype'] == 2) {
        pdo_query("UPDATE `stad` SET `drankp`=? WHERE `stad`='Gent'", [$agent]);
        $msg = "$msg Drank Gent<br>";
    } else {
        pdo_query("UPDATE `stad` SET `drugsp`=?,`drankp`=? WHERE `stad`='Gent'", [$ugent, $agent]);
        $msg = "$msg Drank & Drugs Gent<br>";
    }
}
if (isset($_POST['brugge']) && $_POST['brugge'] == 1) {
    if ($_POST['bruggetype'] == 1) {
        pdo_query("UPDATE `stad` SET `drugsp`=? WHERE `stad`='Brugge'", [$ubrugge]);
        $msg = "$msg Drugs Brugge<br>";
    } elseif ($_POST['bruggetype'] == 2) {
        pdo_query("UPDATE `stad` SET `drankp`=? WHERE `stad`='Brugge'", [$abrugge]);
        $msg = "$msg Drank Brugge<br>";
    } else {
        pdo_query("UPDATE `stad` SET `drugsp`=?,`drankp`=? WHERE `stad`='Brugge'", [$ubrugge, $abrugge]);
        $msg = "$msg Drank & Drugs Brugge<br>";
    }
}
if (isset($_POST['antwerpen']) && $_POST['antwerpen'] == 1) {
    if ($_POST['antwerpentype'] == 1) {
        pdo_query("UPDATE `stad` SET `drugsp`=? WHERE `stad`='Antwerpen'", [$uantwerpen]);
        $msg = "$msg Drugs Antwerpen<br>";
    } elseif ($_POST['antwerpentype'] == 2) {
        pdo_query("UPDATE `stad` SET `drankp`=? WHERE `stad`='Antwerpen'", [$aantwerpen]);
        $msg = "$msg Drank Antwerpen<br>";
    } else {
        pdo_query("UPDATE `stad` SET `drugsp`=?,`drankp`=? WHERE `stad`='Antwerpen'", [$uantwerpen, $aantwerpen]);
        $msg = "$msg Drank & Drugs Antwerpen<br>";
    }
}
if (isset($_POST['hasselt']) && $_POST['hasselt'] == 1) {
    if ($_POST['hasselttype'] == 1) {
        pdo_query("UPDATE `stad` SET `drugsp`=? WHERE `stad`='Hasselt'", [$uhasselt]);
        $msg = "$msg Drugs Hasselt<br>";
    } elseif ($_POST['hasselttype'] == 2) {
        pdo_query("UPDATE `stad` SET `drankp`=? WHERE `stad`='Hasselt'", [$ahasselt]);
        $msg = "$msg Drank Hasselt<br>";
    } else {
        pdo_query("UPDATE `stad` SET `drugsp`=?,`drankp`=? WHERE `stad`='Hasselt'", [$uhasselt, $ahasselt]);
        $msg = "$msg Drank & Drugs Hasselt<br>";
    }
}
if (isset($_POST['amsterdam']) && $_POST['amsterdam'] == 1) {
    if ($_POST['amsterdamtype'] == 1) {
        pdo_query("UPDATE `stad` SET `drugsp`=? WHERE `stad`='Amsterdam'", [$uamsterdam]);
        $msg = "$msg Drugs Amsterdam<br>";
    } elseif ($_POST['amsterdamtype'] == 2) {
        pdo_query("UPDATE `stad` SET `drankp`=? WHERE `stad`='Amsterdam'", [$aamsterdam]);
        $msg = "$msg Drank Amsterdam<br>";
    } else {
        pdo_query("UPDATE `stad` SET `drugsp`=?,`drankp`=? WHERE `stad`='Amsterdam'", [$uamsterdam, $aamsterdam]);
        $msg = "$msg Drank & Drugs Amsterdam<br>";
    }
}
if ($msg == "") {$msg = "Niets<br>";}
echo "$msg <br>Geupdate";
}
else {print"
<form method=POST>
<table width=100% border=0 cellspacing=0 cellpadding=0>
  <tr>
    <td width=25%><input name=brussel type=checkbox value=1 checked>
  Brussel&nbsp;</td>
    <td><select name=brusseltype>
  <option value=1>Drugs</option>
  <option value=2>Drank</option>
  <option value=3>Beiden</option>
</select>&nbsp;</td>
  </tr>
  <tr>
    <td><input name=leuven type=checkbox value=1 checked>
  Leuven&nbsp;</td>
    <td> <select name=leuventype>
  <option value=1>Drugs</option>
  <option value=2>Drank</option>
  <option value=3>Beiden</option>
</select>&nbsp;</td>
  </tr>
  <tr>
    <td><input name=gent type=checkbox value=1 checked>
  Gent&nbsp;</td>
    <td><select name=genttype>
  <option value=1>Drugs</option>
  <option value=2>Drank</option>
  <option value=3>Beiden</option>
</select>&nbsp;</td>
  </tr>
  <tr>
    <td><input name=brugge type=checkbox value=1 checked>
  Brugge&nbsp;</td>
    <td><select name=bruggetype>
  <option value=1>Drugs</option>
  <option value=2>Drank</option>
  <option value=3>Beiden</option>
</select>&nbsp;</td>
  </tr>
  <tr>
    <td><input name=antwerpen type=checkbox value=1 checked>
  Antwerpen&nbsp;</td>
    <td><select name=antwerpentype>
  <option value=1>Drugs</option>
  <option value=2>Drank</option>
  <option value=3>Beiden</option>
</select>&nbsp;</td>
  </tr>
  <tr>
    <td><input name=hasselt type=checkbox value=1 checked>
  Hasselt&nbsp;</td>
    <td><select name=hasselttype>
  <option value=1>Drugs</option>
  <option value=2>Drank</option>
  <option value=3>Beiden</option>
</select>&nbsp;</td>
  </tr>
  <tr>
    <td><input name=amsterdam type=checkbox value=1 checked>
  Amsterdam&nbsp;</td>
    <td><select name=amsterdamtype>
  <option value=1>Drugs</option>
  <option value=2>Drank</option>
  <option value=3>Beiden</option>
</select>&nbsp;</td>
  </tr>
</table>
  <input name=submit type=submit value=Update>
</form>";}
?>
