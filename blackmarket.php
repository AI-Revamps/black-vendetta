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
<body>
<table align=center width=100%> 
	<tr> 
    <td class=subTitle><b>Zwarte Markt</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>
<?
$kogels = mysql_num_rows(mysql_query("SELECT * FROM `mshop` WHERE `type`='bullet'"))or die(mysql_error());
$ws = mysql_num_rows(mysql_query("SELECT * FROM `mshop` WHERE `type`='ws'"))or die(mysql_error());
$car = mysql_num_rows(mysql_query("SELECT * FROM `mshop` WHERE `type`='car'"))or die(mysql_error());
$object = mysql_num_rows(mysql_query("SELECT * FROM `mshop` WHERE `type`='obj'"))or die(mysql_error());

echo"<table width=100%><tr><td width=50%>Ooggetuigen&nbsp;&nbsp;$ws</td><td>Auto's&nbsp;&nbsp;$car</td></tr><tr><td>Kogels&nbsp;&nbsp;$kogels</td><td>Objecten&nbsp;&nbsp;$object</td></tr></table>";

if($_GET['p'] == ws){
  echo"<table width=100%><tr><td width=50% colspan=5>Ooggetuige verkopen</td>/tr><tr><td colspan=5>&nbsp;</td></tr>";
  if($_GET['sell']){
    if ($_POST['submit'] && preg_match('/^[0-9]+$/',$_POST['prijs'] && preg_match('/^[0-9]+$/',$_POST['time'])) {
	  $s = mysql_fetch_object(mysql_query("SELECT *,DATE_FORMAT(`time`,'%H:%i') AS `time` FROM `ws` WHERE `id`='{$_POST['id']}' AND `status`='0'"))or die(mysql_error());
	  if (!$s) { echo "Er is geen ooggetuige met dit ID nummer."; }
	  elseif ($s->status == 1) { echo "Deze ooggetuige verklaring is al te koop."; }
	  elseif ($_POST['prijs'] > 10000000) { echo "De maximum prijs van een ooggetuige verklaring is &euro;10.000.000."; }
	  elseif ($_POST['prijs'] < 100) { echo "De minimum prijs van een ooggetuige verklaring is &euro;100."; }
	  elseif ($_POST['time'] > 300) { echo "De maximum tijd is 300 minuten."; }
	  elseif ($_POST['time'] < 30) { echo "De minimum tijd is 30 minuten."; }
	  else {
		$prijs = $_POST['prijs'];
		$tijd = time() + ($_POST['time']*60);
		mysql_query("INSERT INTO `mshop`(`door`,`type`,`specs`,`aflooptijd`,`bod`,`specs2`,`specs3`) values('{$data->login}','ws','{$_POST['id']}','{$tijd}','{$_POST['prijs']}','{$s->victim}','{$s->suspect}')")or die(mysql_error());
        mysql_query("UPDATE `ws` SET `status`='1',`prijs`='$prijs' WHERE `id`='{$_POST['id']}'")or die(mysql_error());
		$tijd = date('H:i:s d/m/Y', $tijd);
		echo "Je hebt deze ooggetuige verklaring te koop gezet voor &euro;{$_POST['prijs']}. Deze ooggetuige verklaring is te koop tot $tijd.";
	  }
    }
	else {
	  echo "<form method='POST'><input type=text name=id>&nbsp;&nbsp;ID van de ooggetuige<br>
	  <input type=text name=prijs maxlength=10>&nbsp;&nbsp;Prijs<br>
	  <input type=text name=time maxlength=3>&nbsp;&nbsp;Duur (in minuten)<br><br>
	  input type=submit name=submit value=Verkoop></form></td></tr></table>";
    }
  }
  else{
    echo"<tr><td><b>Moord op</b></td><td><b>Prijs</b></td><td><b>Aflooptijd</b></td><td></td></tr>";
    $ws = mysql_query("SELECT * FROM `mshop` WHERE `type`='ws' ORDER BY aflooptijd ASC")or die(mysql_error());
	while($ws = mysql_fetch_object($ws)){
	  $tijd = date('H:i:s d/m/Y', $ws->tijd);
	  $prijs = number_format($ws->bod, 0, ',' , ',');
	  echo"<tr><td>{$ws->specs2}</td><td>$prijs</td><td>$tijd</td><td></td></tr>";
	}
  }
}
?>
</td>
</tr>
</table>
</body>
</html>