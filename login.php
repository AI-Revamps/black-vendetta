<?php
  include("config.php");
  if(isset($_POST['login'],$_POST['password'])) {
    $stmt = db_query("SELECT *,UNIX_TIMESTAMP(`online`) AS `online` FROM `users` WHERE `login`=? AND `pass`=MD5(?)", array($_POST["login"], $_POST["password"]));
    if(($data = $stmt->get_result()->fetch_object()) && $data->activated == 1) {
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
<table align="center" width=100%>
<?php /* ------------------------- */
  if($_GET['x'] == "logout"){
    session_unset($_SESSION['pass']);
    session_destroy($_SESSION['login']);
	session_unset($_SESSION['pass']);
    session_destroy($_SESSION['login']);
    echo"<table width=100% align=center>
		   <tr><td class=subTitle><b>Uitloggen</b></td></tr>
		   <tr><td>&nbsp;</td></tr>
		   <tr><td class=mainTxt>U bent nu uitgelogd. Een ogenblik geduld.<br><a href=index.php><b>Wacht niet<b></a></td></tr>";
    echo"<meta http-equiv=Refresh content=3;url=index.php>";
  }
  else if($_GET['x'] == "lostpass") {
      if(isset($_GET['id'],$_GET['code'])) {
        $stmt                            = db_query("SELECT `login` FROM `temp` WHERE `id`=? AND `code`=? AND `area`='lostpass'", array($_GET["id"], $_GET["code"]));
        $dbres                            = $stmt->get_result();
          $stmt                          = db_query("SELECT `login`,`email` FROM `users` WHERE `login`=?", array($data->login));
          $data                           = $stmt->get_result()->fetch_object();

        $newpass			= rand(100000,999999);
          db_query("UPDATE `users` SET `pass`=MD5(?) WHERE `login`=?", array($newpass, $data->login));
          db_query("DELETE FROM `temp` WHERE `id`=?", array($_GET["id"]));
		mail($data->email, "Vendetta Password", "Je wachtwoord is gereset. Het is nu : $newpass","From: ".Vendetta." <noreply@vendetta.com>");
       // mail($data->email,"Vendetta password","Je wachtwoord is gereset, je kan nu inloggen met: $newpass","From: Vendetta <noreply@vendetta.com>\n");
        print "Je nieuwe wachtwoord is verstuurt naar {$data->email}.\n";
      }
    }
    else if(isset($_POST['email'],$_POST['login'])) {
      $stmt                            = db_query("SELECT `login`,`email` FROM `users` WHERE `login`=? AND `email`=? AND `activated`=1", array($_POST["login"], $_POST["email"]));
      if($data = $stmt->get_result()->fetch_object()) {
        $code				= rand(1000000000,9999999999);
        db_query("INSERT INTO `temp`(`login`,`code`,`area`,`time`) values(?, ?, 'lostpass', NOW())", array($data->login, $code));
        $id				= mysql_insert_id();
        mail($data->email,"Vendetta password","Vraag je wachtwoord op deze link aan. \nhttp://logd.nl/game3/login.php?x=lostpass&id=$id&code=$code","From: logd.nl-game3 <noreply@logd.nl>");
        print "Er is een email met verdere instructies gestuurd naar: {$data->email}.\n";
      }
      else
        print "De gebruikersnaam komt niet overeen met het e-mailadres.\n";
    }

    print <<<ENDHTML
  <tr><td align="center">
	<form method="post">
	  <table width="100%" align="center">
        <tr> 
    <td class="subTitle"><b>Wachtwoord Vergeten</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt"><table width=100%>
		<tr> 
          <td width="49%"><div align="right">Gebruikersnaam:</div></td>
          <td width="2%">&nbsp;</td>
          <td width="49%"><input type="text" name="login"></td>
        </tr>
        <tr> 
          <td width="49%"><div align="right">E-mailadres:</div></td>
          <td width="2%">&nbsp;</td>
          <td width="49%"><input type="text" name="email"></td>
        </tr>
        <tr> 
          <td> <div align="center"> </div></td>
          <td>&nbsp;</td>
          <td><input name="submit" type="submit" style="width: 100" value="Ok"></td>
        </tr>
      </table></table>
    </form></td></tr>
ENDHTML;
  }
  elseif($data) {
    $time = time();
/*  if (($time - $data->online) < 60) { print "Je moet 60 seconden wachten voordat je weer kan inloggen.\n"; exit; }  */
if($data->activated == 0) { print "Je acount is nog niet geactiveerd.\n"; }
else {
  $_SESSION['login']		= $_POST['login'];
  $ip = $_SERVER['REMOTE_ADDR'];
  $dbres = mysql_query("SELECT * FROM `multiple` WHERE `ip`='$ip'");
  $allo = mysql_fetch_object($dbres);
  $allo = ($allo->allo == 1) ? 1 : 0;
  $exi = mysql_num_rows(mysql_query("SELECT * FROM `iplog` WHERE `ip`='$ip' AND `login`='{$_POST['login']}'"));
  if ($exi == 1) { mysql_query("UPDATE `iplog` SET `time`=NOW() WHERE `login`='{$_POST['login']}' AND `ip`='$ip'"); }
  else { mysql_query("INSERT INTO `iplog`(`login`,`ip`,`time`,`allo`,`status`) values('{$_POST['login']}','$ip',NOW(),'$allo','{$data->status}')"); }
  echo"<table width=100% align=center>
		   <tr><td class=subTitle><b>Inloggen</b></td></tr>
		   <tr><td>&nbsp;</td></tr>
		   <tr><td class=mainTxt>U bent nu ingelogd. Een ogenblik geduld.<br><a href=index.php><b>Wacht niet<b></a></td></tr>";
    echo"<meta http-equiv=Refresh content=3;url=index.php>";  
}
}
  else {
      if(isset($_POST['login'],$_POST['pass']))
      print "Verkeerde gebruikersnaam/wachtwoord.\n";

    print <<<ENDHTML
 <tr><td>
	<form method="post">
      <table width="100%" align="center">
        <tr> 
    <td class="subTitle"><b>Inloggen</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt"><table width=100%>
        <tr> 
          <td width="49%"><div align="right">Gebruikersnaam:</div></td>
          <td width="2%">&nbsp;</td>
          <td width="49%"><input type="text" name="login" maxlength=16 width="150"></td>
        </tr>
        <tr> 
          <td width="49%"><div align="right">Wachtwoord:</div></td>
          <td width="2%">&nbsp;</td>
          <td width="49%"><input type="password" name="password" maxlength=16 width="150"></td>
        </tr>
        <tr> 
          <td></td>
          <td></td>
          <td><input type="submit" name="submit" width="150" value="Login"></td>
        </tr>
        <tr> 
          <td colspan="3" align=center> <a href=login.php?x=lostpass>Wachtwoord 
            vergeten?</a></td>
        </tr>
      </table></table>
    </form>
  </td></tr>
ENDHTML;
  }
?>
</table>
</body>
</html>
</table>
