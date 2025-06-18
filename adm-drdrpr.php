<?PHP
include("config.php");
  $dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");
  $data	= mysql_fetch_object($dbres);
if ($data->level < 200) { exit; }
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
<?PHP
if ($_POST['submit']) {
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
if ($_POST['brussel'] == 1) {
if ($_POST['brusseltype'] == 1) {
	mysql_query("UPDATE `stad` SET `drugsp`='$ubrussel' WHERE `stad`='Brussel'");
	$msg = "Drugs Brussel<br>";
	}
elseif ($_POST['brusseltype'] == 2) {
	mysql_query("UPDATE `stad` SET `drankp`='$abrussel' WHERE `stad`='Brussel'");
	$msg = "Drank Brussel<br>";
	}
else {
	mysql_query("UPDATE `stad` SET `drugsp`='$ubrussel',`drankp`='$abrussel' WHERE `stad`='Brussel'");
	$msg = "Drank & Drugs Brussel<br>";
	}
}
if ($_POST['leuven'] == 1) {
if ($_POST['leuventype'] == 1) {
	mysql_query("UPDATE `stad` SET `drugsp`='$uleuven' WHERE `stad`='Leuven'");
	$msg = "$msg Drugs Leuven<br>";
	}
elseif ($_POST['leuventype'] == 2) {
	mysql_query("UPDATE `stad` SET `drankp`='$aleuven' WHERE `stad`='Leuven'");
	$msg = "$msg Drank Leuven<br>";
	}
else {
	mysql_query("UPDATE `stad` SET `drugsp`='$uleuven',`drankp`='$aleuven' WHERE `stad`='Leuven'");
	$msg = "$msg Drank & Drugs Leuven<br>";
	}
}
if ($_POST['gent'] == 1) {
if ($_POST['genttype'] == 1) {
	mysql_query("UPDATE `stad` SET `drugsp`='$ugent' WHERE `stad`='Gent'");
	$msg = "$msg Drugs Gent<br>";
	}
elseif ($_POST['genttype'] == 2) {
	mysql_query("UPDATE `stad` SET `drankp`='$agent' WHERE `stad`='Gent'");
	$msg = "$msg Drank Gent<br>";
	}
else {
	mysql_query("UPDATE `stad` SET `drugsp`='$ugent',`drankp`='$agent' WHERE `stad`='Gent'");
	$msg = "$msg Drank & Drugs Gent<br>";
	}
}
if ($_POST['brugge'] == 1) {
if ($_POST['bruggetype'] == 1) {
	mysql_query("UPDATE `stad` SET `drugsp`='$ubrugge' WHERE `stad`='Brugge'");
	$msg = "$msg Drugs Brugge<br>";
	}
elseif ($_POST['bruggetype'] == 2) {
	mysql_query("UPDATE `stad` SET `drankp`='$abrugge' WHERE `stad`='Brugge'");
	$msg = "$msg Drank Brugge<br>";
	}
else {
	mysql_query("UPDATE `stad` SET `drugsp`='$ubrugge',`drankp`='$abrugge' WHERE `stad`='Brugge'");
	$msg = "$msg Drank & Drugs Brugge<br>";
	}
}
if ($_POST['antwerpen'] == 1) {
if ($_POST['antwerpentype'] == 1) {
	mysql_query("UPDATE `stad` SET `drugsp`='$uantwerpen' WHERE `stad`='Antwerpen'");
	$msg = "$msg Drugs Antwerpen<br>";
	}
elseif ($_POST['antwerpentype'] == 2) {
	mysql_query("UPDATE `stad` SET `drankp`='$aantwerpen' WHERE `stad`='Antwerpen'");
	$msg = "$msg Drank Antwerpen<br>";
	}
else {
	mysql_query("UPDATE `stad` SET `drugsp`='$uantwerpen',`drankp`='$aantwerpen' WHERE `stad`='Antwerpen'");
	$msg = "$msg Drank & Drugs Antwerpen<br>";
	}
}
if ($_POST['hasselt'] == 1) {
if ($_POST['hasselttype'] == 1) {
	mysql_query("UPDATE `stad` SET `drugsp`='$uhasselt' WHERE `stad`='Hasselt'");
	$msg = "$msg Drugs Hasselt<br>";
	}
elseif ($_POST['hasselttype'] == 2) {
	mysql_query("UPDATE `stad` SET `drankp`='$ahasselt' WHERE `stad`='Hasselt'");
	$msg = "$msg Drank Hasselt<br>";
	}
else {
	mysql_query("UPDATE `stad` SET `drugsp`='$uhasselt',`drankp`='$ahasselt' WHERE `stad`='Hasselt'");
	$msg = "$msg Drank & Drugs Hasselt<br>";
	}
}
if ($_POST['amsterdam'] == 1) {
if ($_POST['amsterdamtype'] == 1) {
	mysql_query("UPDATE `stad` SET `drugsp`='$uamsterdam' WHERE `stad`='Amsterdam'");
	$msg = "$msg Drugs Amsterdam<br>";
	}
elseif ($_POST['amsterdamtype'] == 2) {
	mysql_query("UPDATE `stad` SET `drankp`='$aamsterdam' WHERE `stad`='Amsterdam'");
	$msg = "$msg Drank Amsterdam<br>";
	}
else {
	mysql_query("UPDATE `stad` SET `drugsp`='$uamsterdam',`drankp`='$aamsterdam' WHERE `stad`='Amsterdam'");
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