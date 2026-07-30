<?php
  include("config.php");
  $dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");
  $data	= mysql_fetch_object($dbres);  
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
<table width=100%>
  <tr> 
    <td class="subTitle"><b>Respect</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
<?php
if ($_POST['submit'] && preg_match('/^[0-9]+$/',$_POST['aantal'])) {
$naar = strtolower($_POST['to']);
$van = strtolower($data->login);
$aantal = $_POST['aantal'];
$user = mysql_fetch_object(mysql_query("SELECT * FROM `users` WHERE `login`='{$_POST['to']}'"));
if ($data->rp < $_POST['aantal']) { print "Je hebt niet genoeg punten meer om weg te geven $van $naar\n"; }
elseif ($_POST['aantal'] <= 0) { print "Je hebt niet genoeg punten meer om weg te geven\n"; } 
elseif ($naar == $van) { print "Je kan jezelf geen respect punten geven\n"; } 
elseif (!$user->login) { print "Deze gebruiker bestaat niet\n"; } 
else {
if ($_POST['type'] == e){
	mysql_query("UPDATE `users` SET `respect`=`respect`+'$aantal' WHERE `login`='{$_POST['to']}'");
	mysql_query("INSERT INTO `logs`(`time`,`login`,`person`,`code`,`area`,`com`) values(NOW(),'{$data->login}','{$_POST['to']}','{$_POST['aantal']}','respect','{$_POST['com']}')");
    }
 else {
	mysql_query("UPDATE `users` SET `respect`=`respect`-'$aantal' WHERE `login`='{$_POST['to']}'");
	mysql_query("INSERT INTO `logs`(`time`,`login`,`person`,`code`,`area`,`com`) values(NOW(),'{$data->login}','{$_POST['to']}','-{$_POST['aantal']}','respect','{$_POST['com']}')");
      }
    mysql_query("UPDATE `users` SET `rp`=`rp`-'$aantal' WHERE `login`='{$data->login}'");
	print "<meta http-equiv=Refresh content=0;url=respect.php>";
}
}
$newr = mysql_fetch_object(mysql_query("SELECT *,UNIX_TIMESTAMP(`time`) AS `time` FROM `cron` WHERE `name`='week'"));
$new = date('d-m H:i',$newr->time+604800);
print <<<ENDHTML
Je hebt nog $data->rp respect punten om weg te geven. <br> Er komen nieuwe respectpunten op: $new.

<br><br><div align='center'><b>Respect punten versturen</b><br><br>
<form method="post" style="text-align: center">
Naar:<br><input type="text" name="to" maxlength="16"><br>
Aantal:<br><input type=text name="aantal" value=""><br>
Omschrijving:<br><input type=text name="com" value=""><br>
<input type=radio name=type value=e checked>Eer&nbsp;&nbsp;<input type=radio name=type value=s>Schande<br>
<br><input type=submit name=submit value=Verzend><br>
	</form>
ENDHTML;
    print "  <tr>
          <td>&nbsp;</td>
        </tr><tr> 
    <td class=subTitle><b>10 laatste ontvangen respectpunten</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt><table width=100%>";
print "      <tr><td width=125><b>Van</b></td>
	<td><b>Aantal</b></td>
	<td width=200 align=\"center\"><b>Tijd</b></td>
		<td width=100 align=\"center\"><b>Omschrijving</b></td></tr>";
    $dbres				= mysql_query("SELECT *,DATE_FORMAT(`time`,'%d-%m-%Y %H:%i') AS `donatetime` FROM `logs` WHERE `person`='{$data->login}' AND `time` >= '{$data->signup}' AND `area`='respect' ORDER BY `time` DESC LIMIT 0,10");
    $bl				= mysql_query("SELECT * FROM `logs` WHERE `person`='{$data->login}' AND `area`='respect'");
$bla = mysql_num_rows($bl);
if ($bla == 0) {
      print <<<ENDHTML
    <tr><td width=125>Geen</td>
ENDHTML;
}
    while($info = mysql_fetch_object($dbres)) {
$com = $info->com;
if (!$info->com) { $com = Geen; }
      print <<<ENDHTML
      <tr><td width=125>$info->login</td>
	<td >{$info->code}</td>
	<td width=200 align="center">{$info->donatetime}</td>
		<td width=100 align="center">$com</td></tr>
ENDHTML;
    }

    print "  </table></td></tr><tr>
          <td>&nbsp;</td>
        </tr><tr> 
    <td class=subTitle><b>10 laatste gegeven respectpunten</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
    print "  <table width=100%>\n";
print "      <tr><td width=125><b>Naar</b></td>
	<td><b>Aantal</b></td>
	<td width=200 align=\"center\"><b>Tijd</b></td>
		<td width=100 align=\"center\"><b>Omschrijving</b></td></tr>";

    $dbres				= mysql_query("SELECT *,DATE_FORMAT(`time`,'%d-%m-%Y %H:%i') AS `donatetime` FROM `logs` WHERE `login`='{$data->login}' AND `time` >= '{$data->signup}' AND `area`='respect' ORDER BY `time` DESC LIMIT 0,10");
    $bl				= mysql_query("SELECT * FROM `logs` WHERE `login`='{$data->login}' AND `area`='respect'");
$bla = mysql_num_rows($bl);
if ($bla == 0) {
      print <<<ENDHTML
    <tr><td width=125>Geen</td>
ENDHTML;
}
    while($info = mysql_fetch_object($dbres)) {
$com = $info->com;
if (!$info->com) { $com = Geen; }
      print <<<ENDHTML
      <tr><td width=125>$info->person</td>
	<td>{$info->code}</td>
	<td width=200 align="center">{$info->donatetime}</td>
		<td width=100 align="center">$com</td></tr>
ENDHTML;
    }
?>
	</table></table></body></html>