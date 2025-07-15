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
    <td class="subTitle"><b>Poll management</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
<?php
if($data->level < 1000) {echo"Je hebt niet genoeg rechten.";exit;}
if ($_GET['x'] == u) { 
if ($_POST['update']) {
mysql_query("UPDATE poll SET actief='0' WHERE `id`='{$_POST['id']}'") or die(mysql_error());
echo "Deze poll is nu niet meer actief.";
}
else {
$dbres = mysql_query("SELECT * FROM `poll` WHERE `id`='{$_GET['u']}'");
$poll = mysql_fetch_object($dbres); 
print "<form method=post action=adm-poll.php?x=u>
Bent u zeker dat u deze poll wilt archiveren?<br>
<input type=hidden name=id value='$poll->id' readonly=\"true\"><br>
Vraag: {$poll->vraag}<br>
<br><input type=submit name=update value=Ja>
</form>";exit;
}
}
elseif ($_GET['x'] == d) { 
if ($_POST['dell']) {
mysql_query("DELETE FROM `poll` WHERE `id`='{$_POST['id']}'"); 
echo "Poll verwijderd."; 
}
else {
$dbres = mysql_query("SELECT * FROM `poll` WHERE `id`='{$_GET['d']}'");
$poll = mysql_fetch_object($dbres); 
print "<form method=post action=adm-poll.php?x=d>
Bent u zeker dat u deze poll wilt verwijderen?<br>
<input type=hidden name=id value='$poll->id' readonly=\"true\"><br>
Vraag: $poll->vraag<br>
<br><input type=submit name=dell value=Ja>
</form>";exit;
}
}
elseif ($_POST['new']) {
$time = time();
mysql_query("INSERT INTO `poll`(`vraag`,`actief`,`datum`,`keuze1`,`keuze2`,`keuze3`,`keuze4`,`keuze5`,`keuze6`,`keuze7`,`keuze8`) values('{$_POST['vraag']}','1','{$time}','{$_POST['keuze1']}','{$_POST['keuze2']}','{$_POST['keuze3']}','{$_POST['keuze4']}','{$_POST['keuze5']}','{$_POST['keuze6']}','{$_POST['keuze7']}','{$_POST['keuze8']}')") or die(mysql_error());
echo "Poll toegevoegd.";
}
print "<form method=post action=adm-poll.php>
Vraag: <input type=text name=vraag><br>
Keuze1: <input type=text name=keuze1><br>
Keuze2: <input type=text name=keuze2><br>
Keuze3: <input type=text name=keuze3><br>
Keuze4: <input type=text name=keuze4><br>
Keuze5: <input type=text name=keuze5><br>
Keuze6: <input type=text name=keuze6><br>
Keuze7: <input type=text name=keuze7><br>
Keuze8: <input type=text name=keuze8><br>
<br><input type=submit name=new value=Submit>
  <input type=reset name=Reset value=Reset>
</form>";

print <<<ENDHTML
<table width=100%><tr>	  <td align=center><b>ID</b></td> 
      <td align=center><b>Vraag</b></td> 
	  <td align=center><b>Actief</b></td>
	  <td align=center><b>Archief</b></td> 
	  <td align=center><b>Delete</b></td>
	              </tr>

ENDHTML;
$query = "SELECT * FROM `poll` ORDER BY `datum` ASC"; 
$info = mysql_query($query) or die(mysql_error()); 
while ($gegeven = mysql_fetch_object($info)) { 
print <<<ENDHTML

<tr>      
      <td align=center>$gegeven->id</td>
	  <td align=center>$gegeven->vraag</td> 
	  <td align=center>$gegeven->actief</td>
	  <td align=center><a href=adm-poll.php?x=u&u=$gegeven->id>[x]</a></td> 
	  <td align=center><a href=adm-poll.php?x=d&d=$gegeven->id>[x]</a></td>
</tr>
ENDHTML;
}
?>