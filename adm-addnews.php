<?php
include("config.php");
$dbres = mysql_query("SELECT * FROM `users` WHERE `login`='{$_SESSION['login']}'");
$data = mysql_fetch_object($dbres);
if(! check_login()) {
    header("Location: login.php");
    exit;
  }
if($data->level == 1){
  echo"U heeft geen toestemming om deze pagina te bekijken.";
  exit;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<title>Vendetta</title>
<link href="style.css" rel="stylesheet" type="text/css">
<meta name="keywords" content="Vendetta,Crimegame,crimegame,vendetta">
<meta name="language" content="english">
<META name="description" lang="nl" content="Vendetta crimegame met pit.">
</head>


<body>
<?php
if(isset($_POST['addnew'])){
  if(!isset($_POST['title'])){echo"Je hebt geen titel opgegeven.";exit;}
  if(!isset($_POST['text'])){echo"Je hebt geen tekst opgegeven.";exit;}
  $time = time();
  mysql_query("INSERT INTO `news`(`title`,`text`,`time`) values('{$_POST['title']}','{$_POST['text']}','{$time}')")or die("Nieuws toevoegen mislukt."); 
  echo "Nieuws toegevoegd.";
  exit;
}
if(isset($_POST['edit'])){
  if(!isset($_POST['title'])){echo"Je hebt geen titel opgegeven.";exit;}
  if(!isset($_POST['text'])){echo"Je hebt geen tekst opgegeven.";exit;}
  $time = time();
  if($_POST['t'] != 1){$time = $_POST['time'];}
  $id = $_POST['id'];
  $title = $_POST['title'];
  $text = $_POST['text'];
  mysql_query("UPDATE `news` SET `title`='{$title}',`text`='{$text}',`time`='{$time}' WHERE `id`='{$id}'")or die("Het nieuws kon niet worden bewerkt."); 
  echo "Het nieuws is bewerkt.";
  exit;
}
if(isset($_POST['del'])){
  $id = $_POST['id'];
  mysql_query("DELETE FROM `news` WHERE `id`='{$id}'")or die("Het nieuws kon niet worden verwijderd."); 
  echo "Het nieuws is verwijderd.";
  exit;
}
if($_GET['page']=="addnews"){
?>
<table width="100%">
  <tr>
    <td class="subTitle"><b>Plaats nieuws</b></td>
  </tr>
    <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td class="mainTxt"><form method=post>
	                    Titel: <input type=text name=title><br>
						Tekst: <textarea name=text rows=10	cols=50 onkeypress=textCounter(this,this.form.counter,999);></TEXTAREA><br>
						<input type=submit name=addnew value=Toevoegen>
						</form>
	</td>
  </tr>  
</table>

<?php
}
elseif(isset($_GET['d'])){
  $id = $_GET['d'];
  $sql = mysql_query("SELECT * FROM `news` WHERE `id`='$id'");
  $news = mysql_fetch_object($sql);
  $time = date('d/m/Y', $news->time);
  $title = $news->title;
  $text = $news->text;
?>
<table width="100%">
  <tr>
    <td class="subTitle"><b>Bevestig verwijderen</b></td>
  </tr>
    <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td class="mainTxt"><form method=post>
	                    Bent u zeker dat u dit nieuws wilt verwijderen?<br>
						Nr <?php echo $id; ?> <i><b><?php echo $title; ?></b></i> <?php echo $time; ?> <br><br><?php echo $text; ?>
						<input type=hidden name=id value=<?php echo $id; ?>><br>
						<input type=submit name=del value=Ja>
						</form>
	</td>
  </tr>  
</table>
<?php
}
elseif($_GET['page']=="delnews" || isset($_GET['p'])){
?>
<table width="100%">
  <tr>
    <td class="subTitle"><b>Verwijder nieuws</b></td>
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
	    $id = $news->id;
	    $time = date('d/m/Y', $news->time);
	    $title = $news->title;
		$text = $news->text;
	    echo"Nr $id <i><b>$title</b></i> $time <a href=?d=$id>Delete</a><br><br>$text<br><br>**********<br><br>";
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
<?php
}
elseif(isset($_GET['x'])){
  $id = $_GET['x'];
  $sql = mysql_query("SELECT * FROM `news` WHERE `id`='$id'");
  $news = mysql_fetch_object($sql);
  $time = $news->time;
  $title = $news->title;
  $text = $news->text;
?>
<table width="100%">
  <tr>
    <td class="subTitle"><b>Bewerk nieuws</b></td>
  </tr>
    <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td class="mainTxt"><form method=post>
	                    Titel: <input type=text name=title value="<?php echo $title; ?>"><br>
						Tekst: <textarea name=text rows=10	cols=50 onkeypress=textCounter(this,this.form.counter,999);><?php echo $text; ?></TEXTAREA><br>
						Tijd updaten: <input type=checkbox name=t value=1><br>
						<input type=hidden name=id value=<?php echo $id; ?>><input type=hidden name=time value=<?php echo $time; ?>>
						<input type=submit name=edit value=Ja>
						</form>
	</td>
  </tr>  
</table>
<?php
}
elseif($_GET['page']=="editnews" || isset($_GET['e'])){
?>
<table width="100%">
  <tr>
    <td class="subTitle"><b>Bewerk nieuws</b></td>
  </tr>
    <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td class="mainTxt">**********<br>
	  <?php
	  $pp = 10;
	  $start= ($_GET['e'] >= 0) ? $_GET['e']*$pp : 0;
	  $sql = mysql_query("SELECT id FROM `news`");
	  $rows = mysql_num_rows($sql);	  
	  if($start > $rows){
	    $start = 0;
	  }
	  $sql = mysql_query("SELECT * FROM `news` ORDER BY time DESC LIMIT $start,$pp");
	  while($news = mysql_fetch_object($sql)){
	    $id = $news->id;
	    $time = date('d/m/Y', $news->time);
	    $title = $news->title;
		$text = $news->text;
	    echo"Nr $id <i><b>$title</b></i> $time <a href=?x=$id>Edit</a><br><br>$text<br><br>**********<br><br>";
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
          print "<a href=\"?e=0\">&#60;&#60;</a> <a href=\"?e=". ($start/$pp-1) ."\">&#60;</a> ";
		}
        for($i=0; $i<$rows/$pp; $i++) {
		  if($i == $start/$pp){
		    print "<u>". ($i+1) ."</u> ";
		  }
		  else{
            print "<a href=\"?e=$i\">". ($i+1) ."</a> ";
		  }
        }
        if($start+$pp >= $rows){
          print " &#62; &#62;&#62; ";
		}
        else{
          print "<a href=\"?e=". ($start/$pp+1) ."\">&#62;</a> <a href=\"?e=". (ceil($rows/$pp)-1) ."\">&#62;&#62;</a>";
		}
      }
	  ?>
	</td>
  </tr>
</table>
<?php
}
else{
?>
<table width="100%">
  <tr>
    <td class=subTitle><b>Nieuws instellingen</b></td>
  </tr>
    <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td class=mainTxt><table width="100%"><tr><td width="50%"><a href=?page=addnews>Nieuws toevoegen</a></td><td><a href=?page=delnews>Nieuws verwijderen</a></td></tr><tr><td><a href=?page=editnews>Nieuws bewerken</a></td><td>&nbsp;</td></tr></table></td>
  </tr>
</table>
<?php
}
?>
</body>
</html>