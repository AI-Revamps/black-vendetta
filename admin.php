<?php 
session_start();
include("config.php"); 
$username = $_POST["username"];
$password = $_POST["password"];
if($submit){
	if ($username == "$admin" AND $password == "$paswoord") {  
                $login = time();
                $_SESSION['login'] = $login;
	} 
	else{  
		echo"<b>Je hebt een verkeerd paswoord of gebruikersnaam ingegeven!</b><br>";
	}
}
if($_SESSION[login] + 60*60*24 > time()){
?>
<form name="form2" method="post" action="<?php echo"$PHP_SELF"; ?>">Al de berichten wissen:<br>
    <input name="wissen" type="submit" value="Berichten Wissen"><br>Je instellingen wijzigen:<br>
	<input name="wijzigen" type="submit" value="Instellingen Wijzigen">
  <br>
  E&eacute;n van de berichten veranderen:<br>
  <input type="submit" name="veranderen" value="Berichten Wijzigen">
  <br>
  Je uitloggen (anders blijf je 1 dag ingelogd!):<br>
	<input name="uitloggen" type="submit" value="Uitloggen">
</form>
<?
        if($uitloggen){
                unset($_SESSION['login']);
        }

	if($wissen){
		if(!$bestand){
			echo"<b>Er zijn nog geen berichten!</b><br>";
		}
		else{
			$open = fopen("$file", "w");
			fclose($open);
			echo"<b>De berichten zijn verwijderd!</b><br>";
		}
	}
	if($veranderen){
?>
<form name="form3" method="post" action="verander.php">
<?php
if(!$bestand){
	echo"<b>Er zijn nog geen berichten</b><br>";
}
else{
	for($i = 0; $i < $hits; $i++){
		echo"Bericht $i <input type=text name=text[$i] value=\"$getal[$i]\"><br>";
	}
echo"<input name=verander type=submit value=Wijzigen>";
}
?>
</form>
<?php
	}
	if($wijzigen){
?>
<b><form name="form3" method="post" action="wijzig.php">
  <p>Je gebruikersnaam: <br>
    <input name="text" type="text" value="<?php echo"$admin"; ?>">
    <br>
    Je wachtwoord:<br>
    <input name="text2" type="password" value="<?php echo"$paswoord"; ?>">
    <br>
    De tekst die moet komen als iemand een fout wachtwoord of gebruikersnaam heeft 
    ingetypt: <br>
    <input name="text3" type="text" value="<?php echo"$fout"; ?>">
    <br>
    Nieuwe berichten vooraan = &quot;nieuwe&quot; oude = &quot;oude&quot;<br>
    <input name="text4" type="text" value="<?php echo"$vooraan"; ?>">
    <br>
    Maximum aantal karakters dat ze mogen in hun naam:<br>
    <input name="text5" type="text" value="<?php echo"$maxn"; ?>">
    <br>
    Maximum aantal karakters dat ze mogen in hun bericht: <br>
    <input name="text6" type="text" value="<?php echo"$maxb"; ?>">
    <br>
    Als je vanboven nieuwe had aangeduid hoeveel berichten mogen dan in de balk 
    staan:<br>
    <input name="text7" type="text" value="<?php echo"$max"; ?>">
    <br>
    Het bestand waar in geschreven moet worden:<br>
    <input name="text8" type="text" value="<?php echo"$file"; ?>">
    <br>
    Het bestand waar het formpje staat:<br>
    <input name="text9" type="text" value="<?php echo"$form"; ?>">
    <br>
	Het bestand waar de berichtenbalk staat:<br>
    <input name="text18" type="text" value="<?php echo"$bar"; ?>">
    <br>
    De tekst die er moet komen als er nog geen berichten zijn:<br>
    <input name="text11" type="text" value="<?php echo"$leeg"; ?>">
    <br>
    Wat er moet komen als ze niks hebben ingevuld:<br>
    <input name="text12" type="text" value="<?php echo"$ongeldig"; ?>">
    <br>
    De scheiding tussen twee berichten:<br>
    <input name="text13" type="text" value="<?php echo"$tussen"; ?>">
    <br>
    Wat er moet komen als het bericht gepost is:<br>
    <input name="text14" type="text" value="<?php echo"$post"; ?>">
    <br>
    De map waar de afbeeldingen staan(zonder eind&quot;/&quot;):<br>
    <input name="text15" type="text" value="<?php echo"$dir"; ?>">
    <br>
    Het aantal seconden dat de gebruiker moet wachten voor hij nog eens mag posten:<br>
    <input name="text16" type="text" value="<?php echo"$verlooptijd"; ?>">
    <br>
	Als ze te snel na elkaar een bericht gepost hebben:<br>
    <input name="text17" type="text" value="<?php echo"$flood"; ?>">
    <br>
    <input type="submit" name="wijzig" value="Wijzigen">
  </p>
</form>
</b>
<?php
	}	
}
else{
?>  
<form name="form1" method="post" action="<?php echo"$PHP_SELF"; ?>"><p>Gebruikersnaam: 
    <input name="username" type="text" id="username">Paswoord: 
    <input name="password" type="password" id="password">      
    <input name="submit" type="submit" id="submit" value="Login"> 
</form> 
<?php  
}
?>