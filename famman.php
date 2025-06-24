<?php
declare(strict_types=1);
require 'config.php';

$stmt = pdo_query(
    "SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`," .
    "UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`," .
    "UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`," .
    "UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login` = ?",
    [$_SESSION['login']]
);
$data = $stmt->fetch();
  if(! check_login()) {
    header('Location: login.php');
    exit;
  }
  $_GET['_clan']			= $data->famillie;
    if($data->famrang == 5) {
    if(isset($_POST['change_owner'])) {
      $dbres			= pdo_query("SELECT `login`,`famillie` FROM `users` WHERE `login`='{$_POST['owner']}'");
      $owner = $dbres->fetch();
	  if (! $dbres){echo"Je familie moet een don hebben.";exit;}
        pdo_query("UPDATE `users` SET `famrang`='1' WHERE `login`='{$data->login}'");
        pdo_query("UPDATE `users` SET `famrang`='5' WHERE `login`='{$owner->login}'");
        pdo_query("UPDATE `famillie` SET `owner`='{$owner->login}' WHERE `name`='{$data->famillie}'");
/*        header("Location: /fam.php?x={$data->famillie}\n"); */
       }
  elseif(isset($_POST['change_halfdon'])) {
      $dbres			= pdo_query("SELECT * FROM `users` WHERE `login`='{$_POST['halfdon']}'");
      $halfdon = $dbres->fetch();
$famillie = pdo_query("SELECT * FROM `famillie` WHERE `name`='{$data->famillie}'")->fetch();
$exist = pdo_query("SELECT * FROM `users` WHERE `famrang`='4' AND `famillie`='{$famillie->name}'")->fetch();
        pdo_query("UPDATE `users` SET `famrang`='1' WHERE `login`='{$exist->login}'");
        pdo_query("UPDATE `users` SET `famrang`='4' WHERE `login`='{$halfdon->login}'");
        pdo_query("UPDATE `famillie` SET `halfdon`='{$halfdon->login}' WHERE `name`='{$data->famillie}'");
/*        header("Location: /fam.php?x={$data->famillie}\n"); */
       }
elseif(isset($_POST['change_consiglieri'])) {
      $dbres			= pdo_query("SELECT * FROM `users` WHERE `login`='{$_POST['consiglieri']}'");
      $consiglieri = $dbres->fetch();
$famillie = pdo_query("SELECT * FROM `famillie` WHERE `name`='{$data->famillie}'")->fetch();
$exist = pdo_query("SELECT * FROM `users` WHERE `famrang`='3' AND `famillie`='{$famillie->name}'")->fetch();
        pdo_query("UPDATE `users` SET `famrang`='1' WHERE `login`='{$exist->login}'");
        pdo_query("UPDATE `users` SET `famrang`='3' WHERE `login`='{$consiglieri->login}'");
        pdo_query("UPDATE `famillie` SET `consiglieri`='{$consiglieri->login}' WHERE `name`='{$data->famillie}'");
/*        header("Location: /fam.php?x={$data->famillie}\n"); */
       }
       }
if ($_POST['donate'] && $_POST['waar'] == persoon && $data->famrang >=3 && $data->famrang != 4) {
$_POST['obedrag']= str_replace(",","",$_POST['obedrag']);
$famillie = pdo_query("SELECT * FROM `famillie` WHERE `name`='{$data->famillie}'")->fetch();
$query = pdo_query("SELECT * FROM `users` WHERE `login`='{$_POST['naar']}'");
$res = $query->fetch();
$naar = strtolower($_POST['naar']);
$van = strtolower($famillie->name);
if(!preg_match('/^[0-9]+$/',$_POST['obedrag'])){echo"Ongeldig bedrag.";}
elseif ($famillie->bank < $_POST['obedrag']) { print "Zoveel geld staat niet op de familiebank.\n"; }
elseif ($_POST['obedrag'] <= 0) { print "Zoveel geld staat niet op de familiebank.\n"; } 
elseif (!$res->login) { print "Deze gebruiker bestaat niet.\n"; } 
elseif ($res->status == dood) { print "Deze gebruiker is dood.\n"; } 
elseif ($van == $naar) { print "Je kan niet aan je eigen familie doneren.\n"; } 
else {
pdo_query("INSERT INTO `logs`(`time`,`login`,`person`,`code`,`area`,`com`) values(NOW(),'{$_POST['naar']}','{$van}','-{$_POST['obedrag']}','cdonate','{$_POST['com']}')");
pdo_query("UPDATE `famillie` SET `bank`=`bank`-{$_POST['obedrag']} WHERE `name`='{$data->famillie}'");
pdo_query("UPDATE `users` SET `zak`=`zak`+{$_POST['obedrag']} WHERE `login`='{$res->login}'");
print"Het geld is verzonden.";
}
}
elseif ($_POST['donate'] && $_POST['waar'] == fam) {
$_POST['obedrag']= str_replace(",","",$_POST['obedrag']);
$famillie = pdo_query("SELECT * FROM `famillie` WHERE `name`='{$data->famillie}'")->fetch();
$bedrag = $_POST['obedrag'];
$naar = $_POST['naar'];
$van = strtolower($famillie->name);
$query = pdo_query("SELECT * FROM `famillie` WHERE `name`='$naar'");
$res = $query->fetch();
if(!preg_match('/^[0-9]+$/',$_POST['obedrag'])){echo"Ongeldig bedrag.";}
elseif ($famillie->bank < $_POST['obedrag']) { print "Zoveel geld staat niet op de familiebank.\n"; }
elseif ($_POST['obedrag'] <= 0) { print "Zoveel geld staat niet op de familiebank.\n"; } 
elseif (!$res->name) { print "Deze familie bestaat niet.\n"; } 
else {
pdo_query("INSERT INTO `logs`(`time`,`login`,`person`,`code`,`area`,`com`) values(NOW(),'{$famillie->name}','{$res->name}','{$bedrag}','cdonate','{$_POST['com']}')");
pdo_query("INSERT INTO `logs`(`time`,`login`,`person`,`code`,`area`,`com`) values(NOW(),'{$naar}','{$famillie->name}','-{$_POST['obedrag']}','cdonate','{$_POST['com']}')");
pdo_query("UPDATE `famillie` SET `bank`=`bank`+$bedrag WHERE `name`='{$naar}'");
pdo_query("UPDATE `famillie` SET `bank`=`bank`-{$_POST['obedrag']} WHERE `name`='{$van}'");
print"Het geld is verzonden.";
  } 
  }
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
<?php
$famillie = pdo_query("SELECT * FROM `famillie` WHERE `name`='{$data->famillie}'")->fetch();
if ($data->famrang < 2) { exit; }
if($_GET['p'] == members && $data->famrang > 2) {
    if(isset($_POST['members'])) {
      $dbres				= pdo_query("SELECT `login` FROM `users` WHERE `famillie`='{$data->famillie}'");
      while($member = $dbres->fetch()) {
        if(isset($_POST[$member->login])) {
          if($_POST[$member->login] == 1)
            pdo_query("UPDATE `users` SET `famillie`='',`famrang`='0' WHERE `login`='{$member->login}'");
          elseif($_POST[$member->login] == 2)
            pdo_query("UPDATE `users` SET `famrang`='2' WHERE `login`='{$member->login}'");
          elseif($_POST[$member->login] == 3)
            pdo_query("UPDATE `users` SET `famrang`='1' WHERE `login`='{$member->login}'");
        }
      }
    }
$half = pdo_query("SELECT * FROM `users` WHERE `famrang`='4' AND `famillie`='{$data->famillie}'")->fetch();
$cons = pdo_query("SELECT * FROM `users` WHERE `famrang`='3' AND `famillie`='{$data->famillie}'")->fetch();
    print "  <tr> 
    <td class=subTitle><b>Familie Leden</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
	if($data->famrang == 5) {
      print " <form method=\"post\">Don: <select name=\"owner\">\n";
$famillie = pdo_query("SELECT * FROM `famillie` WHERE `name`='{$data->famillie}'")->fetch();
      $dbres				= pdo_query("SELECT `login` FROM `users` WHERE `famillie`='{$data->famillie}' ORDER BY `login`");
      while($member = $dbres->fetch()) {
        if($member->login == $data->login)
          print "	<option value=\"{$member->login}\" selected>{$member->login}</option>\n";
        else
          print "	<option value=\"{$member->login}\">{$member->login}</option>\n";
      }
      print "  </select> <input type=\"submit\" name=\"change_owner\" value=\"Update\"></form>";
      print "  <form method=\"post\">Underboss: <select name=\"halfdon\">\n";
      $dbres				= pdo_query("SELECT `login` FROM `users` WHERE `famillie`='{$data->famillie}' AND `login`!='$data->login' ORDER BY `login`");
      while($member = $dbres->fetch()) {
        if($member->login == $data->login)
          print "	<option value=\"{$member->login}\" selected>{$member->login}</option>\n";
        else
          print "	<option value=\"{$member->login}\">{$member->login}</option>\n";
      }
      print "  </select> <input type=\"submit\" name=\"change_halfdon\" value=\"Update\"></form>\n\n";
      print "  <form method=\"post\">Consiglieri: <select name=\"consiglieri\">\n";
      $dbres				= pdo_query("SELECT `login` FROM `users` WHERE `famillie`='{$data->famillie}' AND `login`!='$data->login' ORDER BY `login`");
      while($member = $dbres->fetch()) {
        if($member->login == $data->login)
          print "	<option value=\"{$member->login}\" selected>{$member->login}</option>\n";
        else
          print "	<option value=\"{$member->login}\">{$member->login}</option>\n";
      }
      print "  </select> <input type=\"submit\" name=\"change_consiglieri\" value=\"Update\"></form>\n\n";
    }
    print <<<ENDHTML
<form method="post"><table width=100%>
  <tr><td align="center" width=15%><b>#</b></td>
	<td style="letter-spacing: normal;" align="center"><b>Login</b></td>
	<td style="letter-spacing: normal;" align="center" width=25%><b>Status</b></td>
	<td style="letter-spacing: normal;" align="center" width=25%><b>Rang</b></td></tr>
ENDHTML;
      $dbres				= pdo_query("SELECT * FROM `users` WHERE `famillie`='{$data->famillie}' AND `login`!='$data->login' AND `famrang`!='5' ORDER BY `famrang` DESC,`login` ASC");
    for($j=1; $member = $dbres->fetch(); $j++) {
      $rank				= Array("","Member","Recruiter","Consiglieri","Underboss","Don");
      $rank = $rank[$member->famrang];
if ($member->xp < 10) { $rang = "$rang1"; }
elseif ($member->xp < 20) { $rang = "$rang2"; }
elseif ($member->xp < 50) { $rang = "$rang3"; }
elseif ($member->xp < 150) { $rang = "$rang4"; }
elseif ($member->xp < 500) { $rang = "$rang5"; }
elseif ($member->xp < 1000) { $rang = "$rang6"; }
elseif ($member->xp < 2000) { $rang = "$rang7"; }
elseif ($member->xp < 3000) { $rang = "$rang8"; }
elseif ($member->xp < 4500) { $rang = "$rang9"; }
elseif ($member->xp < 6000) { $rang = "$rang10"; }
elseif ($member->xp < 8000) { $rang = "$rang11"; }
elseif ($member->xp < 11000) { $rang = "$rang12"; }
elseif ($member->xp < 15000) { $rang = "$rang13"; }
elseif ($member->xp < 20000) { $rang = "$rang14"; }
elseif ($member->xp >= 20000) { $rang = "$rang15"; }
     print <<<ENDHTML
  <tr><td align="center">$j</td>
	<td align="center"><a href="user.php?x={$member->login}">{$member->login}</a></td>
	<td align="center">$rank</td>
	<td align="center">$rang</td>
	<td align="center"><select name="{$member->login}">
		<option value="0">Niets</option>
		<option value="1"$select1>Kick</option>
		<option value="2"$select2>Recruiter</option>
		<option value="3"$select3>Member</option>
		</select>
	</td></tr>
ENDHTML;

  }
if ($data->famrang > 3) {
print <<<ENDHTML
  <td width=125 align="center"><input type="submit" name="members" value="Update"></td>
ENDHTML;
}
print "</table></form></td></tr>";
  }
else if ($_GET['p'] == land) {
if ($data->famrang < 3) {exit;}
	echo "<tr> 
    <td class=subTitle><b>Koop gebied</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
echo "Je hebt grond nodig om members toe te laten. Met 5 mÂ² grond kan je 1 member toelaten.<br><br>";
$famillie = pdo_query("SELECT * FROM `famillie` WHERE `name`='{$data->famillie}'")->fetch();
$stad = pdo_query("SELECT * FROM `stad` WHERE `stad`='{$famillie->stad}'")->fetch();
$fami = pdo_query("SELECT * FROM `famillie` WHERE `stad`='{$famillie->stad}'");
$grond = 0;
while($famillies = $fami->fetch()) {
$grond = ($grond + $famillies->grond);
}
$overige = $stad->grond-$grond;
if ($overige < 0) { echo "Er is geen grond meer in deze stad. Probeer andere families uit te moorden."; }
else {
$kopen = floor($famillie->bank / 50000);
$totaal = pdo_query("SELECT * FROM `users` WHERE `famillie`='$data->famillie'")->rowCount();
$totaal = floor($totaal * 5);
$bla = floor(($famillie->grond - $totaal) / 5);
if ($bla < 0) { $bla = 0; }
print "Er is nog {$overige}mÂ² grond in $famillie->stad<br>1mÂ² kost &euro;50.000<br>Er staat &euro;$famillie->bank op de familiebank. Daarmee kun je {$kopen}mÂ² kopen<br><br>Je kan nog $bla leden toelaten met de grond die $data->famillie bezit.<br><br><form method=post><input type=text name=grond size=5>mÂ²<br><input type=submit name=koop value=Koop></form>";
}
if ($_POST['koop']) {
$prijs = ($_POST['grond'] * 50000);
if ($overige - $_POST['grond'] < 0) { echo "Er is niet zoveel grond meer."; }
elseif ($famillie->bank < $prijs) { echo "Er staat niet genoeg geld op de famillie bank."; }
else {
pdo_query("UPDATE `famillie` SET `bank`=`bank`-$prijs,`grond`=`grond`+{$_POST['grond']} WHERE `name`='{$data->famillie}'");
echo "Je hebt {$_POST['grond']}mÂ² gekocht in $famillie->stad voor &euro;$prijs.";
}
}
}
  else if($_GET['p'] == "info" && $data->famrang >= 4) {
    $dbres				= pdo_query("SELECT * FROM `famillie` WHERE `name`='{$data->famillie}'");
    $famillie				= $dbres->fetch();

    print "  <tr> 
    <td class=subTitle><b>Familie Info</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt><a href=\"fam.php?x={$data->famillie}\">Bekijk familie info</a><br><br>";

    if(isset($_POST['info'])) {
      $famillie->info			= preg_replace('/</','&#60;',substr($_POST['info'],0,500));
      pdo_query("UPDATE `famillie` SET `info`='{$famillie->info}' WHERE `name`='{$data->famillie}'");
      print "Familie info is veranderd.";
    }

    print <<<ENDHTML
  <form method="post">
<table>
  <tr>
    <td valign="top" width=100>Info:
      </td>
    <td><textarea name="info" cols=60 rows=20>{$famillie->info}</textarea></td>
  </tr>
  <tr>
    <td></td>
    <td align="right"><input name="submit" type="submit" value="Update"></td>
  </tr>
  </td></tr>
ENDHTML;
  }
elseif ($_GET['p'] == bank && $data->famrang >= 3 && $data->famrang !=4) {
$fambank = number_format($famillie->bank, 0, ',' , ','); 
    print "  <tr> 
    <td class=subTitle><b>Familiebank</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>
	<table width=50% align=center><tr><td width=50% align=right>In familiebank:</td><td align=left>&euro; {$fambank}</td></tr>
	  <table align=\"center\" width=100%>
	<form method=\"post\"><table align=\"center\">
	  <tr><td align=\"right\">Naar<input type=\"text\" name=\"naar\" maxlength=\"16\"></td></tr>
	  <br><tr><td align=\"right\">&euro;<input type=text name=\"obedrag\" value=\"\"  maxlength=9></td></tr>
	  <tr><td align=\"right\">Beschrijving<input type=text name=\"com\" value=\"\"></td></tr>
	  <br><tr><td align=\"right\"><input type=radio name=waar value=persoon checked>Persoon&nbsp;&nbsp;<input type=radio name=waar value=fam>Familie</td></tr>
  	  <tr><td>&nbsp;</td></tr>
	  <tr><td align=\"center\"><input type=submit name=donate value=Ok></td></tr>
	</table></form>
  </td></tr>
</table></td></tr>";

}
elseif ($_GET['p'] == bulfac) {
if ($data->famrang < 3) {exit;}
	echo "<tr> 
    <td class=subTitle><b>Crusher</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
	$dbres = pdo_query("SELECT * FROM `famillie` WHERE `name`='{$data->famillie}'");
	$famillie = $dbres->fetch();
	$exist = $dbres->rowCount();
	if(isset($_POST['huur'])) {
		$aantal = $_POST['aantal'];
		$prijs = ($aantal * 1000);
		if ($prijs > $famillie->bank) { echo "Er staat niet genoeg geld op de familiebank."; exit; }
		elseif ($famillie->crusher == 1) { echo "Er is al een crusher gehuurd vandaag."; exit; }
		else {
			pdo_query("UPDATE `famillie` SET `crusher`='1',`aantal`='$aantal',`bank`=`bank`-$prijs WHERE `name`='{$famillie->name}'");
			echo "Je hebt een crusher gehuurd voor $aantal auto's.";
		}
	}
	else {
		$auto = ($famillie->aantal < 0) ? 0 : $famillie->aantal;
		if ($famillie->crusher == 0) { 
			echo " </center><FORM METHOD=post>
			<input type=radio name=aantal value=100 checked>100 auto's &euro;100.000<br>
			<input type=radio name=aantal value=500 checked>500 auto's &euro;500.000<br>
			<input type=radio name=aantal value=1000 checked>1000 auto's &euro;1.000.000<br>
			<input type=radio name=aantal value=2500>2500 auto's &euro;2.500.000<br>
			<input type=radio name=aantal value=5000>5000 auto's &euro;5.000.000<br>
			<INPUT name='huur' type='submit' VALUE='Huur'>
			</form>";
		}
		else { echo "Je kunt nog $auto auto's omzetten."; exit; }
	}
  echo "</td></tr></table>";
}
elseif ($_GET['p'] == message) {
if ($data->famrang < 3) {exit;}
    print "  <tr> 
    <td class=subTitle><b>Familie Bericht</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
    if(isset($_POST['message'])) {
        $dbres                = pdo_query("SELECT `login` FROM `users` WHERE `famillie`='{$data->famillie}'");
        while($member = $dbres->fetch()) {
          $_POST['subject']        = preg_replace('/</','&#60;',$_POST['subject']);
          $_POST['message']        = preg_replace('/</','&#60;',$_POST['message']);
          pdo_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Famillie Bericht','{$member->login}','{$_POST['subject']}','{$_POST['message']}')") or die(mysql_error());
        }
           print "Familie Bericht verzonden.";
    }
    print <<<ENDHTML
    <form method="POST"><table>
    <tr><td width=100>Onderwerp:</td>    <td><input type="text" name="subject" value="{$_REQUEST['subject']}" maxlength=25></td></tr>
    <tr><td width=100 valign="top">Bericht:</td>
                        <td><textarea name="message" cols=40 rows=10>{$_REQUEST['message']}</textarea></td></tr>
    <tr><td width=100></td>            <td align="right"><input type="submit" name="submit" value="Verzenden"></td></tr>
  </td></tr>
ENDHTML;
  }
elseif ($_GET['p'] == log && $data->famrang >= 3) {
    print "  <tr> 
    <td class=subTitle><b>Donaties</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt><table width=100%><tr><td width=125><b>Tijd</td>
	<td ><b>Gebruiker</td>
	<td width=100 align=\"center\"><b>Bedrag</td>
	<td width=100 align=\"center\"><b>Omschrijving</td></tr>";
    $dbres				= pdo_query("SELECT *,DATE_FORMAT(`time`,'%d-%m-%Y %H:%i') AS `donatetime` FROM `logs` WHERE `person`='{$data->famillie}' AND `time` >= '{$data->signup}' AND `area`='cdonate' ORDER BY `time` DESC LIMIT 0,25");
    while($info = $dbres->fetch()) {
      $money				= $info->code;
      print <<<ENDHTML
      <tr><td width=125>{$info->donatetime}</td>
	<td ><a href="user.php?x={$info->login}">{$info->login}</a></td>
	<td width=100 align="center">&euro;$money</td>
	<td width=100 align="center">$info->com</td></tr>

ENDHTML;
    }
}
elseif ($_GET['p'] == invite) {
    print " <tr> 
    <td class=subTitle><b>Familie Recruit</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
echo "<form method=post>Gebruiker:<input type=text name=user maxlength=16><br><br><input type=submit name=submit value='Zend uitnodiging'></form>";
if ($_POST['submit']) {
$query = pdo_query("SELECT * FROM `users` WHERE `login`='{$_POST['user']}'");
$user = $query->fetch();
if (!$query || $user->status == dood) { echo "Ongeldige gebruikersnaam, of deze gebruiker is dood."; }
elseif ($user->famillie) { echo "Deze gebruiker heeft al een familie."; }
else { pdo_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$user->login}','Invite','Je bent uitgenodigd om de famillie $data->famillie te joinen. Klik hier op de accepteren of te weigeren: [invite]{$data->famillie}[/invite] ')"); pdo_query("INSERT INTO `invite`(`login`,`famillie`) values('$user->login','$data->famillie')"); 
echo "$user->login is uitgenodigd om $data->famillie te joinen.";
}
}
}
?>
</table>
</body>
</html>
