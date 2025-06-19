<?php
include("config.php");
$dbres = mysql_query("SELECT * FROM `users` WHERE `login`='{$_SESSION['login']}'");
$data = mysql_fetch_object($dbres);
?>
<html>
<head>
<title>Vendetta</title>
<link href="style.css" rel="stylesheet" type="text/css">
<meta name="keywords" content="Vendetta,Crimegame,crimegame,vendetta">
<meta name="language" content="english">
<META name="description" lang="nl" content="Vendetta crimegame met pit.">
</head>

<body>
<table width="100%">
  <tr>
    <td class="subTitle"><b>Nieuws</b></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td class="mainTxt">**********<br>
	  <?php
	  $pp = 10;
	  $start= ($_GET['p'] >= 0) ? $_GET['p']*$pp : 0;
	  $sql = mysql_query("SELECT id FROM `news`");
	  $rows = mysql_num_rows($sql);	  
	  if($start > $rows){
	    $start = 0;
	  }
	  $sql = mysql_query("SELECT * FROM `news` ORDER BY time DESC LIMIT $start,$pp");
	  while($news = mysql_fetch_object($sql)){
	    $time = date('d/m/Y', $news->time);
	    $title = $news->title;
		$text = $news->text;
	    echo"<i><b>$title</b></i> $time<br><br>$text<br><br>**********<br><br>";
	  }
	  print "  <tr><td align=\"center\">";
      if($rows <= $pp){
        print "&#60;&#60; &#60; 1 &#62; &#62;&#62;";
	  }
      else {
        if($start/$pp == 0){
          print "&#60;&#60; &#60; ";
		}
        else{
          print "<a href=\"?p=0\">&#60;&#60;</a> <a href=\"?p=". ($start/$pp-1) ."\">&#60;</a> ";
		}
        for($i=0; $i<$rows/$pp; $i++) {
		  if($i == $start/$pp){
		    print "<u>". ($i+1) ."</u> ";
		  }
		  else{
            print "<a href=\"?p=$i\">". ($i+1) ."</a> ";
		  }
        }
        if($start+$pp >= $rows){
          print " &#62; &#62;&#62; ";
		}
        else{
          print "<a href=\"?p=". ($start/$pp+1) ."\">&#62;</a> <a href=\"?p=". (ceil($rows/$pp)-1) ."\">&#62;&#62;</a>";
		}
      }
	  ?>
	</td>
  </tr>  
</table>
</body>
</html>