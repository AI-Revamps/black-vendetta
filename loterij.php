<?php
  include("config.php");
  $dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");
  $data	= mysql_fetch_object($dbres);  
  if(! check_login()) {
    header("Location: login.php");
    exit;
  }
if ($jisin == 1) { header("Location: jisin.php"); }
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
    <td class="subTitle"><b>Loterij</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
	<?php
	$prijs = 10000; //prijs per lot
	$max = 100; //maximaal aantal loten per persoon
	
	if(isset($_POST['submit']) || isset($_POST['nroflot'])){
	  $sql = mysql_query("SELECT * FROM `loterij` WHERE `login`='{$data->login}'");
	  $urnroflot = mysql_num_rows($sql);
	  if(!preg_match('/^[0-9]+$/',$_POST['nroflot'])){echo"Ongeldig aantal.";}
	  elseif(($urnroflot + $_POST['nroflot']) > 100){echo"Je mag maximaal 100 loten hebben. Je hebt er reeds $urnroflot.";}
	  elseif(($_POST['nroflot']*$prijs) > $data->zak){echo"Je hebt niet genoeg geld op zak.";}
	  else{
	    $kosten = $prijs*$_POST['nroflot'];
		$i = 0;
		while($i < $_POST['nroflot']){
	      mysql_query("INSERT INTO `loterij`(`login`) values('{$data->login}')"); 
		  $i++;
		}
		mysql_query("UPDATE `users` SET `zak`=`zak`-$kosten WHERE `login`='{$data->login}'");
		echo "Je hebt {$_POST['nroflot']} loten gekocht voor &euro;$kosten.";
	  }
	  exit;
	}
	
	$sql = mysql_query("SELECT * FROM `loterij`");
    $lot = mysql_fetch_object($sql); 
	$nroflot = mysql_num_rows($sql);
	$jackpot = $nroflot*10000;
	$jackpot = number_format($jackpot, 0, ',' , ',');
	
	$sql = mysql_query("SELECT UNIX_TIMESTAMP(`time`) AS `time` FROM `cron` WHERE `name`='loterij'");
    $cron = mysql_fetch_object($sql); 
	$cront = date('H:i:s d/m/Y', ($cron->time));
	
	$sql = mysql_query("SELECT * FROM `loterij` WHERE `login`='{$data->login}'");
    $urlot = mysql_fetch_object($sql); 
	$urnroflot = mysql_num_rows($sql);
	$urtxt = "Je hebt ".$urnroflot." lot(en).";
	$nroflotforsale = floor($data->zak/$prijs);
	if($nroflotforsale >= 100){$nroflotforsale = 100;}
	?>
    Loterij trekking voor deze loterij is: <?php echo $cront; ?><br>
    Jackpot: &euro;<?php echo $jackpot; ?><br>
    Totaal aantal gekochte loten: <?php echo $nroflot; ?> <br>
 <br>
<br>
    Prijzen<br>
<br>
    De Jackpot<br>
	&euro;1.000.000<br>
	&euro;500.000<br>
	&euro;250.000<br>
	Mercedes W124 Avus Streamling (0% Schade) <br>
	3.000 kogels <br>
	2.000 kogels <br>
	1.000 kogels <br>
	500 kogels <br>
<br>
<br>
	<?php echo $urtxt; ?><br>
 <br>
	Je hebt &euro;<?php echo $data->zak; ?> op zak.<br>
	Je kunt met je geld op zak <?php echo $nroflotforsale; ?> loten kopen.<br>
<br>
<br>
</td>
</tr>
<tr><td>&nbsp;</td></tr>
<tr> 
    <td class="subTitle"><b>Loten kopen</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
	<form method="post">
	Aantal loten: <input name="nroflot" type="text" value="0" maxlength="3"><br>
	<br>
	<input name="submit" type="submit" value="Koop">
	</form>
	</td>
 </tr>
</table>
</body>
</html>