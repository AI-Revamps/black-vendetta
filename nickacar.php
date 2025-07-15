<?php /* ------------------------- */
include("config.php");
$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");
$data	= mysql_fetch_object($dbres);
$car = mysql_query("SELECT * FROM `cars` ORDER BY rand() LIMIT 0,1");
$garage = mysql_fetch_object($car);
  if(! check_login()) {
    header('Location: login.php');
    exit;
  }
if ($jisin == 1) { header('Location: jisin.php'); }
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
<?php
echo " 
<table align=center width=100%> 
   <tr> 
    <td class=subTitle><b>Steel een wagen</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>
    <table align=center width=100%>"; 
		$gkan = round($data->xp * 0.1);
		if ($gkan > 30) { $gkan = 30; }
		$gkans = rand(1, (100 / $gkan));
$ka = floor(0.05 * $data->xp);
if ($ka > 30) { $ka = rand(15,40); }
$ka2 = rand(0,floor(0.05 * $data->xp+5));
if ($ka2 > 30) { $ka2 = rand(15,40); }
$ka3 = rand(0,floor(0.05 * $data->xp+5));
if ($ka3 > 30) { $ka3 = rand(15,40); }
$kan = 100 / $ka;
if ($ka == 30) { $kans = rand(1,3); }
else { $kans = rand(0, $kan); }
if ($data->level > 254) {$kans = rand (1,2);}
$percent         = rand(0, 100);
$msgnum = rand(0,13);
$waarde = round($garage->waarde - (($garage->waarde / 75) * $percent));
if ($waarde < 500) { $waarde = 500; }
   $message          = Array(
"De benzine was op.",
"Je kreeg de deur niet open.",
"Toen je de deur probeerde te openen vernielde je de wagen zo hard, dat hij niets meer waard was.",
"Je kon de wagen stelen, maar je was iets te enthousiast. Je reed de gevangenis muur om. Je bent gearresteerd.",
"Een agent kwam kijken toen je aan het slot was aan het prutsen. Hij wou je sleutel in beslag nemen omdat je te dronken was, dus liep je weg toen hij zich omdraaide.",
"De wagen was te goed beveiligd.",
"Net toen je de deur van de wagen wou opendoen zag je een stel vrijen op de achterbank. Gelukkig was je sneller weg dan dat ze zich konden aankleden om achter je aan te komen.",
"De wagen die je wou stelen werd net voor je kon beginnen weg getakelt.",
"Het alarm ging af, je kon nog maar net ontsnappen.",
"De politie kwam achter je aan, maar je kon nog ontsnappen.",
"Je kreeg de deur niet open en dus sloeg je het raam in. Je sneed jezelf in je duim en besloot dus maar om de wagen achter te laten.",
"Je moest dringend naar de wc en koos de eerste en beste auto uit. Je had niet veel zin meer om de wagen te stelen en dus heb je hem maar achter gelaten.",
"Toen je net wou wegrijden kwam je er achter dat de banden lek waren.",
"De eigenaar kwam net aanlopen.");

$msg                    = "{$message[$msgnum]}";
$smsgnum = rand(0,9);
$smessage = Array(
"Je kreeg na lang te sleutelen het protier open.",
"De benzine was op, maar toen vond je iets verder een jerrycan die nog halfvol was.",
"Een agent kwam kijken toen je aan het slot was aan het prutsen. Hij wou je sleutel in beslag nemen omdat je te dronken was, maar je sloeg hem neer en reed snel weg.",
"De wagen was goed beveiligd. Van woede sloeg je tegen de deur, ze ging open...",
"Net toen je de deur van de wagen wou opendoen zag je een stel vrijen op de achterbank. Je trok ze uit de wagen en reed snel weg.",
"Ze wouden de wagen die je wou stelen weg takelen, dus heb je de wagen ervoor maar gestolen.",
"Het alarm ging af. Een man kwam je helpen, hij zette het alarm af en wuifde je uit toen je wegreed.",
"Je kreeg de deur niet open en dus sloeg je het raam in.",
"Je moest dringend naar de wc en koos de eerste en beste auto uit. Je had gelukkig op de klink van de passagierskant gepist.",
"De eigenaar kwam net aanlopen. Je sloeg hem op zijn gezicht en reed weg.");
$smsg  = "{$smessage[$smsgnum]}";
$acrime = gmdate('i:s',($data->ac - time()));
if($data->ac - time() > 0) { echo "Je bent nog $acrime aan het uitrusten."; echo "</table></table>"; exit; }
if (isset($_POST['submit'])) {
if (!$_POST['verify']){echo"Je moet een code opgeven.";}
elseif($_POST['verify'] != $_SESSION['verify']){echo"De code die je hebt ingevoerd komt niet overeen met het plaatje.";}
	elseif ($_POST['waar'] == onbekend) {
		if($kans != 1) {
			echo "$msg";
			mysql_query("UPDATE `users` SET `xp`=`xp`+2,`ac`=FROM_UNIXTIME($autotijd),`nrofcar`=`nrofcar`+1 WHERE `login`='{$data->login}'");
			if ($msgnum == 3) {
				mysql_query("INSERT INTO `jail`(`login`,`boete`,`stad`,`famillie`,`time`) VALUES('$data->login','$boete','$data->stad','{$data->famillie}',FROM_UNIXTIME($jailtime))");
			}
			elseif ($msgnum == 10) {
				mysql_query("UPDATE `users` SET `health`=`health`-1 WHERE `login`='{$data->login}'");
			}
			exit;
		}
		else {
			mysql_query("UPDATE `users` SET `xp`=`xp`+2,`ac`=FROM_UNIXTIME($autotijd),`nrofcar`=`nrofcar`+1 WHERE `login`='{$data->login}'");
			mysql_query("INSERT INTO `garage`(`login`,`naam`,`waarde`,`damage`,`stad`) values('{$data->login}','$garage->naam','$waarde','$percent','$data->stad')");
			$id = mysql_insert_id();
			echo "$smsg <br>Je hebt een $garage->auto gejat met {$percent}% schade en met een waarde &euro;$waarde.
			<br><br><center><img src=\"{$garage->url}\" border=0 alt=\"{$garage->auto}\"><br><br><a href=garage.php?x=$id>Klik hier om de auto te verkopen</a></center></table></td></tr>";
			exit;
		}
	}
	elseif ($_POST['waar'] == gebruiker) {
		$dbres = mysql_query("SELECT * FROM `garage` WHERE `stad`='{$data->stad}' AND `login`!='{$data->login}' AND `safe`='0' ORDER BY rand() LIMIT 0,1");
		$auto = mysql_fetch_object($dbres);
		$dbres = mysql_query("SELECT * FROM `cars` WHERE `naam`='{$auto->naam}'");
		$aut = mysql_fetch_object($dbres);
		if (!$auto->naam) { echo "Er is geen gebruiker met een auto in deze stad."; exit; }
		else {
			if ($kans == 1) {
				echo "Je hebt een $aut->auto uit de garage van $auto->login gejat met {$auto->damage}% schade en met een waarde &euro;$auto->waarde.
				<br><br><center><img src=\"{$aut->url}\" width=500 height=240 border=0 alt=\"{$aut->auto}\"></center></table></td></tr>";
				mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$auto->login}','Autodief','$data->login heeft een $aut->auto met $auto->damage% schade en waarde &euro;$auto->waarde uit je garage gestolen.')");
				mysql_query("UPDATE `users` SET `xp`=`xp`+1,`ac`=FROM_UNIXTIME($autotijd),`nrofcar`=`nrofcar`+1 WHERE `login`='{$data->login}'");
				mysql_query("UPDATE `garage` SET `login`='{$data->login}' WHERE `id`='{$auto->id}'");
				exit;
			}
			else {
				echo "$msg";
				mysql_query("UPDATE `users` SET `xp`=`xp`+1,`ac`=FROM_UNIXTIME($autotijd),`nrofcar`=`nrofcar`+1 WHERE `login`='{$data->login}'");
				if ($msgnum == 3) {
					mysql_query("INSERT INTO `jail`(`login`,`boete`,`stad`,`famillie`,`time`) VALUES('$data->login','{$boete}','{$data->stad}','{$data->famillie}',FROM_UNIXTIME($jailtime))");
				}
				elseif ($msgnum == 10) {
					mysql_query("UPDATE `users` SET `health`=`health`-1 WHERE `login`='{$data->login}'");
				}
				exit;
			}
		}
	}
}
print <<<ENDHTML
<FORM METHOD=post ACTION="">
  <table width="350" border="0" cellpadding="0" cellspacing="0">
    <tr>
      <td width="20"><input type=radio name=waar value=onbekend checked></td>
      <td width="6">&nbsp;</td>
      <td width="326">Steel een wagen op een parking....{$ka}% </td>
    </tr>
	<tr>
      <td><input type=radio name=waar value=onbekend></td>
      <td>&nbsp;</td>
      <td>Steel een wagen bij een huis....{$ka2}%</td>
    </tr>
	<tr>
      <td><input type=radio name=waar value=onbekend></td>
      <td>&nbsp;</td>
      <td>Steel een wagen bij een tankstation...{$ka3}%</td>
    </tr>
	<tr>
      <td><input type=radio name=waar value=gebruiker></td>
      <td>&nbsp;</td>
      <td>Steel een wagen van een andere gebruiker....$gkan%</td>
    </tr>
	<tr><td colspan=3><br>Je code is: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src=img.php></td></tr>
    <tr><td colspan=3>Typ hier de code in:    <input type=text name=verify></td></tr>
    <tr>
      <td colspan=3><input name="submit" type="submit" value="Steel"></td>
    </tr>
  </table>
  </form>
ENDHTML;

?>
</table></table>