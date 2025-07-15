<?php
include("config.php");
  $dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");
  $data	= mysql_fetch_object($dbres);
if ($data->level < 200) { exit; }
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
    <td class="subTitle"><b>Multiple Accounting</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">

<?php
if($data->level < 255) {echo"Je hebt niet genoeg rechten.";exit;}
$user = $_GET['x'];
    mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','$user','Waarschuwing','Er is meer dan 1 account op je ip gevonden, dit is tegen de regels. Stuur een bericht naar een admin om je tweede account te rechtvaardigen, anders is de kans groot dat een van je accounts verwijderd wordt.(Verzonden door $data->login)')"); 
echo "Waarschuwing verzonden.";
?>