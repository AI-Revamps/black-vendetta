<?PHP
  session_start();
 include ("config.php");
$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");
$data	= mysql_fetch_object($dbres);
if ($data->status == dood) { header("Location: rip.php"); exit; }
?>
<html>
<head>
<title>Vendetta</title>
<link rel="stylesheet" type="text/css" href="style.css">
<meta name="keywords" content="Vendetta,Crimegame,crimegame,vendetta">
<meta name="language" content="english">
<META name="description" lang="nl" content="Vendetta crimegame met pit.">
</head>
<body>
<?
if (!$_SESSION['login']) {
  $dbres                                = mysql_query("SELECT `id` FROM `users` WHERE UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(`online`) < 300");
  $online                               = mysql_num_rows($dbres);
if ($online == 1) { $aantal = lid; }
else { $aantal = leden; }
if ($online == 1) { $ww = is; }
else { $ww = zijn; }
print "<table width=100%>
  <tr><td class=\"subTitle\"><b>Home</b></td></tr><tr><td>&nbsp;&nbsp;</td></tr><td class=mainTxt align=left>";
echo "<b>Vendetta is een tekst-based online multiplayer role playing game.</b><br><br>Het spel speelt zich af in de onderwereld, tussen gangsters en wiseguys, waar misdaden tot de gewone orde van de dag hoort. Door misdaden te plegen, auto's te stelen, banken te beroven,... kan je een machtige misdadiger worden en alle obstakels op je pad kan je uitroeien door wapens en kogels te kopen en te gaan moorden. Je kan jezelf echter ook beschermen tegen moordenaars door een hoop bondgenoten te verwerven, body guards te kopen, kogelvrije vesten te kopen,...<br><br>Denk je dus klaar te zijn voor deze uitdaging? Klik dan als een echte gangster op de 'Registreer' knop en wandel binnen in de wereld van Vendettagame.<br><br>Veel spelplezier!
      <br><br>
	  <b><font color=FFFFFF>Er $ww momenteel $online $aantal aan het spelen.</font></b></table>";
echo "<br>";
}
if ($_SESSION['login']) {
if ($data->status == dood) { header("Location: rip.php"); exit; }
print "<table width=100%>";
$kogel = mysql_query("SELECT * FROM `kogels` WHERE UNIX_TIMESTAMP(`time`)-UNIX_TIMESTAMP(NOW()) < 0");
while($kogels = mysql_fetch_object($kogel)) {
mysql_query("UPDATE `users` SET `kogels`=`kogels`+$kogels->aantal WHERE `login`='$kogels->login'");
mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$kogels->login}','Kogels','Je kogels zijn niet verkocht')");
mysql_query("DELETE FROM `kogels` WHERE `id`='{$kogels->id}'");
}
$cars = mysql_query("SELECT * FROM `mgarage` WHERE UNIX_TIMESTAMP(`time`)-UNIX_TIMESTAMP(NOW()) < 0");
while($cars = mysql_fetch_object($cars)) {
mysql_query("INSERT INTO `garage`(`id`,`login`,`naam`,`waarde`,`damage`,`stad`) values('{$cars->id}','$cars->login','$cars->naam','$cars->waarde','$cars->damage','$cars->stad')");
mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$cars->login}','Wagen','Je wagen is niet verkocht')");
mysql_query("DELETE FROM `mgarage` WHERE `id`='{$cars->id}'");
}
mysql_query("DELETE FROM `ws` WHERE UNIX_TIMESTAMP(`time`)-UNIX_TIMESTAMP(NOW()) < 0");
$inboxnew = mysql_num_rows(mysql_query("SELECT id FROM `messages` WHERE `read`=0 AND `to`='{$data->login}'"));
$new1 = "nieuw";
$new2 = "nieuwe";
$iword = ($inboxnew == 1) ? $new1 : $new2;
$message1 = "bericht";
$message2 = "berichten";
$xword = ($inboxnew == 1) ? $message1 : $message2;
$min = gmdate('i', $data->play);
$uur = gmdate('G', $data->play);
$dag = floor($data->play / 86400);
if ($inboxnew > 0) { echo "<tr><td class=subTitle><b>Berichten</b></td></tr><tr><td>&nbsp;&nbsp;</td></tr><td class=mainTxt><strong><a href=message.php?p=inbox><b><center>$inboxnew $iword $xword</a></strong></b></center></td></tr><tr><td>&nbsp;&nbsp;</td></tr>"; }
$time = time();
$crime = gmdate('i:s',($data->crime - $time));
$ac = gmdate('i:s', ($data->ac - $time));
$bc = gmdate('H:i:s', ($data->bc - $time));
$pc = gmdate('i:s', ($data->pc - $time));
$tc = gmdate('i:s', ($data->transport - $time));
$kc = gmdate('i:s', ($data->kc - $time));
$bt = gmdate('i:s', ($data->slaap - $time));
//$oc = gmdate('H:i:s', ($data->oc - $time));
if (($data->crime - $time) < 0) { $crime = Nu; }
if (($data->ac - $time) < 0) { $ac = Nu; }
if (($data->bc - $time) < 0) { $bc = Nu; }
if (($data->pc - $time) < 0) { $pc = Nu; }
if (($data->transport - $time) < 0) { $tc = Nu; }
if (($data->kc - $time) < 0) { $kc = Nu; }
if (($data->slaap - $time) < 0) { $bt = Nu; }
/*  (90 - ($time - $data->ac)) */
$tot  = $data->bank + $data->zak;
$sign = mysql_query("SELECT *,UNIX_TIMESTAMP(`start`) AS `start`,DATE_FORMAT(`start`,'%d-%m-%Y %H:%i') FROM `users` WHERE `login`='{$data->login}' AND `activated`=1");
$start = mysql_fetch_object($sign); 
if (!$data->famillie) { $famillie = "Geen"; }
elseif ($data->famillie != NULL) { $famillie = $data->famillie; }
if ($data->{$data->stad} > 0) { $huis = "Ja"; }
else { $huis = "Geen"; }
$respect = round($data->respect,0);
$gam = mysql_query("SELECT * FROM `casino` WHERE `owner`='{$data->login}'");
$game   = mysql_fetch_object($gam);
if (!$data->testament) { $testament = "Geen"; }
else { $testament = $data->testament; }
if (!$data->wapon) { $wapon = "Geen"; }
else {
$dbres = mysql_query("SELECT * FROM `items` WHERE `type`='att' AND `nr`='{$data->wapon}'");
$wapen = mysql_fetch_object($dbres);
$wapon = $wapen->naam;
}
$dbres = mysql_query("SELECT * FROM `items` WHERE `type`='def' AND `nr`='{$data->defence}'");
$defence = mysql_fetch_object($dbres);
$defence = $defence->naam;
if ($game->spel == NULL) { $spel = "Geen"; }
elseif ($game->spel != NULL) { $spel = "$game->spel $game->stad"; }
$health = $data->health;
$rangvordering = round($vordering,2);
$schiet = round($data->se,1); 
if ($rangvordering == 100 && $data->xp < 20000) { $rangvordering = 99.99; }
if ($data->xp > 20000) { $rangvordering = 100; }
$shealth = round(100 - $data->health); 
$svordering = (100 - $rangvordering);
$sschiet = round(100 - $schiet);
if ($rangvordering > 50) { $vltekst = "{$rangvordering}%"; }
else { $vrtekst = "{$rangvordering}%"; }
if ($health > 50) { $hltekst = "{$health}%"; }
else { $hrtekst = "{$health}%"; }
if ($schiet > 50) { $sltekst = "{$schiet}%"; }
else { $srtekst = "{$schiet}%"; }

if ($data->level == 1) { $level = "Normale gebruiker"; }
elseif ($data->level == 255) { $level = "Admin"; }
elseif ($data->level == 200) { $level = "Hulp Admin"; }
else { $level = Webmaster; } 
if ($data->famrang == 1) { $rank = Member; }
elseif ($data->famrang == 2) { $rank = Recruiter; }
elseif ($data->famrang == 3) { $rank = Consiglieri; }
elseif ($data->famrang == 4) { $rank = Halfdon; }
elseif ($data->famrang == 5) { $rank = Don; }
if ($data->trans == 0) { $trans = "Geen"; }
else { 
$dbres = mysql_query("SELECT * FROM `items` WHERE `type`='trans' AND `nr`='{$data->trans}'");
$trans = mysql_fetch_object($dbres);
$trans = $trans->naam; 
}
$zak = number_format($data->zak, 0, ',' , ','); 
$bank = number_format($data->bank, 0, ',' , ','); 
$huwelijk = (!$data->huwelijk) ? "<i>Niet getrouwd</i>" : "$data->login & $data->huwelijk";
print <<<ENDHTML
<tr><td align='center'>
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr> 
        <td class=subTitle colspan=5><b>Status</b></td></tr><tr><td>&nbsp;&nbsp;</td></tr><td class=mainTxt><table width=100%>
            <tr> 
              <td class=subTitle align="center" colspan="2"><b>Algemeen</b></td>
              <td width="4%">&nbsp;</td>
              <td class=subTitle align="center" colspan="2"><b>Bezit</b></td>
            </tr>
            <tr> 
              <td width="24%">ID</td>
              <td width="24%">$data->id</td>
              <td>&nbsp;</td>
              <td width="24%">Huis</td>
              <td width="24%">$huis</td>
            </tr>
            <tr> 
              <td>Naam</td>
              <td>$data->login</td>
              <td>&nbsp;</td>
              <td>Kogels</td>
              <td>$data->kogels</td>
            </tr>
            <tr> 
              <td>Level</td>
              <td>$level</td>
              <td>&nbsp;</td>
              <td>Transport</td>
              <td>$trans</td>
            </tr>
            <tr> 
              <td>Rank</td>
              <td>$rang</td>
              <td>&nbsp;</td>
              <td>Bodyguards</td>
              <td>$data->guard</td>
            </tr>
            <tr> 
              <td>Stad</td>
              <td>$data->stad</td>
              <td>&nbsp;</td>
              <td>Geld op zak</td>
              <td>&euro; $zak</td>
            </tr>
            <tr> 
              <td>Respect</td>
              <td>$respect</td>
              <td>&nbsp;</td>
              <td>Geld op bank</td>
              <td>&euro; $bank</td>
            </tr>
            <tr> 
              <td>Testament</td>
              <td>$data->testament</td>
              <td>&nbsp;</td>
              <td align="center"><div align="left">Wapen</div></td>
              <td align="center"><div align="left">$wapon</div></td>
            </tr>
            <tr> 
              <td>Huwelijk</td>
              <td>$huwelijk</td>
              <td>&nbsp;</td>
              <td>Bescherming</td>
              <td>$defence</td>
            </tr>
            <tr> 
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr> 
              <td class=subTitle align="center" colspan="2"><b>Familie</b></td>
              <td>&nbsp;</td>
              <td class=subTitle align="center" colspan="2"><b>Wachttijden</b></td>
            </tr>
            <tr> 
              <td>Familie</td>
              <td>$famillie</td>
              <td>&nbsp;</td>
              <td width="24%">Misdaad</td>
              <td width="24%">$crime</td>
            </tr>
            <tr> 
              <td>Rank</td>
              <td>$rank</td>
              <td>&nbsp;</td>
              <td>Auto</td>
              <td>$ac</td>
            </tr>
            <tr> 
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>Route 66</td>
              <td>$pc</td>
            </tr>
            <tr> 
              <td class=subTitle align="center" colspan="2"><b>Werkervaring</b></td>
              <td>&nbsp;</td>
              <td>Organised Crime</td>
              <td>$bc</td>
            </tr>
            <tr> 
              <td>Uitbraken</td>
              <td>$data->bo</td>
              <td>&nbsp;</td>
              <td>Transport</td>
              <td>$tc</td>
            </tr>
            <tr> 
              <td>Misdaden</td>
              <td>$data->nrofcrime</td>
              <td>&nbsp;</td>
              <td>Moorden</td>
              <td>$kc</td>
            </tr>
            <tr> 
              <td>Auto stelen</td>
              <td>$data->nrofcar</td>
              <td>&nbsp;</td>
              <td>Kogels</td>
              <td>$bt</td>
            </tr>
            <tr> 
              <td>Route 66</td>
              <td>$data->nrofroute</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr> 
              <td>Organised Crime</td>
              <td>$data->nrofoc</td>
              <td>&nbsp;</td>
              <td colspan="2" class=subTitle><div align="center"><b>Statusbalken</b></div></td>
            </tr>
            <tr> 
              <td>Races</td>
              <td>$data->nrofrace</td>
              <td>&nbsp;</td>
              <td>Rangvordering</td>
              <td> <table width="50%" cellspacing="0" cellpadding="0">
                  <tr> 
                    <td width="$rangvordering%" bgcolor="#FFCC00"><div align="center">$vltekst</div></td>
                    <td width="$svordering%" bgcolor="#CC3300"><div align="center">$vrtekst</div></td>
                  </tr>
                </table></td>
            </tr>
            <tr> 
              <td>Moorden</td>
              <td>$data->nrofkill</td>
              <td>&nbsp;</td>
              <td>Health</td>
              <td> <table width="50%" border="0" cellspacing="0" cellpadding="0">
                  <tr> 
                    <td width="$health%" bgcolor="#009900"><div align="center">$hltekst</div></td>
                    <td width="$shealth%" bgcolor="#CC3300"><div align="center">$hrtekst</div></td>
                  </tr>
                </table></td>
            </tr>
            <tr> 
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>Moordervaring</td>
              <td> <table width="50%" border="0" cellspacing="0" cellpadding="0">
                  <tr> 
                    <td width="$schiet%" bgcolor="#FFFFFF"><div align="center">$sltekst</div></td>
                    <td width="$sschiet%" bgcolor="#CC3300"><div align="center">$srtekst</div></td>
                  </tr>
                </table></td>
            </tr>
            <tr> 
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
          </table>
	</table>
  
ENDHTML;
}

?>

</body>


</html>