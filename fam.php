<?php
  include("config.php");
$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");  
$data    = mysql_fetch_object($dbres);
  if(! check_login()) {
    header('Location: login.php');
    exit;
  }
?>
<html>
<head>
<title>Vendetta</title>
<link rel="stylesheet" type="text/css" href="style.css">
<meta name="keywords" content="Vendetta,Crimegame,crimegame,vendetta">
<meta name="language" content="english">
<META name="description" lang="nl" content="Vendetta crimegame met pit.">
</head>
<table width=100%>
<?php
  if(isset($_GET['x'])) {
    $dbres = mysql_query("SELECT * FROM `famillie` WHERE `name`='{$_GET['x']}'");
    if($famillie = mysql_fetch_object($dbres)) {
    $info = preg_replace("/\n/","<br>\n",$famillie->info);
    $info = preg_replace("/\[img](.*?)\[\/img]/","<img src=\"\\1\">",$info);
	$info = eregi_replace("\\[url=([^\\[]*)\]([^\\[]*)\\[/url\\]","<a href=\"\\1\" target=_blank>\\2</a>",$info); 
	$info = eregi_replace("\[b\]","<b>",$info);
    $info = eregi_replace("\[/b\]","</b>",$info);
    $info = eregi_replace("\[i\]","<i>",$info);
    $info = eregi_replace("\[/i\]","</i>",$info);
    $info = eregi_replace("\[s\]","<s>",$info);
    $info = eregi_replace("\[/s\]","</s>",$info);
    $info = eregi_replace("\[move\]","<marquee>",$info);
    $info = eregi_replace("\[/move\]","</marquee>",$info);
    $info = eregi_replace("\[u\]","<u>",$info);
    $info = eregi_replace("\[/u\]","</u>",$info);
    $info = eregi_replace("\[list\]","<UL>",$info);
    $info = eregi_replace("\[/list\]","</UL>",$info);
    $info = eregi_replace("\[\*\]","<LI>",$info);
    $info = eregi_replace("\[small\]","<font size=1>",$info);
    $info = eregi_replace("\[/small\]","</font>",$info); 
    $info = eregi_replace("\\[color=([^\\[]*)\]([^\\[]*)\\[/color\\]","<font color=\\1>\\2</font>",$info); 
    $info = eregi_replace("\\[face=([^\\[]*)\]([^\\[]*)\\[/face\\]","<font face=\\1>\\2</font>",$info);	
    $info = eregi_replace("\\[size=([^\\[]*)\]([^\\[]*)\\[/size\\]","<font size=\\1>\\2</font>",$info);
        $info = eregi_replace("\(b\)","<img src=http://members.lycos.nl/js6287/chat/img/biere.gif>",$info);
	$info = eregi_replace("\(B\)","<img src=http://members.lycos.nl/js6287/chat/img/biere.gif>",$info);
	$info = eregi_replace(":\)","<img src=http://members.lycos.nl/js6287/chat/img/sourire.gif>",$info);
	$info = eregi_replace(":-\)","<img src=http://members.lycos.nl/js6287/chat/img/sourire.gif>",$info);
	$info = eregi_replace(":d","<img src=http://members.lycos.nl/js6287/chat/img/content.gif>",$info);
	$info = eregi_replace(":-D","<img src=http://members.lycos.nl/js6287/chat/img/content.gif>",$info);
	$info = eregi_replace(":-O","<img src=http://members.lycos.nl/js6287/chat/img/OH-2.gif>",$info);
	$info = eregi_replace(":o","<img src=http://members.lycos.nl/js6287/chat/img/OH-1.gif>",$info);
	$info = eregi_replace(":p","<img src=http://members.lycos.nl/js6287/chat/img/langue.gif>",$info);
	$info = eregi_replace(":-P","<img src=http://members.lycos.nl/js6287/chat/img/langue.gif>",$info);
	$info = eregi_replace("\;\)","<img src=http://members.lycos.nl/js6287/chat/img/clin-oeuil.gif>",$info);
	$info = eregi_replace("\;-\)","<img src=http://members.lycos.nl/js6287/chat/img/clin-oeuil.gif>",$info);
	$info = eregi_replace(":\(","<img src=http://members.lycos.nl/js6287/chat/img/triste.gif>",$info);
	$info = eregi_replace(":-\(","<img src=http://members.lycos.nl/js6287/chat/img/triste.gif>",$info);
	$info = eregi_replace(":\|","<img src=http://members.lycos.nl/js6287/chat/img/OH-3.gif>",$info);
	$info = eregi_replace(":-\|","<img src=http://members.lycos.nl/js6287/chat/img/OH-3.gif>",$info);
	$info = eregi_replace(":\'\(","<img src=http://members.lycos.nl/js6287/chat/img/pleure.gif>",$info);
	$info = eregi_replace("\(h\)","<img src=http://members.lycos.nl/js6287/chat/img/cool.gif>",$info);
	$info = eregi_replace("\(H\)","<img src=http://members.lycos.nl/js6287/chat/img/cool.gif>",$info);
	$info = eregi_replace(":-@","<img src=http://members.lycos.nl/js6287/chat/img/enerve1.gif>",$info);
	$info = eregi_replace(":@","<img src=http://members.lycos.nl/js6287/chat/img/enerve2.gif>",$info);
	$info = eregi_replace(":s","<img src=http://members.lycos.nl/js6287/chat/img/roll-eyes.gif>",$info);
	$info = eregi_replace(":-S","<img src=http://members.lycos.nl/js6287/chat/img/roll-eyes.gif>",$info);
	$info = eregi_replace("\(k\)","<img src=http://members.lycos.nl/js6287/chat/img/bouche.gif>",$info);
	$info = eregi_replace("\(K\)","<img src=http://members.lycos.nl/js6287/chat/img/bouche.gif>",$info);
	$info = eregi_replace("\(l\)","<img src=http://members.lycos.nl/js6287/chat/img/coeur.gif>",$info);
	$info = eregi_replace("\(L\)","<img src=http://members.lycos.nl/js6287/chat/img/coeur.gif>",$info);
	$info = eregi_replace("\(u\)","<img src=http://members.lycos.nl/js6287/chat/img/coeur-brise.gif>",$info);
	$info = eregi_replace("\(U\)","<img src=http://members.lycos.nl/js6287/chat/img/coeur-brise.gif>",$info);
	$info = eregi_replace("\;-P","<img src=http://members.lycos.nl/js6287/chat/img/clin-oeuil-langue.gif>",$info);
	$info = eregi_replace("\;p","<img src=http://members.lycos.nl/js6287/chat/img/clin-oeuil-langue.gif>",$info);
	$info = eregi_replace("\(y\)","<img src=http://members.lycos.nl/js6287/chat/img/pouce-oui.gif>",$info);
	$info = eregi_replace("\(Y\)","<img src=http://members.lycos.nl/js6287/chat/img/pouce-oui.gif>",$info);
	$info = eregi_replace("\(n\)","<img src=http://members.lycos.nl/js6287/chat/img/pouce-non.gif>",$info);
	$info = eregi_replace("\(N\)","<img src=http://members.lycos.nl/js6287/chat/img/pouce-non.gif>",$info);
	$info = eregi_replace("\(6\)","<img src=http://members.lycos.nl/js6287/chat/img/diable.gif>",$info);
	$info = eregi_replace("\(d\)","<img src=http://members.lycos.nl/js6287/chat/img/drink.gif>",$info);
	$info = eregi_replace("\(D\)","<img src=http://members.lycos.nl/js6287/chat/img/drink.gif>",$info);
	$info = eregi_replace("_o_","<img src=http://members.lycos.nl/js6287/chat/img/worship.gif>",$info);
	$info = eregi_replace("\(g\)","<img src=http://members.lycos.nl/js6287/chat/img/gun.gif>",$info);
	$info = eregi_replace("\(G\)","<img src=http://members.lycos.nl/js6287/chat/img/guns.gif>",$info);
$owner = mysql_fetch_object(mysql_query("SELECT `login` FROM `users`  WHERE `famrang`='5' AND `famillie`='{$famillie->name}'"));
$halfdon = mysql_fetch_object(mysql_query("SELECT `login` FROM `users`  WHERE `famrang`='4' AND `famillie`='{$famillie->name}'"));
$consiglieri = mysql_fetch_object(mysql_query("SELECT `login` FROM `users`  WHERE `famrang`='3' AND `famillie`='{$famillie->name}'"));
$fambank = number_format($famillie->bank, 0, ',' , ','); 
if($info == ''){$info = "Geen info";}
      print "  <tr> 
    <td class=subTitle><b>Familie {$famillie->name}</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
      print "  <table width=100%>\n";
      print "	<tr><td width=100>Don:</td>  <td><a href=\"user.php?x={$owner->login}\">{$owner->login}</a></td></tr>\n";
      print "	<tr><td width=100>Onderbaas:</td>  <td><a href=\"user.php?x={$halfdon->login}\">{$halfdon->login}</a></td></tr>\n";
      print "	<tr><td width=100>Consiglieri:</td>  <td><a href=\"user.php?x={$consiglieri->login}\">{$consiglieri->login}</a></td></tr>\n";
      print "	<tr><td width=100>Stad:</td>  <td>{$famillie->stad}</td></tr>\n";
      print "	<tr><td width=100>Grond:</td>  <td>{$famillie->grond}m²</td></tr>\n";
      print "	<tr><td width=100>Familiebank:</td>  <td>&euro; {$fambank}</td></tr></td></tr></table>\n";
      print "<tr>
          <td>&nbsp;</td>
        </tr><tr> 
    <td class=subTitle><b>Info</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>$info</td></tr>\n";
      print "<tr>
          <td>&nbsp;</td>
        </tr><tr> 
    <td class=subTitle><b>Members</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
      $us = mysql_query("SELECT `login` FROM `users` WHERE `famillie`='{$_GET['x']}' ORDER BY `xp` DESC");
      while($usr = mysql_fetch_object($us)) {
		print "\n<a href=user.php?x=$usr->login>$usr->login</a>";
      }
      print "  <tr><td><table width=100%>\n";
    }
 }
  elseif ($_GET['p'] == "list") {
    print "  <tr><td><table align=center width=75%><tr> 
    <td class=subTitle><b>Familie Lijst</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
    print "	<table width=100%><tr><td style=\"letter-spacing: normal;\"><b>Familie</b></td>  <td style=\"letter-spacing: normal;\" width=20%><b>Don</b></td>  <td style=\"letter-spacing: normal;\" width=20%><b>Leden</b></td>  <td style=\"letter-spacing: normal;\" width=20%><b>Gebied</b></td><td width=20%><b>Stad</b></td></tr>\n";
    $dbres				= mysql_query("SELECT * FROM `famillie`");
    while($famillie = mysql_fetch_object($dbres)) {
      $power				= 0;
      $dbres2				= mysql_query("SELECT `xp` FROM `users` WHERE `famillie`='{$famillie->name}'");
      while($member = mysql_fetch_object($dbres2))
      $power				+= $member->xp;
      $familliepower[$famillie->type][$famillie->name] = $power;
    }

    foreach($familliepower as $type => $info) {
      arsort($info);
      foreach($info as $name => $power) {
        $dbres				= mysql_query("SELECT * FROM `famillie` WHERE `name`='$name'");
        $famillie					= mysql_fetch_object($dbres);
	$don = mysql_fetch_object(mysql_query("SELECT * FROM `users` WHERE `famrang`='5' AND `famillie`='{$famillie->name}'"));
        $dbres			= mysql_query("SELECT `id` FROM `users` WHERE `famillie`='$name'");
        $nummembers				= mysql_num_rows($dbres);
        print "	<tr><td><a href=\"?x=$name\">$name</a></td>  <td width=20%><a href=\"user.php?x={$don->login}\">{$don->login}</a></td>  <td width=20%>$nummembers</td>  <td width=20%>{$famillie->grond}m²</td><td width=20%>{$famillie->stad}</td>";
          print "</tr>\n";
      }
    }
    print "  </table></table></td></tr>\n";
  }
  else if($_GET['p'] == "new") {
        $dbres					= mysql_query("SELECT `name` FROM `famillie` WHERE `name`='{$_POST['name']}'");
        $exist = mysql_num_rows($dbres);
    print "<tr> 
    <td class=subTitle><b>Maak een familie</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
if ($data->zak < 25000000) { echo "Je hebt niet genoeg geld op zak om een familie te stichten.</B>"; exit; }
elseif ($data->xp < 8000) { echo "Je rank is te laag om een familie op te starten. Je moet minstens Local Chief zijn."; exit;}
elseif ($data->famillie) { echo "Je bent al lid van een familie."; exit; }
elseif ($exist == 1) { echo "Deze familie bestaat al."; exit; }
else {
    if(isset($_POST['name'])) {
      $_POST['name']				= substr($_POST['name'],0,16);
    if(preg_match('/^[a-zA-Z0-9_\-]+$/',$_POST['name']) == 0) { echo "Ongeldige naam."; return; }
      if(preg_match('/^[A-Za-z0-9_\- ]+$/',$_POST['name'])) {
            $data->famillie	= $_POST['name'];
            $data->famrang	= 5;
            mysql_query("UPDATE `users` SET `famillie`='{$_POST['name']}',`famrang`='5' WHERE `login`='{$data->login}'");
            mysql_query("INSERT INTO `famillie`(`name`,`stad`,`grond`) values('{$_POST['name']}','$data->stad','50')");
            mysql_query("UPDATE `users` SET `zak`=`zak`-25000000 WHERE `login`='{$data->login}'");
            echo "Je hebt een familie gesticht. <script language=\"javascript\">setTimeout('parent.window.location.reload()',300)</script>"; exit;
                }
    }
    print "<br><br><form method=\"post\">De naam van je familie wordt: <input type=\"text\" name=\"name\" value=\"{$_POST['name']}\" maxlength=16> <input type=\"submit\" value=\"Ok\" style=\"width: 100\"></form><br>\n";
  }
  }
  else if($_GET['p'] == "delete" && $data->famrang == 5) {
    print "<tr> 
    <td class=subTitle><b>Verwijder familie</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=mainTxt>";
    if(isset($_POST['delete'])) {
      mysql_query("UPDATE `users` SET `famillie`='',`famrang`='0' WHERE `famillie`='{$data->famillie}'");
      mysql_query("DELETE FROM `famillie` WHERE `name`='{$data->famillie}'");
  echo "De familie is verwijderd.";
      print <<<ENDHTML
<script language="javascript">
setTimeout("parent.window.location.reload()",2000);
</script>
ENDHTML;
    }
    else if(isset($_POST['cancel'])) {
      print <<<ENDHTML
<script language="javascript">
document.location = "status.php";
</script>
ENDHTML;
    }
    else {
      print <<<ENDHTML
  <table><tr><td align="center"><form method="post">
	Bent u zeker dat u deze familie wilt verwijderen?<br><br>
	<input type="submit" name="delete" value="Ja" style="width: 100px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="submit" name="cancel" value="Neen" style="width: 100px;">
  </form></td></tr>
ENDHTML;
    }
  }
?>
</table></table>