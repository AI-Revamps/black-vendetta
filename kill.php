<?php
  include("config.php");
$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");  
$data    = mysql_fetch_object($dbres);
  if(! check_login()) {
    header("Location: login.php");
    exit;
  }
if ($jisin == 1) { header("Location: jisin.php"); }
?>
<html>
<head>
<title>Vendetta</title>
<link rel="stylesheet" type="text/css" href="style.css">
<meta name="keywords" content="Vendetta,Crimegame,crimegame,vendetta">
<meta name="language" content="english">
<META name="description" lang="nl" content="Vendetta crimegame met pit.">
</head>
<table width=100% align=center>
  <tr> 
    <td class="subTitle"><b>Moorden</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
<?
$anum = mysql_query("SELECT `dader` FROM `vermoord` WHERE (UNIX_TIMESTAMP(now())-UNIX_TIMESTAMP(`date`) < 604800) AND `dader`='{$_SESSION['login']}'");
$anums = mysql_num_rows($anum);
$time = time();
$msgnum = rand(1,4);
$message = Array("{$victim->login} had geen schijn van kans, $woord probeerde te gaan lopen, maar uiteindelijk ving $woord een kogel in de rug en viel dood neer.","Je zag {$victim->login} wandelen, hij was alleen... Je vuurde een paar keer waarna $woord achter een auto sprong. Je sloop naar de andere kant van de straat. Daar zat $woord dan, je schoot hem helemaal in prut van een.","Je zorgde dat {$victim->login} niet meer kon gaan lopen, door een paar keer in het been te schieten. Je reed naar de rivier en je zocht een betonmolen. $woord vroeg voor een Red Bull, tevergeefs... $woord zakte als een steen naar beneden. ","Je schoot op {$victim->login}. Nadat de rook was opgetrokken zag je niets meer dan een bloedig hoopje kleren met wat mens in. $woord was totaal kapot geschoten...");
$message2 = Array("100.000, 200.000,... Geld tellen was echt een van zijn favourite bezigheden. HIj was zo druk bezig dat hij niet hoorde dat er een deur achter hem openzwaaide... Een gedaante sloop langzaam naar hem toe... 700.000, 800.000... Hoger dan 1.000.000 raakte hij niet.","BANG! Daar ging hij, de auto die hij net had gestolen, was niet echt van top kwaliteit, maar het kon ermee door. BANG! Deze keer was het niet zijn auto. Hij zakte ineen...","Hij werd na een gevecht wakker op de kade. De cement aan zijn voeten werd al hard. <br>-Je laatste wens, sukkel? <br>-Een Red Bull, als je durft. <br>Hij kreeg het blik tegen zijn hoofd en dronk het uit... Hij wachte op de vleugels, maar ze kwamen niet. Hij zonk als een baksteen.","PFIEEEET! PROOOOEEET! De deur zwaait open. Met een verbaasde blik kijkt hij naar wie binnenstapt, wie komt er nu een wc binnen zonder kloppen? PLONS! Samen met de drol viel hij neer, klaar om door de politie gevonden te worden in een gruwelijk tafereel.");
$msg = $message[$msgnum];
$forummsg = $message2[$msgnum];;
$ws = mysql_fetch_object(mysql_query("SELECT * FROM `users` WHERE UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(`online`) < 43200 AND `login`!='{$data->login}' AND `login`!='{$victim->login}' ORDER BY rand() LIMIT 0,1"));
$wstime = ($time + 172800);
$ktime = gmdate('i:s',($data->kc - $time));
$rcode = rand(3,12);
$code = '';
for( $i = 1; $i <= $rcode; $i++ ) {
$nr = rand(0,9);
$code .= "$nr";
$i++;
}
 if ($data->kc - $time > 0) { echo "Je moet nog $ktime wachten voor je weer kan moorden.";  } 
 if ($anums > 10) { echo "Je bent nog bezig met het bloed van je vorige moorden af te wassen, probeer later nog eens...";  exit; } 
if(isset($_POST['submit'])) {
    if (!$_POST['verify']){echo"Je moet een code opgeven.";exit;}
    elseif($_POST['verify'] != $_SESSION['verify']){echo"De code die je hebt ingevoerd komt niet overeen met het plaatje.";exit;}
	$suspect = mysql_fetch_object(mysql_query("SELECT *,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`safe`) AS `safe` FROM `users` WHERE `login`='{$data->login}'"));
	$victim = mysql_fetch_object(mysql_query("SELECT *,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`safe`) AS `safe` FROM `users` WHERE `login`='{$_POST['victim']}'"));
	$vicfamillie = mysql_fetch_object(mysql_query("SELECT * FROM `famillie` WHERE `name`='{$victim->famillie}'"));
	$susfamillie = mysql_fetch_object(mysql_query("SELECT * FROM `famillie` WHERE `name`='{$suspect->famillie}'"));
	$vicpre = mysql_fetch_object(mysql_query("SELECT * FROM `hitlist` WHERE `login`='{$victim->login}'"));
	$suspre = mysql_fetch_object(mysql_query("SELECT * FROM `hitlist` WHERE `login`='{$suspect->login}'"));
	$vicgame = mysql_fetch_object(mysql_query("SELECT * FROM `casino` WHERE `owner`='{$victim->login}'"));
	$susgame = mysql_fetch_object(mysql_query("SELECT * FROM `casino` WHERE `owner`='{$suspect->login}'"));
    if ($victim->safe - time() > 0) {echo"Je doelwit is ondergedoken, je weet niet waar hij is.";exit;}
	if ($suspect->safe - time() > 0) {echo"Je zit ondergedoken, je kan nu niet moorden.";exit;}
	if ($victim->level >= 200) { echo "Je kan de admins niet doden."; exit; }
	if ($suspect->wapon < 1) { echo "Je hebt nog geen wapen.<br>Ga naar de shop om een wapen te kopen."; exit; }
	if ($_POST['kogels'] > $suspect->kogels) {echo "Zoveel kogel heb je niet.";exit;}
	if ($_POST['kogels'] < 1 ) {echo "Je kan niet schieten met minder dan 1 kogel.";exit;}
 	if ($data->kc - $time > 0) { echo "Je moet nog $ktime wachten voor je weer kan moorden.";exit;  }
    if ($data->level != 1 && $data->login != JanuS) { echo"Jij mag niet moorden.<br>Voor meer info, neem contact op met de admins";exit;}

	if ($victim->xp < 50) { $vicrangk = 1; }
	elseif ($victim->xp < 150) { $vicrangk = 1.5; }
	elseif ($victim->xp < 500) { $vicrangk = 2; }
	elseif ($victim->xp < 1000) { $vicrangk = 2.5; }
	elseif ($victim->xp < 2000) { $vicrangk = 3; }
	elseif ($victim->xp < 3000) { $vicrangk = 3.5; }
	elseif ($victim->xp < 4500) { $vicrangk = 4; }
	elseif ($victim->xp < 6000) { $vicrangk = 4.5; }
	elseif ($victim->xp < 8000) { $vicrangk = 5; }
	elseif ($victim->xp < 11000) { $vicrangk = 5.5; }
	elseif ($victim->xp < 15000) { $vicrangk = 6; }
	elseif ($victim->xp < 20000) { $vicrangk = 6.5; }
	elseif ($victim->xp >= 20000) { $vicrangk = 7; }

	if ($suspect->xp < 50) { $susrangk = 1; }
	elseif ($suspect->xp < 150) { $susrangk = 1.5; }
	elseif ($suspect->xp < 500) { $susrangk = 2; }
	elseif ($suspect->xp < 1000) { $susrangk = 2.5; }
	elseif ($suspect->xp < 2000) { $susrangk = 3; }
	elseif ($suspect->xp < 3000) { $susrangk = 3.5; }
	elseif ($suspect->xp < 4500) { $susrangk = 4; }
	elseif ($suspect->xp < 6000) { $susrangk = 4.5; }
	elseif ($suspect->xp < 8000) { $susrangk = 5; }
	elseif ($suspect->xp < 11000) { $susrangk = 5.5; }
	elseif ($suspect->xp < 15000) { $susrangk = 6; }
	elseif ($suspect->xp < 20000) { $susrangk = 6.5; }
	elseif ($suspect->xp >= 20000) { $susrangk = 7; }

	$suskogels = $_POST['kogels'];

	if ($victim->bf == 0) { $vickogel = 0; }
	elseif ($victim->wapon == 0) { $vickogel = 0; }
	elseif ($victim->bf < 2) { $vickogel = round($suskogels * 0.5); }
	elseif ($victim->bf < 3) { $vickogel = $suskogels; }
	elseif ($victim->bf < 4) { $vickogel = round($suskogels * 2); }
	else { $vickogel = $victim->bf; }

	$vickogels = ($vickogel > $victim->kogels) ? $victim->kogels : $vickogel;
	$dbres = mysql_query("SELECT * FROM `items` WHERE `type`='att' AND `nr`='{$victim->wapon}'");
    $wapen = mysql_fetch_object($dbres);		
	$vicwapon = ($wapen->effect);
	if ($vicwapon == 0) {$vicwapon == 1;}
	$dbres = mysql_query("SELECT * FROM `items` WHERE `type`='def' AND `nr`='{$victim->defence}'");
    $defence = mysql_fetch_object($dbres);
	$vicdefence = ($defence->effect);
	if ($vicdefence == 0) {$vicdefence == 1;}
	$vicai = ($victim->se / 100);
	$vicaim = ($vicai / $vicwapon);
	$suskogels = $_POST['kogels'];
	$suslijfwachten = ($suspect->guard * 100);
	$sushuis = ($suspect->{$data->stad} > 0) ? 5000 : 0;
	$susfampro = ($data->stad == $susfamillie->stad) ? 2500 : 0;
	$dbres = mysql_query("SELECT * FROM `items` WHERE `type`='att' AND `nr`='{$suspect->wapon}'");
    $wapen = mysql_fetch_object($dbres);		
	$suswapon = ($wapen->effect);
	if ($suswapon == 0) {$suswapon == 1;}
	$dbres = mysql_query("SELECT * FROM `items` WHERE `type`='def' AND `nr`='{$suspect->defence}'");
    $defence = mysql_fetch_object($dbres);		
	$susdefence = ($defence->effect);
	if ($susdefence == 0) {$susdefence == 1;}
	$susai = ($data->se / 100);
	$susaim = ($susai / $suswapon);
	$susrand = rand(1,5);
	$susdam = round($suspect->xp/10)+$suslijfwachten+100;
	$susdama = $susdam*$susdefence+$sushuis+$susfampro;
	$susdamag = $susdama*$susrand*$vicaim;
	$susdamag = 100/$susdamag;
	$susdamag = round($susdamag, 12);  
	$susdamag = $susdamag*$vickogels;
	$susdamage = $suspect->health - $susdamag;

	$viclijfwachten = ($victim->guard * 100);
	$vichuis = ($victim->{$data->stad} > 0) ? 5000 : 0;
	$vicfampro = ($data->stad == $vicfamillie->stad) ? 2500 : 0;
	
	$vicrand = rand(1,5);
	$vicdam = round($victim->xp/10)+$viclijfwachten+100;
	$vicdama = $vicdam*$vicdefence+$vichuis+$vicfampro;
	$vicdamag = $vicdama*$vicrand*$susaim;
	$vicdamag = 100/$vicdamag;
	$vicdamag = round($vicdamag, 12);  
	$vicdamag = $vicdamag*$suskogels;
	$vicdamage = $victim->health - $vicdamag;
    if($victim->login == "pitbullgirl"){$vicdamage = 100;}

	$sustestament = round($suspect->bank*0.5);
	$victestament = round($victim->bank*0.5);
	$susse = ($_POST['kogels'] >= 500) ? 0.5 : 0;
	$vicse = ($vickogels >= 500) ? 0.5 : 0;
	$woord = ($victim->geslacht == Man) ? Hij : Ze;
	$msg = $_POST['message'];
	if ($suspect->xp < 10) { $rang1 = 1; }
	elseif ($suspect->xp < 20) { $rang1 = 2; }
	elseif ($suspect->xp < 50) { $rang1 = 3; }
	elseif ($suspect->xp < 150) { $rang1 = 4; }
	elseif ($suspect->xp < 500) { $rang1 = 5; }
	elseif ($suspect->xp < 1000) { $rang1 = 6; }
	elseif ($suspect->xp < 2000) { $rang1 = 7; }
	elseif ($suspect->xp < 3000) { $rang1 = 8; }
	elseif ($suspect->xp < 4500) { $rang1 = 9; }
	elseif ($suspect->xp < 6000) { $rang1 = 10; }
	elseif ($suspect->xp < 8000) { $rang1 = 11; }
	elseif ($suspect->xp < 11000) { $rang1 = 12; }
	elseif ($suspect->xp < 15000) { $rang1 = 13; }
	elseif ($suspect->xp < 20000) { $rang1 = 14; }
	elseif ($suspect->xp >= 20000) { $rang1 = 15; }
	if ($victim->xp < 10) { $rang2 = 1; }
	elseif ($victim->xp < 20) { $rang2 = 2; }
	elseif ($victim->xp < 50) { $rang2 = 3; }
	elseif ($victim->xp < 150) { $rang2 = 4; }
	elseif ($victim->xp < 500) { $rang2 = 5; }
	elseif ($victim->xp < 1000) { $rang2 = 6; }
	elseif ($victim->xp < 2000) { $rang2 = 7; }
	elseif ($victim->xp < 3000) { $rang2 = 8; }
	elseif ($victim->xp < 4500) { $rang2 = 9; }
	elseif ($victim->xp < 6000) { $rang2 = 10; }
	elseif ($victim->xp < 8000) { $rang2 = 11; }
	elseif ($victim->xp < 11000) { $rang2 = 12; }
	elseif ($victim->xp < 15000) { $rang2 = 13; }
	elseif ($victim->xp < 20000) { $rang2 = 14; }
	elseif ($victim->xp >= 20000) { $rang2 = 15; }
	$verschil = ($rang1 - $rang2);
	if ($_POST['ano'] == yes) { $killmsg = $msg; }
	elseif ($_POST['ano'] == no) { $killmsg = "$data->login: $msg"; }
	if ($verschil < -2) { echo "Je kan maximum iemand van 2 rangen hoger doden."; exit; }
	elseif(!$victim->login) {
		echo "Deze gebruiker bestaat niet."; exit;
	} 
	elseif ($suspect->stad != $victim->stad) {
		echo "Je hebt geen idee waar je slachtoffer zich bevind."; exit;
	}
	elseif(round($victim->start/3600-time()/3600) + 24 > 0) {
		echo "{$victim->login} staat nog onder bescherming!"; exit;
	}
	elseif(round($suspect->start/3600-time()/3600) + 24 > 0) {
		echo "Je staat nog onder bescherming, je kan niet moorden/vermoord worden."; exit;
	}
	elseif($data->kogels < $kogels) {
		echo "Zo veel kogels heb je niet."; exit;
	} 
	elseif ($victim->status == dood) { 
		echo "{$victim->login} is al dood!";exit;
	}
	elseif (strtolower($data->login == $victim->login)) { 
		echo "Je kan jezelf niet doden."; exit;
	}
	else {
		if ($vicdamage < 1) {
			echo "{$victim->login} {$msg}<br>{$woord} had &euro;{$victim->zak} in zijn zak, het was zonde om dat achter te laten.";
			mysql_query("INSERT INTO `vermoord`(`login`,`dader`,`date`,`msg`) VALUES ('{$victim->login}','{$data->login}',NOW(),'{$killmsg}')");
			mysql_query("INSERT INTO `logs`(`time`,`login`,`person`,`code`,`area`,`com`) VALUES (NOW(),'{$data->login}','{$victim->login}',1,'kill','')");
			mysql_query("INSERT INTO `ws`(`id`,`login`,`victim`,`suspect`,`prijs`,`status`,`time`) values('$code','{$ws->login}','{$victim->login}','{$suspect->login}','0','0',FROM_UNIXTIME($wstime))");
			mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$ws->login}','Ooggetuige','Je hebt $suspect->login $victim->login zien vermoorden. Je id om deze ooggetuige verklaring te verkopen is $code')"); 
			mysql_query("UPDATE `users` SET `se`=`se`+1,`kc`=FROM_UNIXTIME($killtijd) WHERE `login`='{$data->login}'");
			if ($vicpre->login) { 
				mysql_query("UPDATE `users` SET `zak`=`zak`+$vicpre->prijs WHERE `login`='{$data->login}'"); 
				mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$data->login}','Premielijst','Je hebt $victim->login vermoord, hij stond op de premielijst voor &euro;{$vicpre->prijs}. Dat geld is nu voor jou')"); 
				mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$vicpre->suspect}','Premielijst','Jij hebt $vicpre->login op de premielijst gezet, en $woord is vermoord!')"); 
				mysql_query("DELETE FROM `hitlist` WHERE `login`='{$vicpre->login}'");
			}
			if ($vicgame->spel) { echo "<br>{$victim->login} was de eigenaar van {$vicgame->spel} $vicgame->stad. Het in nu weer te koop.";
				mysql_query("UPDATE `casino` SET `owner`='',`winst`='0' WHERE `spel`='{$vicgame->spel}' AND `stad`='{$vicgame->stad}'");
			}
			if ($victim->famrang > 4) {
				$fam = mysql_fetch_object(mysql_query("SELECT * FROM `famillie` WHERE `name`='{$victim->famillie}'"));
				$ower = mysql_fetch_object(mysql_query("SELECT * FROM `users` WHERE `famrang`='5' AND `famillie`='{$victim->famillie}'"));
				$halfdon = mysql_fetch_object(mysql_query("SELECT * FROM `users` WHERE `famrang`='4' AND `famillie`='{$victim->famillie}'"));
				$consiglieri = mysql_fetch_object(mysql_query("SELECT * FROM `users` WHERE `famrang`='3' AND `famillie`='{$victim->famillie}'"));
				if ($halfdon->login) { mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$halfdon->login}','Famillie Don','Je Don is vermoord. Jij bent nu de nieuwe don van $fam->name.')"); mysql_query("UPDATE `users` SET `famrang`='5' WHERE `login`='{$halfdon->login}'"); }
				elseif ($consiglieri->login) { mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$fam->consiglieri}','Famillie Don','Je Don is vermoord. Jij bent nu de nieuwe don van $fam->name.')"); mysql_query("UPDATE `users` SET `famrang`='5' WHERE `login`='{$consiglieri->login}'"); }
				else { 
					echo "<br>Je slachtoffer was de Don van de famillie {$fam->name}. Hij had geen opvolger... $fam->name is uitgemoord.";
					mysql_query("UPDATE `users` SET `famillie`='',`famrang`='0' WHERE `famillie`='{$victim->famillie}'");
					mysql_query("DELETE FROM `famillie` WHERE `name`='{$victim->famillie}'");
				}
			}
			if ($victim->testament) { 
				mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$victim->testament}','Testament','Jij stond in het testament van $victim->login en $woord is vermoord je krijgt &euro;$victestament.')");
				mysql_query("UPDATE `users` SET `bank`=`bank`+$victestament WHERE `login`='{$victim->testament}'");
				mysql_query("UPDATE `garage` SET `login`='{$victim->testament}' WHERE `login`='{$victim->login}'");
			}
			mysql_query("DELETE FROM `garage` WHERE `login`='{$victim->login}'");
			mysql_query("UPDATE `users` SET `kc`=FROM_UNIXTIME($killtijd),`nrofkill`=`nrofkill`+1,`zak`=`zak`+$victim->zak,`kogels`=`kogels`-$suskogels,`xp`=`xp`+10 WHERE `login`='{$data->login}'");
			mysql_query("UPDATE `stad` SET `drugs`=`drugs`+$victim->drugs,`drank`=`drank`+$victim->drank WHERE `stad`='{$data->stad}'");
			mysql_query("UPDATE `users` SET `testament`='' WHERE `testament`='{$victim->login}'");
			mysql_query("UPDATE `users` SET `status`='dood',`famillie`='',`famrang`='0',`bank`='0' WHERE `login`='{$victim->login}'");
			$subject = "RIP {$victim->login}";
			mysql_query("INSERT INTO forum_topics (user,type,subject,message,date) VALUES ('Notificatie','rip','$subject','$forummsg',NOW())") or die(mysql_error());
		}
			if ($susdamage < 1) {
			echo "<br><br>Je had er beter tweemaal over nagedacht, {$victim->login} schoot je dood in zijn backfire!";
			mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$victim->login}','Moord','Je hebt $suspect->login vermoord in je backfire.')"); 

			mysql_query("INSERT INTO `logs`(`time`,`login`,`person`,`code`,`area`,`com`) VALUES (NOW(),'{$victim->login}','{$data->login}',6,'kill','')");
			mysql_query("INSERT INTO `vermoord`(`login`,`dader`,`date`,`msg`) VALUES ('{$suspect->login}','{$victim->login}',NOW(),'Je bent vermoord door de backFire van {$victim->login}')");
			if ($suspre->login) { 
				mysql_query("UPDATE `users` SET `zak`=`zak`+$pre->prijs WHERE `login`='{$victim->login}'"); 
				mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$suspre->suspect}','Premielijst','Jij hebt $suspre->login op de premielijst gezet, en $woord is vermoord!')"); 
				mysql_query("DELETE FROM `hitlist` WHERE `login`='{$pre->login}'");
			}
			if ($susgame->spel) { mysql_query("UPDATE `casino` SET `owner`='',`winst`='0' WHERE `spel`='{$susgame->spel}' AND `stad`='{$susgame->stad}'");
			}
			if ($suspect->famrang > 4) {
				$fam = mysql_fetch_object(mysql_query("SELECT * FROM `famillie` WHERE `name`='{$suspect->famillie}'"));
				$ower = mysql_fetch_object(mysql_query("SELECT * FROM `users` WHERE `famrang`='5' AND `famillie`='{$suspect->famillie}'"));
				$halfdon = mysql_fetch_object(mysql_query("SELECT * FROM `users` WHERE `famrang`='4' AND `famillie`='{$suspect->famillie}'"));
				$consiglieri = mysql_fetch_object(mysql_query("SELECT * FROM `users` WHERE `famrang`='3' AND `famillie`='{$suspect->famillie}'"));
				if ($halfdon->login) { mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$halfdon->login}','Famillie Don','Je Don is vermoord. Jij bent nu de nieuwe don van $fam->name.')"); mysql_query("UPDATE `users` SET `famrang`='5' WHERE `login`='{$halfdon->login}'"); }
				elseif ($consiglieri->login) { mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$fam->consiglieri}','Famillie Don','Je Don is vermoord. Jij bent nu de nieuwe don van $fam->name.')"); mysql_query("UPDATE `users` SET `famrang`='5' WHERE `login`='{$consiglieri->login}'"); }
			}
			if ($suspect->testament) { 
				mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$suspect->testament}','Testament','Jij stond in het testament van $suspect->login en $woord is vermoord je krijgt &euro;$sustestament.')");
				mysql_query("UPDATE `users` SET `bank`=`bank`+$sustestament WHERE `login`='{$suspect->testament}'");
				mysql_query("UPDATE `garage` SET `login`='{$suspect->testament}' WHERE `login`='{$suspect->login}'");
			}
			
			mysql_query("DELETE FROM `garage` WHERE `login`='{$suspect->login}'");
			mysql_query("UPDATE `stad` SET `drugs`=`drugs`+$suspect->drugs,`drank`=`drank`+$suspect->drank WHERE `stad`='{$data->stad}'");
			mysql_query("UPDATE `users` SET `testament`='' WHERE `testament`='{$suspect->login}'");
			mysql_query("UPDATE `users` SET `status`='dood',`zak`='0',`famillie`='',`famrang`='0',`bank`='0' WHERE `login`='{$suspect->login}'");
		    mysql_query("UPDATE `users` SET ``kogels`=`kogels`-$vickogels,`nrofkill`=`nrofkill`+1 WHERE `login`='{$victim->login}'"); 
			$message = "{$victim->login} schoot {$suspect->login} dood in zijn backfire!";
			$subject = "RIP {$suspect->login}";
			mysql_query("INSERT INTO forum_topics (user,type,subject,message,date) VALUES ('Notificatie','rip','$subject','$message',NOW())") or die(mysql_error());
		}
		if ($vicdamage > 0 && $vicdamag > 0) {
			mysql_query("UPDATE `users` SET `health`='$vicdamage' WHERE `login`='{$victim->login}'");
			echo "<br><br>Je probeerde {$victim->login} te vermoorden maar dit mislukt. $woord is wel gewond geraakt.";
			mysql_query("UPDATE `users` SET `kogels`=`kogels`-$suskogels,`kc`=FROM_UNIXTIME($killtijd) WHERE `login`='{$suspect->login}'");
			mysql_query("UPDATE `users` SET `se`=`se`+0.5 WHERE `login`='{$data->login}'");
			mysql_query("INSERT INTO `logs`(`time`,`login`,`person`,`code`,`area`) values(NOW(),'{$data->login}','{$victim->login}',3,'kill')");
		}
		if ($susdamage > 0 && $susdamag > 0) {
			mysql_query("UPDATE `users` SET `health`='$susdamage' WHERE `login`='{$suspect->login}'");
			echo "<br><br>{$victim->login} schoot terug... Je bent gewond geraakt.";
			mysql_query("UPDATE `users` SET `kogels`=`kogels`-$vickogels WHERE `login`='{$victim->login}'");
			mysql_query("INSERT INTO `logs`(`time`,`login`,`person`,`code`,`area`,`com`) VALUES (NOW(),'{$victim->login}','{$data->login}',5,'kill','')");
		}
		if ($vicdamag < 1) {  
			echo "<br><br>Je bent echt zielig, toen je {$victim->login} probeerde te vermoorden is $woord niet eens gewond geraakt!";  
			mysql_query("UPDATE `users` SET `kogels`=`kogels`-$suskogels,`kc`=FROM_UNIXTIME($killtijd) WHERE `login`='{$suspect->login}'");
			mysql_query("INSERT INTO `logs`(`time`,`login`,`person`,`code`,`area`) values(NOW(),'{$data->login}','{$victim->login}',2,'kill')");
			mysql_query("UPDATE `users` SET `se`=`se`+0.5 WHERE `login`='{$data->login}'");
		}
		if ($susdamag < 1) {  
if ($vickogels > 0) {
			echo "<br><br>{$victim->login} schoot terug, maar je bent niet gewond geraakt.";  
			mysql_query("UPDATE `users` SET `kogels`=`kogels`-$vickogels WHERE `login`='{$victim->login}'");
			mysql_query("INSERT INTO `logs`(`time`,`login`,`person`,`code`,`area`,`com`) VALUES (NOW(),'{$victim->login}','{$data->login}',4,'kill','')");
}
		}
			}
exit;
}
if ($data->kc - time() < 0) {
	print "<form method='post'><b>Doelwit:</b><input type='text' name='victim'><br>
	<b>Kogels:</b><input type='text' name='kogels' maxlength=10><br>
	<b>Bericht:</b><input type='text' name='message' value=''><br>
	<b>Anoniem</b><input type='radio' name='ano' value='yes'checked>&nbsp;&nbsp;&nbsp;<b>Niet anoniem</b><input type='radio' name='ano' value='no'><br><br>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Je code is: <img src=img.php><br>
	Typ hier de code in: <input type=text name=verify><br><br>	
	<input type='submit' name='submit' value='Vermoord!'>
	</form>";
}
print "</table><table width=100%>";
print " <tr>
          <td>&nbsp;</td>
        </tr><tr> 
    <td class=subTitle><b>Zij schoten op jou</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt><table width=100%>";
$dbres				= mysql_query("SELECT *,DATE_FORMAT(`time`,'%d-%m-%Y %H:%i') AS `donatetime` FROM `logs` WHERE `person`='{$data->login}' AND `time` >= '{$data->signup}' AND `area`='kill' ORDER BY `time` DESC LIMIT 0,10");
$bl				= mysql_query("SELECT * FROM `logs` WHERE `person`='{$data->login}' AND `area`='kill'");
$bla = mysql_num_rows($bl);
if ($bla == 0) {
      print <<<ENDHTML
    <tr><td width=125>Geen</td>
ENDHTML;
}
    while($info = mysql_fetch_object($dbres)) {
      $code				= $info->code;
      if ($code == 3) { $code = Gewond; }
      elseif ($code == 2) { $code = "Niet gewond"; }
      elseif ($code == 1) { $code = Dood; }
      elseif ($code == 4) { $code = "BF: Niet gewond"; }
      elseif ($code == 5) { $code = "BF: Gewond"; }
      elseif ($code == 6) { $code = "BF: Dood"; }
      print <<<ENDHTML
      <tr><td width=125>{$info->donatetime}</td>
	<td ><a href="user.php?x={$info->login}">{$info->login}</a></td>
	<td width=100 align="center">$code</td></tr>
ENDHTML;
    }
       print "</table></table><table width=100%> <tr>
          <td>&nbsp;</td>
        </tr><tr> 
    <td class=subTitle><b>Jij schoot op hun</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt><table width=100%>";

    $dbres				= mysql_query("SELECT *,DATE_FORMAT(`time`,'%d-%m-%Y %H:%i') AS `donatetime` FROM `logs` WHERE `login`='{$data->login}' AND `time` >= '{$data->signup}' AND `area`='kill' ORDER BY `time` DESC LIMIT 0,10");
    $bl				= mysql_query("SELECT * FROM `logs` WHERE `login`='{$data->login}' AND `area`='kill'");
$bla = mysql_num_rows($bl);
if ($bla == 0) {
      print <<<ENDHTML
    <tr><td width=125>Geen</td>
ENDHTML;
}
    while($info = mysql_fetch_object($dbres)) {
      $code				= $info->code;
      if ($code == 1) { $code = Dood; }
      elseif ($code == 2) { $code = "Niet Gewond"; }
      elseif ($code == 3) { $code = Gewond; }
      elseif ($code == 4) { $code = "BF: Niet gewond"; }
      elseif ($code == 5) { $code = "BF: Gewond"; }
      elseif ($code == 6) { $code = "BF: Dood"; }
      print <<<ENDHTML
      <tr><td width=125>{$info->donatetime}</td>
	<td><a href="user.php?x={$info->person}">{$info->person}</a></td>
	<td width=100 align="center">$code</td></tr>
ENDHTML;
    }
print "</table>";
?>
</table></html>
