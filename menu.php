<?php
include("config.php");
$dbres = mysql_query("SELECT * FROM `users` WHERE `login`='{$_SESSION['login']}'");
$data = mysql_fetch_object($dbres);
?>
<html>
<head>
<title>Gangster4Crime</title>
<link rel="stylesheet" type="text/css" href="style.css">
<meta name="keywords" content="gangster4crime,Crimegame,crimegame,gangster4crime">
<meta name="language" content="english">
<META name="description" lang="nl" content="gangster4crime crimegame met pit.">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"></head><body leftmargin="0" rightmargin="0">
<base target="hoofd">
<script language="javascript">
function showMenu(id) {
  if(document.getElementById(id).style.visibility == "hidden") {
    document.getElementById(id).style.position		= "static";
    document.getElementById(id).style.visibility	= "visible";
  }
  else {
    document.getElementById(id).style.visibility	= "hidden";
    document.getElementById(id).style.position		= "absolute";
    document.getElementById(id).style.left		= -100;
    document.getElementById(id).style.top		= -100;
  }

}
</script>
<?php
print <<<ENDHTML
<table border="0" width="150">
</head>
ENDHTML;
if ($data->status == levend) {
print <<<ENDHTML
 <table width=100% cellpadding=0 cellspacing=0>
	<tr><td><table width=150 cellpadding=0 cellspacing=0>
		<tr><td class="menutitleOff" onMouseOver="className='menutitleOver';" onMouseOut="className='menutitleOff';" onClick="showMenu('status')">&nbsp;<b>Status</b></td>
			<td></td></tr>
	</table>
	<table width=150 cellpadding=0 cellspacing=0 ID="status">
	<td><table width=100% cellpadding=0 cellspacing=0 class='menu'>
	    <tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='home.php';">&nbsp;&nbsp;Status</td></tr>
		<tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='profile.php';">&nbsp;&nbsp;Profiel</td></tr>
		<tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='message.php';">&nbsp;&nbsp;Berichten</td></tr>
		<tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='respect.php';">&nbsp;&nbsp;Eerpunten</td></tr>
                <tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='wallofshame.php';">&nbsp;&nbsp;Schandpaal</td></tr>
		<tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='getmarried.php';">&nbsp;&nbsp;Trouwen</td></tr>
		<tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='stats.php';">&nbsp;&nbsp;Statistieken</td></tr>
		<tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='hitlist.php?watch';">&nbsp;&nbsp;Premielijst</td></tr>
		<tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='members.php?status=levend';">&nbsp;&nbsp;Members</td></tr>
		<tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='klikmissie.php';">&nbsp;&nbsp;klikmissie</td></tr>
		<tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='msn.php';">&nbsp;&nbsp;msn</td></tr>
		</table></td>
		</table></td></tr>
	<tr><td><table width=150 cellpadding=0 cellspacing=0>
		<tr><td class="menutitleOff" onMouseOver="className='menutitleOver';" onMouseOut="className='menutitleOff';" onClick="showMenu('build')">&nbsp;<b>Plaatsen</b></td>
			<td></td></tr>
	</table>
	<table width=150 cellpadding=0 cellspacing=0 ID="build">
	<td><table width=100% cellpadding=0 cellspacing=0 class='menu'>
		<tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='shop.php';">&nbsp;&nbsp;Winkel/Markt</td></tr>
		<tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='bank.php';">&nbsp;&nbsp;Bank</td></tr>
        <tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='mbulletfactory.php';">&nbsp;&nbsp;Locale Kogelfabriek</td></tr>
		<tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='bulletfactory.php';">&nbsp;&nbsp;Kogelfabriek</td></tr>
	    <tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='garage.php';">&nbsp;&nbsp;Garage</td></tr>
		<tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='bloodbank.php';">&nbsp;&nbsp;Bloedbank</td></tr>

		<tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='transport.php';">&nbsp;&nbsp;Transport</td></tr>
		<tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='detectives.php';">&nbsp;&nbsp;Detective bureau</td></tr>
		<tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='mshop.php';">&nbsp;&nbsp;Zwarte markt</td></tr>
		</table></td>
	</table></td></tr>
    	<tr><td><table width=150 cellpadding=0 cellspacing=0>
		<tr><td class="menutitleOff" onMouseOver="className='menutitleOver';" onMouseOut="className='menutitleOff';" onClick="showMenu('crime')">&nbsp;<b>Misdaden</b></td>
			<td></td></tr>
	</table>
	<table width=150 cellpadding=0 cellspacing=0 ID="crime">
	<td><table width=100% cellpadding=0 cellspacing=0 class='menu'>
		<tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='drank.php';">&nbsp;&nbsp;Drank</td></tr>
		<tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='drugs.php';">&nbsp;&nbsp;Drugs</td></tr>
        <tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='crime.php';">&nbsp;&nbsp;Misdaad</td></tr>
	    <tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='nickacar.php';">&nbsp;&nbsp;Auto stelen</td></tr>
		<tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='heist.php';">&nbsp;&nbsp;Route 66</td></tr>
		<tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='oc.php';">&nbsp;&nbsp;Organised Crime</td></tr>
	    <tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='carrace.php';">&nbsp;&nbsp;Race</td></tr>
		<tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='kill.php';">&nbsp;&nbsp;Moorden</td></tr>
		<tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='jail.php';">&nbsp;&nbsp;Gevangenis</td></tr>
		</table></td>
	</table></td></tr>
	<tr><td><table width=150 cellpadding=0 cellspacing=0>
		<tr><td class="menutitleOff" onMouseOver="className='menutitleOver';" onMouseOut="className='menutitleOff';" onClick="showMenu('fam')">&nbsp;<b>Familie</b></td>
			<td></td></tr>
	</table>
	<table width=150 cellpadding=0 cellspacing=0 ID="fam">
	<td><table width=100% cellpadding=0 cellspacing=0 class='menu'>
		<tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='fam.php?p=list';">&nbsp;&nbsp;Familielijst</tr></td>
		
			
ENDHTML;
if (!$data->famillie) { print "<tr><td class=\"menuOff\" onMouseOver=\"className='menuOver';\" onMouseOut=\"className='menuOff';\" onClick=\"top.frames['hoofd'].location.href='fam.php?p=new';\">&nbsp;&nbsp;Maak een familie</td></tr>"; }
else { 
print "<tr><td class=\"menuOff\" onMouseOver=\"className='menuOver';\" onMouseOut=\"className='menuOff';\" onClick=\"top.frames['hoofd'].location.href='fam.php?x={$data->famillie}';\">&nbsp;&nbsp;Familiepagina</td></tr>"; 
if ($data->famrang > 1) { print "<tr><td class=\"menuOff\" onMouseOver=\"className='menuOver';\" onMouseOut=\"className='menuOff';\" onClick=\"top.frames['hoofd'].location.href='famman.php?p=invite';\">&nbsp;&nbsp;Uitnodigen</tr></td>"; }
if ($data->famrang > 3) { print "<tr><td class=\"menuOff\" onMouseOver=\"className='menuOver';\" onMouseOut=\"className='menuOff';\" onClick=\"top.frames['hoofd'].location.href='famman.php?p=info';\">&nbsp;&nbsp;Familie info</tr></td><tr><td class=\"menuOff\" onMouseOver=\"className='menuOver';\" onMouseOut=\"className='menuOff';\" onClick=\"top.frames['hoofd'].location.href='famman.php?p=message';\">&nbsp;&nbsp;Familiebericht</tr></td><tr><td class=\"menuOff\" onMouseOver=\"className='menuOver';\" onMouseOut=\"className='menuOff';\" onClick=\"top.frames['hoofd'].location.href='famman.php?p=members';\">&nbsp;&nbsp;Members</tr></td>"; }
if ($data->famrang > 2) { print "<tr><td class=\"menuOff\" onMouseOver=\"className='menuOver';\" onMouseOut=\"className='menuOff';\" onClick=\"top.frames['hoofd'].location.href='famman.php?p=bulfac';\">&nbsp;&nbsp;Crusher</tr></td><tr><td class=\"menuOff\" onMouseOver=\"className='menuOver';\" onMouseOut=\"className='menuOff';\" onClick=\"top.frames['hoofd'].location.href='famman.php?p=log';\">&nbsp;&nbsp;Logs</tr></td><tr><td class=\"menuOff\" onMouseOver=\"className='menuOver';\" onMouseOut=\"className='menuOff';\" onClick=\"top.frames['hoofd'].location.href='famman.php?p=land';\">&nbsp;&nbsp;Koop gebied</tr></td>"; }
if ($data->famrang == 5) { print "<tr><td class=\"menuOff\" onMouseOver=\"className='menuOver';\" onMouseOut=\"className='menuOff';\" onClick=\"top.frames['hoofd'].location.href='fam.php?p=delete';\">&nbsp;&nbsp;Verwijder familie</tr></td><tr><td class=\"menuOff\" onMouseOver=\"className='menuOver';\" onMouseOut=\"className='menuOff';\" onClick=\"top.frames['hoofd'].location.href='famman.php?p=bank';\">&nbsp;&nbsp;Familiebank</tr>"; }
if ($data->famrang == 3) { print "<tr><td class=\"menuOff\" onMouseOver=\"className='menuOver';\" onMouseOut=\"className='menuOff';\" onClick=\"top.frames['hoofd'].location.href='famman.php?p=bank';\">&nbsp;&nbsp;Familiebank</tr>";}
}
print <<<ENDHTML
	</table></td>
	</table></td></tr>
	<tr><td><table width=150 cellpadding=0 cellspacing=0>
		<tr><td class="menutitleOff" onMouseOver="className='menutitleOver';" onMouseOut="className='menutitleOff';" onClick="showMenu('guess')">&nbsp;<b>Gokken</b></td>
			<td></td></tr>
	</table>
	<table width=150 cellpadding=0 cellspacing=0 ID="guess">
	<td><table width=100% cellpadding=0 cellspacing=0 class='menu'>
		<tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='guess.php';">&nbsp;&nbsp;Nummerraden</tr></td>
	    <tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='roulette.php';">&nbsp;&nbsp;Roulette</tr></td>
            <tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='blackjack.php';">&nbsp;&nbsp;Blackjack</tr></td>
	    <tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='krassen.php';">&nbsp;&nbsp;Krassen</tr></td>
	    <tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='slots.php';">&nbsp;&nbsp;Fruitmachine</tr></td>
	    </table></td>
	</table></td></tr>
ENDHTML;
if ($data->level >= 200) {
print <<<ENDHTML
	<tr><td><table width=150 cellpadding=0 cellspacing=0>
		<tr><td class="menutitleOff" onMouseOver="className='menutitleOver';" onMouseOut="className='menutitleOff';" onClick="showMenu('admin')">&nbsp;<b>Admin</b></td>
			<td></td></tr>
	</table>
	<table width=150 cellpadding=0 cellspacing=0 ID="admin">
	<td><table width=100% cellpadding=0 cellspacing=0 class='menu'>
	    <tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='adm-search.php';">&nbsp;&nbsp;Zoeken</tr></td>
	    <tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='adm-online.php';">&nbsp;&nbsp;Online</tr></td>
	    <tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='adm-addnews.php';">&nbsp;&nbsp;Nieuws</tr></td>
	    <tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='adm-ban.php';">&nbsp;&nbsp;Bannen</tr></td>
	    <tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='adm-addmulti.php';">&nbsp;&nbsp;Multiple Accounts</tr></td>
	    <tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='adm-msg.php';">&nbsp;&nbsp;Admin bericht</tr></td>
            <tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='adm-bo.php';">&nbsp;&nbsp;Userstats</tr></td>
	    <tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='adm-drdrpr.php';">&nbsp;&nbsp;Drank & Drugs</tr></td>
	    <tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='adm-prison.php';">&nbsp;&nbsp;Gevangenis</tr></td>
	    <tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='adm-items.php';">&nbsp;&nbsp;Items</tr></td>
            <tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='adm-shame.php';">&nbsp;&nbsp;Wall of Shame</tr></td>
            <tr><td class="menuOff" onMouseOver="className='menuOver';" onMouseOut="className='menuOff';" onClick="top.frames['hoofd'].location.href='adm-poll.php';">&nbsp;&nbsp;Poll</tr></td>
            </table></td>
	</table></td></tr>

ENDHTML;
}
print "	<tr><td>&nbsp;</tr></td><tr><td><form method=POST action=red.php>&nbsp;&nbsp;<input size=16 type=text maxlength=16 name=zoek></form></td></tr>";
}
?>