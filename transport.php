<?php
  include("config.php");
  $stmt    = db_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`=?", array($_SESSION['login']));
  $data    = $stmt->get_result()->fetch_object();
  if(! check_login()) {
    header("Location: login.php");
    exit;
  }
if ($jisin == 1) { header("Location: jisin.php"); }
if ($data->trans == 0) { $trans = Geen; }
else { 
  $stmt = db_query("SELECT * FROM `items` WHERE `type`='trans' AND `nr`=?", array($data->trans));
  $tran = $stmt->get_result()->fetch_object();
$trans = $tran->naam;
$ptime = $tran->effect;
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
<?php
 print <<<ENDHTML
<table align=center width=100%>
  <tr> 
    <td class="subTitle"><b>Transport</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
ENDHTML;
$msg = "Je hebt niet genoeg geld op zak.";

$time = time();
$tc = gmdate('i:s', ($data->transport - $time));
$vtime = (time() + round($ptime,0));
$vltime = gmdate('i:s', ($data->transport - time()));
if ($trans == Geen) { echo "Je hebt geen transportmiddel."; exit; }
elseif ($data->transport - time() > 0) { echo "Je kan nog niet reizen, je moet nog $tc wachten..."; exit; }
if (isset($_GET['x'])) {
	if ($_GET['x'] == Brussel) { $prijs = 2000; if ($data->zak < 2000) { echo "$msg"; exit; } }
	elseif ($_GET['x'] == Leuven) { $prijs = 2500; if ($data->zak < 2500) { echo "$msg"; exit; } }
	elseif ($_GET['x'] == Gent) { $prijs = 1500; if ($data->zak < 1500) { echo "$msg"; exit; } }
	elseif ($_GET['x'] == Brugge) { $prijs = 1300;if ($data->zak < 1300) {  echo "$msg"; exit; } }
	elseif ($_GET['x'] == Antwerpen) { $prijs = 2250; if ($data->zak < 2250) { echo "$msg"; exit; } }
	elseif ($_GET['x'] == Hasselt) { $prijs = 1000; if ($data->zak < 1000) { echo "$msg"; exit; } }
        elseif ($_GET['x'] == Amsterdam) { $prijs = 3500; if ($data->zak < 3500) { echo "$msg"; exit; } }
		elseif ($_GET['x'] == Enschede) { $prijs = 3500; if ($data->zak < 4500) { echo "$msg"; exit; } }
		else{echo"Deze stad is onbekend";exit;}
		echo "<BR><center><b>Je bent nu in {$_GET['x']}.</b></center>";
                db_query("UPDATE `users` SET `zak`=`zak`-?,`transport`=FROM_UNIXTIME(?),`stad`=? WHERE `login`=?", array($prijs, $vtime, $_GET['x'], $data->login));

}
else {
print <<<ENDHTML
	<table align=center bordercolorlight="#000000" border="0" bordercolordark="#000000">
	  <tr><td width=100><b>Stad</b></td>    
	  <td align="right"><b>Prijs</b></td></tr>
	  <tr><td width=100><a href="transport.php?x=Brussel">Brussel</a></td><td align="right">&euro;2000</td></tr>
	  <tr><td width=100><a href="transport.php?x=Leuven">Leuven</a></td><td align="right">&euro;2500</td></tr>
	  <tr><td width=100><a href="transport.php?x=Gent">Gent</a></td><td align="right">&euro;1500</td></tr>
	  <tr><td width=100><a href="transport.php?x=Brugge">Brugge</a></td><td align="right">&euro;1300</td></tr>
	  <tr><td width=100><a href="transport.php?x=Antwerpen">Antwerpen<a></td><td align="right">&euro;2250</td></tr>
	  <tr><td width=100><a href="transport.php?x=Hasselt">Hasselt<a></td><td align="right">&euro;1000</td></tr>
          <tr><td width=100><a href="transport.php?x=Amsterdam">Amsterdam<a></td><td align="right">&euro;3500</td></tr>
		            <tr><td width=100><a href="transport.php?x=Enschede">Enschede<a></td><td align="right">&euro;4500</td></tr>
	  </table>
ENDHTML;
}
if($data->rijbewijs = 0){
    print <<<ENDHTML
<html>
<head>
<title>Geen Rijbewijs</title>
<link rel="stylesheet" type="text/css" href="css-v1.css">
</head>
<link rel="stylesheet" type="text/css" href="css-v1.css">
<center><table width=50%>
 <tr><td class="subTitle"><b>Geen rijbewijs</b></td></tr>
<tr><td class="mainTxt"><center>
<tr><td class="mainTxt">
<center>Je hebt geen rijbewijs, dus kan je ook niet reizen met de auto!</td></tr>
 </table>
</body>
</html>
ENDHTML;
    exit;
    }
?></table></table>