<?PHP
  include("config.php");
    $dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");
  $data	= mysql_fetch_object($dbres);
  if(! check_login()) {
    header("Location: login.php");
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

<script language="javascript">
var checked = 0;
function checkAll() {
  checked = !checked;
  for(i=0; i<document.form1.elements.length; i++)
    document.form1.elements[i].checked = checked;
}
</script>
</head>

<body>
<table width="100%">
<?
$pp = 10;
$start = ($_GET['n'] >= 0) ? $_GET['n']*$pp : 0;
$page = $_GET['p'];
if($_GET['p']=="inbox"){
  $title = ucfirst($_GET['p']);
}
elseif($_GET['p']=="new"){
  $title = "Nieuw";
}
elseif($_GET['p']=="send"){
  $title = "Verzonden";
}
elseif($_GET['p']=="saved"){
  $title = "Opgeslagen";
}
elseif($_GET['p']=="read"){
  $title = "Lezen";
}
else{
  $title = "Berichten";
}
print <<<ENDHTML
  <tr> 
    <td class="subTitle"><b>$title</b></td>
  </tr>
  <tr> 
    <td class="mainTxt">
      <a href="?p=inbox">Inbox</a> | <a href="?p=new">Nieuw bericht</a> | <a href="?p=saved">Opgeslagen</a> | <a href="?p=send">Verzonden</a>
      <br>
	  <table width= 100%>
ENDHTML;

if($_GET['p'] == "inbox") {
  print "<form name=form1 method=post action=?p=del><table width= 100%>\n";
  print "<tr><td width=10><input type=checkbox onClick=checkAll()></td>  <td align=center><i>Van:</i></td>  <td align=center width=225><i>Onderwerp:</i></td>  <td align=center width=175><i>Datum:</i></td></tr>\n";
  $dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`time`) AS `time` FROM `messages` WHERE `to`='{$data->login}' AND `save`='0' ORDER BY `time` DESC LIMIT $start,$pp") or die(mysql_error());
  while($message = mysql_fetch_object($dbres)) {
    if(preg_match('/^\s*$/',$message->subject)){
      $message->subject = "Geen";
	}
	$from = $message->from;
	$time = date('H:i:s  d/m/Y',$message->time);
	if($message->read == 1){
      print "<tr><td width=10><input type=checkbox name=id[] value={$message->id}></td>  <td align=center><a href=\"user.php?x={$message->from}\">{$from}</a></td>  <td align=center><a href=?p=read&id={$message->id}>{$message->subject}</a></td>  <td align=center>{$time}</td>  <td><a href='?p=del&id[]={$message->id}'><img src=images/img_delete.gif border=0></a><a href='?p=save&id={$message->id}'><img src=images/img_save.gif border=0></a></td></tr>\n";
    }
	else{
	  print "<tr><td width=10><input type=checkbox name=id[] value={$message->id}></td>  <td align=center><b><a href=\"user.php?x={$message->from}\">{$from}</a></b></td>  <td align=center><b><a href=?p=read&id={$message->id}>{$message->subject}</a></b></td>  <td align=center>{$time}</td><td><a href='?p=del&id[]={$message->id}'><img src=images/img_delete.gif border=0></a><a href='?p=save&id={$message->id}'><img src=images/img_save.gif border=0></a></td></tr>\n";
	}
  }
  print "</table><input type=submit value=Delete style=\"font-size: 10pt\"></form></td></tr>\n";
  $sql = mysql_query("SELECT * FROM `messages` WHERE `to`='{$data->login}' AND `save`='0'");
  $rows = mysql_num_rows($sql);
  print "</table><tr><td><center>";
      if($rows <= $pp){
        print "&#60;&#60; &#60; 1 &#62; &#62;&#62;";
	  }
      else {
        if($start/$pp == 0){
          print "&#60;&#60; &#60; ";
		}
        else{
          print "<a href=\"?p=$page&n=0\">&#60;&#60;</a> <a href=\"?p=$page&n=". ($start/$pp-1) ."\">&#60;</a> ";
		}
        for($i=0; $i<$rows/$pp; $i++) {
		  if($i == $start/$pp){
		    print "<u>". ($i+1) ."</u> ";
		  }
		  else{
            print "<a href=\"?p=$page&n=$i\">". ($i+1) ."</a> ";
		  }
        }
        if($start+$pp >= $rows){
          print " &#62; &#62;&#62; ";
		}
        else{
          print "<a href=\"?p=$page&n=". ($start/$pp+1) ."\">&#62;</a> <a href=\"?p=$page&n=". (ceil($rows/$pp)-1) ."\">&#62;&#62;</a>";
		}
	}
}
elseif($_GET['p'] == "send") {
  print "<table width=100%>\n";
  print "<tr><td align=center><i>Naar:</i></td>  <td align=center width=225><i>Onderwerp:</i></td>  <td align=center width=175><i>Datum:</i></td></tr>\n";
  $dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`time`) AS `time` FROM `messages` WHERE `from`='{$data->login}' AND `save`='0' ORDER BY `time` DESC LIMIT $start,$pp") or die(mysql_error());
  while($message = mysql_fetch_object($dbres)) {
    if(preg_match('/^\s*$/',$message->subject)){
      $message->subject = "Geen";
	}
	$to = $message->to;
	$time = date('H:i:s  d/m/Y',$message->time);
	if($message->read == 1){
      print "<tr><td align=center><a href=\"user.php?x={$message->to}\">{$to}</a></td>  <td align=center><a href=?p=read&id={$message->id}>{$message->subject}</a></td>  <td align=center>{$time}</td></tr>\n";
    }
	else{
	  print "<tr><td align=center><b><a href=\"user.php?x={$message->to}\">{$to}</a></b></td>  <td align=center><b><a href=?p=read&id={$message->id}>{$message->subject}</a></b></td>  <td align=center>{$time}</td></tr>\n";
	}
  }
  print "</table></td></tr>\n";
  $sql = mysql_query("SELECT * FROM `messages` WHERE `from`='{$data->login}' AND `save`='0'");
  $rows = mysql_num_rows($sql);
  print "</table><tr><center>";
      if($rows <= $pp){
        print "&#60;&#60; &#60; 1 &#62; &#62;&#62;";
	  }
      else {
        if($start/$pp == 0){
          print "&#60;&#60; &#60; ";
		}
        else{
          print "<a href=\"?p=$page&n=0\">&#60;&#60;</a> <a href=\"?p=$page&n=". ($start/$pp-1) ."\">&#60;</a> ";
		}
        for($i=0; $i<$rows/$pp; $i++) {
		  if($i == $start/$pp){
		    print "<u>". ($i+1) ."</u> ";
		  }
		  else{
            print "<a href=\"?p=$page&n=$i\">". ($i+1) ."</a> ";
		  }
        }
        if($start+$pp >= $rows){
          print " &#62; &#62;&#62; ";
		}
        else{
          print "<a href=\"?p=$page&n=". ($start/$pp+1) ."\">&#62;</a> <a href=\"?p=$page&n=". (ceil($rows/$pp)-1) ."\">&#62;&#62;</a>";
		}
	}
}
if($_GET['p'] == "saved") {
  print "<table width= 100%>\n";
  print "<tr><td align=center><i>Van:</i></td>  <td align=center width=225><i>Onderwerp:</i></td>  <td align=center width=175><i>Datum:</i></td></tr>\n";
  $dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`time`) AS `time` FROM `messages` WHERE `to`='{$data->login}' AND `save`='1' ORDER BY `time` DESC LIMIT $start,$pp") or die(mysql_error());
  while($message = mysql_fetch_object($dbres)) {
    if(preg_match('/^\s*$/',$message->subject)){
      $message->subject = "Geen";
	}
	$from = $message->from;
	$time = date('H:i:s  d/m/Y',$message->time);
    print "<tr><td align=center><a href=\"user.php?x={$message->from}\">{$from}</a></td>  <td align=center><a href=?p=read&id={$message->id}>{$message->subject}</a></td>  <td align=center>{$time}</td><td><a href='?p=unsave&id={$message->id}'><img src=images/img_unsave.gif border=0></a></td></tr>\n";
  }
  print "</table></td></tr>\n";
  $sql = mysql_query("SELECT * FROM `messages` WHERE `to`='{$data->login}' AND `save`='1'");
  $rows = mysql_num_rows($sql);
  print "</table><tr><center>";
      if($rows <= $pp){
        print "&#60;&#60; &#60; 1 &#62; &#62;&#62;";
	  }
      else {
        if($start/$pp == 0){
          print "&#60;&#60; &#60; ";
		}
        else{
          print "<a href=\"?p=$page&n=0\">&#60;&#60;</a> <a href=\"?p=$page&n=". ($start/$pp-1) ."\">&#60;</a> ";
		}
        for($i=0; $i<$rows/$pp; $i++) {
		  if($i == $start/$pp){
		    print "<u>". ($i+1) ."</u> ";
		  }
		  else{
            print "<a href=\"?p=$page&n=$i\">". ($i+1) ."</a> ";
		  }
        }
        if($start+$pp >= $rows){
          print " &#62; &#62;&#62; ";
		}
        else{
          print "<a href=\"?p=$page&n=". ($start/$pp+1) ."\">&#62;</a> <a href=\"?p=$page&n=". (ceil($rows/$pp)-1) ."\">&#62;&#62;</a>";
		}
	}
}
elseif($_GET['p'] == "read" && is_numeric($_GET['id'])) {
$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`time`) AS `time` FROM `messages` WHERE `id`='{$_GET['id']}' AND (`to`='{$data->login}' OR `from`='{$data->login}')") or die("The message couldn't be opened.");
if($message = mysql_fetch_object($dbres)) {
  if($message->to == $data->login)
    mysql_query("UPDATE `messages` SET `read`='1' WHERE `id`='{$_GET['id']}'") or die("Couldn't update message status.");
  }
  $message->message		= preg_replace('/(http:\/\/\S+)/','<a href="$1" target=\"_blank\">$1</a>',$message->message);
	    $message->message		= preg_replace("/\[invite](.*?)\[\/invite]/","<a href=invite.php?fam=$1>Accepteren/Weigeren</a>",$message->message);
   	    $message->message		= preg_replace("/\[ws](.*?)\[\/ws]/","<a href=ws.php?sell=$1>[x]</a>",$message->message);
        $message->message		= preg_replace("/\[inc](.*?)\[\/inc]/","<iframe src=\\1>",$message->message);
	$message->message = preg_replace("~\\[url=([^\\[]*)\]([^\\[]*)\\[/url\\]~i","<a href=\"\\1\" target=_blank>\\2</a>",$message->message); 
	$message->message = preg_replace("~\[b\]~i","<b>",$message->message);
    $message->message = preg_replace("~\[/b\]~i","</b>",$message->message);
    $message->message = preg_replace("~\[i\]~i","<i>",$message->message);
    $message->message = preg_replace("~\[/i\]~i","</i>",$message->message);
    $message->message = preg_replace("~\[s\]~i","<s>",$message->message);
    $message->message = preg_replace("~\[/s\]~i","</s>",$message->message);
    $message->message = preg_replace("~\[u\]~i","<u>",$message->message);
    $message->message = preg_replace("~\[/u\]~i","</u>",$message->message);
    $message->message = preg_replace("~\[move\]~i","<marquee>",$message->message);
    $message->message = preg_replace("~\[/move\]~i","</marquee>",$message->message);
    $message->message = preg_replace("~\[list\]~i","<UL>",$message->message);
    $message->message = preg_replace("~\[/list\]~i","</UL>",$message->message);
    $message->message = preg_replace("~\[\*\]~i","<LI>",$message->message);
    $message->message = preg_replace("~\[small\]~i","<font size=1>",$message->message);
    $message->message = preg_replace("~\[/small\]~i","</font>",$message->message); 
    $message->message = preg_replace("~\\[color=([^\\[]*)\]([^\\[]*)\\[/color\\]~i","<font color=\\1>\\2</font>",$message->message); 
    $message->message = preg_replace("~\(b\)~i","<img src=http://members.lycos.nl/js6287/chat/img/biere.gif>",$message->message);
    $message->message = preg_replace("~\\[face=([^\\[]*)\]([^\\[]*)\\[/face\\]~i","<font face=\\1>\\2</font>",$message->message);	
	    $message->message = preg_replace("~\\[size=([^\\[]*)\]([^\\[]*)\\[/size\\]~i","<font size=\\1>\\2</font>",$message->message);        
$message->message = preg_replace("~\(B\)~i","<img src=http://members.lycos.nl/js6287/chat/img/biere.gif>",$message->message);
	$message->message = preg_replace("~:\)~i","<img src=http://members.lycos.nl/js6287/chat/img/sourire.gif>",$message->message);
	$message->message = preg_replace("~:-\)~i","<img src=http://members.lycos.nl/js6287/chat/img/sourire.gif>",$message->message);
	$message->message = preg_replace("~:d~i","<img src=http://members.lycos.nl/js6287/chat/img/content.gif>",$message->message);
	$message->message = preg_replace("~:-D~i","<img src=http://members.lycos.nl/js6287/chat/img/content.gif>",$message->message);
	$message->message = preg_replace("~:-O~i","<img src=http://members.lycos.nl/js6287/chat/img/OH-2.gif>",$message->message);
	$message->message = preg_replace("~:o~i","<img src=http://members.lycos.nl/js6287/chat/img/OH-1.gif>",$message->message);
	$message->message = preg_replace("~:p~i","<img src=http://members.lycos.nl/js6287/chat/img/langue.gif>",$message->message);
	$message->message = preg_replace("~:-P~i","<img src=http://members.lycos.nl/js6287/chat/img/langue.gif>",$message->message);
	$message->message = preg_replace("~\;\)~i","<img src=http://members.lycos.nl/js6287/chat/img/clin-oeuil.gif>",$message->message);
	$message->message = preg_replace("~\;-\)~i","<img src=http://members.lycos.nl/js6287/chat/img/clin-oeuil.gif>",$message->message);
	$message->message = preg_replace("~:\(~i","<img src=http://members.lycos.nl/js6287/chat/img/triste.gif>",$message->message);
	$message->message = preg_replace("~:-\(~i","<img src=http://members.lycos.nl/js6287/chat/img/triste.gif>",$message->message);
	$message->message = preg_replace("~:\|~i","<img src=http://members.lycos.nl/js6287/chat/img/OH-3.gif>",$message->message);
	$message->message = preg_replace("~:-\|~i","<img src=http://members.lycos.nl/js6287/chat/img/OH-3.gif>",$message->message);
	$message->message = preg_replace("~:\'\(~i","<img src=http://members.lycos.nl/js6287/chat/img/pleure.gif>",$message->message);
	$message->message = preg_replace("~\(h\)~i","<img src=http://members.lycos.nl/js6287/chat/img/cool.gif>",$message->message);
	$message->message = preg_replace("~\(H\)~i","<img src=http://members.lycos.nl/js6287/chat/img/cool.gif>",$message->message);
	$message->message = preg_replace("~:-@~i","<img src=http://members.lycos.nl/js6287/chat/img/enerve1.gif>",$message->message);
	$message->message = preg_replace("~:@~i","<img src=http://members.lycos.nl/js6287/chat/img/enerve2.gif>",$message->message);
	$message->message = preg_replace("~:s~i","<img src=http://members.lycos.nl/js6287/chat/img/roll-eyes.gif>",$message->message);
	$message->message = preg_replace("~:-S~i","<img src=http://members.lycos.nl/js6287/chat/img/roll-eyes.gif>",$message->message);
	$message->message = preg_replace("~\(k\)~i","<img src=http://members.lycos.nl/js6287/chat/img/bouche.gif>",$message->message);
	$message->message = preg_replace("~\(K\)~i","<img src=http://members.lycos.nl/js6287/chat/img/bouche.gif>",$message->message);
	$message->message = preg_replace("~\(l\)~i","<img src=http://members.lycos.nl/js6287/chat/img/coeur.gif>",$message->message);
	$message->message = preg_replace("~\(L\)~i","<img src=http://members.lycos.nl/js6287/chat/img/coeur.gif>",$message->message);
	$message->message = preg_replace("~\(u\)~i","<img src=http://members.lycos.nl/js6287/chat/img/coeur-brise.gif>",$message->message);
	$message->message = preg_replace("~\(U\)~i","<img src=http://members.lycos.nl/js6287/chat/img/coeur-brise.gif>",$message->message);
	$message->message = preg_replace("~\;-P~i","<img src=http://members.lycos.nl/js6287/chat/img/clin-oeuil-langue.gif>",$message->message);
	$message->message = preg_replace("~\;p~i","<img src=http://members.lycos.nl/js6287/chat/img/clin-oeuil-langue.gif>",$message->message);
	$message->message = preg_replace("~\(y\)~i","<img src=http://members.lycos.nl/js6287/chat/img/pouce-oui.gif>",$message->message);
	$message->message = preg_replace("~\(Y\)~i","<img src=http://members.lycos.nl/js6287/chat/img/pouce-oui.gif>",$message->message);
	$message->message = preg_replace("~\(n\)~i","<img src=http://members.lycos.nl/js6287/chat/img/pouce-non.gif>",$message->message);
	$message->message = preg_replace("~\(N\)~i","<img src=http://members.lycos.nl/js6287/chat/img/pouce-non.gif>",$message->message);
	$message->message = preg_replace("~\(6\)~i","<img src=http://members.lycos.nl/js6287/chat/img/diable.gif>",$message->message);
	$message->message = preg_replace("~\(d\)~i","<img src=http://members.lycos.nl/js6287/chat/img/drink.gif>",$message->message);
	$message->message = preg_replace("~\(D\)~i","<img src=http://members.lycos.nl/js6287/chat/img/drink.gif>",$message->message);
	$message->message = preg_replace("~_o_~i","<img src=http://members.lycos.nl/js6287/chat/img/worship.gif>",$message->message);
	$message->message = preg_replace("~\(g\)~i","<img src=http://members.lycos.nl/js6287/chat/img/gun.gif>",$message->message);
	$message->message = preg_replace("~\(G\)~i","<img src=http://members.lycos.nl/js6287/chat/img/guns.gif>",$message->message);
	$messages = $message->message;
  $from = $message->from;
  $to = $message->to;
  $time = date('H:i:s  d/m/Y',$message->time);
  echo"
        <tr><td width=100>Tijd:</td>     <td>{$time}</td></tr>
        <tr><td width=100>Van:</td>     <td>{$from}</td></tr>
        <tr><td width=100>Naar:</td>       <td>{$to}</td></tr>
        <tr><td width=100>Onderwerp:</td>  <td>{$message->subject}</td></tr>
        <tr><td width=100>Inhoud:</td>  <td></td></tr>
        <tr><td width=100></td>  <td></td></tr>
        <tr><td colspan=2 width=100%>{$messages}</td></tr>
      </table></td></tr>
      <tr><td align=\"right\"><table width= 100%>
  ";
  if($message->from != $data->login){
    print "<td class=mainTxt align=center width=100><a href='?p=new&to={$message->from}&subject=Re: ". str_replace('Re:','',$message->subject) ."'>Antwoord</a></td>  ";
  }
  else{
    print "<tr>";
  }
  if($message->from != $data->login){
    print "<td class=mainTxt align=center width=100><a href='?p=del&id[]={$message->id}'>Delete</a></td></tr>\n";
  }
}
elseif($_GET['p'] == "del") {
  if(isset($_GET['id'])){
    $_POST['id'] = $_GET['id'];
  }
  foreach($_POST['id'] as $msgid) {
    $dbres = mysql_query("SELECT * FROM `messages` WHERE `id`='$msgid' AND (`from`='{$data->login}' OR `to`='{$data->login}')") or die(mysql_error());
    if($message = mysql_fetch_object($dbres)) {
      if($message->save != 1){
        mysql_query("DELETE FROM `messages` WHERE `id`='$msgid'") or die(mysql_error());
      }
	  else{
        echo"Dit bericht staat in je opgeslagen berichtenlijst.";
		exit;
      }
	}
  }
  echo"Bericht(en) verwijderd.";
}
elseif($_GET['p'] == "save") {
  if(isset($_GET['id'])){
    $dbres = mysql_query("SELECT * FROM `messages` WHERE `id`='{$_GET['id']}' AND (`from`='{$data->login}' OR `to`='{$data->login}')") or die(mysql_error());
    if($message = mysql_fetch_object($dbres)) {
      if($message->save != 1){
        mysql_query("UPDATE `messages` SET `save`='1' WHERE `id`='{$_GET['id']}'") or die("Couldn't update message status.");
      }
	  else{
        echo"Dit bericht is reeds opgeslagen.";
		exit;
      }
	  echo"Bericht opgeslagen.";
	}
  }
}
elseif($_GET['p'] == "unsave") {
  if(isset($_GET['id'])){
    $dbres = mysql_query("SELECT * FROM `messages` WHERE `id`='{$_GET['id']}' AND (`from`='{$data->login}' OR `to`='{$data->login}')") or die(mysql_error());
    if($message = mysql_fetch_object($dbres)) {
      if($message->save != 0){
        mysql_query("UPDATE `messages` SET `save`='0' WHERE `id`='{$_GET['id']}'") or die("Couldn't update message status.");
      }
	  else{
        echo"Dit bericht is niet opgeslagen.";
		exit;
      }
	  echo"Bericht verwijderd uit opgeslagen berichtenlijst.";
	}
  }
}
elseif($_GET['p'] == "new") {
  if(isset($_POST['to'],$_POST['message'])) {
    if($_POST['to'] != $data->login) {
      $dbres = mysql_query("SELECT * FROM `users` WHERE `login`='{$_POST['to']}'") or die("Kon de gegevens niet uit de database halen. Sorry voor het ongemak.");
      $info = mysql_fetch_object($dbres);
      if($info == false){
        print "'{$_POST['to']}' bestaat niet.";
	  }
      elseif($info->activated == 0){
        print "'{$_POST['to']}' heeft zijn account niet geactiveerd.";
	  }
      else {
        $subject = preg_replace('/</','&#60;',$_POST['subject']);
        $message = preg_replace('/</','&#60;',$_POST['message']);
        $dbres = mysql_query("SELECT `id` FROM `users` WHERE `login`='{$_POST['to']}'") or die(mysql_error());
        if($recp = mysql_fetch_object($dbres)) {
          mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'{$data->login}','{$_POST['to']}','{$subject}','{$message}')") or die("Het bericht kon niet verzonden worden.");
		  print "Het bericht is met succes verzonden.";
        }
      }
    }
    else{
      print "Je kan geen bericht naar jezelf sturen.";
	}
  }
  $_REQUEST['message'] = stripslashes($_REQUEST['message']);
  echo"
  <table width= 100%><tr><td>
    <form name=\"form1\" method=\"POST\" action=\"?p=new\"><table align=center>
    <tr><td width=100>Naar:</td>        <td><input type=\"text\" name=\"to\" value=\"{$_REQUEST['to']}\" maxlength=16></td></tr>
    <tr><td width=100>Onderwerp:</td>    <td><input type=\"text\" name=\"subject\" value=\"{$_REQUEST['subject']}\" maxlength=25></td></tr>
    <tr><td width=100 valign=\"top\">Bericht:<br><br>
    </td><td><textarea name=\"message\" cols=40 rows=10>{$_REQUEST['message']}</textarea></td></tr>
    <tr><td width=100></td><td><input type=\"submit\" name=\"submit\" value=\"Verzenden\"></td></tr>
  ";
}
?>
