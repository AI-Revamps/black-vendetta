<? include("config.php"); // config includen voor al de gegevens ?>
<iframe src="<? echo "$bar"; ?>" frameborder="0" width="100%" height="*" name="main" scrolling="no"></iframe>
<table height="*" width="*" align="center" valign="middle" style="font-family: verdana">
  <tr> 
    <td align="center"> <table style="border: 1px solid black; font-size: 10pt;" cellpadding="2" cellspacing="5">
        <tr> 
          <td align="center">Status:<br><b>Je kan nu een bericht intypen!</b>
<?
session_start();  
function htmlparse($string){ 
	return htmlentities(trim($string), ENT_QUOTES); 
}
$bericht = htmlparse($bericht); // html uitzetten in het bericht
$naam    = htmlparse($naam);    // html uitzetten in de naam
$bericht = addslashes($bericht);// van een " een \" maken in het bericht
$naam    = addslashes($naam);   // van een " een \" maken in de naam
if ($_POST[Submit]){
	if($_SESSION[tijd] + $verlooptijd > time()){  
		echo "<br><b>$flood</b>";  
	}  
	elseif($_POST[naam] == "" or $_POST[bericht] == ""){ 
			echo("<br><b>$ongeldig</b>");
	}
	elseif($_POST[naam] == "Je naam" or $_POST[bericht] =="Je bericht"){
		echo("<br><b>$ongeldig</b>");
	}
	elseif($_POST[naam] && $_POST[bericht]){
		$tijd = time();  
		session_register("tijd");  
		$open = fopen("$file", "a");  
		fputs($open, "<?\n");  
		fputs($open, '$getal');  
		fputs($open, "[$hits]");  
		fputs($open, " = \"<b>$naam:</b> $bericht <b>$tussen</b> \";\n");  
		fputs($open, "?>\n");  
		fclose($open);
		echo("<br>
            <b>$post</b>"); } } include("$form"); ?> <a href="bar.php" target="main"><img src="<? echo "$dir"; ?>/www.gif" border="0">Vernieuwen</a> 
            / <a href="admin.php"><img src="<? echo "$dir"; ?>/admin.gif" border="0">Admin</a></TD>
        </TR></TABLE></TD></TR></TABLE></TD></TR></TABLE></TD></TR></TABLE>