<?php
//Made by MikoB @ Crime55

//MYSQL
//Voeg in bij `users` klikmissie
// 
  include("config.php");
$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");  
$data    = mysql_fetch_object($dbres);
  
  if(! check_login()) {
    header('Location: login.php');
    exit;
  }
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<table width=100%>
       <tr>
    <td class="subTitle"><b>Klikmissie</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr>
    <td class="mainTxt">
<?php 
  $resultaat = $data->klikmissie; 
  if($resultaat == 1) { 
      echo "Je hebt deze link vandaag al bezocht<br>Probeer het morgen weer! (berekend  sinds uw klik + 24 uur)"; exit;
  } 
else{ 
// bij a href linkje veranderd naar userid= in je eigen en voila
      echo "<a href=\"http://www.top100nl.net/cgi-bin/toplijst.cgi?hitin=1169918290\">Klikken</a>";
mysql_query("UPDATE `users` SET `bank`=`bank`+'10000',`klikmissie`='1' WHERE `login`='{$data->login}'"); 
} 
  ?>
</td></tr></table></body></html><?php mysql_close(); ?> 

