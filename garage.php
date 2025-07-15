<?php
  include("config.php");
  if(! check_login()) {
    header('Location: login.php');
    exit;
  }
if ($jisin == 1) { header('Location: jisin.php'); }
$stmt = db_query("SELECT * FROM `users` WHERE `login`=?", array($_SESSION['login']));
$data = $stmt->get_result()->fetch_object();
$stmt = db_query("SELECT * FROM `famillie` WHERE `name`=?", array($data->famillie));
$famillie = $stmt->get_result()->fetch_object();
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
echo "<table width=100%><tr> 
    <td class=subTitle><b>Garage</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
$id = $_POST['id'];
if(isset($_POST['trans'])) {
    $stmt = db_query("SELECT * FROM `garage` WHERE `id`=? AND `login`=?", array($id, $data->login));
    $res  = $stmt->get_result();
    $b    = $res->fetch_object();
    $c    = $res->num_rows;
$prijs = 1000;
if ($data->zak < $prijs) { echo "Je hebt niet genoeg geld om deze auto te verschepen.";exit; }
if ($b->stad != $data->stad) { echo "Deze auto staat niet in de stad waar je momenteel bent."; exit; }
if ($c == 0) { echo "Deze auto bestaat niet of is niet van jou."; exit; }
db_query("UPDATE `garage` SET `stad`=? WHERE `id`=?", array($_POST['stad'], $id));
db_query("UPDATE `users` SET `zak`=`zak`-? WHERE `login`=?", array($prijs, $data->login));
echo "Je hebt de auto verscheept voor &euro; $prijs."; exit;
}
$stmt = db_query("SELECT * FROM garage WHERE `login`=?", array($data->login));
$au = $stmt->get_result();
$cars = $au->num_rows;
$stmt = db_query("SELECT * FROM `garage` WHERE `login`=?", array($data->login));
$dbres = $stmt->get_result();
$nummer = $dbres->num_rows;
while($member = $dbres->fetch_object())
        $money				+= round(($member->waarde));
echo "<b><center>Je garage bevat $cars auto's</b><br><b>De totaal waarde van je garage is &euro;{$money}</b><br><br></center>
";
$stmt = db_query("SELECT * FROM `famillie` WHERE `name`=?", array($data->famillie));
$famillie = $stmt->get_result()->fetch_object();
if (isset($_POST['verkoopall'])) {
    $stmt = db_query("SELECT * FROM `garage` WHERE `login`=? AND `stad`=?", array($data->login, $data->stad));
    $dbres = $stmt->get_result();
    $money = 0;
    while($member = $dbres->fetch_object()) {
        $money += round($member->waarde);
    }
    $stmt = db_query("SELECT * FROM `garage` WHERE `login`=? AND `stad`=?", array($data->login, $data->stad));
    $dbres = $stmt->get_result();
    $nummer = $dbres->num_rows;
    echo "Je hebt $nummer auto's verkocht voor &euro; {$money}.";
    db_query("UPDATE `users` SET `zak`=`zak`+? WHERE `login`=?", array($money, $data->login));
    db_query("DELETE FROM `garage` WHERE `login`=? AND `stad`=?", array($data->login, $data->stad));
$stmt = db_query("SELECT * FROM `garage` WHERE `login`=? AND `stad`=?", array($data->login, $data->stad));
$dbres = $stmt->get_result();
$nummer = $dbres->num_rows;
$kogels = 0;
while($member = $dbres->fetch_object()) {
    $kogels += 15;
}
echo "Je hebt $nummer auto's gecrusht voor $kogels kogels.";
db_query("UPDATE `users` SET `kogels`=`kogels`+? WHERE `login`=?", array($kogels, $data->login));
db_query("UPDATE `famillie` SET `aantal`=`aantal`-? WHERE `name`=?", array($nummer, $data->famillie));
db_query("DELETE FROM `garage` WHERE `login`=? AND `stad`=?", array($data->login, $data->stad));
}
if(isset($_GET['crush'])) {
$id = $_GET['crush'];
$stmt = db_query("SELECT * FROM `garage` WHERE `id`=? AND `login`=?", array($id, $data->login));
$res  = $stmt->get_result();
$b    = $res->fetch_object();
$c    = $res->num_rows;
$stmt = db_query("SELECT * FROM `famillie` WHERE `name`=?", array($data->famillie));
$dbres = $stmt->get_result();
$famillie = $dbres->fetch_object();
$exist = $dbres->num_rows;
if ($exist == 0) { echo "Je hebt geen famillie."; }
elseif ($b->stad != $data->stad) { echo "Deze auto staat niet in de stad waar je momenteel bent."; exit; }
elseif ($famillie->crusher == 0) { echo "Je famillie heeft geen crusher ingehuurd vandaag.";exit; }
elseif ($famillie->aantal < 1) { echo "Het maximum aantal auto's is bereikt."; }
elseif ($c == 0) { echo "Deze auto bestaat niet"; exit; }
else {
  db_query("DELETE FROM `garage` WHERE `id`=? AND `login`=?", array($_GET['crush'], $data->login));
  db_query("UPDATE `users` SET `kogels`=`kogels`+15 WHERE `login`=?", array($data->login));
  db_query("UPDATE `famillie` SET `aantal`=`aantal`-1 WHERE `name`=?", array($data->famillie));
echo "Je auto is gecrusht.";
}
}
$id = $_GET['safe'];
if(isset($_GET['safe'])) {
    $stmt = db_query("SELECT * FROM `garage` WHERE `id`=? AND `login`=?", array($id, $data->login));
    $res  = $stmt->get_result();
    $b    = $res->fetch_object();
    $c    = $res->num_rows;
$prijs = 10000;
if ($data->zak < $prijs) { echo "Je hebt niet genoeg geld om deze auto in een safehouse te zetten.";exit; }
if ($c == 0) { echo "Deze auto bestaat niet"; exit; }
else {
  db_query("UPDATE `garage` SET `safe`='1' WHERE `id`=? AND `login`=?", array($id, $data->login));
  db_query("UPDATE `users` SET `zak`=`zak`-? WHERE `login`=?", array($prijs, $data->login));
echo "Je auto is in een safehouse geplaatst. Het koste &euro;10.000.";
}
}
$id = $_GET['repair'];
if(isset($_GET['repair'])) {
    $stmt = db_query("SELECT * FROM `garage` WHERE `id`=? AND `login`=?", array($id, $data->login));
    $res   = $stmt->get_result();
    $b     = $res->fetch_object();
    $c     = $res->num_rows;
    $stmt  = db_query("SELECT * FROM `cars` WHERE `naam`=?", array($b->naam));
    $garage = $stmt->get_result()->fetch_object();
$value = $garage->waarde;
$prijs = $garage->waarde - $b->waarde;
if ($c == 0) { echo "Deze auto bestaat niet."; exit; }
elseif ($b->stad != $data->stad) { echo "Deze auto staat niet in de stad waar je momenteel bent."; exit; }
elseif ($b->damage == 0){ print"Deze wagen is niet beschadigd.";}
elseif ($prijs > $data->zak) {print"Je hebt niet genoeg geld op zak.";}
else {
  db_query("UPDATE `garage` SET `waarde`=?,`damage`='0' WHERE `id`=? AND `login`=?", array($value, $_GET['repair'], $data->login));
  db_query("UPDATE `users` SET `zak`=`zak`-? WHERE `login`=?", array($prijs, $data->login));
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
    $stmt = db_query("SELECT * FROM `garage` WHERE `id`=? AND `login`=? AND `stad`=?", array($id, $data->login, $data->stad));
    $res  = $stmt->get_result();
    $b    = $res->fetch_object();
    $c    = $res->num_rows;
if ($c == 0) { echo "Deze auto bestaat niet, of is niet in het stad waar je nu bent."; }
else {
  db_query("UPDATE `users` SET `zak`=`zak`+? WHERE `login`=?", array($b->waarde, $data->login));
  db_query("DELETE FROM `garage` WHERE `id`=? AND `login`=?", array($id, $data->login));
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
$stmt = db_query("SELECT * FROM garage WHERE `login`=? ORDER BY `waarde` DESC", array($data->login));
$info = $stmt->get_result();
$count = 0;
while ($gegeven = $info->fetch_array()) {
    $stmtCar = db_query("SELECT * FROM `cars` WHERE `naam`=?", array($gegeven["naam"]));
    $auto = $stmtCar->get_result()->fetch_object();
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
if ($estad == $stad) { echo "<a href=\"garage.php?x={$verkoop}\"><img alt=verkoop border=0 src=images/sell.gif width=32 height=32></a>"; }
if ($estad != $stad) { echo "<img alt=verkoop src=images/sell2.gif width=32 height=32>"; }
if ($estad == $stad) { echo "<a href=\"garage.php?repair={$verkoop}\"><img alt=repair border=0 src=images/repair.gif width=32 height=32></a>"; }
if ($estad != $stad) { echo "<img alt=repair src=images/repair2.gif width=32 height=32>"; }
if ($famillie->crusher == 1 && $famillie->aantal > 0 && $estad == $stad) { echo "<a href=\"garage.php?crush={$verkoop}\"><img alt=crush border=0 src=images/crush.gif width=32 height=32></a>"; }
else { echo "<img alt=crush src=images/crush2.gif width=32 height=32>"; }
if ($estad == $stad) { echo "<a href=\"garage.php?trans={$verkoop}\"><img alt=transporteer border=0 src=images/trans.gif width=32 height=32></a>"; }
if ($estad != $stad) { echo "<img alt=transporteer src=images/trans2.gif width=32 height=32>"; }
if ($safe == 0) { echo "<a href=\"garage.php?safe={$verkoop}\"><img alt=safehouse border=0 src=images/safe.gif width=32 height=32></a>"; }
if ($safe != 0) { echo "<img alt=safehouse src=images/safe2.gif width=32 height=32>"; }

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
