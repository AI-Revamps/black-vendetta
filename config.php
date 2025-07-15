<?php
declare(strict_types=1);
require_once __DIR__.'/pdo.php';
// Configure secure session cookies
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params(['httponly' => true, 'secure' => $secure]);
session_start();

function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
// Generate a CSRF token for the session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validate CSRF token on POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $postedToken)) {
        die('Invalid CSRF token');
    }
}

// Compatibility layer for old mysql_* functions using PDO
function mysql_query(string $query) {
    return db()->query($query);
}
function mysql_fetch_object(PDOStatement $stmt) {
    return $stmt->fetch(PDO::FETCH_OBJ);
}
function mysql_fetch_assoc(PDOStatement $stmt) {
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function mysql_fetch_array(PDOStatement $stmt) {
    return $stmt->fetch(PDO::FETCH_BOTH);
}
function mysql_num_rows(PDOStatement $stmt) {
    return $stmt->rowCount();
}
function mysql_insert_id() {
    return db()->lastInsertId();
}
function mysql_result(PDOStatement $stmt, int $row = 0, $field = 0) {
    $rows = $stmt->fetchAll(PDO::FETCH_BOTH);
    return $rows[$row][$field] ?? null;
}
function mysql_real_escape_string(string $str) {
    return substr(db()->quote($str), 1, -1);
}
function db_query(string $sql, array $params = []) {
    return pdo_query($sql, $params);
}
  if($data->rijvord > 99) {
mysql_query("UPDATE `users` SET `rijbewijs`='1'  WHERE `login`='".$data->login."'");
mysql_query("UPDATE `users` SET `rijvord`='0'  WHERE `login`='".$data->login."'");
  }
 if(isset($_SESSION['login'])) {
    $dbres				= mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");
    $data				= mysql_fetch_object($dbres);
    $blata = mysql_num_rows($dbres);
include("rangen.php");
include("tijden.php");
include("rangmsg.php");

/* if ($data->bank > 60000000) { $pi = $_SERVER['REMOTE_ADDR']; mysql_query("INSERT INTO `bans`(`ip`,`reden`,`door`) values('$pi','Bug exploiting','Auto')"); } */
	mysql_query("UPDATE `users` SET `health`='100' WHERE (`health` > 100)");
	mysql_query("UPDATE `users` SET `se`='100' WHERE (`se` > 100)");
	mysql_query("UPDATE `users` SET `stad`='Hasselt' WHERE `stad`=''");
if ($blata == 0) { header('Location login.php'); }
if ($data->status != dood) { mysql_query("UPDATE `users` SET `online`=NOW() WHERE `login`='{$data->login}'"); }
$huis = $data->stad;
$ctime = time();
}
  $dbre = mysql_query("SELECT *, UNIX_TIMESTAMP(`time`) AS `getime` FROM `jail` WHERE `login`='{$data->login}'");
  $jisin = mysql_num_rows($dbre);
  $jail = mysql_fetch_object($dbre);
  $tim = ($jail->getime - time());
if ($jisin == 1) {
if ($tim == 1) { $time = gmdate('s', $tim); $word = seconde; $boete = $jail->boete; }
elseif ($tim < 61) { $time = gmdate('s', $tim); $word = seconden; $boete = $jail->boete; }
else { $time = gmdate('i:s', $tim); $word = minuten; $boete = $jail->boete; }
}
$lang =  "nl.php" ;
include($lang); 
mysql_query("DELETE FROM `jail` WHERE UNIX_TIMESTAMP(`time`)-UNIX_TIMESTAMP(NOW()) < 0");
 	$dete = mysql_query("SELECT * FROM `detectives` WHERE UNIX_TIMESTAMP(`time`)-UNIX_TIMESTAMP(NOW()) < 0");
	while($det = mysql_fetch_object($dete)) {
	$suspect = mysql_query("SELECT * FROM `users` WHERE `login`='{$det->van}'");
	$sus = mysql_fetch_object($suspect);
  	$victim = mysql_query("SELECT * FROM `users` WHERE `login`='{$det->naar}'");
  	$vic = mysql_fetch_object($victim);
if ($det->stad == $vic->stad) {
mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$sus->login}','Gevonden','Je detective heeft {$vic->login} gevonden in {$det->stad}.')"); mysql_query("DELETE FROM `detectives` WHERE `naar`='{$vic->login}' AND `stad`='{$det->stad}'");
}
 mysql_query("DELETE FROM `detectives` WHERE UNIX_TIMESTAMP(`time`)-UNIX_TIMESTAMP(NOW()) < 0"); 
}
$ip = $_SERVER['REMOTE_ADDR'];
$iban = mysql_fetch_object(mysql_query("SELECT * FROM `bans` WHERE `IP`='$ip'"));
$lban = mysql_fetch_object(mysql_query("SELECT * FROM `bans` WHERE `IP`='$ip' OR `login`='{$data->login}'"));
$ban = ($data->login) ? $lban : $iban;
 if ($ban) {
    print <<<ENDHTML
<!DOCTYPE html>
<html lang="nl">
<head>
<title>Gangster4Crime</title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
  <table width=100%>
    <tr> 
    <td class="subTitle"><b>Verbannen</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
	Je bent verbannen van Gangster4Crime<br><br>Reden: $ban->reden
    </td></tr>
  </table>
</body>
</html>
ENDHTML;
unset($_SESSION['login']);
    exit;
  }
function check_login() {
$res = mysql_num_rows(mysql_query("SELECT * FROM `users` WHERE `login`='{$_SESSION['login']}'"));
    if($res == 0) {
        unset($_SESSION['login']);
return FALSE; 
}
else { return TRUE; }
}
if ($data->xp < 10) { $jailtime = (TIME() + 20); $boete = 250; }
elseif ($data->xp < 20) { $jailtime = (TIME() + 40); $boete = 500; }
elseif ($data->xp < 50) { $jailtime = (TIME() + 60); $boete = 750; }
elseif ($data->xp < 150) { $jailtime = (TIME() + 90); $boete = 1000; }
elseif ($data->xp < 500) { $jailtime = (TIME() + 120); $boete = 5000;  }
elseif ($data->xp < 1000) { $jailtime = (TIME() + 180); $boete = 7500; }
elseif ($data->xp < 2000) { $jailtime = (TIME() + 190); $boete = 12500; }
elseif ($data->xp < 3000) { $jailtime = (TIME() + 230); $boete = 25000; }
elseif ($data->xp < 4500) { $jailtime = (TIME() + 270); $boete = 50000; }
elseif ($data->xp < 6000) { $jailtime = (TIME() + 320); $boete = 60000; }
elseif ($data->xp < 8000) { $jailtime = (TIME() + 370); $boete = 75000; }
elseif ($data->xp < 11000) { $jailtime = (TIME() + 420); $boete = 85000; }
elseif ($data->xp < 15000) { $jailtime = (TIME() + 480); $boete = 100000; }
elseif ($data->xp < 20000) { $jailtime = (TIME() + 540); $boete = 125000; }
elseif ($data->xp >= 20000) { $jailtime = (TIME() + 600); $boete = 150000; }
if (!$data->famillie) { $famillie = "Geen"; }
else { $famillie = $data->famillie; }
    $dbres				= mysql_query("SELECT UNIX_TIMESTAMP(`time`) AS `time`,`name` FROM `cron`");
    while($x = mysql_fetch_object($dbres))
      $update[$x->name]		= $x->time;
$rand = rand(500,1500);
    if(floor($update['kogels']/ 130) != floor(time()/ 130)) {
      $dbres				= mysql_query("SELECT GET_LOCK('kogels_update',0)");
      if(mysql_result($dbres,0) == 1) {
        mysql_query("UPDATE `cron` SET `time`=NOW() WHERE `name`='kogels'");
mysql_query("DELETE FROM `messages` WHERE `to`=''");
mysql_query("DELETE FROM `iplog` WHERE `login`=''");

		mysql_query("UPDATE `stad` SET `kogels`='100',`prijs`='$rand'");
                mysql_query("SELECT RELEASE_LOCK('kogels_update')");
      }
    }
	
        if(floor($update['uur']/ 3600) != floor(time()/ 3600)) {
      $dbres				= mysql_query("SELECT GET_LOCK('uur_update',0)");
      if(mysql_result($dbres,0) == 1) {
        mysql_query("UPDATE `cron` SET `time`=NOW() WHERE `name`='uur'");
		mysql_query("UPDATE `casino` SET `winst`=`winst`+100 WHERE `spel`='kogelfabriek'");
       mysql_query("SELECT RELEASE_LOCK('uur_update')");
      }
    }
            if(floor($update['day']/ 86400) != floor(time()/ 86400)) {
      $dbres				= mysql_query("SELECT GET_LOCK('day_update',0)");
      if(mysql_result($dbres,0) == 1) {
	  mysql_query("UPDATE `cron` SET `time`=NOW() WHERE `name`='day'");       

	mysql_query("UPDATE `famillie` SET `crusher`='0',`aantal`='0'");
	mysql_query("DELETE FROM `kras`");
$abrussel = rand(1000,6000);
$aleuven = rand(1000,6000);
$agent = rand(1000,6000);
$abrugge = rand(1000,6000);
$aantwerpen = rand(1000,6000);
$ahasselt = rand(1000,6000);
$aamsterdam = rand(1000,6000);
$aenschede = rand(1000,6000);
$ubrussel = rand(6000,15000);
$uleuven = rand(6000,15000);
$ugent = rand(6000,15000);
$ubrugge = rand(6000,15000);
$uantwerpen = rand(6000,15000);
$uhasselt = rand(6000,15000);
$uamsterdam = rand(6000,15000);
$uenschede = rand(6000,15000);
mysql_query("UPDATE `stad` SET `drugsp`='$ubrussel',`drankp`='$abrussel' WHERE `stad`='Brussel'");
mysql_query("UPDATE `stad` SET `drugsp`='$uleuven',`drankp`='$aleuven' WHERE `stad`='Leuven'");
mysql_query("UPDATE `stad` SET `drugsp`='$ugent',`drankp`='$agent' WHERE `stad`='Gent'");
mysql_query("UPDATE `stad` SET `drugsp`='$ubrugge',`drankp`='$abrugge' WHERE `stad`='Brugge'");
mysql_query("UPDATE `stad` SET `drugsp`='$uantwerpen',`drankp`='$aantwerpen' WHERE `stad`='Antwerpen'");
mysql_query("UPDATE `stad` SET `drugsp`='$uhasselt',`drankp`='$ahasselt' WHERE `stad`='Hasselt'");
mysql_query("UPDATE `stad` SET `drugsp`='$uamsterdam',`drankp`='$aamsterdam' WHERE `stad`='Amsterdam'");
mysql_query("UPDATE `stad` SET `drugsp`='$uenschede',`drankp`='$aenschede' WHERE `stad`='Enschede'");
mysql_query("SELECT RELEASE_LOCK('day_update')");
      }
    }
        if(floor($update['week']/ 604800) != floor(time()/ 604800)) {
      $dbres				= mysql_query("SELECT GET_LOCK('week_update',0)");
      if(mysql_result($dbres,0) == 1) {
        mysql_query("UPDATE `cron` SET `time`=NOW() WHERE `name`='week'");
$blah = mysql_query("SELECT * FROM `users` WHERE `activated`='1' AND `status`='levend'");
while($blahh = mysql_fetch_object($blah)) {
$rp = round($blahh->xp / 50);
if ($rp > 0) { mysql_query("UPDATE `users` SET `rp`='$rp' WHERE `login`='{$blahh->login}'"); }
}
        mysql_query("SELECT RELEASE_LOCK('week_update')");
      }
    }
    
/* ------------------------- */ 

?>
