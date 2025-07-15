<?php   
error_reporting(E_ALL);
include("config.php");
  if(! check_login()) {
    header('Location: login.php');
    exit;
  }
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
<body><?php
if(isset($_GET['o'])) {
$order = $_GET['o'];}
else {$order = "DESC";}
if(isset($_GET['s'])) {
$sort = $_GET['s'];}
else {$sort = "xp";}
$begin= ($_GET['p'] >= 0) ? $_GET['p']*20 : 0;
$q = $_GET['q'];
$_GET['q'] = "*{$q}*";
$_GET['q']	= preg_replace('/\*/','%',$_GET['q']);
$us = mysql_query("SELECT level,login,xp,status,UNIX_TIMESTAMP(`online`) AS `online` FROM `users` ORDER BY ".$sort." ".$order." LIMIT $begin,20");
if($_GET['status'] !=""){$status = $_GET['status']; $us = mysql_query("SELECT level,login,xp,status,UNIX_TIMESTAMP(`online`) AS `online` FROM `users` WHERE `status`='{$_GET['status']}' ORDER BY ".$sort." ".$order." LIMIT $begin,20");}
if($_GET['online'] =="ja"){$online = $_GET['online']; $us = mysql_query("SELECT level,login,xp,status,UNIX_TIMESTAMP(`online`) AS `online` FROM `users` WHERE UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(`online`) < 300 ORDER BY ".$sort." ".$order." LIMIT $begin,20");}
if ($_GET['q'] != "") {
if($_GET['status'] !=""){$status = $_GET['status']; $us = mysql_query("SELECT level,login,xp,status,UNIX_TIMESTAMP(`online`) AS `online` FROM `users` WHERE `status`='{$_GET['status']}' AND `login` LIKE '{$_GET['q']}' ORDER BY ".$sort." ".$order." LIMIT $begin,20");}
elseif($_GET['online'] =="ja"){$online = $_GET['online']; $us = mysql_query("SELECT level,login,xp,status,UNIX_TIMESTAMP(`online`) AS `online` FROM `users` WHERE UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(`online`) < 300 AND `login` LIKE '{$_GET['q']}' ORDER BY ".$sort." ".$order." LIMIT $begin,20");}
else {$us = mysql_query("SELECT level,login,xp,status,UNIX_TIMESTAMP(`online`) AS `online` FROM `users` WHERE `login` LIKE '{$_GET['q']}' ORDER BY ".$sort." ".$order." LIMIT $begin,20");}}
$total = mysql_num_rows($us);
$x                    = 0;
$page = $_GET['p'];
print "<table width=100%>
<tr> 
    <td class=subTitle><b>Members</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>
<table width=100% align=center>
<tr><td colspan=4 align=right><a href=?s=$sort&q=$q&o=$order&status=levend>Levend</a>&nbsp;&nbsp;<a href=?s=$sort&q=$q&o=$order&status=dood>Dood</a>&nbsp;&nbsp;<a href=?s=$sort&q=$q&o=$order&online=ja>Online</a>&nbsp;&nbsp;<a href=?s=$sort&q=$q&o=$order>Alle</a></td><tr>
<tr><td width=20%><b>Naam</b>&nbsp;<a href=?p=$page&q=$q&status=$status&online=$online&s=login&o=ASC><font face=Wingdings>&acirc;</font></a>&nbsp;<a href=?p=$page&status=$status&online=$online&q=$q&s=login&o=DESC><font face=Wingdings>&aacute;</font></a></td>
<td><b>Rang</b>&nbsp;<a href=?p=$page&s=xp&q=$q&status=$status&online=$online&o=DESC><font face=Wingdings>&acirc;</font></a>&nbsp;<a href=?p=$page&status=$status&q=$q&online=$online&s=xp&o=ASC><font face=Wingdings>&aacute;</font></a></td>
<td><b>Status</b>&nbsp;<a href=?p=$page&q=$q&status=$status&online=$online&s=status&o=ASC><font face=Wingdings>&acirc;</font></a>&nbsp;<a href=?p=$page&status=$status&q=$q&online=$online&s=status&o=DESC><font face=Wingdings>&aacute;</font></a></td>
<td><b>Online</b>&nbsp;<a href=?p=$page&q=$q&status=$status&online=$online&s=online&o=ASC><font face=Wingdings>&acirc;</font></a>&nbsp;<a href=?p=$page&status=$status&q=$q&online=$online&s=online&o=DESC><font face=Wingdings>&aacute;</font></a></td>";
for($j=$begin+1; $usr = mysql_fetch_object($us); $j++) {
if ($usr->xp < 10) { $rang = "$rang1"; }
elseif ($usr->xp < 20) { $rang = "$rang2"; }
elseif ($usr->xp < 50) { $rang = "$rang3"; }
elseif ($usr->xp < 150) { $rang = "$rang4"; }
elseif ($usr->xp < 500) { $rang = "$rang5"; }
elseif ($usr->xp < 1000) { $rang = "$rang6"; }
elseif ($usr->xp < 2000) { $rang = "$rang7"; }
elseif ($usr->xp < 3000) { $rang = "$rang8"; }
elseif ($usr->xp < 4500) { $rang = "$rang9"; }
elseif ($usr->xp < 6000) { $rang = "$rang10"; }
elseif ($usr->xp < 8000) { $rang = "$rang11"; }
elseif ($usr->xp < 11000) { $rang = "$rang12"; }
elseif ($usr->xp < 15000) { $rang = "$rang13"; }
elseif ($usr->xp < 20000) { $rang = "$rang14"; }
elseif ($usr->xp >= 20000) { $rang = "$rang15"; }
$statu = ($usr->status == levend) ? "<b><font color=009900>Levend</font></b>" : "<b><font color=red>Dood</font></b>";
$on	= (($usr->online + 300) > time()) ? "<b>Ja</b>" : "Nee";
		print "<tr><td><a href=user.php?x=$usr->login>$usr->login</a></td><td>$rang</td><td>$statu</td><td>$on</td></tr>";
      }
$us = mysql_query("SELECT id FROM `users`");
if($_GET['status'] !=""){$us = mysql_query("SELECT id FROM `users` WHERE `status`='{$_GET['status']}'");}
if($_GET['online'] =="ja"){$us = mysql_query("SELECT id FROM `users` WHERE UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(`online`) < 300");}
if ($_GET['q'] != "") {$us = mysql_query("SELECT id FROM `users` WHERE `login` LIKE '{$_GET['q']}'");
if($_GET['status'] !=""){$us = mysql_query("SELECT id FROM `users` WHERE `status`='{$_GET['status']}' AND `login` LIKE '{$_GET['q']}'");}
if($_GET['online'] =="ja"){$us = mysql_query("SELECT id FROM `users` WHERE UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(`online`) < 300 AND `login` LIKE '{$_GET['q']}'");}
}
print "</table><br><center><tr><td>&nbsp;&nbsp;</td></tr><tr> 
    <td class=subTitle><b>Zoeken</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt><form method=get>
	Voorbeeld: <b>a</b> zal een lijst geven met alle namen waar een a in voorkomt. <br>Door bvb <b>a*c</b> te typen kan je in het midden van een woord een letter (of groep letters) die je niet kent laten zoeken.<br>
	<br><input type=text name=q value={$_REQUEST['q']}> <input type=submit value=Zoek!>
	</form></td></tr>";
    print "  <tr><td align=\"center\">";
     if(mysql_num_rows($us) <= 20)
    print "&#60; 1 &#62;</td></tr></table>\n";
  else {
    if($begin/20 == 0)
      print "&#60;&#60; ";
    else
      print "<a href=\"?s=$sort&status=$status&q=$q&online=$online&o=$order&p=". ($begin/20-1) ."\">&#60;&#60;</a> ";

    for($i=0; $i<mysql_num_rows($us)/20; $i++) {
      print "<a href=\"?s=$sort&status=$status&q=$q&online=$online&o=$order&p=$i\">". ($i+1) ."</a> ";
    }

    if($begin+20 >= mysql_num_rows($us))
      print "&#62;&#62; ";
    else
      print "<a href=\"?s=$sort&status=$status&q=$q&online=$online&o=$order&p=". ($begin/20+1) ."\">&#62;&#62;</a>";
  }

?>
</body>
</html>