<?php
  include("config.php");
$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");  
$data    = mysql_fetch_object($dbres);
  if(! check_login()) {
    header('Location: login.php');
    exit;
  }
if ($jisin == 1) { header('Location: jisin.php'); }
?> 
<html>
<head>
<title>Vendetta</title>
<link rel="stylesheet" type="text/css" href="style.css">
<meta name="keywords" content="Vendetta,Crimegame,crimegame,vendetta">
<meta name="language" content="english">
<META name="description" lang="nl" content="Vendetta crimegame met pit.">
</head>
<?PHP 
if (isset($_GET['watch'])) { 
	echo "<table align=center width=100%> 
	<tr> 
    <td class=subTitle><b>Premielijst</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
echo "<a href=\"hitlist.php\">Zet een prijs op iemand zijn hoofd</a><br><br>"; 
}
if($_SERVER['REQUEST_METHOD']=='POST') {
	echo "<table align=center width=100%> 
	<tr> 
    <td class=subTitle><b>Premielijst</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
$naam = $_POST['naam'];
$prijs = $_POST['prijs'];
$pr = mysql_query("SELECT * FROM `hitlist` WHERE `login`='{$naam}'");
$pre = mysql_fetch_object($pr);
$us = mysql_query("SELECT * FROM `users` WHERE `login`='{$naam}'");
$user = mysql_fetch_object($us);
if ($user->xp < 10) { $mpr = "100000"; }
elseif ($user->xp < 20) { $mpr = "100000"; }
elseif ($user->xp < 50) { $mpr = "150000"; }
elseif ($user->xp < 150) { $mpr = "250000"; }
elseif ($user->xp < 500) { $mpr = "500000"; }
elseif ($user->xp < 1000) { $mpr = "1000000"; }
elseif ($user->xp < 2000) { $mpr = "2000000"; }
elseif ($user->xp < 3000) { $mpr = "3000000"; }
elseif ($user->xp < 4500) { $mpr = "3000000"; }
elseif ($user->xp < 6000) { $mpr = "3000000"; }
elseif ($user->xp < 8000) { $mpr = "4000000"; }
elseif ($user->xp < 11000) { $mpr = "4000000"; }
elseif ($user->xp < 15000) { $mpr = "5000000"; }
elseif ($user->xp < 20000) { $mpr = "75000000"; }
elseif ($user->xp >= 20000) { $mpr = "10000000"; }
if (!$user->login) { echo "Deze persoon bestaat niet."; }
elseif ($user->status == dood) { echo "Deze persoon is al dood."; }
elseif ($pre->login) { echo "$user->login Deze persoon staat al op de premielijst."; }
elseif ($_POST['prijs'] < $mpr) { echo "De minimale prijs voor iemand met deze rang is &euro;$mpr."; }
elseif ($data->zak < $prijs) { echo "Je hebt niet genoeg geld op zak."; }
else {
mysql_query("INSERT INTO `hitlist`(`login`,`prijs`,`suspect`) VALUES('{$user->login}','{$prijs}','{$data->login}')");
         mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$user->login}','Premielijst','Je bent op de premielijst gezet.')");
mysql_query("UPDATE `users` SET `zak`=`zak`-$prijs WHERE `login`='{$data->login}'");
echo "Deze persoon staat nu op de premielijst.";
}
echo "</td></tr></table>";
}
elseif(isset($_GET['x'])) {
	echo "<table align=center width=100%> 
	<tr> 
    <td class=subTitle><b>Premielijst</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
$victim = mysql_query("SELECT * FROM `hitlist` WHERE `login`='{$_GET['x']}'");
$vic = mysql_fetch_object($victim);
$prijs = ($vic->prijs*2);
if (!$vic->login) {
echo "Deze persoon staat niet op de premielijst.";
}
elseif ($data->zak < $prijs) {
echo "Je hebt niet genoeg geld op zak om deze persoon van de premielijst af te kopen.";
}
else {
   mysql_query("UPDATE `users` SET `zak`=`zak`-$prijs WHERE `login`='{$data->login}'");
echo "Je hebt deze persoon van de premielijst gekocht.";
   mysql_query("DELETE FROM `hitlist` WHERE `login`='{$vic->login}'");
            mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$vic->suspect}','Premielijst',' $vic->login is van de premielijst gekocht.')"); 
}
echo "</td></tr></table>";
}
elseif (isset($_GET['watch'])) {
	echo "<table align=center width=100%>";
echo "<td align=center><b>Login</b></td> 
      <td  align=center><b>Geld</b></td> 
	  <td  align=center><b>Koop Af</b></td>
      </tr>"; 

$query = "SELECT `login`,`prijs` FROM hitlist ORDER BY `prijs`"; 
$info = mysql_query($query) or die(mysql_error()); 
$count = 0; 
while ($gegeven = mysql_fetch_array($info)) { 
$name = $gegeven["login"]; 
$prijs = $gegeven["prijs"]; 
$count++; 
$afkoop = $prijs*2;
echo "<tr> 
        <td width=20% align=center><a href=\"user.php?x={$name}\">{$name}</a></td> 
                <td width=24% align=center>&euro; {$prijs}</td>
                <td width=24% align=center><a href=\"hitlist.php?x={$name}\">&euro; {$afkoop}</a></td></tr> 
";
}
echo "</table>";
echo  "<META HTTP-EQUIV='refresh' CONTENT='5'; URL='$PHP_SELF'>";
}
else {
	echo "<table align=center width=100%> 
	<tr> 
    <td class=subTitle><b>Premielijst</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
	echo "Zet hier iemand op de premielijst.<BR>Degene die iemand vermoord die op de premielijst staat krijgt het geld.<BR><BR>";
	echo "<form method='post'>
	<b>Naam:</b>&nbsp;&nbsp;<input type='text' name='naam' value='naam'><br>
	<b>Prijs:</b>&nbsp;&nbsp;&nbsp;&nbsp;<input type='text' name='prijs' value='prijs' maxlength=9><br><br>
	<input type='submit' name='submit' value='Zet erop'>
	</form></td></tr></table>";
}
?> 
</table></table>