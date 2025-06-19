<?php 

include("config.php"); 

?> 
<html> 

<head> 
<title>Vendetta</title> 
<link rel="stylesheet" type="text/css" href="style.css"> 

</head> 

<body> 
<table align="center" width=100%> 
  <tr><td class="subTitle"><b>Poll:</b></td></tr> 
  <tr><td class="mainTxt"> 

<?php 
/* database verbinding */ 


/* ip van de bezoeker bezoeker */ 
if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}

/* pollid, als er geen id is opgegeven wordt id 0 gebruikt en dan wordt de nieuwste actieve poll weergegeven */ 
if(isset($_GET['pollid']) && is_numeric($_GET['pollid'])) {
    $pollid = $_GET['pollid'];
} else {
    $pollid = 0; 
} 


class wmpoll { 
    function __construct($bezoeker) { 
        $this->bezoeker = $bezoeker; 
    } 
    // Backwards compatibility for PHP 4 style constructor
    function wmpoll($bezoeker) {
        $this->__construct($bezoeker);
    }

    function htmlparse($string){ 
        return htmlentities(trim($string), ENT_QUOTES); 
    } 

    function stem($vote) { 
        if(is_numeric($vote) && $vote >= 1 && $vote <= 10) { 
            $id = $this->list['id']; 
            $gestemd = $this->list['gestemd']."(".$this->bezoeker.",".$vote.")"; 
            $sql = @mysql_query("UPDATE poll SET antwoord".$vote."=antwoord".$vote."+1, gestemd='".$gestemd."' WHERE id='$id'"); 
            if($sql) { 
                $this->list["antwoord".$vote]++; 
            } 
        } 
    } 

    function archief($aantal=0) { 
        GLOBAL $_SERVER;
        if($aantal != 0) { 
            $limit = " LIMIT ".$aantal; 
        } else { 
            $limit = ""; 
        } 
        $sql = @mysql_query("SELECT id, vraag FROM poll ORDER BY id DESC".$limit); 
        echo "<select name=\"pollarchief\" onChange=\"window.location=('".$_SERVER['PHP_SELF']."?pollid='+this.options[this.selectedIndex].value)\">\n<option value=\"\">Archief</option>/n";
        while($list = @mysql_fetch_assoc($sql)) { 
            echo "<option value=\"".$list['id']."\">".$this->htmlparse($list['vraag'])."</option>\n"; 
        } 
        echo "</select>\n"; 
    } 

    function toon($id=0, $magstemmen=1, $balkje=200, $kleur1="#A9A9A9", $kleur2="#FF9900") { 
        GLOBAL $_POST, $_SERVER;
        if($id == 0) { 
            $sql = @mysql_query("SELECT * FROM poll WHERE actief='1' ORDER BY id DESC LIMIT 1"); 
        } else { 
            $id = addslashes($id); 
            $sql = @mysql_query("SELECT * FROM poll WHERE id='$id'"); 
        } 

        // bestaat poll? 
        $bestaat = @mysql_num_rows($sql); 
        if($bestaat == 0 && $id == 0) { 
            echo "Error: er is geen actieve poll!\n"; 
        } elseif($bestaat == 0) { 
            echo "Error: deze poll bestaat niet!\n"; 
        } else { 

            $this->list = @mysql_fetch_assoc($sql); 

            // mag de bezoeker stemmen? 
            if($magstemmen == 0 || preg_match("/\(".$this->bezoeker.",/", $this->list['gestemd'])) { 
                $magstemmen = 0; 
            } else { 
                $magstemmen = 1; 
            } 

            // poll type 
            if($this->list['actief'] == 1) { 
                $type = "Actief"; 
            } else { 
                $type = "Archief"; 
                $magstemmen = 0; 
            } 

            // stem opslaan 
            if($magstemmen == 1 && isset($_POST['pollvote']) && isset($_POST['pollid']) && $_POST['pollid'] == $this->list['id']) {
                $this->stem($_POST['pollvote']);
                $magstemmen = 0; 
            } 

            // totaal aantal stemmen 
            $totaal = 0; 
            for($x=1; $x<=10; $x++) { 
                $totaal = $totaal + $this->list["antwoord".$x]; 
            } 

            // poll weergeven 
            if($magstemmen == 1) { 
                echo "<form action=\"".$_SERVER['REQUEST_URI']."\" method=\"POST\">\n<input type=\"hidden\" name=\"pollid\" value=\"".$this->list['id']."\">\n";
            } 
            echo "<b>".$this->htmlparse($this->list['vraag'])."</b><br>\nStemmen: ".$totaal."<br>\nDatum: ".date("d-m-Y", $this->list['datum'])."<br>\nType: ".$type."<br><br>\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\">\n"; 

            for($x=1; $x<=10; $x++) { 
                if(!empty($this->list["keuze".$x])) { 
                    // resultaten berekenen 
                    if($totaal != 0) { 
                        $procent = round(($this->list["antwoord".$x]/$totaal)*100); 
                        $balk = ($this->list["antwoord".$x]/$totaal)*$balkje; 
                    } else { 
                        $procent = 0; 
                        $balk = 0; 
                    } 

                    echo "<tr>"; 
                    if($magstemmen == 1) { 
                        echo "<td><input type=\"radio\" name=\"pollvote\" value=\"".$x."\"></td>"; 
                    } 
                    echo "<td><b>".$this->htmlparse($this->list["keuze".$x])."</b>&nbsp;&nbsp;&nbsp;</td><td>".$procent." %&nbsp;&nbsp;&nbsp;</td><td>\n<table width=\"".$balkje."\" height=\"10\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" style=\"border: 1px solid ".$kleur1.";\"><tr><td width=\"".$balk."\" bgcolor=\"".$kleur2."\"></td><td width=\"".($balkje-$balk)."\"></td></tr></table>\n</td></tr>\n"; 
                } 
            } 

            echo "</table>\n"; 

            if($magstemmen == 1) { 
                echo "<input type=\"submit\" name=\"submit\" value=\"Stem\">\n</form>\n"; 
            } 
        } 
    } 
} 


/* class starten 
params: 
1: kenmerk van de bezoeker, dus bijv. ip of userid. Let op: als de bezoeker heeft gestemd zal dit kenmerk in de database worden gezet zodat de bezoeker niet nog een keer kan stemmen */ 
$poll = new wmpoll($ip); 

/* poll weergeven 
params: 
1: pollid, 0: nieuwste actieve poll 
2: mag de bezoekers stemmen, 1: ja 0: nee 
3: breedte van de balkjes, in pixels 
4: lijnkleur van de balkjes 
5: vulkleur van de balkjes */ 
$poll->toon($pollid, 1, 300, "#333333", "#333333"); 

/* archief weergeven 
params: 
1: hoeveel pollen maximaal weergeven, 0: geen limiet */ 

echo "<br><br>\n<b>Archief:</b><br>\n"; 
$poll->archief(0); 
?> 

  </td></tr> 
</table> 
</body> 
</html>