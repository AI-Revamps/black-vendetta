<?php /* ------------------------- */
  include("config.php");
?>
<html>


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
    <td class="subTitle"><b>Registreren</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
<?php
  if(isset($_GET['id'],$_GET['code'])) {
    $id            = $_GET['id'];
    $code          = $_GET['code'];
    $dbres          = mysql_query("SELECT * FROM `temp` WHERE `area`='signup' AND `id`='$id' AND `code`='$code'");
    if($data = mysql_fetch_object($dbres)) {
      mysql_query("UPDATE `users` SET `activated`='1',`start`=NOW() WHERE `login`='{$data->login}'");
      mysql_query("DELETE FROM `temp` WHERE `id`='$id'");
	  if($data->forwerdedFor != ""){
	  mysql_query("UPDATE `users` SET `respect`=`respect`+5 WHERE `id`='{$data->forwardedFor}'");
	  mysql_query("INSERT INTO `logs`(`time`,`login`,`person`,`code`,`area`,`com`) values(NOW(),'{$data->login}','{$data->forwardedFor}','5','respect','ReferrerID')");}
      print "De activatie is voltooid, je kunt nu inloggen.";
    }
    else
      print "Incorrecte activatie-code...";
  }
elseif(isset($_POST['submit'])) {
  $gebruiker = $_POST['gebruiker'];
  $pass = $_POST['pass'];
  $refer = $_POST['refer'];
  $passconfirm = $_POST['passconfirm'];
  $email = $_POST['email'];
  $ip = $_SERVER['REMOTE_ADDR'];
  $geslacht = $_POST['geslacht'];
  $steden = Array("Brussel","Leuven","Gent","Brugge","Hasselt","Antwerpen","Amsterdam","Enschede");
  $rstad = rand(0,7);
  $stad = "$steden[$rstad]";
  $ipexist = mysql_query("SELECT * FROM `users` WHERE `ip`='{$ip}' AND `status`='levend'");
  $ipexist = mysql_num_rows($ipexist);
  $ipallowed = mysql_query("SELECT * FROM `multiple` WHERE `ip`='{$ip}'");
  $ipallowed = mysql_num_rows($ipallowed); 
  $exist = mysql_query("SELECT * FROM `users` WHERE `login`='{$gebruiker}'");
  $exist = mysql_num_rows($exist);
  $eexist = mysql_query("SELECT * FROM `users` WHERE `login`='{$gebruiker}' AND `status`='levend'");
  $eexist = mysql_num_rows($eexist);
  $rexist = mysql_query("SELECT * FROM `users` WHERE `id`='{$refer}'");
  $rexists = mysql_fetch_object($rexist);
  $rexist = mysql_num_rows($rexist);
  $referlogin = $rexists->login;
  if(preg_match('/^[a-zA-Z0-9_\-]+$/', $gebruiker) == 0) { print "De opgegeven gebruikersnaam is ongeldig, je mag enkel letters of cijfers gebruiken.\n"; }
  elseif(!$pass || $pass != $passconfirm) { print "De opgegeven wachtwoorden zijn niet identiek.\n"; }
  elseif(preg_match('/^.+@.+\..+$/',$email) == 0) { print "Het opgegeven e-mailadres is ongeldig.\n"; }
  elseif($ipexist == 1 && $ipallowed != 1) { print "Er is al een account gemaakt op dit IP adres.\n"; }
  elseif($exist == 1) { print "De opgegeven gebruikersnaam is al in gebruik.\n"; }
  elseif($eexist == 1) { print "Er is al iemand aangemeld met dit e-mailadres.\n"; }
  elseif($rexist != 1 && $refer!= "") { print "De opgegeven referrerID bestaat niet.\n"; }
  else {
        $code          = rand(100000,999999);
        mysql_query("INSERT INTO `users`(`start`,`login`,`pass`,`ip`,`email`,`stad`,`geslacht`,`activated`) values(NOW(),'{$gebruiker}',MD5('{$pass}'),'{$ip}','{$email}','{$stad}','{$geslacht}','1')");
        mysql_query("INSERT INTO `temp`(login,ip,code,area,time,forwardedFor) values('$gebruiker','$ip',$code,'signup',NOW(),'$referlogin')"); 
        $id          = mysql_insert_id();
      mail($email,"Vendetta activatie-code","Hallo $gebruiker,\n\nBedankt voor het aanmelden op Vendetta (game 3-logd.nl).\nKlik hier om je account te activeren:\nhttp://logd.nl/game3/register.php?id=$id&code=$code","From: game3-logd.nl <noreply@logd.nl>\n"); 
	  //print "Je bent geregistreerd, je kan je nu aanmelden <br><a href='login.php'>Login</a>";
	  //mail($email,"Vendetta registratie","Hallo $gebruiker,\n\nBedankt voor het aanmelden op Vendetta.\n Je gebruikersnaam: $gebruiker \n Je wachtwoord: $pass","From: Vendetta <noreply@vendetta.com>\n"); 
	  print "Je bent geregistreerd, er is een e-mail gestuurd naar $email met een activatie-code...\n";
        
      }
}
$refer = $_GET['refer'];
 print <<<ENDHTML
   <form method="post">
        <input type="hidden" name="csrf_token" value="{$_SESSION['csrf_token']}">
        <table width="100%">
          <tr> 
            <td width="49%"><div align="right">Login:</div></td>
            <td width="2%">&nbsp; </td>
            <td width="49%"><input type="text" name="gebruiker" maxlength=16 width="150"></td>
          </tr>
          <tr> 
            <td width="49%"><div align="right">Wachtwoord:</div></td>
            <td width="2%">&nbsp;</td>
            <td width="49%"><input type="password" name="pass" maxlength=12 width="150"></td>
          </tr>
          <tr> 
            <td width="49%"><div align="right">Herhaal:</div></td>
            <td width="2%">&nbsp;</td>
            <td width="49%"><input type="password" name="passconfirm" maxlength=12 width="150"></td>
          </tr>
          <tr> 
            <td width=431><div align="right">E-mail:</div></td>
            <td>&nbsp;</td>
            <td><input type="text" name="email" maxlength=64 width="150"></td>
          </tr>
          <tr> 
            <td width=431><div align="right">Geslacht:</div></td>
            <td>&nbsp;</td>
            <td><select name="geslacht" width="150">
                <option value="Man">Man</option>
                <option value="Vrouw">Vrouw</option>
              </select></td>
          </tr>
		  <tr> 
            <td width=431><div align="right">ReferrerID (optioneel):</div></td>
            <td>&nbsp;</td>
            <td><input type="text" name="refer" value="$refer" maxlength=64 width="150"></td>
          </tr>          <tr> 
            <td></td>
            <td></td>
            <td>
                <input type="submit" name="submit" with="100" value="Aanmelden">
            </td>
          </tr>
        </table>
      </form>
</td></tr>

</table>
</body>
</html>
ENDHTML;
?>