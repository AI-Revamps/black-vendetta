<?php
  include('config.php');
  $dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");
  $data	= mysql_fetch_object($dbres);
  if(! check_login()) {
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
<table width=100% align=center>
  <tr> 
    <td class="subTitle"><b>Tip/Bug</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
  <?
echo "Bug gevonden? Of wil je iets nieuws? Meld het hier...";
$ip = $_SERVER['REMOTE_ADDR'];
print "<table><tr><td width=100% align=center><form method='post'>";
print "<input type='hidden' name='naam' value='$data->login'>";
print "<input type='hidden' name='email' value='$data->email'>";
print "<tr><td width=100%>Je Tip<br><textarea rows=\"7\" name=\"tip\" cols=\"40\"></textarea><br></td></tr>
<tr><td><br>Je code is: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src=img.php></td></tr>
<tr><td>Typ hier de code in:    <input type=text name=verify></td></tr>";
print "<tr><td width=100%><input type=submit name=submit value=Verzenden></td></tr>";
print "</table></td></tr></form>";
if (isset($_POST['submit'],$_POST['tip'])) {
if (!$_POST['verify']){echo"Je moet een code opgeven.";}
elseif($_POST['verify'] != $_SESSION['verify']){echo"De code die je hebt ingevoerd komt niet overeen met het plaatje.";}
else{$naam = $_POST['naam'];
$email = $_POST['email'];
$tip = $_POST['tip'];
mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'$naam','JanuS','Tip/Bug','Naam: $naam <br>E-mail: $email <br>IP: $ip <br>Tip: $tip')");
echo "Tip verzonden."; exit;
}
}
?>