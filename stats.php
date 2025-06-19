<?php
  include("config.php");
$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");  
$data    = mysql_fetch_object($dbres);
  if(! check_login()) {
    header("Location: login.php");
    exit;
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
<?php
$dbres = mysql_query("SELECT * FROM `users` WHERE `activated`='1'");
$gebruikers = mysql_num_rows($dbres);
$dbres = mysql_query("SELECT * FROM `users` WHERE `status`='levend' AND `activated`='1'");
$levend = mysql_num_rows($dbres);
$dbres = mysql_query("SELECT * FROM `users` WHERE `status`='dood' AND `activated`='1'");
$dood = mysql_num_rows($dbres);

$dbres = mysql_query("SELECT * FROM `users`");
while($blah = mysql_fetch_object($dbres)){
  $cash = $cash+$blah->zak;
  $bank = $bank+$blah->bank;
  $kogel = $kogel+$blah->kogels;
}
$bank = number_format($bank, 0, ',' , ',');
$cash = number_format($cash, 0, ',' , ',');
$kogel = number_format($kogel, 0, ',' , ',');
$dbres = mysql_query("SELECT * FROM `users` WHERE  UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(`online`) < 300");
$online = mysql_num_rows($dbres);
$dbres = mysql_query("SELECT * FROM `stad` WHERE `stad`='Brussel'");
$brussel = mysql_fetch_object($dbres);
$sbrussel = mysql_num_rows(mysql_query("SELECT * FROM `users` WHERE `stad`='Brussel' AND `status`='levend'"));
$dbres = mysql_query("SELECT * FROM `stad` WHERE `stad`='Leuven'");
$leuven = mysql_fetch_object($dbres);
$sleuven = mysql_num_rows(mysql_query("SELECT * FROM `users` WHERE `stad`='Leuven' AND `status`='levend'"));
$dbres = mysql_query("SELECT * FROM `stad` WHERE `stad`='Gent'");
$gent = mysql_fetch_object($dbres);
$sgent = mysql_num_rows(mysql_query("SELECT * FROM `users` WHERE `stad`='Gent' AND `status`='levend'"));
$dbres = mysql_query("SELECT * FROM `stad` WHERE `stad`='Brugge'");
$brugge = mysql_fetch_object($dbres);
$sbrugge = mysql_num_rows(mysql_query("SELECT * FROM `users` WHERE `stad`='Brugge' AND `status`='levend'"));
$dbres = mysql_query("SELECT * FROM `stad` WHERE `stad`='Antwerpen'");
$antwerpen = mysql_fetch_object($dbres);
$santwerpen = mysql_num_rows(mysql_query("SELECT * FROM `users` WHERE `stad`='Antwerpen' AND `status`='levend'"));
$dbres = mysql_query("SELECT * FROM `stad` WHERE `stad`='Hasselt'");
$hasselt = mysql_fetch_object($dbres);
$shasselt = mysql_num_rows(mysql_query("SELECT * FROM `users` WHERE `stad`='Hasselt' AND `status`='levend'"));
$dbres = mysql_query("SELECT * FROM `stad` WHERE `stad`='Amsterdam'");
$amsterdam = mysql_fetch_object($dbres);
$samsterdam = mysql_num_rows(mysql_query("SELECT * FROM `users` WHERE `stad`='Amsterdam' AND `status`='levend'"));
$dbres = mysql_query("SELECT * FROM `stad` WHERE `stad`='Enschede'");
$enschede = mysql_fetch_object($dbres);
$senschede = mysql_num_rows(mysql_query("SELECT * FROM `users` WHERE `stad`='Enschede' AND `status`='levend'"));

print <<<ENDHTML
<table width=100% align=center>
  <tr> 
    <td class="subTitle"><b>Statistieken</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
  <table width=100%><tr><td align=center><b>Gebruikers</b><br><br>
<table border="0" width="39%">
	<tr>
		<td>Totaal aantal spelers</td>
		<td>$gebruikers</td>
	</tr>
	<tr>
		<td>Totaal aantal levende spelers</td>
		<td>$levend</td>
	</tr>
	<tr>
		<td>Totaal aantal dode spelers</td>
		<td>$dood</td>
	</tr>
	<tr>
		<td>Totaal aantal online spelers</td>
		<td>$online</td>
	</tr>
	<tr>
		<td>Totaal geld op zak</td>
		<td>&euro; $cash</td>
	</tr>
	<tr>
		<td>Totaal geld op de bank</td>
		<td>&euro; $bank</td>
	</tr>
	<tr>
		<td>Totaal aantal kogels</td>
		<td>$kogel</td>
	</tr>
	</td></tr></table></table>
<table align=center width=100%><tr><td><b><center>10 Laatste doden</center></b><br>
      <table align=center width=50%>
  <tr> 
        <td align=center> 
        <b>Login</b></td> 
        <td align=center> 
        <b>Tijd</b></td> 
      </tr>
ENDHTML;
$query = mysql_query("SELECT *,DATE_FORMAT(`date`,'%d-%m %H:%i') AS `time` FROM `vermoord` ORDER BY `date` DESC LIMIT 0,10");
while($q = mysql_fetch_object($query)) {
echo "<tr> 
        <td  align=center><a href=user.php?x=$q->login>$q->login</a></td> 
        <td  align=center>$q->time</td> 
</tr> ";
}
echo "</td></tr></table></table>";

print <<<ENDHTML
<table align=center width=100%><tr><td><b><center>10 meest ge&euml;erde spelers</center></b><br>
      <table align=center width=50%>
  <tr> 
        <td align=center> 
        <b>Login</b></td> 
        <td align=center> 
        <b>Respect</b></td> 
      </tr>
ENDHTML;
$query = mysql_query("SELECT login,respect FROM `users` WHERE `status`='levend' ORDER BY `respect` DESC LIMIT 0,10");
while($q = mysql_fetch_object($query)) {
$respect = round($q->respect,0);
echo "<tr> 
        <td  align=center><a href=user.php?x=$q->login>$q->login</a></td> 
        <td  align=center>$respect</td> 
</tr> ";
}
echo "</td></tr></table></table>";
print <<<ENDHTML
<table align=center width=100%><tr><td><b><center>10 minst ge&euml;erde spelers</center></b><br>
      <table align=center width=50%>
  <tr> 
        <td align=center> 
        <b>Login</b></td> 
        <td align=center> 
        <b>Respect</b></td> 
      </tr>
ENDHTML;
$query = mysql_query("SELECT login,respect FROM `users` WHERE `status`='levend' ORDER BY `respect` ASC LIMIT 0,10");
while($q = mysql_fetch_object($query)) {
$respect = round($q->respect,0);
echo "<tr> 
        <td  align=center><a href=user.php?x=$q->login>$q->login</a></td> 
        <td  align=center>$respect</td> 
</tr> ";
}
echo "</td></tr></table></table>";

print <<<ENDHTML
<table width=100%>  <tr><td><center><b>Steden</b><br><br><table width=100%>
<table border="0" width="39%">
	<tr>
		<td><b>Stad</b></td>
		<td><b>Drugs</b></td>
		<td><b>Drank</b></td>
		<td><b>Kogels</b></td>
		<td><b>Spelers</b></td>
	</tr>
	<tr>
		<td>Brussel</td>
		<td>$brussel->drugs</td>
		<td>$brussel->drank</td>
		<td>$brussel->kogels</td>
		<td>$sbrussel</td>
	</tr>
	<tr>
		<td>Leuven</td>
		<td>$leuven->drugs</td>
		<td>$leuven->drank</td>
		<td>$leuven->kogels</td>
		<td>$sleuven</td>
	</tr>
	<tr>
		<td>Gent</td>
		<td>$gent->drugs</td>
		<td>$gent->drank</td>
		<td>$gent->kogels</td>
		<td>$sgent</td>
	</tr>
	<tr>
		<td>Brugge</td>
		<td>$brugge->drugs</td>
		<td>$brugge->drank</td>
		<td>$brugge->kogels</td>
		<td>$sbrugge</td>
	</tr>
	<tr>
		<td>Antwerpen</td>
		<td>$antwerpen->drugs</td>
		<td>$antwerpen->drank</td>
		<td>$antwerpen->kogels</td>
		<td>$santwerpen</td>
	</tr>
	<tr>
		<td>Hasselt</td>
		<td>$hasselt->drugs</td>
		<td>$hasselt->drank</td>
		<td>$hasselt->kogels</td>
		<td>$shasselt</td>
	</tr>
        <tr>
		<td>Amsterdam</td>
		<td>$amsterdam->drugs</td>
		<td>$amsterdam->drank</td>
		<td>$amsterdam->kogels</td>
		<td>$samsterdam</td>
	</tr>
	</tr>
        <tr>
		<td>Enschede</td>
		<td>$enschede->drugs</td>
		<td>$enschede->drank</td>
		<td>$enschede->kogels</td>
		<td>$senschede</td>
	</tr>
	</table>
	<br><br>
</table>
<table align=center width=100%><tr><td><center><br><b>Nummer raden</b><br><br>
      <table align=center width=100%>
  <tr> 
        <td align=center> 
        <b>Stad</b></td> 
        <td align=center> 
        <b>Eigenaar</b></td> 
                <td align=center> 
        <b>Winst</b></td>
		<td align=center> 
        <b>Inzet</b></td>
      </tr>
ENDHTML;
$query = "SELECT * FROM casino WHERE `spel`='guess' ORDER BY `stad`"; 
$info = mysql_query($query) or die(mysql_error()); 
$count = 0; 
while ($gegeven = mysql_fetch_array($info)) { 
$winst = $gegeven["winst"]; 
$eigenaar = $gegeven["owner"]; 
$stad = $gegeven["stad"]; 
$inzet = $gegeven["inzet"]; 
$count++; 
if ($winst < 0) { $winst = "<font color=red><b>$winst</b></font>"; }
else { $winst = "<font color=009900><b>$winst</b></font>"; }
if (!$eigenaar) { $eigenaar = "<i>Te koop</i>"; }
else { $eigenaar = "<a href=user.php?x={$eigenaar}>$eigenaar</a>"; }
print <<<ENDHTML

<tr> 
        <td  align=center>$stad</td> 
        <td  align=center>$eigenaar</td> 
                <td align=center>&euro;{$winst}</td> 
				<td align=center>&euro;{$inzet}</td> 
ENDHTML;
}
print <<<ENDHTML
</td></tr></table><table align=center width=100%><tr><td><br><center><b>Blackjack</b><br><br>
      <table align=center width=100%>
  <tr> 
        <td align=center> 
        <b>Stad</b></td> 
        <td align=center> 
        <b>Eigenaar</b></td> 
                <td align=center> 
        <b>Winst</b></td>
		<td align=center> 
        <b>Inzet</b></td>
      </tr>
ENDHTML;
$query = "SELECT * FROM casino WHERE `spel`='blackjack' ORDER BY `stad`"; 
$info = mysql_query($query) or die(mysql_error()); 
$count = 0; 
while ($gegeven = mysql_fetch_array($info)) { 
$winst = $gegeven["winst"]; 
$eigenaar = $gegeven["owner"]; 
$stad = $gegeven["stad"];
$inzet = $gegeven["inzet"];  
$count++; 
if ($winst < 0) { $winst = "<font color=red><b>$winst</b></font>"; }
else { $winst = "<font color=009900><b>$winst</b></font>"; }
if (!$eigenaar) { $eigenaar = "<i>Te koop</i>"; }
else { $eigenaar = "<a href=user.php?x={$eigenaar}>$eigenaar</a>"; }
print <<<ENDHTML

<tr> 
        <td  align=center>$stad</td> 
        <td  align=center>$eigenaar</td> 
                <td align=center>&euro;{$winst}</td> 
				<td align=center>&euro;{$inzet}</td> 
				
ENDHTML;
}
print <<<ENDHTML
</td></tr></table><table align=center width=100%><tr><td><br><center><b>Fruitmachine</b><br><br>
      <table align=center width=100%>
  <tr> 
        <td align=center> 
        <b>Stad</b></td> 
        <td align=center> 
        <b>Eigenaar</b></td> 
                <td align=center> 
        <b>Winst</b></td>
		<td align=center> 
        <b>Inzet</b></td>
      </tr>
ENDHTML;
$query = "SELECT * FROM casino WHERE `spel`='fruitmachine' ORDER BY `stad`"; 
$info = mysql_query($query) or die(mysql_error()); 
$count = 0; 
while ($gegeven = mysql_fetch_array($info)) { 
$winst = $gegeven["winst"]; 
$eigenaar = $gegeven["owner"]; 
$stad = $gegeven["stad"]; 
$inzet = $gegeven["inzet"]; 
$count++; 
if ($winst < 0) { $winst = "<font color=red><b>$winst</b></font>"; }
else { $winst = "<font color=009900><b>$winst</b></font>"; }
if (!$eigenaar) { $eigenaar = "<i>Te koop</i>"; }
else { $eigenaar = "<a href=user.php?x={$eigenaar}>$eigenaar</a>"; }
print <<<ENDHTML

<tr> 
        <td  align=center>$stad</td> 
        <td  align=center>$eigenaar</td> 
                <td align=center>&euro;{$winst}</td> 
				<td align=center>&euro;{$inzet}</td> 
ENDHTML;
}
print <<<ENDHTML
</td></tr></table><table align=center width=100%><tr><td><br><center><b>Roulette</b><br><br>
      <table align=center width=100%>
  <tr> 
        <td align=center> 
        <b>Stad</b></td> 
        <td align=center> 
        <b>Eigenaar</b></td> 
                <td align=center> 
        <b>Winst</b></td>
		<td align=center> 
        <b>Inzet</b></td>
      </tr>
ENDHTML;
$query = "SELECT * FROM casino WHERE `spel`='roulette' ORDER BY `stad`"; 
$info = mysql_query($query) or die(mysql_error()); 
$count = 0; 
while ($gegeven = mysql_fetch_array($info)) { 
$winst = $gegeven["winst"]; 
$eigenaar = $gegeven["owner"]; 
$stad = $gegeven["stad"]; 
$inzet = $gegeven["inzet"]; 
$count++; 
if ($winst < 0) { $winst = "<font color=red><b>$winst</b></font>"; }
else { $winst = "<font color=009900><b>$winst</b></font>"; }
if (!$eigenaar) { $eigenaar = "<i>Te koop</i>"; }
else { $eigenaar = "<a href=user.php?x={$eigenaar}>$eigenaar</a>"; }
print <<<ENDHTML

<tr> 
        <td  align=center>$stad</td> 
        <td  align=center>$eigenaar</td> 
                <td align=center>&euro;{$winst}</td> 
				<td align=center>&euro;{$inzet}</td> 
ENDHTML;
}
echo "</td></tr></table><table align=center width=100%><tr><td><br><center><b>Kogelfabrieken</b><br><br>
      <table align=center width=100%>
  <tr> 
        <td align=center> 
        <b>Stad</b></td> 
        <td align=center> 
        <b>Eigenaar</b></td> 
        <td align=center> 
        <b>Kogels</b></td>
		<td align=center> 
        <b>Prijs</b></td>
      </tr>";
$query = "SELECT winst,spel,owner,stad,inzet FROM casino WHERE `spel`='kogelfabriek' ORDER BY `stad`"; 
$info = mysql_query($query) or die(mysql_error()); 
$count = 0; 
while ($gegeven = mysql_fetch_array($info)) { 
$winst = $gegeven["winst"]; 
$prijs = $gegeven["inzet"]; 
$eigenaar = $gegeven["owner"]; 
$stad = $gegeven["stad"]; 
$count++; 
if (!$eigenaar) { $eigenaar = "<i>Te koop</i>"; }
else { $eigenaar = "<a href=user.php?x={$eigenaar}>$eigenaar</a>"; }
print <<<ENDHTML

<tr> 
        <td  align=center>$stad</td> 
        <td  align=center>$eigenaar</td> 
        <td align=center>$winst</td> 
		<td  align=center>&euro; $prijs</td> 
ENDHTML;
}
echo "</td></tr></table>";
?>