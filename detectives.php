<?php
  include('config.php');
$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");  
$data    = mysql_fetch_object($dbres);
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
<?php
print <<<ENDHTML
<table width=100% align=center>
  <tr> 
    <td class="subTitle"><b>Detectives</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
ENDHTML;
if ($_POST['rall']) {
mysql_query("DELETE FROM `detectives` WHERE `van`='{$data->login}'");
}
$user = mysql_query("SELECT * FROM `users` WHERE `login`='{$_POST['naar']}'");
$exist = mysql_fetch_object($user);
if (isset($_POST['submit'])) { 
	if (!$exist->login) { echo "Deze gebruiker bestaat niet."; exit; }
	elseif ($exist->status == dood) { echo "Deze gebruiker is dood."; exit; }
	elseif (strtolower($data->login == $_POST['naar'])) { echo "Je kan jezelf niet laten zoeken."; exit; }
	elseif ($_POST['stad'] != Alle) { 
		if ($data->zak < 10000) { echo "Je hebt niet genoeg geld op zak."; exit; }
		else {
mysql_query("INSERT INTO `log`(`wat`,`aantal`,`code`,`van`) values('Detective','1','{$_POST['stad']}','$data->login')");
mysql_query("INSERT INTO `detectives`(`van`,`naar`,`stad`,`time`) values('{$data->login}','{$_POST['naar']}','{$_POST['stad']}',FROM_UNIXTIME($zoektijd))");
			echo "Je detective is op pad gestuurd."; 
			mysql_query("UPDATE `users` SET `zak`=`zak`-10000 WHERE `login`='{$data->login}'");
		}
	}
	elseif ($_POST['stad'] == Alle) {
		if ($data->zak < 60000) { echo "Je hebt niet genoeg geld op zak."; exit; }
		else {
		mysql_query("INSERT INTO `detectives`(`van`,`naar`,`stad`,`time`) values('{$data->login}','{$_POST['naar']}','Brussel',FROM_UNIXTIME($zoektijd))");
		mysql_query("INSERT INTO `detectives`(`van`,`naar`,`stad`,`time`) values('{$data->login}','{$_POST['naar']}','Leuven',FROM_UNIXTIME($zoektijd))");
		mysql_query("INSERT INTO `detectives`(`van`,`naar`,`stad`,`time`) values('{$data->login}','{$_POST['naar']}','Gent',FROM_UNIXTIME($zoektijd))");
		mysql_query("INSERT INTO `detectives`(`van`,`naar`,`stad`,`time`) values('{$data->login}','{$_POST['naar']}','Brugge',FROM_UNIXTIME($zoektijd))");
		mysql_query("INSERT INTO `detectives`(`van`,`naar`,`stad`,`time`) values('{$data->login}','{$_POST['naar']}','Antwerpen',FROM_UNIXTIME($zoektijd))");
		mysql_query("INSERT INTO `detectives`(`van`,`naar`,`stad`,`time`) values('{$data->login}','{$_POST['naar']}','Hasselt',FROM_UNIXTIME($zoektijd))");
        mysql_query("INSERT INTO `detectives`(`van`,`naar`,`stad`,`time`) values('{$data->login}','{$_POST['naar']}','Amsterdam',FROM_UNIXTIME($zoektijd))");
		mysql_query("INSERT INTO `detectives`(`van`,`naar`,`stad`,`time`) values('{$data->login}','{$_POST['naar']}','Enschede',FROM_UNIXTIME($zoektijd))");
        mysql_query("UPDATE `users` SET `zak`=`zak`-70000 WHERE `login`='{$data->login}'");
		echo "Je detectives zijn op pad gestuurd.";
		}
	}
}
print "
	<form method='post'>
Huur een detective in om iemand te zoeken. 1 detective kost &euro;10.000.<br><br><table><tr><td width=100 align=left><b>Zoek naar: </b><input type='text' name='naar' value=''><br>
<b>Stad:</b><br></td></tr>
<tr><td width=100><input type='radio' name='stad' value='Brussel'>Brussel<br></td></tr>
<tr><td width=100><input type='radio' name='stad' value='Leuven'>Leuven<br></td></tr>
<tr><td width=100><input type='radio' name='stad' value='Gent'>Gent<br></td></tr>
<tr><td width=100><input type='radio' name='stad' value='Brugge'>Brugge<br></td></tr>
<tr><td width=100><input type='radio' name='stad' value='Antwerpen'>Antwerpen<br></td></tr>
<tr><td width=100><input type='radio' name='stad' value='Hasselt'>Hasselt<br></td></tr>
<tr><td width=100><input type='radio' name='stad' value='Amsterdam'>Amsterdam<br></td></tr>
<tr><td width=100><input type='radio' name='stad' value='Enschede'>Enschede<br></td></tr>
<tr><td width=100><input type='radio' name='stad' value='Alle' checked>Alle steden<br></td></tr>
<tr><td width=100><br><input type='submit' name='submit' value='Zoek'></form></td></tr></html>";
/// Einde uitvoer van query 
$query = "SELECT *,UNIX_TIMESTAMP(`time`) AS `time` FROM `detectives` WHERE `van`='{$data->login}' ORDER BY `time` ASC"; 
$huren = mysql_query("SELECT * FROM `detectives` WHERE `van`='{$data->login}'"); 
$huur = mysql_num_rows($huren);
$huur = 1;
$info = mysql_query($query) or die(mysql_error()); 
$count = 0; 
if ($huur == 1) {
echo "<tr><td width=5%> 
        #</td> 
        <td width=20%> 
        <b>Zoeken naar</b></td> 
                <td width=24%> 
        <b>Stad</b></td> 
                <td width=24%> 
        <b>Tijd</b></td> 
                      <td width=24%> 
        <b>Verwijder</b></td></tr> 
      "; 
while ($gegeven = mysql_fetch_array($info)) { 
$stad= $gegeven["stad"]; 
$time = $gegeven["time"]; 
$name = $gegeven["naar"]; 
$id = $gegeven["id"];
$zoektime = gmdate('i:s', ($time - TIME()));
if (!$zoektime) { $zoektime = "Niet gevonden."; }
$count++; 

/// De uit database gehaalde gegevens weergeven 
echo "<tr> 
                <td width=5%>".$count."</td> 
		        <td width=20%>{$name}</td> 
                <td width=24%>{$stad}</td>
                <td width=24%>{$zoektime}</td>
                <td width=24%><a href=detectives.php?x={$id}>[x]</a></td>";
}
}
print "<tr><td><br><form method=POST><input type=submit name=rall value='Verwijder alles'></form></td></tr>";
if (isset($_GET['x'])) { mysql_query("DELETE FROM `detectives` WHERE `id`='{$_GET['x']}' AND `van`='{$data->login}'"); echo "Je hebt je detective ontslagen.<br><br>"; }
?>