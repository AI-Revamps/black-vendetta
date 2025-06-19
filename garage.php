<?php
  include("config.php");
  if(! check_login()) {
    header('Location: login.php');
    exit;
  }
if ($jisin == 1) { header('Location: jisin.php'); }
$data = mysql_fetch_object(mysql_query("SELECT * FROM `users` WHERE `login`='{$_SESSION['login']}'"));
$dbres = mysql_query("SELECT * FROM `famillie` WHERE `name`='{$data->famillie}'");
$famillie = mysql_fetch_object($dbres);
?>
<html>
<head>
<title>Vendetta</title>
<link rel="stylesheet" type="text/css" href="style.css">
<meta name="keywords" content="Vendetta,Crimegame,crimegame,vendetta">
<meta name="language" content="english">
<META name="description" lang="nl" content="Vendetta crimegame met pit.">
</head>
<?php
echo "<table width=100%><tr> 
    <td class=subTitle><b>Garage</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
if(isset($_POST['trans'])) {
$id = $_POST['id'];
$a = mysql_query("SELECT * FROM `garage` WHERE `id`='{$id}' AND `login`='{$data->login}'") or die(mysql_error());
$b = mysql_fetch_object($a);
$c = mysql_num_rows($a);
$prijs = 1000;
if ($data->zak < $prijs) { echo "Je hebt niet genoeg geld om deze auto te verschepen.";exit; }
if ($b->stad != $data->stad) { echo "Deze auto staat niet in de stad waar je momenteel bent."; exit; }
if ($c == 0) { echo "Deze auto bestaat niet of is niet van jou."; exit; }
mysql_query("UPDATE `garage` SET `stad`='{$_POST['stad']}' WHERE `id`='{$id}'") or die(mysql_error()); 
mysql_query("UPDATE `users` SET `zak`=`zak`-$prijs WHERE `login`='{$data->login}'") or die(mysql_error());
echo "Je hebt de auto verscheept voor &euro; $prijs."; exit;
}
$au = mysql_query("SELECT * FROM garage WHERE `login`='{$data->login}'");
$cars = mysql_num_rows($au);
      $dbres				= mysql_query("SELECT * FROM `garage` WHERE `login`='{$data->login}'");
$nummer = mysql_num_rows($dbres);
      while($member = mysql_fetch_object($dbres))
        $money				+= round(($member->waarde));
echo "<b><center>Je garage bevat $cars auto's</b><br><b>De totaal waarde van je garage is &euro;{$money}</b><br><br></center>
";
$dbres = mysql_query("SELECT * FROM `famillie` WHERE `name`='{$data->famillie}'");
$famillie = mysql_fetch_object($dbres);
if (isset($_POST['verkoopall'])) {
      $dbres				= mysql_query("SELECT * FROM `garage` WHERE `login`='{$data->login}' AND `stad`='{$data->stad}'");
$money = 0;
      while($member = mysql_fetch_object($dbres)) {
        $money				+= round(($member->waarde));
}
$dbres				= mysql_query("SELECT * FROM `garage` WHERE `login`='{$data->login}' AND `stad`='{$data->stad}'");
$nummer = mysql_num_rows($dbres);
echo "Je hebt $nummer auto's verkocht voor &euro; {$money}.";
mysql_query("UPDATE `users` SET `zak`=`zak`+$money WHERE `login`='{$data->login}'");
mysql_query("DELETE FROM `garage` WHERE `login`='{$data->login}' AND `stad`='{$data->stad}'");
}
if (isset($_POST['crushall'])) {
$dbres				= mysql_query("SELECT * FROM `garage` WHERE `login`='$data->login' AND `stad`='$data->stad'");
$nummer = mysql_num_rows($dbres);
$kogels = 0;
      while($member = mysql_fetch_object($dbres)) {
        $kogels				+= 15;
if (!$kogels) { $kogels = 0; }
}
echo "Je hebt $nummer auto's gecrusht voor $kogels kogels.";
mysql_query("UPDATE `users` SET `kogels`=`kogels`+$kogels WHERE `login`='{$data->login}'");
mysql_query("UPDATE `famillie` SET `aantal`=`aantal`-$nummer WHERE `name`='{$data->famillie}'");
mysql_query("DELETE FROM `garage` WHERE `login`='{$data->login}' AND `stad`='{$data->stad}'");
}
if(isset($_GET['crush'])) {
$id = $_GET['crush'];
$a = mysql_query("SELECT * FROM `garage` WHERE `id`='{$id}' AND `login`='{$data->login}'");
$b = mysql_fetch_object($a);
$c = mysql_num_rows($a);
$dbres = mysql_query("SELECT * FROM `famillie` WHERE `name`='{$data->famillie}'");
$famillie = mysql_fetch_object($dbres);
$exist = mysql_num_rows($dbres);
if ($exist == 0) { echo "Je hebt geen famillie."; }
elseif ($b->stad != $data->stad) { echo "Deze auto staat niet in de stad waar je momenteel bent."; exit; }
elseif ($famillie->crusher == 0) { echo "Je famillie heeft geen crusher ingehuurd vandaag.";exit; }
elseif ($famillie->aantal < 1) { echo "Het maximum aantal auto's is bereikt."; }
elseif ($c == 0) { echo "Deze auto bestaat niet"; exit; }
else {
  mysql_query("DELETE FROM `garage` WHERE `id`='{$_GET['crush']}' AND `login`='{$data->login}'");
  mysql_query("UPDATE `users` SET `kogels`=`kogels`+15 WHERE `login`='{$data->login}'");
mysql_query("UPDATE `famillie` SET `aantal`=`aantal`-1 WHERE `name`='{$data->famillie}'");
echo "Je auto is gecrusht.";
}
}
if(isset($_GET['safe'])) {
$id = $_GET['safe'];
$a = mysql_query("SELECT * FROM `garage` WHERE `id`='{$id}' AND `login`='{$data->login}'") or die(mysql_error());
$b = mysql_fetch_object($a);
$c = mysql_num_rows($a);
$prijs = 10000;
if ($data->zak < $prijs) { echo "Je hebt niet genoeg geld om deze auto in een safehouse te zetten.";exit; }
if ($c == 0) { echo "Deze auto bestaat niet"; exit; }
else {
  mysql_query("UPDATE `garage` SET `safe`='1' WHERE `id`='{$id}' AND `login`='{$data->login}'") or die(mysql_error());
  mysql_query("UPDATE `users` SET `zak`=`zak`-$prijs WHERE `login`='{$data->login}'") or die(mysql_error());
echo "Je auto is in een safehouse geplaatst. Het koste &euro;10.000.";
}
}
if(isset($_GET['repair'])) {
$id = $_GET['repair'];
$a = mysql_query("SELECT * FROM `garage` WHERE `id`='{$id}' AND `login`='{$data->login}'");
$b = mysql_fetch_object($a);
$c = mysql_num_rows($a);
$car = mysql_query("SELECT * FROM `cars` WHERE `naam`='{$b->naam}'");
$garage = mysql_fetch_object($car);
$value = $garage->waarde;
$prijs = $garage->waarde - $b->waarde;
if ($c == 0) { echo "Deze auto bestaat niet."; exit; }
elseif ($b->stad != $data->stad) { echo "Deze auto staat niet in de stad waar je momenteel bent."; exit; }
elseif ($b->damage == 0){ print"Deze wagen is niet beschadigd.";}
elseif ($prijs > $data->zak) {print"Je hebt niet genoeg geld op zak.";}
else {
  mysql_query("UPDATE `garage` SET `waarde`='$value',`damage`='0' WHERE `id`='{$_GET['repair']}' AND `login`='{$data->login}'");
  mysql_query("UPDATE `users` SET `zak`=`zak`-$prijs WHERE `login`='{$data->login}'");
echo "Je auto is gerepaired. Het koste &euro; $prijs.";
}
}
if(isset($_GET['trans'])) {
$id = $_GET['trans'];
echo "	<form method=post>
		ID: <input type=text name=id value='$id'><br>
		<select name=stad>
		<option value=Brussel>Brussel</option>
		<option value=Leuven>Leuven</option>
		<option value=Gent>Gent</option>
		<option value=Brugge>Brugge</option>
		<option value=Antwerpen>Antwerpen</option>
		<option value=Hasselt>Hasselt</option>
        <option value=Amsterdam>Amsterdam</option>
		</select><br><br><form method=post><input type=submit name=trans value=Transporteer></form>
";exit;
}
if(isset($_GET['x'])) {
$id = $_GET['x'];
$a = mysql_query("SELECT * FROM `garage` WHERE `id`='{$id}' AND `login`='{$data->login}' AND `stad`='{$data->stad}'");
$b = mysql_fetch_object($a);
$c = mysql_num_rows($a);
if ($c == 0) { echo "Deze auto bestaat niet, of is niet in het stad waar je nu bent."; }
else {
  mysql_query("UPDATE `users` SET `zak`=`zak`+$b->waarde WHERE `login`='{$data->login}'");
  mysql_query("DELETE FROM `garage` WHERE `id`='{$id}' AND `login`='{$data->login}'");
echo "Je auto is verkocht."; 
}
}
echo "


    <table align=center width=100%>";
echo "  <tr>
        <td align=center>
        #</td>
        <td align=center>
        <b>Naam</b></td>
        <td align=center>
        <b>Waarde</b></td>
        <td align=center>
        <b>Schade</b></td>
        <td align=center>
        <b>Stad</b></td>
	    <td align=center>
        <b>Opties</b></td>
	    </tr>";

/// Einde uitvoer van query
$query = "SELECT * FROM garage WHERE `login`='{$data->login}' ORDER BY `waarde` DESC";
$info = mysql_query($query) or die(mysql_error());
$count = 0;
while ($gegeven = mysql_fetch_array($info)) {
$auto = mysql_fetch_object(mysql_query("SELECT * FROM `cars` WHERE `naam`='{$gegeven["naam"]}'"));
$naam = $auto->auto;
$waarde = $gegeven["waarde"];
$schade = $gegeven["damage"];
$verkoop = $gegeven["id"];
$stad = $gegeven["stad"];
$id = $gegeven["id"];
$safe = $gegeven["safe"];
$count++;
$stad = strtolower($stad);
$estad = strtolower($data->stad);
/// De uit database gehaalde gegevens weergeven
echo "
 <tr>
 		<td width=5% align=center>{$id}</td>
        <td width=20% align=center>{$naam}</td>
		<td width=20% align=center>&euro;{$waarde}</td>
		<td width=10% align=center>{$schade}%</td>
		<td width=10% align=center>{$stad}</td>
		<td width=20% align=center>";
if ($estad == $stad) { echo "<a href=\"garage.php?x={$verkoop}\"><img alt=verkoop border=0 src=http://members.lycos.nl/js6287/images/sell.gif width=32 height=32></a>"; }
if ($estad != $stad) { echo "<img alt=verkoop src=http://members.lycos.nl/js6287/images/sell2.gif width=32 height=32>"; }
if ($estad == $stad) { echo "<a href=\"garage.php?repair={$verkoop}\"><img alt=repair border=0 src=http://members.lycos.nl/js6287/images/repair.gif width=32 height=32></a>"; }
if ($estad != $stad) { echo "<img alt=repair src=http://members.lycos.nl/js6287/images/repair2.gif width=32 height=32>"; }
if ($famillie->crusher == 1 && $famillie->aantal > 0 && $estad == $stad) { echo "<a href=\"garage.php?crush={$verkoop}\"><img alt=crush border=0 src=http://members.lycos.nl/js6287/images/crush.gif width=32 height=32></a>"; }
else { echo "<img alt=crush src=http://members.lycos.nl/js6287/images/crush2.gif width=32 height=32>"; }
if ($estad == $stad) { echo "<a href=\"garage.php?trans={$verkoop}\"><img alt=transporteer border=0 src=http://members.lycos.nl/js6287/images/trans.gif width=32 height=32></a>"; }
if ($estad != $stad) { echo "<img alt=transporteer src=http://members.lycos.nl/js6287/images/trans2.gif width=32 height=32>"; }
if ($safe == 0) { echo "<a href=\"garage.php?safe={$verkoop}\"><img alt=safehouse border=0 src=http://members.lycos.nl/js6287/images/safe.gif width=32 height=32></a>"; }
if ($safe != 0) { echo "<img alt=safehouse src=http://members.lycos.nl/js6287/images/safe2.gif width=32 height=32>"; }

}

echo "
    </table>
    </body>
    </html>";

echo"<form method='post'>
	<input type='submit' name='verkoopall' value='Verkoop Alles'></form>";
if ($famillie->crusher == 1 && $famillie->aantal > 0) {
echo "<form method='post'>
<input type='submit' name='crushall' value='Crush alles'></form>";
}
?>
</td></tr>
</table><br><br></table>
