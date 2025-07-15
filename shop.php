<?php
  include('config.php');
  $dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`safe`) AS `safe`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");
  $data	= mysql_fetch_object($dbres);
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
<table width=100% align=center>
  <tr> 
    <td class="subTitle"><b>Winkel</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
  <?php
if (isset($_POST['safe1'])) { 
if($_POST['safe'] != 60 && $_POST['safe'] != 60 && $_POST['safe'] != 120 && $_POST['safe'] != 360 && $_POST['safe'] != 720 && $_POST['safe'] != 1440 && $_POST['safe'] != 2880){
echo"Er is een error opgetreden. U bent nu &euro; 1.000.000 kwijt.";
exit;
}
$safe = $_POST['safe'];
$safetijd = (time() + ($safe*60));
$prijs = (($safe/60)*500000);
	if ($data->zak < $prijs) { echo "Je hebt niet genoeg geld op zak."; exit; }
	else { mysql_query("UPDATE `users` SET `zak`=`zak`-$prijs,`safe`=FROM_UNIXTIME($safetijd) WHERE `login`='{$data->login}'"); echo "Je bent nu ondergedoken"; exit; }

}
elseif (isset($_POST['huis'])) { 
if ($_POST['wat'] == huis) { 
	if ($data->zak < 850000) { echo "Je hebt niet genoeg geld op zak."; exit; }
	else { mysql_query("UPDATE `users` SET `{$huis}`='1',`zak`=`zak`-850000 WHERE `login`='{$data->login}'"); echo "Je hebt een huis gekocht"; exit; }
}
}
elseif (isset($_POST['guard'])) { 
if ($_POST['wat'] == guard1) { 
	if ($data->zak < 25000) { echo "Je hebt niet genoeg geld op zak."; exit; }
	else { mysql_query("UPDATE `users` SET `guard`=1,`zak`=`zak`-25000 WHERE `login`='{$data->login}'"); echo "Je hebt een lijfwacht gekocht"; exit; }
}
elseif ($_POST['wat'] == guard2) { 
	if ($data->zak < 50000) { echo "Je hebt niet genoeg geld op zak."; exit; }
	else { mysql_query("UPDATE `users` SET `guard`=2,`zak`=`zak`-50000 WHERE `login`='{$data->login}'"); echo "Je hebt een lijfwacht gekocht"; exit; }
}
elseif ($_POST['wat'] == guard3) { 
	if ($data->zak < 100000) { echo "Je hebt niet genoeg geld op zak."; exit; }
	else { mysql_query("UPDATE `users` SET `guard`=3,`zak`=`zak`-100000 WHERE `login`='{$data->login}'"); echo "Je hebt een lijfwacht gekocht"; exit; }
}
elseif ($_POST['wat'] == guard4) { 
	if ($data->zak < 250000) { echo "Je hebt niet genoeg geld op zak."; exit; }
	else { mysql_query("UPDATE `users` SET `guard`=4,`zak`=`zak`-250000 WHERE `login`='{$data->login}'"); echo "Je hebt een lijfwacht gekocht"; exit; }
}
elseif ($_POST['wat'] == guard5) { 
	if ($data->zak < 500000) { echo "Je hebt niet genoeg geld op zak."; exit; }
	else { mysql_query("UPDATE `users` SET `guard`=5,`zak`=`zak`-500000 WHERE `login`='{$data->login}'"); echo "Je hebt een lijfwacht gekocht"; exit; }
}
}
elseif (isset($_POST['trans'])) { 
$dbres = mysql_query("SELECT * FROM `items` WHERE `type`='trans' AND `nr`='{$_POST['wat']}'");
$trans = mysql_fetch_object($dbres);
	if ($data->zak < $trans->aprijs) { echo "Je hebt niet genoeg geld op zak."; exit; }
	else { mysql_query("UPDATE `users` SET `trans`='{$trans->nr}',`zak`=`zak`-$trans->aprijs WHERE `login`='{$data->login}'"); echo "Je hebt een $trans->naam gekocht"; exit; }
}	
elseif (isset($_POST['vtrans'])) { 
$dbres = mysql_query("SELECT * FROM `items` WHERE `type`='trans' AND `nr`='{$_POST['wat']}'");
$trans = mysql_fetch_object($dbres);
	if ($data->trans != $trans->nr) { echo "Je hebt geen $trans->naam."; exit; }
	else { mysql_query("UPDATE `users` SET `trans`=0,`zak`=`zak`+$trans->vprijs WHERE `login`='{$data->login}'"); echo "Je hebt je $trans->naam verkocht"; exit; }
}
elseif (isset($_POST['wapon'])) { 
$dbres = mysql_query("SELECT * FROM `items` WHERE `type`='att' AND `nr`='{$_POST['wat']}'");
$wapen = mysql_fetch_object($dbres);
	if ($data->zak < $wapen->aprijs) { echo "Je hebt niet genoeg geld op zak."; exit; }
	else { mysql_query("UPDATE `users` SET `wapon`='{$wapen->nr}',`zak`=`zak`-$wapen->aprijs WHERE `login`='{$data->login}'"); echo "Je hebt een $wapen->naam gekocht"; exit; }
}	
elseif (isset($_POST['vwapon'])) { 
$dbres = mysql_query("SELECT * FROM `items` WHERE `type`='att' AND `nr`='{$_POST['wat']}'");
$wapen = mysql_fetch_object($dbres);
	if ($data->wapon != $wapen->nr) { echo "Je hebt geen $wapen->naam."; exit; }
	else { mysql_query("UPDATE `users` SET `wapon`=0,`zak`=`zak`+$wapen->vprijs WHERE `login`='{$data->login}'"); echo "Je hebt je $wapen->naam verkocht"; exit; }
}
elseif (isset($_POST['defence'])) { 
$dbres = mysql_query("SELECT * FROM `items` WHERE `type`='def' AND `nr`='{$_POST['wat']}'");
$defence = mysql_fetch_object($dbres);
	if ($data->zak < $defence->aprijs) { echo "Je hebt niet genoeg geld op zak."; exit; }
	else { mysql_query("UPDATE `users` SET `defence`='{$defence->nr}',`zak`=`zak`-$defence->aprijs WHERE `login`='{$data->login}'"); echo "Je hebt een $defence->naam gekocht"; exit; }
}	
elseif (isset($_POST['vdefence'])) { 
$dbres = mysql_query("SELECT * FROM `items` WHERE `type`='def' AND `nr`='{$_POST['wat']}'");
$defence = mysql_fetch_object($dbres);
	if ($data->defence != $defence->nr) { echo "Je hebt geen $defence->naam."; exit; }
	else { mysql_query("UPDATE `users` SET `defence`=0,`zak`=`zak`+$defence->vprijs WHERE `login`='{$data->login}'"); echo "Je hebt je $defence->naam verkocht"; exit; }
}
elseif (isset($_POST['vhuis'])) { 
if ($_POST['wat'] == huis1) { 
	if ($data->{$huis} < 1) { echo "Je hebt geen huis."; ;exit; }
	else { mysql_query("UPDATE `users` SET `{$huis}`='0',`zak`=`zak`+750000 WHERE `login`='{$data->login}'"); echo "Je hebt je huis verkocht"; exit; }
}
}
print "<table><tr><td width=100% align=center><form method='post'>";
print "<tr><td width=100%><b>Safehouse</b></td></tr>"; 
if ($data->safe - time() > 0) { $safe = gmdate('H:i:s',($data->safe - time())); print "<tr><td width=100%>Je zit nog $safe in een safehouse</td></tr>"; }
if ($data->safe - time() < 0) { print "<tr><td width=100%><select name=safe width=150>
                <option value=60>1 uur &euro; 500.000</option>
                <option value=120>2 uur &euro; 1.000.000</option>
				<option value=360>6 uur &euro; 3.000.000</option>
				<option value=720>12 uur &euro; 6.000.000</option>
				<option value=1440>24 uur &euro; 12.000.000</option>
				<option value=2880>48 uur &euro; 24.000.000</option>
				</select>
              Verberg je in een safehouse.<br></td></tr>"; }
if ($data->safe - time() < 0) { print "<tr><td width=100%><br><input type='submit' name='safe1' value='Duik onder'></td></tr>"; }
print "<tr><td width=100%>&nbsp;</td></tr>";
print "<tr><td width=100%><b>Huis</b></td></tr>"; 
if (!$data->{$huis}) { print "<tr><td width=100%><input type='radio' name='wat' value='huis' checked>Koop een Huis &euro;850000<br></td></tr>"; }
if ($data->{$huis} < 1) { print "<tr><td width=100%><br><input type='submit' name='huis' value='Koop'></td></tr>"; }
if ($data->{$huis} > 0) { print "<tr><td width=100%><input type='radio' name='wat' value='huis1' checked>Verkoop je huis voor &euro;750000<br></td></tr>"; }
if ($data->{$huis} > 0) { print "<tr><td width=100%><br><input type='submit' name='vhuis' value='Verkoop'></td></tr>"; }
print "<tr><td width=100%>&nbsp;</td></tr>";
if ($data->guard != 5) { print "<tr><td width=100%><b>Bodyguards</b></td></tr>"; }
if ($data->guard == 0) { print "<tr><td width=100%><input type='radio' name='wat' value='guard1' checked>Koop een Lijfwacht &euro;25000<br></td></tr>"; }
if ($data->guard == 1) { print "<tr><td width=100%><input type='radio' name='wat' value='guard2'>Koop een Lijfwacht &euro;50000<br></td></tr>"; }
if ($data->guard == 2) { print "<tr><td width=100%><input type='radio' name='wat' value='guard3'>Koop een Lijfwacht &euro;100000<br></td></tr>"; }
if ($data->guard == 3) { print "<tr><td width=100%><input type='radio' name='wat' value='guard4'>Koop een Lijfwacht &euro;250000<br></td></tr>"; }
if ($data->guard == 4) { print "<tr><td width=100%><input type='radio' name='wat' value='guard5'>Koop een Lijfwacht &euro;500000<br></td></tr>"; }
if ($data->guard != 5) { print "<tr><td width=100%><br><input type='submit' name='guard' value='Koop'></td></tr>"; }
print "<tr><td width=100%>&nbsp;</td></tr>";
print "<tr><td width=100%><b>Transport</b></td></tr>"; 
if ($data->trans < 1) {
   $dbres = mysql_query("SELECT * FROM `items` WHERE `type`='trans'  ORDER BY `nr`");
   while($trans = mysql_fetch_object($dbres)) {
   print "<tr><td width=100%><input type='radio' name='wat' value='$trans->nr'>Koop een $trans->naam voor &euro;{$trans->aprijs}<br></td></tr>";  
   }
   print "<tr><td width=100%><br><input type='submit' name='trans' value='Koop'></td></tr>"; 
}
else {
$dbres = mysql_query("SELECT * FROM `items` WHERE `type`='trans' AND `nr`='{$data->trans}'");
$trans = mysql_fetch_object($dbres);
print "<tr><td width=100%><input type='radio' name='wat' value='$data->trans' checked>Verkoop je $trans->naam voor &euro;{$trans->vprijs}<br></td></tr>
<tr><td width=100%><br><input type='submit' name='vtrans' value='Verkoop'></td></tr>" ;
}
print "<tr><td width=100%>&nbsp;</td></tr>";
print "<tr><td width=100%><b>Wapens</b></td></tr>"; 
if ($data->wapon < 1) {
   $dbres = mysql_query("SELECT * FROM `items` WHERE `type`='att' ORDER BY `nr`");
   while($wapen = mysql_fetch_object($dbres)) {
   print "<tr><td width=100%><input type='radio' name='wat' value='$wapen->nr'>Koop een $wapen->naam voor &euro;{$wapen->aprijs}<br></td></tr>";  
   }
   print "<tr><td width=100%><br><input type='submit' name='wapon' value='Koop'></td></tr>"; 
}
else {
$dbres = mysql_query("SELECT * FROM `items` WHERE `type`='att' AND `nr`='{$data->wapon}'");
$wapen = mysql_fetch_object($dbres);
print "<tr><td width=100%><input type='radio' name='wat' value='$data->wapon' checked>Verkoop je $wapen->naam voor &euro;{$wapen->vprijs}<br></td></tr>
<tr><td width=100%><br><input type='submit' name='vwapon' value='Verkoop'></td></tr>" ;
}
print "<tr><td width=100%>&nbsp;</td></tr>";
print "<tr><td width=100%><b>Bescherming</b></td></tr>"; 
if ($data->defence < 1) {
   $dbres = mysql_query("SELECT * FROM `items` WHERE `type`='def' AND `nr`!='0' ORDER BY `nr`");
   while($defence = mysql_fetch_object($dbres)) {
   print "<tr><td width=100%><input type='radio' name='wat' value='$defence->nr'>Koop een $defence->naam voor &euro;{$defence->aprijs}<br></td></tr>";  
   }
   print "<tr><td width=100%><br><input type='submit' name='defence' value='Koop'></td></tr>"; 
}
else {
$dbres = mysql_query("SELECT * FROM `items` WHERE `type`='def' AND `nr`='{$data->defence}'");
$defence = mysql_fetch_object($dbres);
print "<tr><td width=100%><input type='radio' name='wat' value='$data->defence' checked>Verkoop je $defence->naam voor &euro;{$defence->vprijs}<br></td></tr>
<tr><td width=100%><br><input type='submit' name='vdefence' value='Verkoop'></td></tr>" ;
}
print "</table></td></tr></form>";
?>
</table></table>