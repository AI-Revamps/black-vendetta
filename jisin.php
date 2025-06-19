<?php
  include("config.php");
    $dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");
  $data	= mysql_fetch_object($dbres);
  ?>
<html>
<head>
<title>Vendetta</title>
<link rel="stylesheet" type="text/css" href="style.css">
<meta name="keywords" content="Vendetta,Crimegame,crimegame,vendetta">
<meta name="language" content="english">
<META name="description" lang="nl" content="Vendetta crimegame met pit.">
</head>
<?php
$vc = mysql_fetch_object(mysql_query("SELECT `bo`,`boete` FROM `jail` WHERE `login`='$data->login'"));
echo " 
<table align=center width=100%> 
  <tr> 
    <td class=subTitle><b>Gevangenis</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>
    <table align=center width=100%>"; 
 if (!$time && !$word) { print "<tr><td align=center>Je bent nu uit de gevangenis.</td></tr>";exit; }
else { 
	print "<tr><td align=center> Je zit nog $time $word in de gevangenis.<br><br>";
	if ($vc->bo < 2) { echo "<a href=jail.php?bo=yes>Probeer te ontsnappen</a><br><br>"; }
	echo "<a href=jail.php?x={$data->login}>Koop jezelf vrij voor &euro;$vc->boete</a></td></tr>"; exit;
}
?>