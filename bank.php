  <?php
  include("config.php");
  $dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");
  $data	= mysql_fetch_object($dbres);  
  if(! check_login()) {
    header("Location: login.php");
    exit;
  }
if ($jisin == 1) { include("jisin.php");exit; }  
  ?>
<!DOCTYPE html>
<html lang="nl">
<head>
<title>Vendetta</title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<table width=100%>
  <tr><td class="subTitle"><b>Bank</b></td></tr>
<?php

  if(isset($_POST['out']) && preg_match('/^[0-9]+$/',$_POST['amount'])) {
    if($_POST['amount'] <= $data->bank) {
      $data->zak            += $_POST['amount'];
      $data->bank            -= $_POST['amount'];
      mysql_query("UPDATE `users` SET `bank`={$data->bank},`zak`={$data->zak} WHERE `login`='{$data->login}'");
    }
    else
      print "  <tr><td class=\"mainTxt\">Zoveel geld staat er niet op de bank</td></tr>\n";
  }
  else if(isset($_POST['in']) && preg_match('/^[0-9]+$/',$_POST['amount'])) {
    if($_POST['amount'] <= $data->zak) {
          $data->zak            -= $_POST['amount'];
          $data->bank            += $_POST['amount'];
          $data->bankleft--;
          mysql_query("UPDATE `users` SET `bank`={$data->bank},`zak`={$data->zak} WHERE `login`='{$data->login}'");
    }
    else
      print "  <tr><td class=\"mainTxt\">Zoveel geld heb je niet</td></tr>\n";
  }

  print <<<ENDHTML
  <tr><td class="mainTxt" align="center">
    <table align="center">
      <tr><td width=100>Contant:</td>    <td align="right">&euro;{$data->zak}</td></tr>
      <tr><td width=100>Op de bank:</td>    <td align="right">&euro;{$data->bank}</td></tr>
    </table>
    <form method="post"><table align="center">
      <tr><td align="center">\$<input type="text" name="amount" maxlength="7">,-
        <input type="submit" name="out"  value="UIT" style="width: 100;">
        <input type="submit" name="in" value="IN"  style="width: 100;"></td></tr>
    </table></form>
  </td></tr>
ENDHTML;
?>
</table></body></html>