<?php
class progress_bar 
{ 
    var $percent; 
    var $width; 

    function progress_bar($percent = 1, $width = 100) 
    { 
        $this->percent = $percent; 
        $this->width = $width; 
      } 

    function create() 
    { 
        ?>         
        <br><br>Voortgang: 
        <div style="width: <? echo(($this->percent * .01) * $this->width); ?>px; background-color: red;" height="10" id="progress" bgcolor="blue"> </div> 
        <div id="tekst">Even geduld, het wordt verwerkt...</div> 
        <? 
    } 

    function set_percent_adv($cur_amount, $max_amount) 
    { 
        $this->percent = ($cur_amount / $max_amount) * 100; 
        echo('<script>e=document.getElementById("progress"); e.style.width = '.($this->percent / 100) * $this->width.' + \'px\'; f=document.getElementById("tekst"); f.innerHTML = \''.$cur_amount.'/'.$max_amount.'\';</script>'); 
         
    } 
} 
define("secured", true); 
include("config.php"); 
if(!check_login()) 
{ 
    header("Location: login.php"); 
    exit; 
} 

function mv($value) 
{ 
    return mysql_real_escape_string($value); 
} 

mysql_query("UPDATE `users` SET `online`=NOW() WHERE `login`='{$data->login}'"); 

?> 
<html> 
<link rel="stylesheet" type="text/css" href="style.css"> 
<title>Crime55</title><table width="100%"> 
<tr><td class="subTitle"><b>Uitnodigen</b></td></tr> 
<tr><td>&nbsp;&nbsp;</td></tr>
    <tr><td class='mainTxt'> 
<?php 

if($data->msn == 1) 
    die('Je hebt deze actie al één keer uitgevoerd, dit mag je maar één keer doen.</td></tr></table>'); 

if(isset($_POST['submit'])) 
{ 
    $file = $_FILES['file']['tmp_name']; 
    $extensie = strtolower(end(explode(".", $_FILES['file']['name']))); 

    if($file == "none") 
    { 
        print 'Je moet wel een bestand kiezen.'; 
    } 
    elseif(filesize($file) > (2048*500)) 
    { 
        print 'Je bestand mag niet groter zijn dan 500 kB'; 
    } 
    elseif($extensie != "ctt") 
    { 
        print 'Je bestand moet de extensie .ctt hebben.'; 
    } 
    else 
    { 
        //$rand = md5(time()).".dat"; 
        //$file = move_uploaded_file($file, $rand); 
        $file1 = file($file); 
        $adressen = array(); 

        foreach($file1 as $regel) 
        { 
            if(!(strpos($regel, "list"))) 
            { 
                $pos = strpos($regel, "<contact"); 
                $pos1 = strpos($regel, "type="); 
                if($pos1 > 0) 
                    $adressen[] = substr($regel, ($pos+18), (strpos($regel, "</contact>")-$pos-18)); 
                elseif($pos > 0) 
                    $adressen[] = substr($regel, ($pos+9), (strpos($regel, "</contact>")-$pos-9)); 
            } 
        } 

        $adressen1 = array(); 
        foreach($adressen as $adres) 
        {     
            if(preg_match('/^.+@.+\..+$/', $adres) != 0 && $adres != $data->email && strlen($adres) > 6) 
            { 
                $adressen1[] = $adres; 
            } 
        } 
        $adressen = $adressen1; 

        $count = count($adressen); 

        print 'Er is in totaal naar '.$count.' contactpersonen een uitnodiging met daarin jouw referral link verstuurd, je kunt dit maar één keer doen. We hopen dat deze actie jou veel bankgeld oplevert. <b>Let op!</b> Klik dit venster niet weg totdat alle mailtjes verzonden zijn, je moet de pagina ook niet vernieuwen omdat hij dan stopt met verzenden en je kunt niet opnieuw beginnen.'; 

        $headers  = 'From: logd.nl <info@logd.nl>' . "\n"; 
        $headers .= 'MIME-Version: 1.0' . "\n"; 
        $headers .= 'Return-Path: info@logd.nl' . "\n"; 
        $headers .= 'Reply-To: info@logd.nl' . "\n"; 
        $headers .= 'X-Mailer: PHP'; 

        $i = 0; 

        $pbar = new progress_bar(1, 150); 
        if($count > 150) 
            $num_tasks = 150; 
        else 
            $num_tasks = $count; 
        $pbar->create(); 

        if($count >= 50) 
mysql_query("UPDATE `users` SET `bank`=`bank`+10000 WHERE `login`='$data->login'");
mysql_query("UPDATE `users` SET `msn`=`msn`+1 WHERE `login`='$data->login'");
     
        foreach($adressen as $adres) 
        { 
            $i++; 
            if($i < 151) 
            { 
                usleep(1000000); 
                flush(); 
                $pbar->set_percent_adv($i, $num_tasks); 
                if(preg_match('/^.+@.+\..+$/', $adres) != 0 && !(preg_match("/\r/i", $adres) || preg_match("/\n/i", $adres))) 
                { 
                    $tekst = "Beste,\r\n\r\neen van de spelers van logd.nl - game3, ".$data->login." (".$data->email."), wil jou graag uitnodigen om mee te spelen. vendetta is een online textbased game waar je jezelf zo hoog mogelijk in de ledenlijst moet zien te werken.\r\n\r\n".$data->login." hoopt dat je jezelf wilt aanmelden via zijn referral link, hiervoor krijgt ".$data->login." een beloning:\r\nhttp://www.Crime55.nl/register.php?ref=".$data->login.".\r\n\r\nMet vriendelijke groeten,\r\Crime55.nl Crew (en ".$data->login.")"; 
                    $tekst = wordwrap($tekst, 70); 
                    mail($adres, 'Uitnodiging voor vendetta', $tekst, $headers); 
                    mysql_query("INSERT INTO `[msn]` (mail, datum, ref) VALUES ('".mv($adres)."', '".time()."', '".mv($data->login)."')"); 
                } 
            } 
        } 

        unlink($file); 

        //print_r($file); 
    } 
} 
else 
{ 
    print '<form method="post" action="" enctype="multipart/form-data">Via deze pagina kun je je mensen uit je msn lijst een uitnodiging voor vendetta sturen met daarin jou referral link. Zo kun je snel al je vrienden op de hoogte stellen van je link. Je kunt maar één keer aan heel je lijst een bericht sturen, zorg dus voor een gevulde lijst zodat je zoveel mogelijk mensen bereikt. Je krijgt hiervoor zowieso <b>10.000 Bankgeld</b> als er meer als 50 mensen in je lijst stonden.<br><br><img src="images/msn.jpg"><br><br>Je .ctt contactpersonenbestand:<br><input type="file" name="file" id="file"><br><br><input type="submit" name="submit" value="Verstuur"></form>'; 
} 

?> 

</td></tr></table> 

</body> 

</html> 
<body oncontextmenu="return false" ondragstart="return false" onselectstart="return false"> 
</html> 
<?php mysql_close(); ?> 