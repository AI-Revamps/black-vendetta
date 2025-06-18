<?php
  include('config.php');
  $dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");
  $data	= mysql_fetch_object($dbres);
  if(! check_login()) {
    header('Location: login.php');
    exit;
  }
if ($data->level < 255) {
echo "<script>  
<!--  
if (window.Event)   
  document.captureEvents(Event.MOUSEUP);   
 function nocontextmenu()    
{  
 event.cancelBubble = true  
 event.returnValue = false;  
  return false;  
}  
 function norightclick(e)   
{  
 if (window.Event)   
 {  
  if (e.which == 2 || e.which == 3)  
   return false;  
 }  
 else  
  if (event.button == 2 || event.button == 3)  
  {  
   event.cancelBubble = true  
   event.returnValue = false;  
   return false;  
  }  
 }  
 document.oncontextmenu = nocontextmenu;   
document.onmousedown = norightclick;   
 function disableselect(e){
return false
}
function reEnable(){
return true
}
document.onselectstart=new Function ('return false')
if (window.sidebar){
document.onmousedown=disableselect
document.onclick=reEnable
}

//-->   
</script>
";
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
    <td class="subTitle"><b>Profiel</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
<?php
  if(isset($_GET['x'])) {
    $dbres				= mysql_query("SELECT *,UNIX_TIMESTAMP(`online`) AS `online`,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`bc`) AS `bc` FROM `users` WHERE `login`='{$_GET['x']}'");
    $user = mysql_fetch_object($dbres);
if($user->pc - time() > 0 || $user->xp < 150) { $route = Nee; }
else { $route = Ja; }
if($user->bc - time() > 0 || $user->xp < 500) { $oc = Nee; }
else { $oc = Ja; }
if (!$user) { echo "Deze gebruiker bestaat niet."; exit; }
    $online				= (($user->online + 300) > time()) ? "<b>Ja</b>" : "Nee";
    $famillie				= ($user->famillie) ? "$user->famillie" : "<i>Geen</i>";
	$info = preg_replace("/\n/","<br>\n",$user->info);
    $info = ($user->info) ? preg_replace("/\[img](.*?)\[\/img]/","<img src=\"\\1\">",$info) : "<i>Geen</i>";
    $info = preg_replace("/\[img](.*?)\[\/img]/","<img src=\"\\1\">",$info);
	$info = eregi_replace("\\[url=([^\\[]*)\]([^\\[]*)\\[/url\\]","<a href=\"\\1\" target=_blank>\\2</a>",$info); 
	$info = eregi_replace("\[b\]","<b>",$info);
    $info = eregi_replace("\[/b\]","</b>",$info);
    $info = eregi_replace("\[i\]","<i>",$info);
    $info = eregi_replace("\[/i\]","</i>",$info);
    $info = eregi_replace("\[s\]","<s>",$info);
    $info = eregi_replace("\[/s\]","</s>",$info);
    $info = eregi_replace("\[move\]","<marquee>",$info);
    $info = eregi_replace("\[/move\]","</marquee>",$info);
    $info = eregi_replace("\[u\]","<u>",$info);
    $info = eregi_replace("\[/u\]","</u>",$info);
    $info = eregi_replace("\[list\]","<UL>",$info);
    $info = eregi_replace("\[/list\]","</UL>",$info);
    $info = eregi_replace("\[\*\]","<LI>",$info);
    $info = eregi_replace("\[small\]","<font size=1>",$info);
    $info = eregi_replace("\[/small\]","</font>",$info); 
    $info = eregi_replace("\\[color=([^\\[]*)\]([^\\[]*)\\[/color\\]","<font color=\\1>\\2</font>",$info); 
    $info = eregi_replace("\\[face=([^\\[]*)\]([^\\[]*)\\[/face\\]","<font face=\\1>\\2</font>",$info);	
    $info = eregi_replace("\\[size=([^\\[]*)\]([^\\[]*)\\[/size\\]","<font size=\\1>\\2</font>",$info);	
    $info = eregi_replace("\[bo\]","$user->bo",$info);
	$info = eregi_replace("\[crime\]","$user->nrofcrime",$info);
	$info = eregi_replace("\[oc\]","$user->nrofoc",$info);
	$info = eregi_replace("\[auto\]","$user->nrofcar",$info);
	$info = eregi_replace("\[race\]","$user->nrofrace",$info);
	$info = eregi_replace("\[route\]","$user->nrofroute",$info);
	$info = eregi_replace("\[kill\]","$user->nrofkill",$info);
	$info = eregi_replace("\(b\)","<img src=http://members.lycos.nl/js6287/chat/img/biere.gif>",$info);
	$info = eregi_replace("\(B\)","<img src=http://members.lycos.nl/js6287/chat/img/biere.gif>",$info);
	$info = eregi_replace(":\)","<img src=http://members.lycos.nl/js6287/chat/img/sourire.gif>",$info);
	$info = eregi_replace(":-\)","<img src=http://members.lycos.nl/js6287/chat/img/sourire.gif>",$info);
	$info = eregi_replace(":d","<img src=http://members.lycos.nl/js6287/chat/img/content.gif>",$info);
	$info = eregi_replace(":-D","<img src=http://members.lycos.nl/js6287/chat/img/content.gif>",$info);
	$info = eregi_replace(":-O","<img src=http://members.lycos.nl/js6287/chat/img/OH-2.gif>",$info);
	$info = eregi_replace(":o","<img src=http://members.lycos.nl/js6287/chat/img/OH-1.gif>",$info);
	$info = eregi_replace(":p","<img src=http://members.lycos.nl/js6287/chat/img/langue.gif>",$info);
	$info = eregi_replace(":-P","<img src=http://members.lycos.nl/js6287/chat/img/langue.gif>",$info);
	$info = eregi_replace("\;\)","<img src=http://members.lycos.nl/js6287/chat/img/clin-oeuil.gif>",$info);
	$info = eregi_replace("\;-\)","<img src=http://members.lycos.nl/js6287/chat/img/clin-oeuil.gif>",$info);
	$info = eregi_replace(":\(","<img src=http://members.lycos.nl/js6287/chat/img/triste.gif>",$info);
	$info = eregi_replace(":-\(","<img src=http://members.lycos.nl/js6287/chat/img/triste.gif>",$info);
	$info = eregi_replace(":\|","<img src=http://members.lycos.nl/js6287/chat/img/OH-3.gif>",$info);
	$info = eregi_replace(":-\|","<img src=http://members.lycos.nl/js6287/chat/img/OH-3.gif>",$info);
	$info = eregi_replace(":\'\(","<img src=http://members.lycos.nl/js6287/chat/img/pleure.gif>",$info);
	$info = eregi_replace("\(h\)","<img src=http://members.lycos.nl/js6287/chat/img/cool.gif>",$info);
	$info = eregi_replace("\(H\)","<img src=http://members.lycos.nl/js6287/chat/img/cool.gif>",$info);
	$info = eregi_replace(":-@","<img src=http://members.lycos.nl/js6287/chat/img/enerve1.gif>",$info);
	$info = eregi_replace(":@","<img src=http://members.lycos.nl/js6287/chat/img/enerve2.gif>",$info);
	$info = eregi_replace(":s","<img src=http://members.lycos.nl/js6287/chat/img/roll-eyes.gif>",$info);
	$info = eregi_replace(":-S","<img src=http://members.lycos.nl/js6287/chat/img/roll-eyes.gif>",$info);
	$info = eregi_replace("\(k\)","<img src=http://members.lycos.nl/js6287/chat/img/bouche.gif>",$info);
	$info = eregi_replace("\(K\)","<img src=http://members.lycos.nl/js6287/chat/img/bouche.gif>",$info);
	$info = eregi_replace("\(l\)","<img src=http://members.lycos.nl/js6287/chat/img/coeur.gif>",$info);
	$info = eregi_replace("\(L\)","<img src=http://members.lycos.nl/js6287/chat/img/coeur.gif>",$info);
	$info = eregi_replace("\(u\)","<img src=http://members.lycos.nl/js6287/chat/img/coeur-brise.gif>",$info);
	$info = eregi_replace("\(U\)","<img src=http://members.lycos.nl/js6287/chat/img/coeur-brise.gif>",$info);
	$info = eregi_replace("\;-P","<img src=http://members.lycos.nl/js6287/chat/img/clin-oeuil-langue.gif>",$info);
	$info = eregi_replace("\;p","<img src=http://members.lycos.nl/js6287/chat/img/clin-oeuil-langue.gif>",$info);
	$info = eregi_replace("\(y\)","<img src=http://members.lycos.nl/js6287/chat/img/pouce-oui.gif>",$info);
	$info = eregi_replace("\(Y\)","<img src=http://members.lycos.nl/js6287/chat/img/pouce-oui.gif>",$info);
	$info = eregi_replace("\(n\)","<img src=http://members.lycos.nl/js6287/chat/img/pouce-non.gif>",$info);
	$info = eregi_replace("\(N\)","<img src=http://members.lycos.nl/js6287/chat/img/pouce-non.gif>",$info);
	$info = eregi_replace("\(6\)","<img src=http://members.lycos.nl/js6287/chat/img/diable.gif>",$info);
	$info = eregi_replace("\(d\)","<img src=http://members.lycos.nl/js6287/chat/img/drink.gif>",$info);
	$info = eregi_replace("\(D\)","<img src=http://members.lycos.nl/js6287/chat/img/drink.gif>",$info);
	$info = eregi_replace("_o_","<img src=http://members.lycos.nl/js6287/chat/img/worship.gif>",$info);
	$info = eregi_replace("\(g\)","<img src=http://members.lycos.nl/js6287/chat/img/gun.gif>",$info);
	$info = eregi_replace("\(G\)","<img src=http://members.lycos.nl/js6287/chat/img/guns.gif>",$info);
/* $info = str_replace("\'", "'", $info); */
$rank = ($user->famrang == 5) ? "Don van" : "Famillie";
$famillie = ($user->famillie) ? "<a href=\"fam.php?x=$famillie\">$user->famillie</a>" : "Geen";
$pic = ($user->pic) ? "<img src=\"{$user->pic}\">" : "";
if ($user->xp < 10) { $rang = "$rang1"; }
elseif ($user->xp < 20) { $rang = "$rang2"; }
elseif ($user->xp < 50) { $rang = "$rang3"; }
elseif ($user->xp < 150) { $rang = "$rang4"; }
elseif ($user->xp < 500) { $rang = "$rang5"; }
elseif ($user->xp < 1000) { $rang = "$rang6"; }
elseif ($user->xp < 2000) { $rang = "$rang7"; }
elseif ($user->xp < 3000) { $rang = "$rang8"; }
elseif ($user->xp < 4500) { $rang = "$rang9"; }
elseif ($user->xp < 6000) { $rang = "$rang10"; }
elseif ($user->xp < 8000) { $rang = "$rang11"; }
elseif ($user->xp < 11000) { $rang = "$rang12"; }
elseif ($user->xp < 15000) { $rang = "$rang13"; }
elseif ($user->xp < 20000) { $rang = "$rang14"; }
elseif ($user->xp >= 20000) { $rang = "$rang15"; }
$status = ($user->status == levend) ? "<b><font color=009900>Levend</font></b>" : "<b><font color=red>Dood</font></b>";
$huwelijk = (!$user->huwelijk) ? "<i>Niet getrouwd</i>" : "$user->login & $user->huwelijk";
$respect = round($user->respect);
if ($user->zak < 1) { $zak = "Blut"; }
elseif ($user->zak < 10000) { $zak = "Arm"; }
elseif ($user->zak < 100000) { $zak = "Modaal"; }
elseif ($user->zak < 1000000) { $zak = "Rijk"; }
elseif ($user->zak < 10000000) { $zak = "Zeer rijk"; }
else { $zak = "Rijker dan god"; }
if ($user->pic) {
print <<<ENDHTML
  <table align="center">
	  <tr><td width=125>$pic</td><td align="right"></td></tr></table>
ENDHTML;
}
	 	  print <<<ENDHTML
	<table align="center">
ENDHTML;
if ($user->status == dood) {
  $topicsubject = "RIP {$user->login}" ;
  $top = mysql_query("SELECT id FROM forum_topics WHERE `subject`='{$topicsubject}'") or die(mysql_error());
  $topi = mysql_fetch_object($top);
  $riptopic = $topi->id;
  print "<tr align=left><td rowspan=500 align=left><img src=images/dead.gif border=0 onClick=\"location.href='forum.php?topic=$riptopic';\"></td></tr>"; }
if ($user->level >= 200) { print "<tr align=left><td rowspan=500 align=left><img src=images/crew.gif border=0></td></tr>"; }
print <<<ENDHTML
	  <tr><td width=125>Nickname:</td>		<td align="right">{$user->login}</td></tr>
	  <tr><td width=125>Geslacht:</td>		<td align="right">{$user->geslacht}</td></tr>
	  <tr><td width=125>Huwelijk:</td>		<td align="right">$huwelijk</td></tr>
	  <tr><td width=125>{$rank}:</td>		<td align="right">$famillie</td></tr>
	  <tr><td width=125>Geld op zak:</td>	<td align="right">{$zak}</td></tr>
	  <tr><td width=125>Status:</td>	    <td align="right">$status</td></tr>
	  <tr><td width=125>Rang:</td>          <td align="right">{$rang}</td></tr>
	  <tr><td width=125>Route 66:</td>      <td align="right">{$route}</td></tr>
	  <tr><td width=125>Organised Crime:</td>      <td align="right">{$oc}</td></tr>
	  <tr><td>Respect:</td>                 <td align="right">{$respect}</td></tr>
 	  <tr><td width=125>Online:</td>	<td align="right">$online</td></tr>
ENDHTML;
$sql = mysql_query("SELECT spel,stad FROM casino WHERE `owner`='{$user->login}' ORDER BY `stad`") or die(mysql_error()); 
$count = 0;
$property = mysql_num_rows($sql);
if($property == 0){$bezit ="Geen";echo"<tr><td width=125>Bezit:</td><td align=right>$bezit</td></tr>";}
else{
while ($gegeven = mysql_fetch_object($sql)) {	
$bezit = "$gegeven->spel $gegeven->stad";
if($count == 0){echo"<tr><td width=125>Bezit:</td><td align=right>$bezit</td></tr>";}
else{echo"<tr><td width=125>&nbsp;</td><td align=right>$bezit</td></tr>";}
$count++;
}
}
echo"	  
	</table>
  </td></tr>
  <tr>
          <td>&nbsp;</td>
        </tr><tr> 
    <td class=subTitle><b>Info</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt><i>
{$info}
  </i></td></tr>
";
print "<tr>
          <td>&nbsp;</td>
        </tr><tr> 
    <td class=subTitle><b>Vrienden</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
      $fre = mysql_query("SELECT * FROM `friends` WHERE `login`='{$_GET['x']}'");
$nr = mysql_num_rows($fre);
if ($nr == 0) {	  echo"* Geen";}
else{
	  while($fr = mysql_fetch_object($fre)) {
		print "\n* <a href=user.php?x=$fr->friend>$fr->friend</a>";
      }
	 }
      print "  *</td></tr><tr><td><table width=100%>\n<tr><td align=right><table cellpadding=0 cellspacing=0>
	<tr>";
	  if($data->login != $user->login) { print "<td class=\"mainTxt\" align=\"center\" width=100><a href=\"message.php?p=new&to={$user->login}\">Stuur Bericht</a></td>\n<td class=\"mainTxt\" align=\"center\" width=100><a href=\"profile.php?addfr={$user->login}\">Vriendenlijst</a></td>	    <td width=5> </td>\n"; }
    }

?>
</table>

</body>


</html>
</table>