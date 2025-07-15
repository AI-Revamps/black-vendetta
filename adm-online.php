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
<table align=center width=100%><tr> 
    <td class="subTitle"><b>Online</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
<table width="100%" border=0 cellspacing=0 cellpadding=0>
  <tr>
    <td width='25%'>&nbsp;</td>
    <td width='30%'><center><b>Login</b></center></td>
    <td width='45%'><center><b>Rang</b></center></td>
  </tr>
<?php


if ($data->level < 200) { exit; }
$dbres = mysql_query("SELECT * FROM `users` WHERE  UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(`online`) < 300 ORDER BY `xp` DESC");
$aantal = mysql_num_rows($dbres);
$i = 0; 
while ($info = mysql_fetch_object($dbres)) { 
if ($info->xp < 10) { $rang = "$rang1"; }
elseif ($info->xp < 20) { $rang = "$rang2";}
elseif ($info->xp < 50) { $rang = "$rang3"; }
elseif ($info->xp < 150) { $rang = "$rang4"; }
elseif ($info->xp < 500) { $rang = "$rang5"; }
elseif ($info->xp < 1000) { $rang = "$rang6"; }
elseif ($info->xp < 2000) { $rang = "$rang7"; }
elseif ($info->xp < 3000) { $rang = "$rang8"; }
elseif ($info->xp < 4500) { $rang = "$rang9"; }
elseif ($info->xp < 6000) { $rang = "$rang10"; }
elseif ($info->xp < 8000) { $rang = "$rang11"; }
elseif ($info->xp < 11000) { $rang = "$rang12"; }
elseif ($info->xp < 15000) { $rang = "$rang13"; }
elseif ($info->xp < 20000) { $rang = "$rang14"; }
elseif ($info->xp >= 20000) { $rang = "$rang15"; }
echo "<tr>
    <td width='25%'><center><a href=adm-search.php?del=$info->login>[Delete]</a> <a href=adm-search.php?login=$info->login>[Login]</a> <a href=adm-bo.php?q=$info->login>[Stats]</a> <a href=adm-search.php?ban=$info->ip>[Ban]</a></td>
    <td width='30%'><center><a href=user.php?x={$info->login}>$info->login</a></td>
    <td width='45%'><center>$rang</td>
  </tr>";
$count++; 
}
if ($count == 1){$ww = "is";}
else {$ww = "zijn";}
if ($count == 1){$lid = "lid";}
else {$lid = "leden";}
echo "<td><br><br><b>Er $ww $count $lid online</b></td></tr></table>";
?>