<?php
  include("config.php");
  $dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");
  $data	= mysql_fetch_object($dbres);  
  if(! check_login()) {
    print"<link rel=stylesheet type=text/css href=style.css>";exit;
  }
if ($data->status == dood) { print"<link rel=stylesheet type=text/css href=style.css>";exit; }
?>
<html>
<head>
<title>Vendetta</title>
<link href="style.css" rel="stylesheet" type="text/css">
<meta name="keywords" content="Vendetta,Crimegame,crimegame,vendetta">
<meta name="language" content="english">
<META name="description" lang="nl" content="Vendetta crimegame met pit.">
</head>

<body>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td class="subTitle"><b>Status:</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt"><table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td>
<?php
if ($data->login) {
$bla = mysql_query("SELECT * FROM `users` WHERE `status`='levend' AND `activated`='1' ORDER BY `xp` DESC");
$aantal = mysql_num_rows($bla);
$in = 1;
while($blah = mysql_fetch_object($bla)) {
if ($blah->login == $data->login) { if ($in > 10000) { $i = "10000+"; } echo "<b><center>Positie: {$in}</center></b></td></tr><tr><td>"; }
$in++;
}
$bla = mysql_query("SELECT * FROM `users` WHERE `status`='levend' ORDER BY `se` DESC LIMIT 0,1");
$blah = mysql_fetch_object($bla);
if ($blah->login == $data->login) { echo "Hoogste ks.</td></tr><tr><td>"; }
$bla = mysql_query("SELECT * FROM `users` WHERE `status`='levend' ORDER BY `bank` DESC LIMIT 0,1");
$blah = mysql_fetch_object($bla);
if ($blah->login == $data->login) { echo "Rijkste.</td></tr><tr><td>"; }
$bla = mysql_query("SELECT * FROM `users` WHERE `status`='levend' ORDER BY `respect` DESC LIMIT 0,1");
$blah = mysql_fetch_object($bla);
if ($blah->login == $data->login) { echo "Meeste respectpunten.</td></tr><tr><td>"; }
$bla = mysql_query("SELECT * FROM `users` WHERE `status`='levend' ORDER BY `kogels` DESC LIMIT 0,1");
$blah = mysql_fetch_object($bla);
if ($blah->login == $data->login) { echo "Meeste kogels.</td></tr><tr><td>"; }
$bla = mysql_query("SELECT * FROM `users` WHERE `status`='levend' ORDER BY `bo` DESC LIMIT 0,1");
$blah = mysql_fetch_object($bla);
if ($blah->login == $data->login) { echo "Meeste uitbraken.</td></tr><tr><td>"; }
$bla = mysql_query("SELECT * FROM `users` WHERE `status`='levend' ORDER BY `nrofcrime` DESC LIMIT 0,1");
$blah = mysql_fetch_object($bla);
if ($blah->login == $data->login) { echo "Meeste misdaden.</td></tr><tr><td>"; }
$bla = mysql_query("SELECT * FROM `users` WHERE `status`='levend' ORDER BY `nrofcar` DESC LIMIT 0,1");
$blah = mysql_fetch_object($bla);
if ($blah->login == $data->login) { echo "Meeste auto stelen.</td></tr><tr><td>"; }
$bla = mysql_query("SELECT * FROM `users` WHERE `status`='levend' ORDER BY `nrofroute` DESC LIMIT 0,1");
$blah = mysql_fetch_object($bla);
if ($blah->login == $data->login) { echo "Meeste route 66.</td></tr><tr><td>"; }
$bla = mysql_query("SELECT * FROM `users` WHERE `status`='levend' ORDER BY `nrofoc` DESC LIMIT 0,1");
$blah = mysql_fetch_object($bla);
if ($blah->login == $data->login) { echo "Meeste oc's.</td></tr><tr><td>"; }
$bla = mysql_query("SELECT * FROM `users` WHERE `status`='levend' ORDER BY `nrofrace` DESC LIMIT 0,1");
$blah = mysql_fetch_object($bla);
if ($blah->login == $data->login) { echo "Meeste race's.</td></tr><tr><td>"; }
$bla = mysql_query("SELECT * FROM `users` WHERE `status`='levend' ORDER BY `nrofkill` DESC LIMIT 0,1");
$blah = mysql_fetch_object($bla);
if ($blah->login == $data->login) { echo "Meeste moorden.</td></tr><tr><td>"; }
}
echo "<META HTTP-EQUIV='refresh' CONTENT='60'; URL='$PHP_SELF'>";
$health = $data->health;
$rangvordering = round($vordering,2);
$schiet = round($data->se,1); 
if ($rangvordering == 100 && $data->xp < 20000) { $rangvordering = 99.99; }
if ($data->xp > 20000) { $rangvordering = 100; }
$shealth = round(100 - $data->health); 
$srangvordering = (100 - $rangvordering);
$sschiet = round(100 - $schiet);
if ($rangvordering > 50) { $vltekst = "{$rangvordering}%"; }
else { $vrtekst = "{$rangvordering}%"; }
if ($health > 50) { $hltekst = "{$health}%"; }
else { $hrtekst = "{$health}%"; }
if ($schiet > 50) { $sltekst = "{$schiet}%"; }
else { $srtekst = "{$schiet}%"; }
?></td>
        </tr>
        <tr>
          <td><br>
		  </td>
        </tr>
        <tr>
          <td>Rank: <?php echo"{$rang}";?></td>
        </tr>
        <tr>
          <td>Stad: <?php echo"{$data->stad}";?></td>
        </tr>
        <tr>
          <td>Health: <?php echo"<table width=\"50%\" border=0 cellspacing=0 cellpadding=0>
            <tr> 
              <td width=$health% bgcolor=#009900><div align=center>$hltekst</div></td>
              <td width=$shealth% bgcolor=#CC3300><div align=center>$hrtekst</div></td>
            </tr>
          </table>";?></td>
        </tr>
        <tr>
          <td>Rangvordering: <?php echo"<table width=\"50%\" border=0 cellspacing=0 cellpadding=0>
            <tr> 
              <td width=$rangvordering% bgcolor=#FFCC00><div align=center>$vltekst</div></td>
              <td width=$srangvordering% bgcolor=#CC3300><div align=center>$vrtekst</div></td>
            </tr>
          </table>";?></td>
        </tr>
        <tr>
          <td>Ks: <?php echo"<table width=\"50%\" border=0 cellspacing=0 cellpadding=0>
            <tr> 
              <td width=$schiet% bgcolor=#FFFFFF><div align=center>$sltekst</div></td>
              <td width=$sschiet% bgcolor=#CC3300><div align=center>$srtekst</div></td>
            </tr>
          </table>";?></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td><? $inboxnew = mysql_num_rows(mysql_query("SELECT id FROM `messages` WHERE `read`=0 AND `to`='{$data->login}'"));
$new1 = "nieuw";
$new2 = "nieuwe";
$iword = ($inboxnew == 1) ? $new1 : $new2;
$message1 = "bericht";
$message2 = "berichten";
$xword = ($inboxnew == 1) ? $message1 : $message2;
if ($inboxnew > 0) { echo "<strong><a href=message.php?p=inbox target=hoofd><b><center>$inboxnew $iword $xword</a></strong></b></center>"; }?>
		  </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td class="subTitle"><b>Links:</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt"><table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
          <td><a href="klikmissie.php" target="hoofd"><b>Klikmissie - 10000</b></a>
		  </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td class="subTitle"><b>Nieuws:</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt"><table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td><? $query = "SELECT * FROM `news` ORDER BY `time` DESC LIMIT 0,5"; 
$info = mysql_query($query) or die(mysql_error()); 
$count = 0; 
while ($gegeven = mysql_fetch_array($info)) { 
$bericht = $gegeven['title'];
if (!$gegeven['title']) { $bericht = "Geen"; }
$tekst = $bericht;
$count++; 
$tekst = dec_str($tekst, 15); 

echo "<a href=news.php target=hoofd><b>$tekst<b></a><br><br>";
}
function dec_str($line, $len)
{
    if (strlen($line) > $len)
        $afgekort = substr($line, 0, $len) . "..";
    else
        $afgekort = $line;

    return ($afgekort);
} 
?>
		  </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  </table>
</body>
</html>
