<?php
include("config.php");
		if($data->status == dood)  {
			$dood = mysql_query("SELECT * FROM `vermoord` WHERE `login`='{$data->login}'");
			$dinfo   = mysql_fetch_object($dood);
			if ($dinfo->msg == NULL) { $kmsg = "<i>Geen</i>"; }
			else { $kmsg = $dinfo->msg; }
print <<<ENDHTML
			<html>
			<head>
			<title>Vendetta</title>
			<link rel="stylesheet" type="text/css" href="style.css">
			</head>
			 <table width=100%>
			  	    <tr><td class=subTitle><b>RIP</b></td></tr><tr><td>&nbsp;&nbsp;</td></tr><td class=mainTxt>
			<font color="#FFFFFF">
			<center><b>RIP $data->login</b><br><br>
			<img name="rip" src="images/rip.gif"><br>
			Je bent omgelegd!<br>Bericht van de dader: $kmsg<br><Br>
			<a href="register.php">Maak nieuwe account</a></center></font>
			    </td></tr>
			  </table>
			</body>
			</html>
ENDHTML;
			    unset($_SESSION['login']);
			    exit;
	  }
	elseif ($data->health < 1) { mysql_query("UPDATE `users` SET `status`='dood' WHERE `login`='{$data->login}'");
	echo "Je bent dood..."; 
	exit;
	}
			    unset($_SESSION['login']);
?>