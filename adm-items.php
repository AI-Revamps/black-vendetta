<?php
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
<table width=100%>
<tr> 
    <td class="subTitle"><b>Item management</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
<?php
if($data->level < 1000) {echo"Je hebt niet genoeg rechten.";exit;}
if ($_GET['x'] == u) { 
if ($_POST['update']) {
mysql_query("
UPDATE `items` SET 
`nr`='{$_POST['nr']}',
`type`='{$_POST['type']}',
`naam`='{$_POST['naam']}',
`aprijs`='{$_POST['aprijs']}',
`vprijs`='{$_POST['vprijs']}',
`effect`='{$_POST['effect']}'
 WHERE `id`='{$_POST['id']}' ; ") or die(mysql_error());
echo "Item geupdate.";
}
else {
$dbres = mysql_query("SELECT * FROM `items` WHERE `id`='{$_GET['u']}'");
$user = mysql_fetch_object($dbres); 
print "<form method=post action=adm-items.php?x=u>
ID: <input type=text name=id value='$user->id' readonly=\"true\"><br>
Nr: <input type=text name=nr value='$user->nr'><br>
Type: <input type=text name=type value='$user->type'><br>
Naam: <input type=text name=naam value='$user->naam'><br>
Aankoop prijs: <input type=text name=aprijs value='$user->aprijs'><br>
Verkoop prijs: <input type=text name=vprijs value='$user->vprijs'><br>
Effect: <input type=text name=effect value='$user->effect'><br>
<br><input type=submit name=update value=Submit>
  <input type=reset name=Reset value=Reset>
</form>";exit;
}
}
elseif ($_GET['x'] == d) { 
mysql_query("DELETE FROM `items` WHERE `id`='{$_GET['d']}'"); 
echo "Item verwijderd."; 
}
elseif ($_POST['new']) {
mysql_query("INSERT INTO `items`(`nr`,`type`,`naam`,`aprijs`,`vprijs`,`effect`) values('{$_POST['nr']}','{$_POST['type']}','{$_POST['naam']}','{$_POST['aprijs']}','{$_POST['vprijs']}','{$_POST['effect']}')") or die(mysql_error());
echo "Item toegevoegd.";
}
print "<form method=post action=adm-items.php>
Nr: <input type=text name=nr><br>
Type: <input type=text name=type><br>
Naam: <input type=text name=naam><br>
Aankoop prijs: <input type=text name=aprijs><br>
Verkoop prijs: <input type=text name=vprijs><br>
Effect: <input type=text name=effect><br>
<br><input type=submit name=new value=Submit>
  <input type=reset name=Reset value=Reset>
</form>";

print <<<ENDHTML
<table width=100%><tr>	  <td align=center><b>ID</b></td> 
      <td align=center><b>Nr</b></td> 
	  <td align=center><b>Type</b></td>
      <td align=center><b>Naam</b></td> 
	  <td align=center><b>Aankoop</b></td>
	  <td align=center><b>Verkoop</b></td> 
	  <td align=center><b>Effect</b></td>
	  <td align=center><b>Edit</b></td> 
	  <td align=center><b>Delete</b></td>
	              </tr>

ENDHTML;
$query = "SELECT * FROM `items` ORDER BY `type` ASC"; 
$info = mysql_query($query) or die(mysql_error()); 
while ($gegeven = mysql_fetch_object($info)) { 
print <<<ENDHTML

<tr>      
      <td align=center>$gegeven->id</td>
	  <td align=center>$gegeven->nr</td> 
	  <td align=center>$gegeven->type</td>
      <td align=center>$gegeven->naam</td> 
	  <td align=center>&euro;$gegeven->aprijs</td>
	  <td align=center>&euro;$gegeven->vprijs</td> 
	  <td align=center>$gegeven->effect</td>
	  <td align=center><a href=adm-items.php?x=u&u=$gegeven->id>[x]</a></td> 
	  <td align=center><a href=adm-items.php?x=d&d=$gegeven->id>[x]</a></td>
</tr>
ENDHTML;
}
?>