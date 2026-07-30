<?PHP
include("config.php");
  $dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");
  $data	= mysql_fetch_object($dbres);
if ($data->level < 200) { exit; }
?>
<html>
<head>
<title>Vendetta</title>
<link rel="stylesheet" type="text/css" href="style.css">
<meta name="keywords" content="Vendetta,Crimegame,crimegame,vendetta">
<meta name="language" content="english">
<META name="description" lang="nl" content="Vendetta crimegame met pit.">
</head>
<table align=center width=100%>
  <tr> 
    <td class="subTitle"><b>Wall Of Shame</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
<?
if ($data->level < 1000) {echo"Je hebt niet genoeg rechten";exit;}
if (isset($_GET['del'])) { mysql_query("DELETE FROM `shame` WHERE `id`='{$_GET['del']}'")or die (mysql_error()); echo "Verwijderd"; }

$dbres	= mysql_query("SELECT `login` FROM `users` WHERE `status`='levend' ORDER BY `login`")or die (mysql_error());
      echo "<form method=\"post\">Speler: <select name=\"p\">";
     while($person = mysql_fetch_object($dbres)) {
        
        print "	<option value=\"{$person->login}\">{$person->login}</option>\n";}
        print "  </select> <br><input type=text name=com value=Reden><br><input type=\"submit\" name=\"post\" value=\"Spijker op schandpaal\"></form><br><br>";
		
		
if ($_POST['post']) {
$vict = mysql_query("SELECT * FROM `shame` WHERE `cheater`='{$_POST['p']}'")or die (mysql_error());
	$isin = mysql_num_rows($vict);
if ($isin != 0) { echo "Deze persoon hangt al aan de schandpaal."; }
elseif ($_POST['com']) { mysql_query("INSERT INTO `shame`(`com`,`time`,`cheater`,`person`) values('{$_POST['com']}',NOW(),'{$_POST['p']}','{$data->login}')")or die (mysql_error()); echo "De persoon is op de schandpaal gespijkerd!"; }
else { echo "Je moet een bericht opgeven"; }
}
print <<<ENDHTML
<table width=100%><tr>	  <td align=center><b>Persoon</b></td> 
      <td align=center><b>Tijd</b></td> 
            <td align=center><b>Reden</b></td>
			<td align=center><b>Door</b></td>
			<td align=center><b>Delete</b></td> </tr>

ENDHTML;
$query = "SELECT * FROM `shame` ORDER BY `id` ASC"; 
$info = mysql_query($query) or die(mysql_error()); 
$count = 0; 
while ($gegeven = mysql_fetch_object($info)) { 
$time = $gegeven->time; 
$cheater = $gegeven->cheater;
$com = $gegeven->com;
$door = $gegeven->person;
$id = $gegeven->id;
$count++; 
print <<<ENDHTML

<tr>      	<td align=center>$cheater</td> 
      <td align=center>$time</td> 
            <td align=center>$com</td>
			<td align=center>$door</td>
          	<td align=center><a href=adm-shame.php?del={$id}>[x]</a></td></tr>
ENDHTML;
}
?>