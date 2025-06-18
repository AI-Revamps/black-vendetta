<?php
session_start();
$width      = 50; // breedte
$height     =  50; // hoogte
$len        =  1; // lengte tekst
$fontsize   =  50; // lettertype

unset($random_text);

$lchar = 0;
$char  = 0;
/**************************************************
$random_text is de code
**************************************************/
// tekst maken
for($i = 0; $i < $len; $i++) {
    while($char == $lchar) {
        $char = rand(48, 109);
        if($char > 57) $char += 7;
        if($char > 90) $char += 6;
    } 
    $random_text .= chr($char);
    $lchar = $char;
}

$fontwidth  = ImageFontWidth($fontsize) * strlen($random_text);
$fontheight = ImageFontHeight($fontsize);

// afbeelding grootte
$im = @imagecreate($width,$height);

// achtergrond maken
$background_colour = imagecolorallocate($im, 204, 0, 0);

// tekst kleur
$text_colour = imagecolorallocate($im, rand(150,255), rand(150,255), rand(150,255)); 

// border
imagerectangle($im, 0, 0, $width-1, $height-1, $text_colour);

// string tekenen
imagestring($im, $fontsize, rand(3, $width-$fontwidth-3), rand(2, $height-$fontheight-3), $random_text, $text_colour);

//output
header("Content-type: image/png");
imagepng($im,'',80);

imagedestroy($im);

$_SESSION["verify"] = $random_text;
?>
