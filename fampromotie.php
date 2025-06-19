<?php
  include("config.php");
$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");  
$data    = mysql_fetch_object($dbres);
  if(! check_login()) {
    header('Location: login.php');
    exit;
  }
if ($data->famrang != 5 && $data->famrang !=3) { exit; }
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
  <tr> 
    <td class="subTitle"><b>Promotiegeld</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
<?php
$dbres = mysql_query("SELECT * FROM `famillie` WHERE `name`='{$data->famillie}'");
$famillie = mysql_fetch_object($dbres);
if(isset($_POST['submit']) && $_POST['rang2'] >= 0 && $_POST['rang3'] >= 0 && $_POST['rang4'] >= 0 && $_POST['rang5'] >= 0 && $_POST['rang6'] >= 0 && $_POST['rang7'] >= 0 && $_POST['rang8'] >= 0 && $_POST['rang9'] >= 0 && $_POST['rang10'] >= 0 && $_POST['rang11'] >= 0 && $_POST['rang12'] >= 0 && $_POST['rang13'] >= 0 && $_POST['rang14'] >= 0){
  mysql_query("UPDATE `famillie` SET 
  `rang2`='{$_POST['rang2']}',
  `rang3`='{$_POST['rang3']}',
  `rang4`='{$_POST['rang4']}',
  `rang5`='{$_POST['rang5']}',
  `rang6`='{$_POST['rang6']}',
  `rang7`='{$_POST['rang7']}',
  `rang8`='{$_POST['rang8']}',
  `rang9`='{$_POST['rang9']}',
  `rang10`='{$_POST['rang10']}',
  `rang11`='{$_POST['rang11']}',
  `rang12`='{$_POST['rang12']}',
  `rang13`='{$_POST['rang13']}',
  `rang14`='{$_POST['rang14']}' 
  WHERE `name`='{$data->famillie}'")or die(mysql_error());
   echo "Promotiegeld deupdate. Een ogenblik geduld.";
   print "<meta http-equiv=Refresh content=3;url=fampromotie.php>";
   exit;
}
echo"Vul hier het promotiegeld in.<br><br>
<form method=\"post\"><table> 
  <tr><td width=110><b>Rang:</b></td><td><b>Bedrag:</b></td></tr>
  <tr><td width=110>Deliveryboy</td>                <td><input type=\"text\" name=\"rang2\" value=\"{$famillie->rang2}\"></td></tr>
  <tr><td width=110>Picciotto</td>                  <td><input type=\"text\" name=\"rang3\" value=\"{$famillie->rang3}\"></td></tr>
  <tr><td width=110>Shoplifter</td>                 <td><input type=\"text\" name=\"rang4\" value=\"{$famillie->rang4}\"></td></tr>
  <tr><td width=110>Pickpocket</td>                 <td><input type=\"text\" name=\"rang5\" value=\"{$famillie->rang5}\"></td></tr>
  <tr><td width=110>Thief</td>                      <td><input type=\"text\" name=\"rang6\" value=\"{$famillie->rang6}\"></td></tr>
  <tr><td width=110>Associate</td>                  <td><input type=\"text\" name=\"rang7\" value=\"{$famillie->rang7}\"></td></tr>
  <tr><td width=110>Mobster</td>                    <td><input type=\"text\" name=\"rang8\" value=\"{$famillie->rang8}\"></td></tr>
  <tr><td width=110>Soldier</td>                    <td><input type=\"text\" name=\"rang9\" value=\"{$famillie->rang9}\"></td></tr>
  <tr><td width=110>Swindler</td>                   <td><input type=\"text\" name=\"rang10\" value=\"{$famillie->rang10}\"></td></tr>
  <tr><td width=110>Assassin</td>                   <td><input type=\"text\" name=\"rang11\" value=\"{$famillie->rang11}\"></td></tr>
  <tr><td width=110>Local Chief</td>                <td><input type=\"text\" name=\"rang12\" value=\"{$famillie->rang12}\"></td></tr>
  <tr><td width=110>Chief</td>                      <td><input type=\"text\" name=\"rang13\" value=\"{$famillie->rang13}\"></td></tr>
  <tr><td width=110>Bruglione</td>                  <td><input type=\"text\" name=\"rang14\" value=\"{$famillie->rang14}\"></td></tr>
  </table><input type=submit name=submit value=Ok></form>
  ";
?>
<body>

</body>
</html>
