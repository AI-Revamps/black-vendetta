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
if (!$_GET['x']) { 
	echo "<table align=center width=100%> 
	<tr> 
    <td class=subTitle><b>Zwarte Markt</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
$kogels = mysql_num_rows(mysql_query("SELECT * FROM `kogels`"));
$ws = mysql_num_rows(mysql_query("SELECT * FROM `ws` WHERE `status`='1'"));
$cars = mysql_num_rows(mysql_query("SELECT * FROM `mgarage`"));
	echo "<a href=mshop.php?x=bullets>Kogels</a>({$kogels})<br><a href=mshop.php?x=ws>Ooggetuigen</a>({$ws})<br><a href=mshop.php?x=cars>Auto's</a>({$cars})";
}
if ($_GET['x'] == bullets) {
	if ($_GET['buy']) {
	echo "<table width=100%><tr> 
    <td class=subTitle><b>Kogels</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
	$s = mysql_fetch_object(mysql_query("SELECT * FROM `kogels` WHERE `id`='{$_GET['buy']}'"));
	if (!$s) { echo "Deze aanbieding bestaat niet."; }
	elseif ($s->prijs > $data->zak) { echo "Je hebt niet genoeg geld op zak."; }
	else {
		mysql_query("UPDATE `users` SET `zak`=`zak`-$s->prijs,`kogels`=`kogels`+$s->aantal WHERE `login`='{$data->login}'");
		mysql_query("UPDATE `users` SET `zak`=`zak`+$s->prijs WHERE `login`='{$s->login}'");
		mysql_query("INSERT INTO `messages`(`from`,`to`,`subject`,`message`,`time`) values('Notificatie','$s->login','Kogels verkocht','Je hebt je kogels verkocht voor &euro;$s->prijs.',NOW())");
		mysql_query("DELETE FROM `kogels` WHERE `id`='$s->id'");
		echo "Je hebt $s->aantal kogels gekocht voor &euro;$s->prijs.";
	}
}
	elseif ($_GET['page'] == sell) {
		$selltime = (time() + 21600);
		echo "<table width=100%><tr> 
    <td class=subTitle><b>Kogels</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
		if ($_POST['submit'] && preg_match('/^[0-9]+$/',$_POST['aantal']) && preg_match('/^[0-9]+$/',$_POST['prijs'])) {
			$aantal = $_POST['aantal'];
			$mprijs = (2000 * $aantal);
			if ($data->kogels < $_POST['aantal']) { echo "Je hebt niet zoveel kogels."; }
			elseif ($_POST['aantal'] < 100) { echo "Het minimale aantal kogels dat je kan verkopen is 100."; }
			elseif ($_POST['prijs'] < 25000) { echo "De minimum prijs van kogels is &euro;25000"; }
			elseif ($_POST['prijs'] > $mprijs) { echo "Je mag kogels voor maximaal &euro;2000 per stuk verkopen."; }
			else {
				mysql_query("INSERT INTO `kogels`(`login`,`aantal`,`prijs`,`time`) values('$data->login','{$_POST['aantal']}','{$_POST['prijs']}',FROM_UNIXTIME($selltime))");
				mysql_query("UPDATE `users` SET `kogels`=`kogels`-{$_POST['aantal']} WHERE `login`='{$data->login}'");
				echo "Je hebt {$_POST['aantal']} kogels te koop gezet. Klik <a href=mshop.php?x=bullets>hier</a> om terug te gaan naar de kogels verkoop lijst.";
			}
		}
		else {
			echo "<form method='POST'><input type=text name=aantal maxlength=5>&nbsp;&nbsp;Aantal kogels<br>
			<input type=text name=prijs maxlength=7>&nbsp;&nbsp;Prijs<br><br>
			<input type=submit name=submit value='Zet te koop'></form></td></tr></table>";
		}
	}
	else {	echo "<table width=100%><tr> 
    <td class=subTitle><b>Kogels</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
	echo "<a href=mshop.php?x=bullets&page=sell>Verkoop kogels</a>";
	echo "<table width=100%><tr>
	<td align=center><b>Aantal</b></td>
	<td align=center><b>Deadline</b></td>
	<td align=center><b>Prijs</b></td>
	<td align=center><b>Koop</b></td>
	</tr>";
	$query = mysql_query("SELECT *,DATE_FORMAT(`time`,'%H:%i:%s') AS `time` FROM `kogels` ORDER BY `time` ASC");
	while($s = mysql_fetch_object($query)) {
		echo "<tr align=center>
		<td>$s->aantal</td>
		<td>$s->time</td>
		<td>&euro;$s->prijs</td>
		<td><a href=mshop.php?x=bullets&buy=$s->id>[X]</a></td>
		</tr>";
		}
	}
}
if ($_GET['x'] == cars) {
	if ($_GET['buy']) {
	echo "<table width=100%><tr> 
    <td class=subTitle><b>Auto's</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
	$s = mysql_fetch_object(mysql_query("SELECT * FROM `mgarage` WHERE `id`='{$_GET['buy']}'"));
	if (!$s) { echo "Deze aanbieding bestaat niet."; }
	elseif ($s->prijs > $data->zak) { echo "Je hebt niet genoeg geld op zak."; }
	else {
		mysql_query("UPDATE `users` SET `zak`=`zak`-$s->prijs WHERE `login`='{$data->login}'");
		mysql_query("UPDATE `users` SET `zak`=`zak`+$s->prijs WHERE `login`='{$s->login}'");
		mysql_query("INSERT INTO `garage`(`id`,`login`,`naam`,`waarde`,`damage`,`stad`) values('{$s->id}','$data->login','$s->naam','$s->waarde','$s->damage','$s->stad')");
		mysql_query("INSERT INTO `messages`(`from`,`to`,`subject`,`message`,`time`) values('Notificatie','$s->login','Wagen verkocht','Je hebt je wagen verkocht voor &euro;$s->prijs.',NOW())");
		mysql_query("DELETE FROM `mgarage` WHERE `id`='$s->id'");
		$car = mysql_query("SELECT * FROM `cars` WHERE `naam`='{$s->naam}'");
		$auto = mysql_fetch_object($car);
		echo "Je hebt een $auto->auto gekocht met een waarde van &euro; $s->waarde en met $s->damage % schade.";
	}
}
	elseif ($_GET['page'] == sell) {
		$selltime = (time() + 21600);
		echo "<table width=100%><tr> 
    <td class=subTitle><b>Auto's</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
		if ($_POST['submit'] && preg_match('/^[0-9]+$/',$_POST['prijs'])) {
			$auto = mysql_fetch_object(mysql_query("SELECT * FROM `garage` WHERE `id`='{$_POST['car']}' AND `login`='{$data->login}'"));
			if (!$auto) { echo "Deze wagen is niet van jou."; exit; }
			elseif ($auto->damage > 90) { echo "Deze wagen is te hard beschadigd."; exit; }
			elseif ($_POST['prijs'] < 500) { echo "De minimum prijs een wagen is &euro;500."; }
			else {
			mysql_query("INSERT INTO `mgarage`(`id`,`login`,`naam`,`waarde`,`damage`,`stad`,`time`,`prijs`) values('{$_POST['car']}','{$auto->login}','{$auto->naam}','{$auto->waarde}','{$auto->damage}','{$auto->stad}',FROM_UNIXTIME($selltime),'{$_POST['prijs']}')")or die (mysql_error());
			mysql_query("DELETE FROM `garage` WHERE `login`='$data->login' AND `id`='{$_POST['car']}'")or die (mysql_error());
			echo "Je hebt deze wagen te koop gezet. Klik <a href=mshop.php?x=cars>hier</a> om terug te gaan naar de auto verkoop lijst.";
			}
		}
		else {
			echo "<form method='POST'><input type=text name=car maxlength=5>&nbsp;&nbsp;Wagen<br>
			<input type=text name=prijs maxlength=7>&nbsp;&nbsp;Prijs<br><br>
			<input type=submit name=submit value='Zet te koop'></form></td></tr></table>";
		}
	}
	else {	echo "<table width=100%><tr> 
    <td class=subTitle><b>Auto's</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
	echo "<a href=mshop.php?x=cars&page=sell>Verkoop een auto</a>";
	echo "<table width=100%><tr>
	<td align=center><b>Type</b></td>
	<td align=center><b>Schade</b></td>
	<td align=center><b>Deadline</b></td>
	<td align=center><b>Prijs</b></td>
	<td align=center><b>Koop</b></td>
	</tr>";
	$query = mysql_query("SELECT *,DATE_FORMAT(`time`,'%H:%i:%s') AS `time` FROM `mgarage` ORDER BY `time` ASC");
	while($s = mysql_fetch_object($query)) {
		$car = mysql_query("SELECT * FROM `cars` WHERE `naam`='{$s->naam}'");
		$auto = mysql_fetch_object($car);
		echo "<tr align=center>
		<td>$auto->auto</td>
		<td>$s->damage</td>
		<td>$s->time</td>
		<td>&euro;$s->prijs</td>
		<td><a href=mshop.php?x=cars&buy=$s->id>[X]</a></td>
		</tr>";
		}
	}
}
elseif ($_GET['x'] == ws) {
	if ($_GET['buy']) {
	echo "<table width=100%><tr> 
    <td class=subTitle><b>Ooggetuigen</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
	$s = mysql_fetch_object(mysql_query("SELECT * FROM `ws` WHERE `id`='{$_GET['buy']}' AND `status`='1'"));
	if (!$s) { echo "Deze ooggetuige is niet te koop of is al verkocht."; }
	elseif ($s->prijs > $data->zak) { echo "Je hebt niet genoeg geld op zak."; }
	else {
		mysql_query("UPDATE `users` SET `zak`=`zak`-$s->prijs WHERE `login`='{$data->login}'");
		mysql_query("UPDATE `users` SET `zak`=`zak`+$s->prijs WHERE `login`='{$s->login}'");
		mysql_query("INSERT INTO `messages`(`from`,`to`,`subject`,`message`,`time`) values('Notificatie','$s->login','Ooggetuige verkocht','Je ooggetuige van de moord op $s->victim is verkocht voor &euro;$s->prijs.',NOW())");
		mysql_query("INSERT INTO `messages`(`from`,`to`,`subject`,`message`,`time`) values('Notificatie','$data->login','Ooggetuige','De moordenaar van $s->victim is $s->suspect',NOW())");
		mysql_query("DELETE FROM `ws` WHERE `id`='$s->id'");
		echo "Je hebt deze ooggetuige verklaring gekocht.";
	}
}
	elseif ($_GET['page'] == sell) {
echo "<table width=100%><tr> 
    <td class=subTitle><b>Ooggetuigen</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
		if ($_POST['submit'] && preg_match('/^[0-9]+$/',$_POST['prijs'])) {
			$s = mysql_fetch_object(mysql_query("SELECT *,DATE_FORMAT(`time`,'%H:%i') AS `time` FROM `ws` WHERE `id`='{$_POST['id']}' AND `status`='0'"));
			if (!$s) { echo "Er is geen ooggetuige met dit ID nummer."; }
			elseif ($s->status == 1) { echo "Deze ooggetuige verklaring is al te koop."; }
			elseif ($_POST['prijs'] > 10000000) { echo "De maximum prijs van een ooggetuige verklaring is &euro;10.000.000."; }
			elseif ($_POST['prijs'] < 100) { echo "De minimum prijs van een ooggetuige verklaring is &#164;100."; }
			else {
				$prijs = $_POST['prijs'];
				mysql_query("UPDATE `ws` SET `login`='$data->login',`status`='1',`prijs`='$prijs' WHERE `id`='{$_POST['id']}'");
				echo "Je hebt deze ooggetuige verklaring te koop gezet voor &euro;{$_POST['prijs']}. Deze ooggetuige verklaring is te koop tot $s->time. Klik <a href=mshop.php?x=ws>hier</a> om terug te gaan naar de ooggetuigen lijst.";
			}
		}
		else {
			echo "<form method='POST'><input type=text name=id>&nbsp;&nbsp;ID van de ooggetuige<br>
			<input type=text name=prijs maxlength=7>&nbsp;&nbsp;Prijs<br><br>
			<input type=submit name=submit value=Verkoop></form></td></tr></table>";
		}
	}
	else {
		echo "<table width=100%><tr> 
    <td class=subTitle><b>Ooggetuigen</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>
		<a href=mshop.php?x=ws&page=sell>Verkoop ooggetuige</a>";
		echo "<table width=100%><tr>
		<td align=center><b>Moord op</b></td>
		<td align=center><b>Deadline</b></td>
		<td align=center><b>Prijs</b></td>
		<td align=center><b>Koop</b></td>
		</tr>";
		$query = mysql_query("SELECT *,DATE_FORMAT(`time`,'%H:%i:%s') AS `time` FROM `ws` WHERE `status`='1' ORDER BY `time` ASC");
		while($s = mysql_fetch_object($query)) {
			echo "<tr align=center>
			<td><a href=user.php?x=$s->victim>$s->victim</a></td>
			<td>$s->time</td>
			<td>&euro;$s->prijs</td>
			<td><a href=mshop.php?x=ws&buy=$s->id>[X]</a></td>
			</tr>";
		}
	}
}
?> 
</table>