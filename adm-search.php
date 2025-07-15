<?php
include("config.php");
  $dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");
  $data	= mysql_fetch_object($dbres);
if ($data->level < 200) { exit; }
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
<table align=center width=100%>
  <tr> 
    <td class="subTitle"><b>Zoeken</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
<?php
if (isset($_GET['login'])) { 
$_SESSION['login'] = $_GET['login'];
$blabla = mysql_query("SELECT * FROM `users` WHERE `login`='{$_GET['login']}'");
$bla = mysql_fetch_object($blabla);
if ($bla->level >= $data->level) { echo "Je hebt niet genoeg rechten om in dit account te komen.";  unset($_SESSION['login']); unset($_SESSION['pass']);exit; }
if ($data->level < 255) { echo "Je hebt niet genoeg rechten om in dit account te komen.";  unset($_SESSION['login']); unset($_SESSION['pass']);exit; }
else {
mysql_query("INSERT INTO `log`(`wat`,`aantal`,`code`,`van`) values('Login','1','{$_GET['login']}','$data->login')");
echo "Je bent nu ingelogd als " . h($_SESSION['login']); exit;
}
}
elseif (isset($_GET['del'])) {
if($data->level < 1000) {echo"Je hebt niet genoeg rechten.";exit;}
$user = mysql_fetch_object(mysql_query("SELECT * FROM `users` WHERE `login`='{$_GET['del']}'"));
if (!$user->login) { echo "Deze gebruiker bestaat niet.";exit; }
elseif ($user->level >= 200) { echo "Deze gebruiker is een admin en kan je niet verwijderen."; exit; }
else {   mysql_query("INSERT INTO `backup-users` SELECT * FROM `users` WHERE `login`='{$_GET['del']}'"); mysql_query("DELETE FROM `users` WHERE `login`='{$_GET['del']}'"); mysql_query("DELETE FROM `iplog` WHERE `login`='{$_GET['del']}'"); echo "Deze gebruiker is verwijderd. Check nog wel of deze gebruiker een casino had"; }
}
elseif (isset($_GET['ban'])) {
if($data->level < 1000) {echo"Je hebt niet genoeg rechten.";exit;}
$buser = mysql_fetch_object(mysql_query("SELECT * FROM `bans` WHERE `ip`='{$_GET['ban']}'"));
$user = mysql_fetch_object(mysql_query("SELECT * FROM `users` WHERE `ip`='{$_GET['ban']}'"));
if ($buser->ip == $_GET['ban']) { echo "Dit ip adres is al verbannen."; exit; }
elseif ($user->level >= 200) { echo "Deze gebruiker is een admin en kun je niet bannen."; exit; }
else { mysql_query("INSERT INTO `bans`(`ip`,`door`) values('{$_GET['ban']}','$data->login')"); echo "Deze gebruiker is verbannen."; }
}
if ($_POST['search']) {
$need = $_POST['need'];
$gegevens = $_POST['gegevens'];
$dbres = mysql_query("SELECT *,DATE_FORMAT(`time`,'%d-%m-%Y %H:%i') AS `time` FROM `iplog` WHERE `$need`='$gegevens'");
$inf = mysql_fetch_object(mysql_query("SELECT * FROM `users` WHERE `login`='{$gegevens}'"));
echo "
<table><tr><td align=center>&nbsp;</td>
<td align=center><b>Login</td>
<td align=center><b>IP</td>
<td align=center><b>Tijd</td></tr> ";
echo "<b><tr><td width=10% align=center><b><a href=adm-search.php?del=" . h($inf->login) . ">[Delete]</a> <a href=adm-search.php?login=" . h($inf->login) . ">[Login]</a> <a href=adm-bo.php?q=" . h($inf->login) . ">[Stats]</a> <a href=adm-search.php?ban=" . h($inf->ip) . ">[Ban]</a></b></td><td width=5% align=center><b><a href=user.php?x=" . h($inf->login) . ">" . h($inf->login) . "</a></b></td><td width=5% align=center><b>" . h($inf->ip) . "</b></td><td width=5% align=center><b>" . h($inf->online) . "</b></td></tr>";
while ($info = mysql_fetch_object($dbres)) {
echo "<tr><td width=10% align=center><a href=adm-search.php?del=" . h($info->login) . ">[Delete]</a> <a href=adm-search.php?login=" . h($info->login) . ">[Login]</a> <a href=adm-search.php?ban=" . h($info->ip) . ">[Ban]</a></td><td width=5% align=center>" . h($info->login) . "</td><td width=5% align=center>" . h($info->ip) . "</td><td width=5% align=center>" . h($info->time) . "</td></tr>";
}
}
elseif($_GET['p'] == "multi") {
    $begin				= ($_GET['b'] > 0) ? $_GET['b']*10 : 0;
    $dbres				= mysql_query("SELECT `allo`,`login`,`ip`,DATE_FORMAT(`time`,'%d-%m-%Y %H.%i') AS `time` FROM `iplog` WHERE `allo`='0' AND `status`='levend' ORDER BY `time` DESC");

    while($member = mysql_fetch_object($dbres))
      $ips[$member->ip]			.= ",{$member->login}:{$member->time},";
    foreach($ips as $ip => $logins) {
      if(! preg_match('/,,/',$logins))
        unset($ips[$ip]);
    }
    $total				= count($ips);
    $x					= 0;
    print "  <center><b>Multi-Accounts</b></center><br>\n";
    print "  <table width=100%>\n";
    foreach($ips as $ip => $logins) {
      if($x >= $begin && $x < $begin+10) {
        foreach(explode(',,',$logins) as $logine) {
          $logine			= preg_replace('/(^,|,$)/','',$logine);
          list($logine,$online,$allo)		= explode(':',$logine);
          echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
          echo "  <tr>";
          echo "    <td width=\"100\"><div align=\"center\"><strong><a href=adm-warn.php?x=" . h($logine) . ">[Warn]</a> <a href=adm-bo.php?q=" . h($logine) . ">[Stats]</a></strong></div></td>";
          echo "    <td><div align=\"center\"><strong><a href=user.php?x=" . h($logine) . ">" . h($logine) . "</a></strong></div></td>";
          echo "    <td width=\"20%\"><div align=\"center\"><strong>" . h($ip) . "</strong></div></td>";
          echo "    <td width=\"20%\"><div align=\"center\"><strong>" . h($online) . "</strong></div></td>";
          echo "  </tr>";
          echo "</table>";
        }
        print "	<tr><td><br></td></tr>\n\n";
      }
      $x++;
    }
    print "  </table></td></tr>\n";

    print "  <tr><td align=\"center\">";
    if($total <= 10)
      print "&#60; 1 &#62;</td></tr>\n";
    else {
      if($begin/10 == 0)
        print "&#60;&#60; ";
      else
        print "<a href=\"adm-search.php?p=multi&b=". ($begin/10-1) ."\">&#60;&#60;</a> ";

      for($i=0; $i<$total/10; $i++) {
        print "<a href=\"adm-search.php?p=multi&b=$i\">". ($i+1) ."</a> ";
      }

      if($begin+10 >= $total)
        print "&#62;&#62;</td></tr>";
      else
        print "<a href=\"adm-search.php?p=multi&b=". ($begin/10+1) ."\">&#62;&#62;</a></td></tr>";
    }
  }
/* print "<a href=adm-search.php?p=multi>Multi Account Scan</a><br><br>"; */
else { print "<form method='POST'>Gebruikersnaam:<input type='text' name='gegevens'><br>Login<input type='radio' name='need' value='login' checked>&nbsp;ip<input type='radio' name='need' value='ip'><br><br><input type='submit' name='search' value='Zoek'></form>"; } 
?>