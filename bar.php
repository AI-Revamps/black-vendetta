<table width="100%" height="40" border="1" bordercolor="#000000">
  <tr>
    <td valign="center">
<marquee>
<? 
include("config.php");
for($i = 0; $i <= $hits; $i++){
$getal[$i] =  str_replace(":-s", "<img src=\"$dir/smilie1.gif\" border=\"0\">", $getal[$i]); 
$getal[$i] =  str_replace(":-S", "<img src=\"$dir/smilie1.gif\" border=\"0\">", $getal[$i]);  
$getal[$i] =  str_replace(":s", "<img src=\"$dir/smilie1.gif\" border=\"0\">", $getal[$i]); 
$getal[$i] =  str_replace(":S", "<img src=\"$dir/smilie1.gif\" border=\"0\">", $getal[$i]); 
$getal[$i] =  str_replace(":cool:", "<img src=\"$dir/smilie2.gif\" border=\"0\">", $getal[$i]); 
$getal[$i] =  str_replace(":,(", "<img src=\"$dir/smilie3.gif\" border=\"0\">", $getal[$i]); 
$getal[$i] =  str_replace(":,-(", "<img src=\"$dir/smilie3.gif\" border=\"0\">", $getal[$i]); 
$getal[$i] =  str_replace(":-d", "<img src=\"$dir/smilie4.gif\" border=\"0\">", $getal[$i]); 
$getal[$i] =  str_replace(":-D", "<img src=\"$dir/smilie4.gif\" border=\"0\">", $getal[$i]); 
$getal[$i] =  str_replace(":d", "<img src=\"$dir/smilie4.gif\" border=\"0\">", $getal[$i]); 
$getal[$i] =  str_replace(":D", "<img src=\"$dir/smilie4.gif\" border=\"0\">", $getal[$i]); 
$getal[$i] =  str_replace(":@", "<img src=\"$dir/smilie5.gif\" border=\"0\">", $getal[$i]); 
$getal[$i] =  str_replace(":-@", "<img src=\"$dir/smilie5.gif\" border=\"0\">", $getal[$i]); 
$getal[$i] =  str_replace(":(", "<img src=\"$dir/smilie6.gif\" border=\"0\">", $getal[$i]); 
$getal[$i] =  str_replace(":-(", "<img src=\"$dir/smilie6.gif\" border=\"0\">", $getal[$i]); 
$getal[$i] =  str_replace(":|", "<img src=\"$dir/smilie7.gif\" border=\"0\">", $getal[$i]); 
$getal[$i] =  str_replace(":-|", "<img src=\"$dir/smilie7.gif\" border=\"0\">", $getal[$i]); 
$getal[$i] =  str_replace(":$", "<img src=\"$dir/smilie8.gif\" border=\"0\">", $getal[$i]); 
$getal[$i] =  str_replace(":-$", "<img src=\"$dir/smilie8.gif\" border=\"0\">", $getal[$i]); 
$getal[$i] =  str_replace(":-)", "<img src=\"$dir/smilie9.gif\" border=\"0\">", $getal[$i]); 
$getal[$i] =  str_replace(":)", "<img src=\"$dir/smilie9.gif\" border=\"0\">", $getal[$i]); 
$getal[$i] =  str_replace(":-p", "<img src=\"$dir/smilie10.gif\" border=\"0\">", $getal[$i]);
$getal[$i] =  str_replace(":-P", "<img src=\"$dir/smilie10.gif\" border=\"0\">", $getal[$i]);
$getal[$i] =  str_replace(":p", "<img src=\"$dir/smilie10.gif\" border=\"0\">", $getal[$i]); 
$getal[$i] =  str_replace(":P", "<img src=\"$dir/smilie10.gif\" border=\"0\">", $getal[$i]); 
$getal[$i] =  str_replace(";)", "<img src=\"$dir/smilie11.gif\" border=\"0\">", $getal[$i]); 
$getal[$i] =  str_replace(";-)", "<img src=\"$dir/smilie11.gif\" border=\"0\">", $getal[$i]);
$getal[$i] = preg_replace("/\[b\](.*?)\[\/b\]/si", "<b>\\1</b>", $getal[$i]);
$getal[$i] = preg_replace("/\[u\](.*?)\[\/u\]/si", "<u>\\1</u>", $getal[$i]);
$getal[$i] = preg_replace("/\[i\](.*?)\[\/i\]/si", "<i>\\1</i>", $getal[$i]);
$getal[$i] = preg_replace("/\[color=(.*?)\](.*?)\[\/color\]/si", "<font color=\"\\1\">\\2</font>", $getal[$i]);
$getal[$i] = eregi_replace("[[:graph:]]+@[^<>[:space:]]+[[:alnum:]]", "<a href=\"mailto:\\0\">\\0</a>", $getal[$i]);
$getal[$i] = eregi_replace("(^|[>[:space:]\n])([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])([<[:space:]\n]|$)","\\1<a href=\"\\2://\\3\\4\" target=\"_blank\">\\2://\\3\\4</a>\\5", $getal[$i]);
$getal[$i] = preg_replace("/\[URL\](.+?)\[\/URL\]/si",'<a href="\1" target="_blank">\1</a>', $getal[$i]); 
$getal[$i] = eregi_replace("\\[email\\]([^\\[]*)\\[/email\\]", "<a href=\"mailto:\\1\">\\1</a>", $getal[$i]);  
$getal[$i] = eregi_replace("\\[email=([^\\[]*)\\]([^\\[]*)\\[/email\\]", "<a href=\"mailto:\\1\">\\2</a>", $getal[$i]); 
}
if(!$bestand){  
	echo("$leeg");  
}
else{  
	if($vooraan == "oude") {  
    	for($g = 0; $i <= $hits; $i++) {   
			echo "$getal[$i]";
		}
	}  
    elseif($vooraan == "nieuwe") {
		if($hits <= $max){
			for($i = $hits; $i >= 0; $i--) {   
				echo "$getal[$i]";  
       		}
		}
		else{
			for($s = $hits; $i > $hits-$max; $i--){
				echo "$getal[$i]";
			}
		}
	}
} 
?>

</marquee>
	</td>
  </tr>
</table>
