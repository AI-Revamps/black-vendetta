<?php
  include('config.php');
  $dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");
  $data	= mysql_fetch_object($dbres);
$i = $data->id;
?>
<html>
<head>
<title>Vendetta</title>
<link rel="stylesheet" type="text/css" href="style.css">
<meta name="keywords" content="Vendetta,Crimegame,crimegame,vendetta">
<meta name="language" content="english">
<META name="description" lang="nl" content="Vendetta crimegame met pit.">
<script language="javascript" src="http://www.mollie.nl/partners/js/2687.js"> </script>
</head>
<table width=100% align=center>
  <tr> 
    <td class="subTitle"><b>Doneer</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
	Je kan Vendetta steunen door te doneren. Hiermee haal je ook veel voordelen. Een donatie eindigt na 14 dagen of indien je sterft.<br>Wanneer je de donatie voltooit krijg je een code die je onderaan deze pagina kan invoeren.<br><br>
	<table width="100%">
        <tr> 
          <td width="20%"> </td>
          <td width="20%">Niet donateurs</td>
          <td width="20%">Donateur</td>
          <td width="20%">Zilveren Donateur</td>
          <td width="20%">Gouden Donateur</td>
        </tr>
        <tr> 
          <td> <br> </td>
          <td> </td>
          <td> </td>
          <td></td>
          <td></td>
        </tr>
        <tr> 
          <td>Klik limiet </td>
          <td>15</td>
          <td>25 </td>
          <td>35</td>
          <td>50</td>
        </tr>
        <tr> 
          <td>Afbeelding</td>
          <td>Geen</td>
          <td>Donater</td>
          <td>Silver Donater</td>
          <td>Golden Donater</td>
        </tr>
        <tr> 
          <td>Timers</td>
          <td>Neen</td>
          <td>Neen</td>
          <td>Ja</td>
          <td>Ja</td>
        </tr>
        <tr> 
          <td>Profiel caracters</td>
          <td>500</td>
          <td>1000</td>
          <td>1500</td>
          <td>2000</td>
        </tr>
        <tr> 
          <td>Bescherming</td>
          <td>1 maal</td>
          <td>2 maal</td>
          <td>3 maal</td>
          <td>4 maal</td>
        </tr>
      </table><br>Ook krijg je per donatie &euro;50.000 in je zak en 500 kogels.<br><a href=\"#\" onClick="mbetaal('id=2687&parameter[1]=<?php echo $i; ?>');return false;"><center>Klik hier Doneer nu!</center></a><br><br>
	  <form method='POST'>Code&nbsp;&nbsp;<input type=text name=code><br><br><input type=submit name=submit value=Submit></form>
  <?php
if (isset($_GET['betaalcode'],$_GET['betaalnummer'])) {
$code = $_GET['betaalcode'];
$nummer = $_GET['betaalnummer'];
$id = $_GET['parameter'][1];
$ip = $_SERVER['REMOTE_ADDR'];
$time = (time()+(14*24*60*60));
if($ip != "82.94.255.118" && $ip != "82.94.255.119"){echo"Er is een hack poging onderschept.";}
else{
$dbres = mysql_query("SELECT * FROM `users` WHERE `id`='{$id}'");
$data = mysql_fetch_object($dbres);
$keychars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  $length = 10;

  // genereren
  $code = "";
  $max=strlen($keychars)-1;
  for ($i=0;$i<=$length;$i++) {
    $code .= substr($keychars, rand(0, $max), 1);
  }
while(mysql_num_rows(mysql_query("SELECT * FROM `donate` WHERE `code`='{$code}'")) == 1){
//parameters
  $keychars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  $length = 10;

  // genereren
  $code = "";
  $max=strlen($keychars)-1;
  for ($i=0;$i<=$length;$i++) {
    $code .= substr($keychars, rand(0, $max), 1);
  }
}
mysql_query("INSERT INTO `donate`(`door`,`code`) values('{$data->login}','{$code}')"); 

mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$data->login}','Donatie','Je donatiecode is: $code')"); 

echo"Het Vendetta team dankt je voor je donatie. Je donatiecode is: &nbsp;$code";
}
}
elseif(isset($_POST['code'],$_POST['submit'])){
  $dbres = mysql_query("SELECT * FROM `donate` WHERE `code`='{$_POST['code']}'");
  $don	= mysql_fetch_object($dbres);
  $nr = mysql_num_rows($dbres);
  $time = (time()+(14*24*60*60));
  if($nr != 1){echo"Deze donatie code is ongeldig.";}
  elseif($don->status != 0){echo"Deze donatie code is te koop gesteld op de veiling.";}
  else{
    mysql_query("UPDATE `users` SET `zak`=`zak`+50000 WHERE `login`='{$data->login}'");
    mysql_query("UPDATE `users` SET `kogels`=`kogels`+500 WHERE `login`='{$data->login}'");
    mysql_query("UPDATE `users` SET `paid`=`paid`+1 WHERE `login`='{$data->login}'");
    if($data->paid > 3){
      mysql_query("UPDATE `users` SET `paid`='3' WHERE `login`='{$data->login}'");
    }
    if($data->paidtime1 <= $data->paidtime2 && $data->paidtime1 <= $data->paidtime3){
      mysql_query("UPDATE `users` SET `paidtime1`='{$time}' WHERE `login`='{$data->login}'");
    }
    elseif($data->paidtime2 <= $data->paidtime1 && $data->paidtime2 <= $data->paidtime3){
      mysql_query("UPDATE `users` SET `paidtime2`='{$time}' WHERE `login`='{$data->login}'");
    }
    elseif($data->paidtime3 <= $data->paidtime1 && $data->paidtime3 <= $data->paidtime2){
      mysql_query("UPDATE `users` SET `paidtime3`='{$time}' WHERE `login`='{$data->login}'");
    }
	echo "De donatie code is correct. Bedankt voor het doneren.";
	mysql_query("DELETE FROM `donate` WHERE `code`='{$_POST['code']}'"); 
  }
}
?>