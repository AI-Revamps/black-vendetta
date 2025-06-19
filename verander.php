<?php
include("admin.php");
if($verander){
	$open = fopen("$file", "w");
	fputs($open, "<?php");
	for($i = 1; $i <= $hits; $i++){
		fputs($open, '$getal');  
		fputs($open, "[$i]");  
		fputs($open, " = \"$text[$i]\";\n");  
	}
	fputs($open, "?>");
	fclose($open);
	echo"<b>De berichten zijn gewijzigd!</b><br>";
}  
?>