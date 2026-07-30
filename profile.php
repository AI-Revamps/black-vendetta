<?php
  include('config.php');
  $dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");
  $data	= mysql_fetch_object($dbres);  
  if(! check_login()) {
    header('Location: login.php');
    exit;
  }
if ($data->bf == 0) { $hbf = Geen; }
elseif ($data->bf == 1) { $hbf = "De Helft"; }
elseif ($data->bf == 2) { $hbf = "Zelfde Aantal"; }
elseif ($data->bf == 3) { $hbf = "Dubbele Aantal"; }
else { $hbf = $data->bf; }
?>
<html>
<head>
<title>Vendetta</title>
<link rel="stylesheet" type="text/css" href="style.css">
<meta name="keywords" content="Vendetta,Crimegame,crimegame,vendetta">
<meta name="language" content="english">
<META name="description" lang="nl" content="Vendetta crimegame met pit.">
<script language="javascript">
function textCounter( field, countfield, maxlimit ) { if ( field.value.length > maxlimit ) { field.value = field.value.substring( 0, maxlimit ); return false; }
else { countfield.value = maxlimit - field.value.length; } } </script>
</head>
<?php /* ------------------------- */
print "<table width=100%><tr> 
    <td class=subTitle><b>Profiel:</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
  if(isset($_POST['profile'])) {
	$info = $_POST['info'];
    mysql_query("UPDATE `users` SET `info`='{$info}' WHERE `login`='{$data->login}'");
    mysql_query("UPDATE `users` SET `pic`='{$_POST['pic']}' WHERE `login`='{$data->login}'");
    echo "Je profiel is veranderd.<br><br><meta http-equiv=Refresh content=0;url=profile.php>";
  }
    elseif($_POST['testa']) {
$tname = strtolower($_POST['testament']);
$zelf = strtolower($data->login);
$exis = mysql_query("SELECT * FROM `users` WHERE `login`='{$tname}'");
$exist = mysql_num_rows($exis);
if ($tname == $zelf) { echo "Je kan jezelf niet in je testament zetten.<br><br>";}
elseif ($exist == 0) { echo "Deze gebruiker bestaat niet.<br><br>"; }
else {
      mysql_query("UPDATE `users` SET `testament`='{$tname}' WHERE `login`='{$data->login}'");
      echo "Je testament is veranderd.<meta http-equiv=Refresh content=0;url=profile.php><br><br>"; 
		}
    }
elseif($_POST['friend']) {
$fname = strtolower($_POST['friends']);
$zelf = strtolower($data->login);
$exis = mysql_query("SELECT * FROM `users` WHERE `login`='{$fname}'");
$exist = mysql_num_rows($exis);
$bl = mysql_query("SELECT * FROM `friends` WHERE `login`='{$data->login}'");
$bla = mysql_num_rows($bl);
$bl = mysql_query("SELECT * FROM `friends` WHERE `login`='{$data->login}' AND `friend`='{$fname}'");
$alr = mysql_num_rows($bl);
if ($fname == $zelf) { echo "Je kan jezelf niet in je vriendenlijst zetten.<br><br>";}
elseif ($exist == 0) { echo "Deze gebruiker bestaat niet.<br><br>"; }
elseif ($alr != 0) { echo "Deze gebruiker staat al in je vriendenlijst.<br><br>"; }
elseif ($bla >= 20) { echo "Je kan maximaal 20 gebruikers in je vriendenlijst zetten.<br><br>"; }
else {
      mysql_query("INSERT INTO `friends`(`login`,`friend`) values('{$data->login}','{$fname}')");
      echo "Deze persoon is aan je vriendenlijst toegevoegd.<br><br>"; 
		}
    }
elseif(isset($_GET['delfr'])) {
mysql_query("DELETE FROM `friends` WHERE `friend`='{$_GET['delfr']}' AND `login`='{$data->login}'"); echo "Deze persoon is uit je vriendenlijst verwijderd.";
    }
elseif(isset($_GET['addfr'])) {
$fname = strtolower($_GET['addfr']);
$zelf = strtolower($data->login);
$exis = mysql_query("SELECT * FROM `users` WHERE `login`='{$fname}'");
$exist = mysql_num_rows($exis);
$bl = mysql_query("SELECT * FROM `friends` WHERE `login`='{$data->login}'");
$bla = mysql_num_rows($bl);
$bl = mysql_query("SELECT * FROM `friends` WHERE `login`='{$data->login}' AND `friend`='{$fname}'");
$alr = mysql_num_rows($bl);
if ($fname == $zelf) { echo "Je kan jezelf niet in je vriendenlijst zetten.<br><br>";}
elseif ($exist == 0) { echo "Deze gebruiker bestaat niet.<br><br>"; }
elseif ($alr != 0) { echo "Deze gebruiker staat al in je vriendenlijst.<br><br>"; }
elseif ($bla >= 20) { echo "Je kan maximaal 20 gebruikers in je vriendenlijst zetten.<br><br>"; }
else {
 mysql_query("INSERT INTO `friends`(`login`,`friend`) values('{$data->login}','{$_GET['addfr']}')"); echo "Deze persoon is aan je vriendenlijst toegevoegd.";
    }
}
elseif($_POST['subkogels']) {
	if ($_POST['standaard'] == ander && preg_match('/^[0-9]+$/',$_POST['anders'])) { 
		if ($_POST['anders'] < 100) { echo "Het minimum kogels is 100.<br><br>";}
		elseif ($_POST['anders'] > $data->kogels) { echo "Zoveel kogels heb je niet.<br><br>"; }
		else {
		      mysql_query("UPDATE `users` SET `bf`='{$_POST['anders']}' WHERE `login`='{$data->login}'"); echo "Je backfire is veranderd.<br><br><meta http-equiv=Refresh content=0;url=profile.php>";
		}
	}	
	else { mysql_query("UPDATE `users` SET `bf`='{$_POST['standaard']}' WHERE `login`='{$data->login}'"); echo "Je backfire is veranderd.<br><br><meta http-equiv=Refresh content=0;url=profile.php>"; }
}
  else if(isset($_POST['password'])) {
 $pass                = $_POST['oldpw'];

    if($data->pass != MD5($pass)){
      echo "Fout wachtwoord.<br><br>"; 
    }
    elseif($_POST['pass'] != "" && $_POST['pass'] == $_POST['confirm']) {
      mysql_query("UPDATE `users` SET `pass`=MD5('{$_POST['pass']}') WHERE `login`='{$data->login}'");
      unset($_SESSION['login']); echo "<script language=\"javascript\">setTimeout('parent.window.location.reload()',0)</script>"; exit;
    }
    else
      echo "De twee wachtwoorden waren niet identiek.<br><br>"; 
}
  $data->info				= stripslashes($data->info);
  print <<<ENDHTML
	<form method="post"><table align="center">
	  <tr><td width=100>Referrerlink: <a href=help.php#refer>[?]</a></td>	<td><a href=http://www.vendettagame.be/register.php?refer={$data->id}>http://www.vendettagame.be/register.php?refer={$data->id}</a></td></tr>
	  <tr><td width=100>E-Mail:</td>	<td>{$data->email}</td></tr>
 	  <tr><td width=100>Plaatje:</td>	<td><input type=text name=pic value='$data->pic' maxlength=255></td></tr>
	  <tr><td width=100 valign="top">Info:</td>
						<td><TEXTAREA NAME=info ROWS=10	COLS=50 onkeypress=textCounter(this,this.form.counter,999);>$data->info</TEXTAREA></td></tr>
	  <tr><td></td><td align="right"><input type="submit" name="profile" value="Verander"></td></tr>
	</table></form>
  </td></tr>
<tr>
          <td>&nbsp;</td>
        </tr>
  <tr> 
    <td class="subTitle"><b>Wachtwoord</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
	<form method="post"><table width=100% align="center">
	  <tr><td width=100>Oud:</td>		<td><input type="password" name="oldpw" maxlength=16></td></tr>
	  <tr><td width=100>Nieuw:</td>		<td><input type="password" name="pass" maxlength=16></td></tr>
	  <tr><td width=100>Herhaal:</td>	<td><input type="password" name="confirm" maxlength=16></td></tr>
	  <tr><td></td>				<td align="right"><input type="submit" name="password" value="Verander"></td></tr>
	</table></form>
  </td></tr>
  <tr>
          <td>&nbsp;</td>
        </tr><tr> 
    <td class="subTitle"><b>Testament</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
	<form method="post"><table width=100% align="center">
<b>In jou testament: $data->testament</b><br>
	  <tr><td width=100>Testament:</td>		<td><input type="text" name="testament" maxlength=16></td></tr>
	  <tr><td></td>				<td align="right"><input type="submit" name="testa" value="Verander"></td></tr>
	</table></form>
  </td></tr>
  <tr>
          <td>&nbsp;</td>
        </tr><tr> 
    <td class="subTitle"><b>Backfire</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
	<form method="post"><table width=100% align="center">
<b>Vul hier je backfire in.</b><br>
	  <tr><td width=100>Huidig Aantal kogels:</td>		<td>{$hbf}</td></tr>
	  <tr><td width=100><select name=standaard><option value=0>Geen</option><option value=1>De helft</option><option value=2>Zelfde aantal kogels</option><option value=3>Dubbele aantal kogels</option><option value=ander>Anders...</option></td><td><input type=text name=anders maxlength=5></td></tr>
	  <tr><td></td>				<td align="right"><input type="submit" name="subkogels" value="Verander"></td></tr>
	</table></form>
  </td></tr>
  <tr>
          <td>&nbsp;</td>
        </tr><tr> 
    <td class="subTitle"><b>Vrienden</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
<b>Voeg hier mensen toe aan je vriendenlijst. Je mag maar 20 mensen in je vriendenlijst zetten.</b><br>
	<form method="post"><table width=100% align="center">
	  <tr><td width=100>Voeg toe:</td>		<td><input type="text" name="friends" maxlength=16></td></tr>
	  <tr><td></td>				<td align="right"><input type="submit" name="friend" value="Voeg toe"></td></tr>
	</table></form><br><br>
ENDHTML;
print"Volgende spelers staan al in je vriendenlijst.<br><br>";
$fre = mysql_query("SELECT * FROM `friends` WHERE `login`='{$data->login}'");
$nr = mysql_num_rows($fre);
if ($nr == 0) {	  echo"* Geen *";}
else{
	  while($fr = mysql_fetch_object($fre)) {
		print "\n $fr->friend &nbsp;&nbsp;<a href=user.php?x=$fr->friend>Profiel</a>&nbsp;&nbsp;&nbsp;<a href=profile.php?delfr=$fr->friend>Delete</a><br>";
      }
	 }
print"</td></tr>";

/* ------------------------- */ ?>
</table>
</body>
</html>
</table>
<br>