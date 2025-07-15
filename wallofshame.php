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
<!DOCTYPE html>
<html lang="nl">
<head>
<title>Gangster4Crime</title>
<link rel="stylesheet" type="text/css" href="style.css">
<meta name="keywords" content="Gangster4Crime,Crimegame,crimegame,Gangster4Crime">
<meta name="language" content="english">
<META name="description" lang="nl" content="Gangster4Crime crimegame met pit.">
</head>
<table width=100%>
  <tr> 
    <td class="subTitle"><b>Schandpaal</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
<?php
if (isset($_GET['hit'])) {  
$victim = mysql_fetch_object(mysql_query("SELECT * FROM `users` WHERE `login`='{$_GET['hit']}'"));
$exists = mysql_fetch_object(mysql_query("SELECT * FROM `shame` WHERE `cheater`='{$_GET['hit']}'"));
if ($data->zak < 10000) {echo"Je hebt niet genoeg geld op zak.";exit;}
if (!$exists) {echo"Deze speler staat niet op de schandpaal.";exit;}
if (!$victim) {echo"Deze speler bestaat niet.";exit;}
if ($victim->status == dood) { echo "{$victim->login} is al dood!";exit;}
echo "Je hebt een tomaat gegooid.<br>"; 
if ($victim->health-5 <= 0) { mysql_query("UPDATE `users` SET `status`='dood',`famillie`='',`famrang`='0',`bank`='0' WHERE `login`='{$_GET['hit']}'"); 
mysql_query("UPDATE `stad` SET `drugs`=`drugs`+$victim->drugs,`drank`=`drank`+$victim->drank WHERE `stad`='{$data->stad}'");
mysql_query("UPDATE `users` SET `testament`='' WHERE `testament`='{$victim->login}'");
mysql_query("DELETE FROM `garage` WHERE `login`='{$victim->login}'");
echo" Je hebt $victim->login gedood, hij had &euro; $victim->zak op zak, dat is nu van jou.<br>";
mysql_query("UPDATE `users` SET `zak`=`zak`+$victim->zak,`xp`=`xp`+10 WHERE `login`='{$data->login}'");
}
mysql_query("UPDATE `users` SET `health`=`health`-5 WHERE `login`='{$_GET['hit']}'"); 
mysql_query("UPDATE `users` SET `zak`=`zak`-10000 WHERE `login`='{$data->login}'"); 
}

print <<<ENDHTML
<table width=100%><tr>	  <td align=center><b>Persoon</b></td> 
      <td align=center><b>Tijd</b></td> 
            <td align=center><b>Reden</b></td>
			<td align=center><b>Health</b></td>
			<td align=center><b>Tomaat</b></td> </tr>

ENDHTML;
$query = "SELECT * FROM `shame` ORDER BY `id` DESC"; 
$info = mysql_query($query) or die(mysql_error()); 
$count = 0; 
$aantal = mysql_num_rows($info);
while ($gegeven = mysql_fetch_object($info)) { 
$time = $gegeven->time; 
$cheater = $gegeven->cheater;
$com = $gegeven->com;
$player = mysql_fetch_object(mysql_query("SELECT * FROM `users` WHERE `login`='{$cheater}'"));
$count++; 
print <<<ENDHTML

<tr>      	<td align=center>{$cheater}</td>
      		<td align=center>{$time}</td>
			<td align=center>{$com}</td>
			<td align=center>{$player->health}</td>
ENDHTML;
if ($player->health > 0 ) {echo"<td align=center><a href=wallofshame.php?hit={$cheater}>[x]</a></td></tr>";}
else {echo"<td align=center>[x]</td></tr>";}
}
if ($aantal == 0) { echo"Er hangt niemand aan de schandpaal.";exit;}
?>