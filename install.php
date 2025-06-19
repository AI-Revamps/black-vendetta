<?php
if($_POST[Submit]){
	if(isset($_POST[Submit])){
		$open = fopen("config.php", "w");
		fputs($open, "<?php\n");
		fputs($open, '$admin');
		fputs($open, " = \"$text\";\n");
		fputs($open, '$paswoord');
		fputs($open, " = \"$text2\";\n");
		fputs($open, '$fout');
		fputs($open, " = \"$text3\";\n");
		fputs($open, '$vooraan');
		fputs($open, " = \"$text4\";\n");
		fputs($open, '$maxn');
		fputs($open, " = \"$text5\";\n");
		fputs($open, '$maxb');
		fputs($open, " = \"$text6\";\n");
		fputs($open, '$max');
		fputs($open, " = \"$text7\";\n");
		fputs($open, '$file');
		fputs($open, " = \"$text8\";\n");
		fputs($open, '$form');
		fputs($open, " = \"$text9\";\n");
		fputs($open, '$bar');
		fputs($open, " = \"$text18\";\n");
		fputs($open, '$leeg');
		fputs($open, " = \"$text11\";\n");
		fputs($open, '$ongeldig');
		fputs($open, " = \"$text12\";\n");
		fputs($open, '$tussen');
		fputs($open, " = \"$text13\";\n");
		fputs($open, '$post');
		fputs($open, " = \"$text14\";\n");
		fputs($open, '$dir');
		fputs($open, " = \"$text15\";\n");
		fputs($open, '$verlooptijd');
		fputs($open, " = $text16;\n");
		fputs($open, '$flood');
		fputs($open, " = \"$text17\";\n");
		fputs($open, "include(\"");
		fputs($open, '$file');
		fputs($open, "\");\n");
		fputs($open, '$bestand');
		fputs($open, " = file(");
		fputs($open, '$file');
		fputs($open, ");\n");
		fputs($open, '$hits');
		fputs($open, " = count(");
		fputs($open, '$getal');
		fputs($open, ");\n");
		fputs($open, "\n?>");
		fclose($open);
		echo"<b>De berichtenbalk is geïnstalleerd! Je kunt nu install.php verwijderen uit je database!<b>";
	}
	else{
		echo"<b>Je moet al de velden invullen!</b><br>";
	}
}
else{
?>
<b>
<form name="form1" method="post" action="">
  <p>Je gebruikersnaam: <br>
    <input name="text" type="text" value="Gebruikersnaam">
    <br>
    Je wachtwoord:<br>
    <input name="text2" type="password" value="Wachtwoord">
    <br>
    De tekst die moet komen als iemand een fout wachtwoord of gebruikersnaam heeft 
    ingetypt: <br>
    <input name="text3" type="text" value="Je hebt een fout paswoord/gebruikersnaam ingegeven!" size="60">
    <br>
    Nieuwe berichten vooraan = &quot;nieuwe&quot; oude = &quot;oude&quot;:<br>
    <input name="text4" type="text" value="nieuwe">
    <br>
    Maximum aantal karakters dat ze mogen in hun naam:<br>
    <input name="text5" type="text" value="20">
    <br>
    Maximum aantal karakters dat ze mogen in hun bericht: <br>
    <input name="text6" type="text" value="50">
    <br>
    Als je vanboven nieuwe had aangeduid hoeveel berichten mogen dan in de balk 
    staan:<br>
    <input name="text7" type="text" value="10">
    <br>
    Het bestand waar in geschreven moet worden:<br>
    <input name="text8" type="text" value="text.php">
    <br>
    Het bestand waar het formpje staat:<br>
    <input name="text9" type="text" value="form.php">
    <br>
	Het bestand waar de berichtenbalk staat:<br>
    <input name="text18" type="text" value="bar.php">
    <br>
    De tekst die er moet komen als er nog geen berichten zijn:<br>
    <input name="text11" type="text" value="Er zijn geen berichten!" size="60">
    <br>
    Wat er moet komen als ze niks hebben ingevuld:<br>
    <input name="text12" type="text" value="Je moet een geldige naam en bericht invullen!" size="60">
    <br>
    De scheiding tussen twee berichten:<br>
    <input name="text13" type="text" value="|-|" size="10">
    <br>
    Wat er moet komen als het bericht gepost is:<br>
    <input name="text14" type="text" value="Je bericht is succesvol gepost!" size="60">
    <br>
    De map waar de afbeeldingen staan(zonder eind&quot;/&quot;):<br>
    <input name="text15" type="text" value="smilies">
    <br>
    Het aantal seconden dat de gebruiker moet wachten voor hij nog eens mag posten:<br>
    <input name="text16" type="text" value="20">
    <br>
	Als ze te snel na elkaar een bericht gepost hebben:<br>
    <input name="text17" type="text" value="Je heb zojuist al een bericht geplaatst!" size="60">
    <br>
    <input type="submit" name="Submit" value="Instaleren">
  </p>
</form>
</b>
<?php
}
?>