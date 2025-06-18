<?php
  include("config.php");
$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");  
$data    = mysql_fetch_object($dbres);
  if(!check_login()) {
    header('Location: login.php');
    exit;
  }
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
    <td class="subTitle"><b>Familie Recruit</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
<?PHP 
if (isset($_GET['accept'])) {
$totaal = mysql_num_rows(mysql_query("SELECT * FROM `users` WHERE `famillie`='{$_GET['fam']}'"));
$totaal = floor($totaal*5);
$famillie = mysql_fetch_object(mysql_query("SELECT * FROM `famillie` WHERE `name`='{$_GET['fam']}'"));
if (!$_GET['fam']) { exit; }
if ($_GET['accept'] == 1) { 
$query = mysql_query("SELECT * FROM `invite` WHERE `login`='{$data->login}' AND `famillie`='{$_GET['fam']}'");
$user = mysql_fetch_object($query);
$exist = mysql_num_rows($query);
if ($exist == 0) { echo "Je hebt geen uitnodiging ontvangen om deze familie te joinen."; exit; }
elseif ($data->famillie) { echo "Je hebt al een familie."; exit; }
elseif ($totaal >= $famillie->grond) { echo "Deze familie heeft niet genoeg grond."; }
else {
mysql_query("UPDATE `users` SET `famillie`='{$_GET['fam']}',`famrang`='1' WHERE `login`='{$data->login}'");  
echo "Je zit nu in {$_GET['fam']}"; mysql_query("DELETE FROM `invite` WHERE `login`='$data->login' AND `famillie`='{$_GET['fam']}'");
mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$famillie->recruiter}','Invite','$data->login heeft de uitnodiging geaccepteerd.')"); 
}
}
elseif ($_GET['accept'] == 0) { echo "Je hebt geweigerd."; mysql_query("DELETE FROM `invite` WHERE `login`='{$data->login}' AND `famillie`='{$_GET['fam']}'"); exit; }
}
elseif (isset($_GET['fam'])) {
$query = mysql_query("SELECT * FROM `invite` WHERE `login`='{$data->login}' AND `famillie`='{$_GET['fam']}'");
$user = mysql_fetch_object($query);
$exist = mysql_num_rows($query);
if ($exist == 0) { echo "Je hebt geen uitnodiging ontvangen om deze familie te joinen."; exit; }
elseif ($data->famillie) { echo "Je hebt al een familie."; exit; }
else { echo "<a href=invite.php?accept=1&fam={$_GET['fam']}>Accepteren</a>&nbsp;&nbsp;<a href=invite.php?accept=0&fam={$_GET['fam']}>Weigeren</a>"; }
}
?>