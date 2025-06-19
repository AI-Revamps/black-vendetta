<?php /* ------------------------- */

  $UPDATE_DB                = 1;
  include("config.php");
  if(! check_login()) {
   header("Location: login.php");
    exit;
  }

  mysql_query("UPDATE `users` SET `online`=NOW() WHERE `login`='{$data->login}'");

/* ------------------------- */ ?>
<?php
if (!empty($data) AND $data->topbalk == 1) {    
include('upper.php');
}
?>
<body style="background: #808080; margin: 0px;">
<body>
<br>
<table width="736">  
<CENTER><SCRIPT TYPE="text/javascript" LANGUAGE="JavaScript" src="http://dutchleader.nl/php/banex/view.php?id=gamebackup"></SCRIPT></CENTER><link rel="stylesheet" type="text/css" href="<?php echo ($_COOKIE['v'] == 1) ? "style.css" : "style.css"; ?>">
<tr><td class="subTitle"><span class="style1">Klik missie</span></td></table>
<table width="736">  
<?php
 



  $resultaat = $data->klikmissie;
  if($resultaat ==1) {
      echo "Je hebt deze link dit uur al bezocht<br>Probeer het volgende uur weer!";
  }
else{
      echo "Je hebt op Criminalzs2 gestemt<br>Er word automatisch €2.500 bij opgeteld, en je hebt 0 belcredits gekregen !! (klik ook op ga door naar de top50)<br><br>bedankt voor het stemmen!!";  
mysql_query("UPDATE `[users]` SET `bank`=`bank`+'2500' WHERE `login`='$data->login'");
mysql_query("UPDATE `[users]` SET `credits`=`credits`+'0' WHERE `login`='$data->login'");
mysql_query("UPDATE `[users]` SET `klikmissie`=`klikmissie`+'1' WHERE `login`='$data->login'");
mysql_query("UPDATE `[users]` SET `klikdl`=`klikdl`+'1' WHERE `login`='$data->login'");
}
  ?></tr></tr></table><?php mysql_close(); ?>