<?php /* ------------------------- */
declare(strict_types=1);
require 'config.php';

$stmt = pdo_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login` = ?", [$_SESSION['login']]);
$data = $stmt->fetch();
if (!$data || $data->level < 200) { exit; }
?>
<html>
<head>
<title>Vendetta</title>
<link rel="stylesheet" type="text/css" href="style.css">
<meta name="keywords" content="Vendetta,Crimegame,crimegame,vendetta">
<meta name="language" content="english">
<META name="description" lang="nl" content="Vendetta crimegame met pit.">
</head>
<table width=100%><tr> 
    <td class="subTitle"><b>Userstats</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
<?php
if ($_GET['x'] == 'xp') {
    if ($data->level < 1000) { print "Je hebt niet genoeg rechten."; exit; }
    else { pdo_query("UPDATE `users` SET `xp`=`xp`+10 WHERE `login` = ?", [$_GET['u']]); }
}
if ($_GET['x'] == query) { 
if ($data->level < 255) {echo"Je hebt niet genoeg rechten.";exit;}
if ($_POST['submit']) {
if ($data->level < 1000) { print "Je hebt niet genoeg rechten.";exit; }
$stmt = pdo_query(
    "SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`," .
    "UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`," .
    "UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`," .
    "UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` " .
    "FROM `users` WHERE `id` = ?",
    [$_POST['id']]
);
$user = $stmt->fetch();
pdo_query(
    "UPDATE `users` SET `email`=?,`login`=?,`ip`=?,`level`=?,`stad`=?,`geslacht`=?," .
    "`activated`=?,`health`=?,`xp`=?,`respect`=?,`bo`=?,`testament`=?,`huwelijk`=?," .
    "`zak`=?,`bank`=?,`trans`=?,`kogels`=?,`wapon`=?,`guard`=?,`defence`=?," .
    "`drugs`=?,`drank`=?,`Brussel`=?,`Hasselt`=?,`Gent`=?,`Leuven`=?," .
    "`Antwerpen`=?,`Amsterdam`=?,`Enschede`=?,`Brugge`=?,`se`=?,`rp`=?," .
    "`bf`=?,`famillie`=?,`famrang`=? WHERE `login`=?",
    [
        $_POST['email'], $_POST['login'], $_POST['ip'], $_POST['level'],
        $_POST['stad'], $_POST['geslacht'], $_POST['activated'], $_POST['health'],
        $_POST['xp'], $_POST['respect'], $_POST['bo'], $_POST['testament'],
        $_POST['huwelijk'], $_POST['zak'], $_POST['bank'], $_POST['trans'],
        $_POST['kogels'], $_POST['wapon'], $_POST['guard'], $_POST['defence'],
        $_POST['drugs'], $_POST['drank'], $_POST['brussel'], $_POST['hasselt'],
        $_POST['gent'], $_POST['leuven'], $_POST['antwerpen'], $_POST['amsterdam'],
        $_POST['enschede'], $_POST['brugge'], $_POST['se'], $_POST['rp'],
        $_POST['bf'], $_POST['famillie'], $_POST['famrang'], $_POST['login']
    ]
);
pdo_query(
    "INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','JanuS','Webmasters actions','Volgende gebruiker: {$_POST['login']}<br>Geld op zak: {$user->zak} naar {$_POST['zak']} <br>Geld op bank: {$user->bank} naar {$_POST['bank']} <br>Level: {$user->level} naar {$_POST['level']} <br>KS van: {$user->se} naar {$_POST['se']} <br>Guards van: {$user->guard} naar {$_POST['guard']} <br>XP van: {$user->xp} naar {$_POST['xp']} <br><br>Uitgevoerd door: {$data->login}')"
);

echo "Opdracht uitgevoerd.";
}
else {
$stmt = pdo_query(
    "SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`," .
    "UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`," .
    "UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`," .
    "UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` " .
    "FROM `users` WHERE `login` = ?",
    [$_GET['u']]
);
$user = $stmt->fetch();
print "<form method=post>
ID: <input type=text name=id value='$user->id' readonly=\"true\"><br>
Login: <input type=text name=login value='$user->login'><br>
IP: <input type=text name=ip value='$user->ip'><br>
Level: <input type=text name=level value='$user->level'><br>
E-mail: <input type=text name=email value='$user->email'><br>
Stad: <input type=text name=stad value='$user->stad'><br>
Geslacht: <input type=text name=geslacht value='$user->geslacht'><br>
Actief: <input type=text name=activated value='$user->activated'><br>
Health: <input type=text name=health value='$user->health'><br>
XP: <input type=text name=xp value='$user->xp'><br>
Respect: <input type=text name=respect value='$user->respect'><br>
Te geven respect: <input type=text name=rp value='$user->rp'><br>
Bust-outs: <input type=text name=bo value='$user->bo'><br>
Testament: <input type=text name=testament value='$user->testament'><br>
Huwelijk: <input type=text name=huwelijk value='$user->huwelijk'><br><br>
Zak: <input type=text name=zak value='$user->zak'><br>
Bank: <input type=text name=bank value='$user->bank'><br>
Transport: <input type=text name=trans value='$user->trans'><br>
Kogels: <input type=text name=kogels value='$user->kogels'><br>
Wapen: <input type=text name=wapon value='$user->wapon'><br>
Bodyguards: <input type=text name=guard value='$user->guard'><br>
Defence: <input type=text name=defence value='$user->defence'><br>
Drugs: <input type=text name=drugs value='$user->drugs'><br>
Drank: <input type=text name=drank value='$user->drank'><br><br>
Brussel: <input type=text name=brussel value='$user->Brussel'><br>
Hasselt: <input type=text name=hasselt value='$user->Hasselt'><br>
Gent: <input type=text name=gent value='$user->Gent'><br>
Leuven: <input type=text name=leuven value='$user->Leuven'><br>
Antwerpen: <input type=text name=antwerpen value='$user->Antwerpen'><br>
Brugge: <input type=text name=brugge value='$user->Brugge'><br>
Amsterdam: <input type=text name=amsterdam value='$user->Amsterdam'><br>
Enschede: <input type=text name=enschede value='$user->Enschede'><br><br>
KS: <input type=text name=se value='$user->se'><br>
BackFire: <input type=text name=bf value='$user->bf'><br><br>
Famillie: <input type=text name=famillie value='$user->famillie'><br>
Famrang: <input type=text name=famrang value='$user->famrang'><br>

<br><input type=submit name=submit value=Submit>
  <input type=reset name=Reset value=Reset>
</form>";exit;
}
}
if ($_GET['x'] == info) { 
$stmt = pdo_query("SELECT * FROM `users` WHERE `login` = ?", [$_GET['u']]);
$user = $stmt->fetch();
$stmt = pdo_query("SELECT * FROM `famillie` WHERE `name` = ?", [$user->famillie]);
$famillie = $stmt->fetch();
if ($_GET['v']) { $stmt = pdo_query("SELECT * FROM `users` WHERE `login` = ?", [$_GET['v']]); $van = $stmt->fetch(); }
else { $stmt = pdo_query("SELECT * FROM `users` WHERE `login` = ?", [$data->login]); $van = $stmt->fetch(); }
if (!$van->login) { echo "Deze gebruiker bestaat niet"; exit; }
elseif (!$user->login) { echo "Deze gebruiker bestaat niet"; exit; }
elseif ($user->status == dood) { echo "Deze gebruiker is al dood"; exit; }
$stmt = pdo_query("SELECT * FROM `famillie` WHERE `name` = ?", [$van->famillie]);
$famillievan = $stmt->fetch();
$huis = ($user->{$user->stad} > 0) ? Ja : Nee; 
$fam = ($user->stad == $famillie->stad) ? Ja : Nee;
if ($user->bf == 0) { $bf = Geen; }
elseif ($user->bf == 1) { $bf = Zelfde; }
elseif ($user->bf == 2) { $bf = Dubbele; }
else { $bf = $user->bf; }
echo "<center>Gebruiker: $user->login <br>Stad: $user->stad <br>Health: $user->health <br>Huis: $huis <br>Lijfwachten: $user->guard <br>Famillie Bescherming: $fam"; 
if ($user->xp < 50) { $rangk = 1; }
elseif ($user->xp < 150) { $rangk = 1.5; }
elseif ($user->xp < 500) { $rangk = 2; }
elseif ($user->xp < 1000) { $rangk = 2.5; }
elseif ($user->xp < 2000) { $rangk = 3; }
elseif ($user->xp < 3000) { $rangk = 3.5; }
elseif ($user->xp < 4500) { $rangk = 4; }
elseif ($user->xp < 6000) { $rangk = 4.5; }
elseif ($user->xp < 8000) { $rangk = 5; }
elseif ($user->xp < 11000) { $rangk = 5.5; }
elseif ($user->xp < 15000) { $rangk = 6; }
elseif ($user->xp < 20000) { $rangk = 6.5; }
elseif ($user->xp >= 20000) { $rangk = 7; }
$lijfwachten = ($user->guard * 0.1);
$huis = ($user->{$user->stad} > 0) ? Ja : Nee; 
$home = ($huis == Ja)  ? 0.4 : 0; 
$homes = $home*100;
echo "<br>Huis Bescherming: $homes%";
$fampro = ($fam == Ja) ? 0.05 : 0; 
$fampros = $fampro*100;
echo "<br>Famillie Bescherming: $fampros%";
$protection = (1 - ($home + $fampro + $lijfwachten));
$stmt = pdo_query("SELECT * FROM `items` WHERE `type`='att' AND `nr` = ?", [$van->wapon]);
$wapen = $stmt->fetch();
$wapon = ($wapen->effect);
if ($wapon == 0) {$wapon == 1;}
$protections = $protection*100/$wapon;
echo "<br>Doeltreffende kogels: $protections%";
$need = ($rangk * $user->health);
$need = ($need / $protection);
$need = ($need * $wapon);
$need = ceil($need * (100 / $van->se));
$sneed = $need;
echo "<br>Kogels nodig: $need";
echo "<br>Schietervaring $van->login: $van->se%";
echo "<br>Backfire $user->login: $user->bf";
$lijfwachten = ($van->guard * 0.1);
$huis = ($van->{$van->stad} > 0) ? Ja : Nee; 
$home = ($huis == Ja)  ? 0.4 : 0; 
$fam = ($van->stad == $famillievan->stad) ? Ja : Nee;
$fampro = ($fam == Ja) ? 0.05 : 0; 
$protection = (1 - ($home + $fampro + $lijfwachten));
$stmt = pdo_query("SELECT * FROM `items` WHERE `type`='att' AND `nr` = ?", [$user->wapon]);
$wapen = $stmt->fetch();
$wapon = ($wapen->effect);
if ($wapon == 0) {$wapon == 1;}
if ($van->xp < 50) { $rangkvan = 1; }
elseif ($van->xp < 150) { $rangkvan = 1.5; }
elseif ($van->xp < 500) { $rangkvan = 2; }
elseif ($van->xp < 1000) { $rangkvan = 2.5; }
elseif ($van->xp < 2000) { $rangkvan = 3; }
elseif ($van->xp < 3000) { $rangkvan = 3.5; }
elseif ($van->xp < 4500) { $rangkvan = 4; }
elseif ($van->xp < 6000) { $rangkvan = 4.5; }
elseif ($van->xp < 8000) { $rangkvan = 5; }
elseif ($van->xp < 11000) { $rangkvan = 5.5; }
elseif ($van->xp < 15000) { $rangkvan = 6; }
elseif ($van->xp < 20000) { $rangkvan = 6.5; }
elseif ($van->xp >= 20000) { $rangkvan = 7; }
$need = ($rangkvan * $van->health);
$need = ($need / $protection);
$need = ($need * $wapon);
$need = ceil($need * (100 / $user->se));
if ($user->bf > 3) {
$bfkill = ($user->bf >= $need && $user->kogels >= $need) ? Ja : Nee; 
}
if ($user->bf < 4){
$bfkill = (((($user->bf)/2)*$sneed) >= $need && $user->kogels >= ((($user->bf)/2)*$sneed)) ? Ja : Nee;
}
echo "<br>Kogels $user->login: $user->kogels";
echo "<br>BF kill: $bfkill<br><br><br>";exit;
}
$begin= ($_GET['p'] >= 0) ? $_GET['p']*20 : 0;
$q = $_GET['q'];
$_GET['q'] = "*{$q}*";
$_GET['q']	= preg_replace('/\*/','%',$_GET['q']);
$stmt = pdo_query("SELECT * FROM `users` ORDER BY `xp` DESC LIMIT $begin,20");
if ($_GET['q'] != "") {
    $search = $_GET['q'];
    $stmt = pdo_query("SELECT * FROM `users` WHERE `login` LIKE ? ORDER BY `xp` DESC LIMIT $begin,20", [$search]);
}
$total = $stmt->rowCount();
$x = 0;
$page = $_GET['p'];
for($j=$begin+1; $info = $stmt->fetch(); $j++) {
if ($info->xp < 10) { $rang = "$rang1"; }
elseif ($info->xp < 20) { $rang = "$rang2";}
elseif ($info->xp < 50) { $rang = "$rang3"; }
elseif ($info->xp < 150) { $rang = "$rang4"; }
elseif ($info->xp < 500) { $rang = "$rang5"; }
elseif ($info->xp < 1000) { $rang = "$rang6"; }
elseif ($info->xp < 2000) { $rang = "$rang7"; }
elseif ($info->xp < 3000) { $rang = "$rang8"; }
elseif ($info->xp < 4500) { $rang = "$rang9"; }
elseif ($info->xp < 6000) { $rang = "$rang10"; }
elseif ($info->xp < 8000) { $rang = "$rang11"; }
elseif ($info->xp < 11000) { $rang = "$rang12"; }
elseif ($info->xp < 15000) { $rang = "$rang13"; }
elseif ($info->xp < 20000) { $rang = "$rang14"; }
elseif ($info->xp >= 20000) { $rang = "$rang15"; }
$status = ($info->status == levend) ? "" : "<b>&#8224;</b>";
echo "<table width=100%><tr>
    <td width='25%'><center><a href=?x=xp&u=$info->login>[XP up]</a>";echo" <a href=?x=query&u=$info->login>[Query]</a></td>
    <td width='30%'><center><a href=user.php?x={$info->login}>$info->login $status</a></td>
    <td width='45%'><center>$rang</td>
  </tr>";
}
$rowsStmt = pdo_query("SELECT COUNT(id) AS c FROM `users`");
if ($_GET['q'] != "") { $rowsStmt = pdo_query("SELECT COUNT(id) AS c FROM `users` WHERE `login` LIKE ?", [$search]); }
$rows = $rowsStmt->fetch()->c;
print "</table><br><center><tr><td>&nbsp;&nbsp;</td></tr><tr>
    <td class=subTitle><b>Zoeken</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt><form method=get>
	Voorbeeld: <b>a</b> zal een lijst geven met alle namen waar een a in voorkomt. <br>Door bvb <b>a*c</b> te typen kan je in het midden van een woord een letter (of groep letters) die je niet kent laten zoeken.<br>
	<br><input type=text name=q value={$_REQUEST['q']}> <input type=submit value=Zoek!>
	</form></td></tr>";
    print "  <tr><td align=\"center\">";
    if($rows <= 20)
    print "&#60; 1 &#62;</td></tr></table>\n";
  else {
    if($begin/20 == 0)
      print "&#60;&#60; ";
    else
      print "<a href=\"?s=$sort&q=$q&o=$order&p=". ($begin/20-1) ."\">&#60;&#60;</a> ";

    for($i=0; $i<$rows/20; $i++) {
      print "<a href=\"?s=$sort&q=$q&o=$order&p=$i\">". ($i+1) ."</a> ";
    }

    if($begin+20 >= $rows)
      print "&#62;&#62; ";
    else
      print "<a href=\"?s=$sort&q=$q&o=$order&p=". ($begin/20+1) ."\">&#62;&#62;</a>";
  }

?>
</table></td></tr></table>