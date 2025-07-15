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
	$info = preg_replace("~\\[url=([^\\[]*)\]([^\\[]*)\\[/url\\]~i","<a href=\"\\1\" target=_blank>\\2</a>",$info); 
	$info = preg_replace("~\[b\]~i","<b>",$info);
    $info = preg_replace("~\[/b\]~i","</b>",$info);
    $info = preg_replace("~\[i\]~i","<i>",$info);
    $info = preg_replace("~\[/i\]~i","</i>",$info);
    $info = preg_replace("~\[s\]~i","<s>",$info);
    $info = preg_replace("~\[/s\]~i","</s>",$info);
    $info = preg_replace("~\[move\]~i","<marquee>",$info);
    $info = preg_replace("~\[/move\]~i","</marquee>",$info);
    $info = preg_replace("~\[u\]~i","<u>",$info);
    $info = preg_replace("~\[/u\]~i","</u>",$info);
    $info = preg_replace("~\[list\]~i","<UL>",$info);
    $info = preg_replace("~\[/list\]~i","</UL>",$info);
    $info = preg_replace("~\[\*\]~i","<LI>",$info);
    $info = preg_replace("~\[small\]~i","<font size=1>",$info);
    $info = preg_replace("~\[/small\]~i","</font>",$info); 
    $info = preg_replace("~\\[color=([^\\[]*)\]([^\\[]*)\\[/color\\]~i","<font color=\\1>\\2</font>",$info); 
    $info = preg_replace("~\\[face=([^\\[]*)\]([^\\[]*)\\[/face\\]~i","<font face=\\1>\\2</font>",$info);	
    $info = preg_replace("~\\[size=([^\\[]*)\]([^\\[]*)\\[/size\\]~i","<font size=\\1>\\2</font>",$info);	
    $info = preg_replace("~\[bo\]~i","$user->bo",$info);
	$info = preg_replace("~\[crime\]~i","$user->nrofcrime",$info);
	$info = preg_replace("~\[oc\]~i","$user->nrofoc",$info);
	$info = preg_replace("~\[auto\]~i","$user->nrofcar",$info);
	$info = preg_replace("~\[race\]~i","$user->nrofrace",$info);
	$info = preg_replace("~\[route\]~i","$user->nrofroute",$info);
	$info = preg_replace("~\[kill\]~i","$user->nrofkill",$info);
	$info = preg_replace("~\(b\)~i","<img src=images/img/biere.gif>",$info);
	$info = preg_replace("~\(B\)~i","<img src=images/img/biere.gif>",$info);
	$info = preg_replace("~:\)~i","<img src=images/img/sourire.gif>",$info);
	$info = preg_replace("~:-\)~i","<img src=images/img/sourire.gif>",$info);
	$info = preg_replace("~:d~i","<img src=images/img/content.gif>",$info);
	$info = preg_replace("~:-D~i","<img src=images/img/content.gif>",$info);
	$info = preg_replace("~:-O~i","<img src=images/img/OH-2.gif>",$info);
	$info = preg_replace("~:o~i","<img src=images/img/OH-1.gif>",$info);
	$info = preg_replace("~:p~i","<img src=images/img/langue.gif>",$info);
	$info = preg_replace("~:-P~i","<img src=images/img/langue.gif>",$info);
	$info = preg_replace("~\;\)~i","<img src=images/img/clin-oeuil.gif>",$info);
	$info = preg_replace("~\;-\)~i","<img src=images/img/clin-oeuil.gif>",$info);
	$info = preg_replace("~:\(~i","<img src=images/img/triste.gif>",$info);
	$info = preg_replace("~:-\(~i","<img src=images/img/triste.gif>",$info);
	$info = preg_replace("~:\|~i","<img src=images/img/OH-3.gif>",$info);
	$info = preg_replace("~:-\|~i","<img src=images/img/OH-3.gif>",$info);
	$info = preg_replace("~:\'\(~i","<img src=images/img/pleure.gif>",$info);
	$info = preg_replace("~\(h\)~i","<img src=images/img/cool.gif>",$info);
	$info = preg_replace("~\(H\)~i","<img src=images/img/cool.gif>",$info);
	$info = preg_replace("~:-@~i","<img src=images/img/enerve1.gif>",$info);
	$info = preg_replace("~:@~i","<img src=images/img/enerve2.gif>",$info);
	$info = preg_replace("~:s~i","<img src=images/img/roll-eyes.gif>",$info);
	$info = preg_replace("~:-S~i","<img src=images/img/roll-eyes.gif>",$info);
	$info = preg_replace("~\(k\)~i","<img src=images/img/bouche.gif>",$info);
	$info = preg_replace("~\(K\)~i","<img src=images/img/bouche.gif>",$info);
	$info = preg_replace("~\(l\)~i","<img src=images/img/coeur.gif>",$info);
	$info = preg_replace("~\(L\)~i","<img src=images/img/coeur.gif>",$info);
	$info = preg_replace("~\(u\)~i","<img src=images/img/coeur-brise.gif>",$info);
	$info = preg_replace("~\(U\)~i","<img src=images/img/coeur-brise.gif>",$info);
	$info = preg_replace("~\;-P~i","<img src=images/img/clin-oeuil-langue.gif>",$info);
	$info = preg_replace("~\;p~i","<img src=images/img/clin-oeuil-langue.gif>",$info);
	$info = preg_replace("~\(y\)~i","<img src=images/img/pouce-oui.gif>",$info);
	$info = preg_replace("~\(Y\)~i","<img src=images/img/pouce-oui.gif>",$info);
	$info = preg_replace("~\(n\)~i","<img src=images/img/pouce-non.gif>",$info);
	$info = preg_replace("~\(N\)~i","<img src=images/img/pouce-non.gif>",$info);
	$info = preg_replace("~\(6\)~i","<img src=images/img/diable.gif>",$info);
	$info = preg_replace("~\(d\)~i","<img src=images/img/drink.gif>",$info);
	$info = preg_replace("~\(D\)~i","<img src=images/img/drink.gif>",$info);
	$info = preg_replace("~_o_~i","<img src=images/img/worship.gif>",$info);
	$info = preg_replace("~\(g\)~i","<img src=images/img/gun.gif>",$info);
	$info = preg_replace("~\(G\)~i","<img src=images/img/guns.gif>",$info);
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