<?php 
// dit script mag nergens anders dan criminalspoint gepost zonder de toesteming van rick van beek
// mogt je de copyricht weg willen neem dan contact op met rick van beek of een van de crew leden van criminalspoint




  $UPDATE_DB                = 1; 
  include("config.php"); 
  if(! check_login()) { 
    header("Location: login.php"); 
    exit; 
  } 

  mysql_query("UPDATE `users` SET `online`=NOW() WHERE `login`='{$data->login}'"); 

$url="http://logd.nl/game3/"; //url van je website
$p=$_GET['p']; // get van pagina
$time = time();
$rijbewijstijd = (time() + 300); //de cijfers zijn het aantal secondes dat je moet wachten totdat je het weer kan proberen
if($data->rijbewijs > 0){
    print <<<ENDHTML




<html>
<head>

<title>Gefeliciteerd RijbewijsHouder</title>
<link rel="stylesheet" type="text/css" href="style.css">

</head>


<link rel="stylesheet" type="text/css" href="style.css">
<center><table width=50%>
    <tr><td class="subTitle"><b>Behaald</b></td></tr>
    <tr><td class="mainTxt"><center>
<tr><td class="mainTxt">
	<center>Je hebt je Rijbewijs al behaald, je kan nu autostelen!</td></tr>
<tr><td class="mainTxt">
	<center>Veel geluk met het autostelen!</td></tr>
  </table>
</body>

</html>
ENDHTML;
    exit;
    }

/* ------------------------- */ ?><head> 
<title>Rijbewijs</title> 
<title></title> 
<link rel="stylesheet" type="text/css" href="<?php echo ($_COOKIE['v'] == 2) ? "style.css" : "style.css"; ?>"> 


<LINK href="style.css" type=text/css rel=stylesheet>
   

<TBODY></TBODY></TABLE>

<TABLE width="80%" align=center>
  <TBODY>
  <TR>
    <TD class=subtitle colspan=3><b>Rijbewijs</b></TD></TD>
 <?php
echo" <tr><td class=maintxt colspan=2 align=left>Welkom op het Rijschool!
Welkom <font color=red>$data->login</font>,
    <BR>
Dit is een nieuwe optie in vendetta, de Rijschool!
<BR>
<BR>
veel succes!
<td class=maintxt colspan=1 align=right><img src=http://www.florkoninckx.be/userFiles/img/rijbewijs.jpg witdh=150></td></tr>
<TD class=subtitle colspan=3><b>Rijbewijs</b></TD></TD>
      <tr><td class=maintxt colspan=1><center><A href=\"$url/rijbewijs.php?p=inschrijven\">
          Inschrijven:
        <td class=maintxt colspawn=1><center><A href=\"$url/rijbewijs.php?p=rijden\">Rijden:
        <td class=maintxt colspawn=1><center><A href=\"$url/rijbewijs.php?p=info\">Vordering:</tr>
  </TD></TR>
";

if(empty($p)){


echo"
    <TD class=subtitle colspan=3><b>Rijbewijs</b></TD></TD>
     
</LI> </TD></TR>";
} 
elseif($p == info){


echo"<TR>
   <TD class=maintxt colspan=1 align=right>Rijvordering<TD class=maintxt colspan=2><font color=yellow><b>$data->rijvord</b></font>%
      <tr><TD class=maintxt colspan=1 align=right>Rijlessen over<TD class=maintxt colspan=2><font color=yellow><b>$data->lessen</b></font>
        <tr><TD class=maintxt colspan=1 align=right>Aantal keer geslaagd<TD class=maintxt colspan=2><font color=yellow><b>$data->geslaagd</b></font>
       <tr><TD class=maintxt colspan=1 align=right>Aantal keer gezakt<TD class=maintxt colspan=2> <font color=yellow><b>$data->gezakt</b></font> </td></tr> 
      
</LI> </TD></TR>";
} 


elseif($p == inschrijven){

$dbres				= mysql_query("SELECT * FROM `users` WHERE `login`='$data->login'");

        $info		= mysql_fetch_object($dbres);
$kost = 100; //HIER JE KOSTEN DIE JE WILT PER 10 lessen!
$hoeveel = $info->cash/$kost; //HIER DE BEREKING HOEVEEL Lessen JE MET JE CASH KAN KOPEN
print <<<ENDHTML

  <TR>
    <TD class=maintxt colspan=3 align=center>Welkom <b><font color=red>$data->login,</font></b> 
       Ik ben jouw instructor genaamd keesje.<br>
       De lessen zijn niet gratis, je betaald per 10 lessen €<b><font color=red>{$kost}</font></b>! 
      <BR>Veel succes,<b><font color=red>en rij veilig!</font></b>.<br>
      Jij kan met jou cash geld, <b><font color=red>{$hoeveel}</font></b> les(sen) Betalen! 
      <FORM METHOD=post ACTION=>
<center><input name=bieden value=50><br>
   <INPUT type=submit  name=junk type=submit VALUE=Koop><br><br>
</FORM> 
ENDHTML;
if(!empty($_POST['junk'])) {
$bieden = $_POST['bieden'];
$bieden = htmlspecialchars($_POST['bieden']);
$bieden = substr($_POST['bieden'],0,11);
$kost1 = $kost*$_POST['bieden']; 
if($data->cash < $kost1) {
print "  <tr><td colspan=3 class=\"mainTxt\"><center><b><font color=yellow>Hmzz Shit je hebt niet genoeg Geld, ga eerst ff wat geld halen.</b></font></td></tr>\n";
}
elseif($bieden < 0 OR !preg_match('/^[0-9]{1,15}$/',$_POST['bieden'])) {
print "  <tr><td colspan=3 class=\"mainTxt\"><center><font color=yellow><b>Hé, Abuse werkt hier helaas niet voor jou;)!</b></font></td></tr>\n";
}
elseif($bieden < 1) {
print "  <tr><td colspan=3 class=\"mainTxt\"><center><font color=yellow><b>Hé, Koop wel meer dan 1 
	Les;)!</b></font></td></tr>\n";
}
else {
$landz = $bieden*1;
  mysql_query("UPDATE `users` SET `lessen`=lessen+'$bieden' WHERE `login`='{$data->login}'");
  mysql_query("UPDATE `users` SET `cash`=cash-'$kost1' WHERE `login`='$data->login'");
print "<tr><td colspan=3 class=\"mainTxt\"><center><font color=yellow>je hebt ".$bieden." les(sen) genomen, voor {$kost1}</b></font></td></tr>\n"; 

} }
 echo"</TD></TR>";

}elseif($p == rijden){
$dbres				= mysql_query("SELECT * FROM `[rijschool]`");

        $rijschool		= mysql_fetch_object($dbres);

echo" <form method=post>
   <tr><td class=maintxt colspan=3 align=center><b><font color=yellow>Heej, Rij hier een kleine tentamen af</font>!</b>
   <br>
   <font color=yellow><b>Zo krijg je meer Rijvordering zodat je uiteindelijk je rijbewijs zult halen</font>!</b>
   <br>
   <br>
   <input type=submit name=rijden value=\"rijden\" size=200>    </form>"; 
if(!empty($_POST['rijden'])) {
$rand=rand(1,70);
$vord=rand(0.1,3);
$rijtijd = gmdate('i:s',($data->rijbewijstijd - time()));
if($data->rijbewijstijd - time() > 0) { 
print " <tr><td colspan=3 class=\"mainTxt\"><center><b><font color=yred>Je moet nog $rijtijd wachten, Je instructor is bezig met een andere leerling.</b></font></td></tr>\n";
}
elseif($data->lessen < 1) { 
print " <tr><td colspan=3 class=\"mainTxt\"><center><b><font color=red>Tja, je lessen zijn op!</b></font></td></tr>\n";
}
elseif($data->lessen < 1){
echo"Geen Lessen Gekocht, geen les voor jou dan!";
}
elseif($rand > 50){
mysql_query("UPDATE `users` SET `rijbewijstijd`='$rijbewijstijd' WHERE `login`='{$data->login}'"); 
 mysql_query("UPDATE `users` SET `rijvord`=rijvord+'$vord' WHERE `login`='$data->login'");
mysql_query("UPDATE `users` SET `lessen`=lessen-'1' WHERE `login`='{$data->login}'");
  mysql_query("UPDATE `users` SET `geslaagd`=geslaagd+'1' WHERE `login`='$data->login'");
print " <tr><td colspan=3 class=\"mainTxt\"><center><img src=/images/chick.jpg witd=100 height=100><b><font color=yellow>je hebt $vord% ervaring opgedaan, dit wordt opgeteld bij je huidige ervaring!</b></font></td></tr>\n";
}
elseif($rand > 40){
mysql_query("UPDATE `users` SET `rijbewijstijd`='$rijbewijstijd' WHERE `login`='{$data->login}'"); 
   mysql_query("UPDATE `users` SET `gezakt`=gezakt+'1' WHERE `login`='$data->login'");
mysql_query("UPDATE `users` SET `lessen`=lessen-'1' WHERE `login`='{$data->login}'");
print "  <tr><td colspan=3 class=\"mainTxt\"><center><img src=/images/nerd.jpg witd=100 height=100><b><font color=yellow>Les nummer 1, Kijk nooit naar je instructor de tieten!</b></font></td></tr>\n";
}
elseif($rand > 20){
mysql_query("UPDATE `users` SET `rijbewijstijd`='$rijbewijstijd' WHERE `login`='{$data->login}'"); 
  mysql_query("UPDATE `users` SET `gezakt`=gezakt+'1' WHERE `login`='$data->login'");
mysql_query("UPDATE `users` SET `lessen`=lessen-'1' WHERE `login`='{$data->login}'");
print "  <tr><td colspan=3 class=\"mainTxt\"><center><img src=/images/nerd.jpg witd=100 height=100><b><font color=yellow>Les nummer 2, Stop altijd bij een StopBord!</b></font></td></tr>\n";
	}
	elseif($rand > 15){
mysql_query("UPDATE `users` SET `rijbewijstijd`='$rijbewijstijd' WHERE `login`='{$data->login}'"); 
  mysql_query("UPDATE `users` SET `gezakt`=gezakt+'1' WHERE `login`='$data->login'");
mysql_query("UPDATE `users` SET `lessen`=lessen-'1' WHERE `login`='{$data->login}'");
print "  <tr><td colspan=3 class=\"mainTxt\"><center><img src=/images/nerd.jpg witd=100 height=100><b><font color=yellow>Les nummer 3, Altijd je gordel omdoen!</b></font></td></tr>\n";
}
	elseif($rand > 10){
  mysql_query("UPDATE `users` SET `gezakt`=gezakt+'1' WHERE `login`='$data->login'");
mysql_query("UPDATE `users` SET `lessen`=lessen-'1' WHERE `login`='{$data->login}'");
mysql_query("UPDATE `users` SET `rijbewijstijd`='$rijbewijstijd' WHERE `login`='{$data->login}'"); 

print "  <tr><td colspan=3 class=\"mainTxt\"><center><img src=/images/nerd.jpg witd=100 height=100><b><font color=yellow>Les nummer 4, Vergeet nooit de sleutels!</b></font></td></tr>\n";
}
	elseif($rand < 5){
mysql_query("UPDATE `users` SET `rijbewijstijd`='$rijbewijstijd' WHERE `login`='{$data->login}'"); 
mysql_query("UPDATE `users` SET `lessen`=lessen-'1' WHERE `login`='{$data->login}'");
  mysql_query("UPDATE `users` SET `gezakt`=gezakt+'1' WHERE `login`='$data->login'");
print "  <tr><td colspan=3 class=\"mainTxt\"><center><img src=/images/nerd.jpg witd=100 height=100><b><font color=yellow>Les nummer 5, Nee, een auto kan niet vliegen!</b></font></td></tr>\n";
}



}

echo"</tr></td>";
}

 ?>
  <TR><TD class=maintxt colspan=3 align=middle>dit script is gemaakt door Hishem en een klein beetje door dragontje124. 
	Rijbewijs v1.0</TD></TR>
</TR></TBODY></TABLE>