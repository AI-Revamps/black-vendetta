<?php
/*
CREATE TABLE forum_reacties (
  id int(10) unsigned NOT NULL auto_increment,
  topic_id int(10) NOT NULL default '0',
  user varchar(30) NOT NULL default '',
  subject varchar(50) NOT NULL default '',
  message text NOT NULL,
  date datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

CREATE TABLE forum_topics (
  id int(10) unsigned NOT NULL auto_increment,
  type varchar(255) NOT NULL default '',
  user varchar(30) NOT NULL default '',
  subject varchar(50) NOT NULL default '',
  message text NOT NULL,
  date datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (id)
) TYPE=MyISAM;
*/
require("config.php");
if(isset($_GET['p'])){$_GET['p']=$_GET['p'];}
else {$_GET['p']=0;}
$dbres = mysql_query("SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`,UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`,UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`,UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` FROM `users` WHERE `login`='{$_SESSION['login']}'");
  $data	= mysql_fetch_object($dbres);
  if(! check_login()) {
    header('Location: login.php');
    exit;
  }
?>
<html>
<head>
<title>Vendetta Forum</title>
<link href="style.css" rel="stylesheet" type="text/css">
<meta name="keywords" content="Vendetta,Crimegame,crimegame,vendetta">
<meta name="language" content="english">
<META name="description" lang="nl" content="Vendetta crimegame met pit.">
</head>
<style>
td.forumTxt		{ border: 1px solid #000000; background: #E1E1E1; font-family: "verdana"; font-size: 8pt; padding-left: 5px; }
td.forumTitle	{ border: 1px solid #000000; background: #999999; font-family: "verdana"; font-size: 8pt; padding-left: 5px; }
</style>

<body>
<?php
error_reporting(E_ALL);
$nrpp = 10; //posts per pagina
$nrtpp = 20; //topics per pagina
?>
<table width=100% align=center>
  <tr> 
    <td class="subTitle"><b>Forum</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
<?php
if(isset($_GET['del']))
{
$topics = mysql_query("SELECT id,user FROM forum_topics WHERE `id`='{$_GET['del']}'") or die(mysql_error());
$object = mysql_fetch_object($topics);
   if($data->login == $object->user || $data->level >= 255){
   mysql_query("DELETE FROM `forum_topics` WHERE `id`='{$_GET['del']}'"); 
   mysql_query("DELETE FROM `forum_reacties` WHERE ` topic_id `='{$_GET['del']}'"); 
   echo "<br><br>Topic verwijderd!<br>
                > <a href=javascript:history.go(-1)>Ga terug</a><br><br>";
   }
   else {
   echo"<br><br>Dit topic is niet door jou geplaatst!<br>
                > <a href=javascript:history.go(-1)>Ga terug</a><br><br>";
   }
}
elseif(isset($_GET['delr']))
{
$reacties = mysql_query("SELECT id,user FROM forum_reacties WHERE `id`='{$_GET['delr']}'") or die(mysql_error());
$object = mysql_fetch_object($reacties);
   if($data->login == $object->user || $data->level >= 255){
   mysql_query("DELETE FROM `forum_reacties` WHERE `id`='{$_GET['delr']}'"); 
   echo "<br><br>Reactie verwijderd!<br>
                > <a href=javascript:history.go(-1)>Ga terug</a><br><br>";
   }
   else {
   echo"<br><br>Deze reactie is niet door jou geplaatst!<br>
                > <a href=javascript:history.go(-1)>Ga terug</a><br><br>";
   }
}
elseif(isset($_GET['edit']))
{
$reacties = mysql_query("SELECT message,subject,user FROM forum_topics WHERE `id`='{$_GET['edit']}'") or die(mysql_error());
$object = mysql_fetch_object($reacties);
   if($data->login == $object->user || $data->level >= 255){
   if($_SERVER['REQUEST_METHOD'] != 'POST')   
        {
?>
<form method="post">
  <table width="100%">
    <tr><td>&nbsp;&nbsp;</td></tr>
	<tr>
          <td colspan="2" align="center"><b>Verander je topic</b></td>
    </tr>
      <?php echo"<input name=user type=hidden size=50 maxlength=30 value='$data->login'>"; ?>
    <tr>
      <td align="right" width="30%">Onderwerp: </td>
      <?php echo"<td align=left><input name=subject type=text size=50 maxlength=50 value='$object->subject'></td>"; ?>
    </tr>
    <tr>
	<?php echo"<td colspan=2 align=center><textarea name=message cols=64 rows=5>$object->message</textarea></td>"; ?>
    </tr>
    <tr>
      <td colspan="2" align="center"><input type="submit" name="Submit" value="Verzenden">
      <input type="reset" name="Reset" value="Wis velden"></td>
    </tr>
  </table>
</form>
<?php
        }
        else
        {
            if($_POST['subject'] != "" AND $_POST['message'] != "")
            {
                if(strlen(str_replace(" ", "", $_POST['subject'])) < 2)
                {
                ?>
                <br><br>Vul een goed onderwerp in!<br>
                > <a href="javascript:history.go(-1)">Ga terug</a><br><br>
                <?php
                }
                elseif(strlen(str_replace(" ", "", $_POST['message'])) < 2)
                {
                ?>
                <br><br>Vul een goed bericht in!<br>
                > <a href="javascript:history.go(-1)">Ga terug</a><br><br>
                <?php
                }
                else
                {
				mysql_query("UPDATE `forum_topics` SET `subject`='".addslashes($_POST['subject'])."',`message`='".addslashes($_POST['message'])."' WHERE `id`='{$_GET['edit']}'");
                echo "<script language=\"JavaScript\">top.location.href='javascript:history.go(-2)';</script>";
                }
            }
            else
            {
            ?>
            <br><br>Onderwerp en bericht zijn verplichte velden!<br>
            > <a href="javascript:history.go(-1)">Ga terug</a><br><br>
            <?php
            }
        }
   }
   else {
   echo"<br><br>Deze reactie is niet door jou geplaatst!<br>
                > <a href=javascript:history.go(-1)>Ga terug</a><br><br>";
   }
}
elseif(isset($_GET['editr']))
{
$reacties = mysql_query("SELECT message,subject,user FROM forum_reacties WHERE `id`='{$_GET['editr']}'") or die(mysql_error());
$object = mysql_fetch_object($reacties);
   if($data->login == $object->user || $data->level >= 255){
   if($_SERVER['REQUEST_METHOD'] != 'POST')   
        {
?>
<form method="post">
  <table width="100%">
    <tr><td>&nbsp;&nbsp;</td></tr>
	<tr>
          <td colspan="2" align="center"><b>Verander je reactie</b></td>
    </tr>
      <?php echo"<input name=user type=hidden size=50 maxlength=30 value='$data->login'>"; ?>
    <tr>
      <td align="right" width="30%">Onderwerp: </td>
      <?php echo"<td align=left><input name=subject type=text size=50 maxlength=50 value='$object->subject'></td>"; ?>
    </tr>
    <tr>
	<?php echo"<td colspan=2 align=center><textarea name=message cols=64 rows=5>$object->message</textarea></td>"; ?>
    </tr>
    <tr>
      <td colspan="2" align="center"><input type="submit" name="Submit" value="Verzenden">
      <input type="reset" name="Reset" value="Wis velden"></td>
    </tr>
  </table>
</form>
<?php
        }
        else
        {
            if($_POST['subject'] != "" AND $_POST['message'] != "")
            {
                if(strlen(str_replace(" ", "", $_POST['subject'])) < 2)
                {
                ?>
                <br><br>Vul een goed onderwerp in!<br>
                > <a href="javascript:history.go(-1)">Ga terug</a><br><br>
                <?php
                }
                elseif(strlen(str_replace(" ", "", $_POST['message'])) < 2)
                {
                ?>
                <br><br>Vul een goed bericht in!<br>
                > <a href="javascript:history.go(-1)">Ga terug</a><br><br>
                <?php
                }
                else
                {
				mysql_query("UPDATE `forum_reacties` SET `subject`='".addslashes($_POST['subject'])."',`message`='".addslashes($_POST['message'])."' WHERE `id`='{$_GET['editr']}'");
                echo "<script language=\"JavaScript\">top.location.href='javascript:history.go(-2)';</script>";
                }
            }
            else
            {
            ?>
            <br><br>Onderwerp en bericht zijn verplichte velden!<br>
            > <a href="javascript:history.go(-1)">Ga terug</a><br><br>
            <?php
            }
        }
   }
   else {
   echo"<br><br>Deze reactie is niet door jou geplaatst!<br>
                > <a href=javascript:history.go(-1)>Ga terug</a><br><br>";
   }
}
elseif(isset($_GET['topic']))
{
// Voor de topic
$topic = mysql_query("SELECT id,type,user,subject,message,date FROM forum_topics WHERE id = ".addslashes($_GET['topic'])) or die(mysql_error());
$aantal_topics = mysql_num_rows($topic);
    if($aantal_topics == 1)
    {
        while($object = mysql_fetch_assoc($topic))
        {
        $id = $object['id'];
        $subject = stripslashes($object['subject']);
		$user = $object['user'];
		$type = $object['type'];
		$familie = mysql_query("SELECT * FROM `famillie` WHERE `name`='$type'");
		$familie = mysql_num_rows($familie);
		if($familie == 1 && $data->famillie != $type){ echo"Deze pagina is niet voo jou toegankelijk, ze is enkel voor de $type familie.<br> &gt; <a href=\"javascript:history.go(-1)\">Ga terug</a><br><br>";exit;}	
?>
<table width="75%" align="center">
  <tr> 
    <td colspan="2" align="center"><b><a href=<?php echo $_SERVER['PHP_SELF'] ?>>Categorie&euml;n</a> - <a href=<?php echo $_SERVER['PHP_SELF']."?type=".$object['type']; ?>><?php echo"{$object['type']}"; ?></a> - <?php echo stripslashes(htmlspecialchars($object['subject'])); ?></b></td>
  </tr>  
  <tr> 
    <td colspan="2" align="center"> <? $begin= ($_GET['p'] >= 0) ? $_GET['p']*$nrpp : 0;
$nr = mysql_query("SELECT id,user,subject,message,date FROM forum_reacties WHERE topic_id = ".addslashes($_GET['topic'])) or die(mysql_error());
     if(mysql_num_rows($nr) <= $nrpp)
    print "&#60; 1 &#62;";
  else {
    if($begin/$nrpp == 0)
      print "&#60;&#60; ";
    else
      print "<a href=\"?topic={$_GET['topic']}&p=". ($begin/$nrpp-1) ."\">&#60;&#60;</a> ";

    for($i=0; $i<mysql_num_rows($nr)/$nrpp; $i++) {
      print "<a href=\"?topic={$_GET['topic']}&p=$i\">". ($i+1) ."</a> ";
    }

    if($begin+$nrpp >= mysql_num_rows($nr))
      print "&#62;&#62; ";
    else
      print "<a href=\"?topic={$_GET['topic']}&p=". ($begin/$nrpp+1) ."\">&#62;&#62;</a>";
  }
  ?>
     </td>
  </tr> 
  <? if($_GET['p'] == 0) {
    $object['message'] = preg_replace("/\[img](.*?)\[\/img]/","<img src=\"\\1\">",$object['message']);
	$object['message'] = eregi_replace("\\[url=([^\\[]*)\]([^\\[]*)\\[/url\\]","<a href=\"\\1\" target=_blank>\\2</a>",$object['message']); 
	$object['message'] = eregi_replace("\[b\]","<b>",$object['message']);
    $object['message'] = eregi_replace("\[/b\]","</b>",$object['message']);
    $object['message'] = eregi_replace("\[i\]","<i>",$object['message']);
    $object['message'] = eregi_replace("\[/i\]","</i>",$object['message']);
    $object['message'] = eregi_replace("\[s\]","<s>",$object['message']);
    $object['message'] = eregi_replace("\[/s\]","</s>",$object['message']);
    $object['message'] = eregi_replace("\[move\]","<marquee>",$object['message']);
    $object['message'] = eregi_replace("\[/move\]","</marquee>",$object['message']);
    $object['message'] = eregi_replace("\[u\]","<u>",$object['message']);
    $object['message'] = eregi_replace("\[/u\]","</u>",$object['message']);
    $object['message'] = eregi_replace("\[list\]","<UL>",$object['message']);
    $object['message'] = eregi_replace("\[/list\]","</UL>",$object['message']);
    $object['message'] = eregi_replace("\[\*\]","<LI>",$object['message']);
    $object['message'] = eregi_replace("\[small\]","<font size=1>",$object['message']);
    $object['message'] = eregi_replace("\[/small\]","</font>",$object['message']); 
    $object['message'] = eregi_replace("\\[color=([^\\[]*)\]([^\\[]*)\\[/color\\]","<font color=\\1>\\2</font>",$object['message']); 
	    $object['message'] = eregi_replace("\\[size=([^\\[]*)\]([^\\[]*)\\[/size\\]","<font size=\\1>\\2</font>",$object['message']);
$object['message'] = eregi_replace("\(b\)","<img src=http://members.lycos.nl/js6287/chat/img/biere.gif>",$object['message']);
	$object['message'] = eregi_replace("\(B\)","<img src=http://members.lycos.nl/js6287/chat/img/biere.gif>",$object['message']);
	$object['message'] = eregi_replace(":\)","<img src=http://members.lycos.nl/js6287/chat/img/sourire.gif>",$object['message']);
	$object['message'] = eregi_replace(":-\)","<img src=http://members.lycos.nl/js6287/chat/img/sourire.gif>",$object['message']);
	$object['message'] = eregi_replace(":d","<img src=http://members.lycos.nl/js6287/chat/img/content.gif>",$object['message']);
	$object['message'] = eregi_replace(":-D","<img src=http://members.lycos.nl/js6287/chat/img/content.gif>",$object['message']);
	$object['message'] = eregi_replace(":-O","<img src=http://members.lycos.nl/js6287/chat/img/OH-2.gif>",$object['message']);
	$object['message'] = eregi_replace(":o","<img src=http://members.lycos.nl/js6287/chat/img/OH-1.gif>",$object['message']);
	$object['message'] = eregi_replace(":p","<img src=http://members.lycos.nl/js6287/chat/img/langue.gif>",$object['message']);
	$object['message'] = eregi_replace(":-P","<img src=http://members.lycos.nl/js6287/chat/img/langue.gif>",$object['message']);
	$object['message'] = eregi_replace("\;\)","<img src=http://members.lycos.nl/js6287/chat/img/clin-oeuil.gif>",$object['message']);
	$object['message'] = eregi_replace("\;-\)","<img src=http://members.lycos.nl/js6287/chat/img/clin-oeuil.gif>",$object['message']);
	$object['message'] = eregi_replace(":\(","<img src=http://members.lycos.nl/js6287/chat/img/triste.gif>",$object['message']);
	$object['message'] = eregi_replace(":-\(","<img src=http://members.lycos.nl/js6287/chat/img/triste.gif>",$object['message']);
	$object['message'] = eregi_replace(":\|","<img src=http://members.lycos.nl/js6287/chat/img/OH-3.gif>",$object['message']);
	$object['message'] = eregi_replace(":-\|","<img src=http://members.lycos.nl/js6287/chat/img/OH-3.gif>",$object['message']);
	$object['message'] = eregi_replace(":\'\(","<img src=http://members.lycos.nl/js6287/chat/img/pleure.gif>",$object['message']);
	$object['message'] = eregi_replace("\(h\)","<img src=http://members.lycos.nl/js6287/chat/img/cool.gif>",$object['message']);
	$object['message'] = eregi_replace("\(H\)","<img src=http://members.lycos.nl/js6287/chat/img/cool.gif>",$object['message']);
	$object['message'] = eregi_replace(":-@","<img src=http://members.lycos.nl/js6287/chat/img/enerve1.gif>",$object['message']);
	$object['message'] = eregi_replace(":@","<img src=http://members.lycos.nl/js6287/chat/img/enerve2.gif>",$object['message']);
	$object['message'] = eregi_replace(":s","<img src=http://members.lycos.nl/js6287/chat/img/roll-eyes.gif>",$object['message']);
	$object['message'] = eregi_replace(":-S","<img src=http://members.lycos.nl/js6287/chat/img/roll-eyes.gif>",$object['message']);
	$object['message'] = eregi_replace("\(k\)","<img src=http://members.lycos.nl/js6287/chat/img/bouche.gif>",$object['message']);
	$object['message'] = eregi_replace("\(K\)","<img src=http://members.lycos.nl/js6287/chat/img/bouche.gif>",$object['message']);
	$object['message'] = eregi_replace("\(l\)","<img src=http://members.lycos.nl/js6287/chat/img/coeur.gif>",$object['message']);
	$object['message'] = eregi_replace("\(L\)","<img src=http://members.lycos.nl/js6287/chat/img/coeur.gif>",$object['message']);
	$object['message'] = eregi_replace("\(u\)","<img src=http://members.lycos.nl/js6287/chat/img/coeur-brise.gif>",$object['message']);
	$object['message'] = eregi_replace("\(U\)","<img src=http://members.lycos.nl/js6287/chat/img/coeur-brise.gif>",$object['message']);
	$object['message'] = eregi_replace("\;-P","<img src=http://members.lycos.nl/js6287/chat/img/clin-oeuil-langue.gif>",$object['message']);
	$object['message'] = eregi_replace("\;p","<img src=http://members.lycos.nl/js6287/chat/img/clin-oeuil-langue.gif>",$object['message']);
	$object['message'] = eregi_replace("\(y\)","<img src=http://members.lycos.nl/js6287/chat/img/pouce-oui.gif>",$object['message']);
	$object['message'] = eregi_replace("\(Y\)","<img src=http://members.lycos.nl/js6287/chat/img/pouce-oui.gif>",$object['message']);
	$object['message'] = eregi_replace("\(n\)","<img src=http://members.lycos.nl/js6287/chat/img/pouce-non.gif>",$object['message']);
	$object['message'] = eregi_replace("\(N\)","<img src=http://members.lycos.nl/js6287/chat/img/pouce-non.gif>",$object['message']);
	$object['message'] = eregi_replace("\(6\)","<img src=http://members.lycos.nl/js6287/chat/img/diable.gif>",$object['message']);
	$object['message'] = eregi_replace("\(d\)","<img src=http://members.lycos.nl/js6287/chat/img/drink.gif>",$object['message']);
	$object['message'] = eregi_replace("\(D\)","<img src=http://members.lycos.nl/js6287/chat/img/drink.gif>",$object['message']);
	$object['message'] = eregi_replace("_o_","<img src=http://members.lycos.nl/js6287/chat/img/worship.gif>",$object['message']);
	$object['message'] = eregi_replace("\(g\)","<img src=http://members.lycos.nl/js6287/chat/img/gun.gif>",$object['message']);
	$object['message'] = eregi_replace("\(G\)","<img src=http://members.lycos.nl/js6287/chat/img/guns.gif>",$object['message']);
  ?>
  <tr> 
    <td colspan="2" class="forumTitle"><table width="100%">
        <tr> 
          <td width="20%">Door: </td>
          <td><?php echo "<a href=user.php?x=$user>$user</a>";?> &nbsp;&nbsp;&nbsp; <?php echo"<a href=message.php?p=new&to=$user><img border=0 src=http://members.lycos.nl/js6287/mail.gif height=11 width=11></a>&nbsp;"?>
            <?php if ($data->login == $object['user'] || $data->level >= 255){echo"<a href=?del=".$object['id']."><img border=0 src=http://members.lycos.nl/js6287/del.png height=11 width=11></a>&nbsp;<a href=?edit=".$object['id']."><img border=0 src=http://members.lycos.nl/js6287/edit.png height=11 width=11></a>";}?>
          </td>
        </tr>
        <tr> 
          <td>Titel:</td>
          <td><b><?php echo stripslashes(htmlspecialchars($object['subject'])); ?></b></td>
        </tr>
        <tr> 
          <td>Tijd:</td>
          <td><?php echo $object['date']; ?></td>
        </tr>
      </table></td>
  </tr>

  <tr> 
    <td class="forumTitle" width="20%">Bericht: </td>
    <td class="forumTxt"><i><?php echo nl2br(stripslashes($object['message'])); ?></i></td>
  </tr>
</table>
<?php
}
        }
    // Voor de reacties
    $message = mysql_query("SELECT id,user,subject,message,date FROM forum_reacties WHERE topic_id = ".addslashes($_GET['topic'])." ORDER BY 'date' ASC LIMIT $begin,$nrpp") or die(mysql_error());
    $aantal_messages = mysql_num_rows($message);
        if($aantal_messages != 0)
        {
            while($object = mysql_fetch_assoc($message))
            {
	$user = $object['user'];
	$object['message'] = preg_replace("/\[img](.*?)\[\/img]/","<img src=\"\\1\">",$object['message']);
	$object['message'] = eregi_replace("\\[url=([^\\[]*)\]([^\\[]*)\\[/url\\]","<a href=\"\\1\" target=_blank>\\2</a>",$object['message']); 
	$object['message'] = eregi_replace("\[b\]","<b>",$object['message']);
    $object['message'] = eregi_replace("\[/b\]","</b>",$object['message']);
    $object['message'] = eregi_replace("\[i\]","<i>",$object['message']);
    $object['message'] = eregi_replace("\[/i\]","</i>",$object['message']);
    $object['message'] = eregi_replace("\[s\]","<s>",$object['message']);
    $object['message'] = eregi_replace("\[/s\]","</s>",$object['message']);
    $object['message'] = eregi_replace("\[move\]","<marquee>",$object['message']);
    $object['message'] = eregi_replace("\[/move\]","</marquee>",$object['message']);
    $object['message'] = eregi_replace("\[u\]","<u>",$object['message']);
    $object['message'] = eregi_replace("\[/u\]","</u>",$object['message']);
    $object['message'] = eregi_replace("\[list\]","<UL>",$object['message']);
    $object['message'] = eregi_replace("\[/list\]","</UL>",$object['message']);
    $object['message'] = eregi_replace("\[\*\]","<LI>",$object['message']);
    $object['message'] = eregi_replace("\[small\]","<font size=1>",$object['message']);
    $object['message'] = eregi_replace("\[/small\]","</font>",$object['message']); 
    $object['message'] = eregi_replace("\\[color=([^\\[]*)\]([^\\[]*)\\[/color\\]","<font color=\\1>\\2</font>",$object['message']); 
		    $object['message'] = eregi_replace("\\[size=([^\\[]*)\]([^\\[]*)\\[/size\\]","<font size=\\1>\\2</font>",$object['message']);
$object['message'] = eregi_replace("\(b\)","<img src=http://members.lycos.nl/js6287/chat/img/biere.gif>",$object['message']);
	$object['message'] = eregi_replace("\(B\)","<img src=http://members.lycos.nl/js6287/chat/img/biere.gif>",$object['message']);
	$object['message'] = eregi_replace(":\)","<img src=http://members.lycos.nl/js6287/chat/img/sourire.gif>",$object['message']);
	$object['message'] = eregi_replace(":-\)","<img src=http://members.lycos.nl/js6287/chat/img/sourire.gif>",$object['message']);
	$object['message'] = eregi_replace(":d","<img src=http://members.lycos.nl/js6287/chat/img/content.gif>",$object['message']);
	$object['message'] = eregi_replace(":-D","<img src=http://members.lycos.nl/js6287/chat/img/content.gif>",$object['message']);
	$object['message'] = eregi_replace(":-O","<img src=http://members.lycos.nl/js6287/chat/img/OH-2.gif>",$object['message']);
	$object['message'] = eregi_replace(":o","<img src=http://members.lycos.nl/js6287/chat/img/OH-1.gif>",$object['message']);
	$object['message'] = eregi_replace(":p","<img src=http://members.lycos.nl/js6287/chat/img/langue.gif>",$object['message']);
	$object['message'] = eregi_replace(":-P","<img src=http://members.lycos.nl/js6287/chat/img/langue.gif>",$object['message']);
	$object['message'] = eregi_replace("\;\)","<img src=http://members.lycos.nl/js6287/chat/img/clin-oeuil.gif>",$object['message']);
	$object['message'] = eregi_replace("\;-\)","<img src=http://members.lycos.nl/js6287/chat/img/clin-oeuil.gif>",$object['message']);
	$object['message'] = eregi_replace(":\(","<img src=http://members.lycos.nl/js6287/chat/img/triste.gif>",$object['message']);
	$object['message'] = eregi_replace(":-\(","<img src=http://members.lycos.nl/js6287/chat/img/triste.gif>",$object['message']);
	$object['message'] = eregi_replace(":\|","<img src=http://members.lycos.nl/js6287/chat/img/OH-3.gif>",$object['message']);
	$object['message'] = eregi_replace(":-\|","<img src=http://members.lycos.nl/js6287/chat/img/OH-3.gif>",$object['message']);
	$object['message'] = eregi_replace(":\'\(","<img src=http://members.lycos.nl/js6287/chat/img/pleure.gif>",$object['message']);
	$object['message'] = eregi_replace("\(h\)","<img src=http://members.lycos.nl/js6287/chat/img/cool.gif>",$object['message']);
	$object['message'] = eregi_replace("\(H\)","<img src=http://members.lycos.nl/js6287/chat/img/cool.gif>",$object['message']);
	$object['message'] = eregi_replace(":-@","<img src=http://members.lycos.nl/js6287/chat/img/enerve1.gif>",$object['message']);
	$object['message'] = eregi_replace(":@","<img src=http://members.lycos.nl/js6287/chat/img/enerve2.gif>",$object['message']);
	$object['message'] = eregi_replace(":s","<img src=http://members.lycos.nl/js6287/chat/img/roll-eyes.gif>",$object['message']);
	$object['message'] = eregi_replace(":-S","<img src=http://members.lycos.nl/js6287/chat/img/roll-eyes.gif>",$object['message']);
	$object['message'] = eregi_replace("\(k\)","<img src=http://members.lycos.nl/js6287/chat/img/bouche.gif>",$object['message']);
	$object['message'] = eregi_replace("\(K\)","<img src=http://members.lycos.nl/js6287/chat/img/bouche.gif>",$object['message']);
	$object['message'] = eregi_replace("\(l\)","<img src=http://members.lycos.nl/js6287/chat/img/coeur.gif>",$object['message']);
	$object['message'] = eregi_replace("\(L\)","<img src=http://members.lycos.nl/js6287/chat/img/coeur.gif>",$object['message']);
	$object['message'] = eregi_replace("\(u\)","<img src=http://members.lycos.nl/js6287/chat/img/coeur-brise.gif>",$object['message']);
	$object['message'] = eregi_replace("\(U\)","<img src=http://members.lycos.nl/js6287/chat/img/coeur-brise.gif>",$object['message']);
	$object['message'] = eregi_replace("\;-P","<img src=http://members.lycos.nl/js6287/chat/img/clin-oeuil-langue.gif>",$object['message']);
	$object['message'] = eregi_replace("\;p","<img src=http://members.lycos.nl/js6287/chat/img/clin-oeuil-langue.gif>",$object['message']);
	$object['message'] = eregi_replace("\(y\)","<img src=http://members.lycos.nl/js6287/chat/img/pouce-oui.gif>",$object['message']);
	$object['message'] = eregi_replace("\(Y\)","<img src=http://members.lycos.nl/js6287/chat/img/pouce-oui.gif>",$object['message']);
	$object['message'] = eregi_replace("\(n\)","<img src=http://members.lycos.nl/js6287/chat/img/pouce-non.gif>",$object['message']);
	$object['message'] = eregi_replace("\(N\)","<img src=http://members.lycos.nl/js6287/chat/img/pouce-non.gif>",$object['message']);
	$object['message'] = eregi_replace("\(6\)","<img src=http://members.lycos.nl/js6287/chat/img/diable.gif>",$object['message']);
	$object['message'] = eregi_replace("\(d\)","<img src=http://members.lycos.nl/js6287/chat/img/drink.gif>",$object['message']);
	$object['message'] = eregi_replace("\(D\)","<img src=http://members.lycos.nl/js6287/chat/img/drink.gif>",$object['message']);
	$object['message'] = eregi_replace("_o_","<img src=http://members.lycos.nl/js6287/chat/img/worship.gif>",$object['message']);
	$object['message'] = eregi_replace("\(g\)","<img src=http://members.lycos.nl/js6287/chat/img/gun.gif>",$object['message']);
	$object['message'] = eregi_replace("\(G\)","<img src=http://members.lycos.nl/js6287/chat/img/guns.gif>",$object['message']);
?>
<br>
<table width="75%" align="center">
  <tr> 
    <td colspan="2" class="forumTitle"><table width="100%">
        <tr> 
          <td width="20%">Door: </td>
          <td><?php echo "<a href=user.php?x=$user>$user</a>";?> &nbsp;&nbsp;&nbsp; <?php echo"<a href=message.php?p=new&to=$user><img border=0 src=http://members.lycos.nl/js6287/mail.gif height=11 width=11></a>&nbsp;"?>
            <?php if ($data->login == $object['user'] || $data->level >= 255){echo"<a href=?delr=".$object['id']."><img border=0 src=http://members.lycos.nl/js6287/del.png height=11 width=11></a>&nbsp;<a href=?editr=".$object['id']."><img border=0 src=http://members.lycos.nl/js6287/edit.png height=11 width=11></a>";}?>
          </td>
        </tr>
        <tr> 
          <td>Titel:</td>
          <td><b><?php echo stripslashes(htmlspecialchars($object['subject'])); ?></b></td>
        </tr>
        <tr> 
          <td>Tijd:</td>
          <td><?php echo $object['date']; ?></td>
        </tr>
      </table></td>
  </tr>
  <tr> 
    <td class="forumTitle" width="20%" align="left">Bericht: </td>
    <td class="forumTxt"><i><?php echo nl2br(stripslashes($object['message'])); ?></i></td>
  </tr>
</table>
<?php
            }
     if(mysql_num_rows($nr) <= $nrpp)
    print "&#60; 1 &#62;";
  else {
    if($begin/$nrpp == 0)
      print "&#60;&#60; ";
    else
      print "<a href=\"?topic={$_GET['topic']}&p=". ($begin/$nrpp-1) ."\">&#60;&#60;</a> ";

    for($i=0; $i<mysql_num_rows($nr)/$nrpp; $i++) {
      print "<a href=\"?topic={$_GET['topic']}&p=$i\">". ($i+1) ."</a> ";
    }

    if($begin+$nrpp >= mysql_num_rows($nr))
      print "&#62;&#62; ";
    else
      print "<a href=\"?topic={$_GET['topic']}&p=". ($begin/$nrpp+1) ."\">&#62;&#62;</a>";
  }
  		}
		else
        {
?>
<center><br><br>Er zijn geen reacties!<br><br></center>
<?php
        }
        if($_SERVER['REQUEST_METHOD'] != 'POST')   
        {
?>
<form method="post">
  <table width="100%">
    <tr><td>&nbsp;&nbsp;</td></tr>
	<tr>
          <td colspan="2" align="center"><b>Plaats een reactie</b></td>
    </tr>
      <?php echo"<input name=user type=hidden size=50 maxlength=30 value='$data->login'>"; ?>
    <tr>
      <td align="right" width="30%">Onderwerp: </td>
      <td align="left"><input name="subject" type="text" value="Re: <?php echo $subject; ?>" size="50" maxlength="50"></td>
    </tr>
    <tr>
      <td colspan="2" align="center"><textarea name="message" cols="64" rows="5"></textarea></td>
    </tr>
    <tr>
      <td colspan="2" align="center"><input type="submit" name="Submit" value="Verzenden">
      <input type="reset" name="Reset" value="Wis velden"></td>
    </tr>
  </table>
</form>
<?php
        }
        else
        {
            if($_POST['subject'] != "" AND $_POST['message'] != "")
            {
                if(strlen(str_replace(" ", "", $_POST['subject'])) < 2)
                {
                ?>
                <br><br>Vul een goed onderwerp in!<br>
                > <a href="javascript:history.go(-1)">Ga terug</a><br><br>
                <?php
                }
                elseif(strlen(str_replace(" ", "", $_POST['message'])) < 2)
                {
                ?>
                <br><br>Vul een goed bericht in!<br>
                > <a href="javascript:history.go(-1)">Ga terug</a><br><br>
                <?php
                }
                else
                {
                mysql_query("INSERT INTO forum_reacties (topic_id,user,subject,message,date) VALUES (".$id.",'".addslashes($_POST['user'])."','".addslashes($_POST['subject'])."','".addslashes($_POST['message'])."',NOW())") or die(mysql_error());
                echo "<META HTTP-EQUIV=refresh CONTENT=0; URL=forum.php?topic=".$id.">";
                }
            }
            else
            {
            ?>
            <br><br>Onderwerp en bericht zijn verplichte velden!<br>
            > <a href="javascript:history.go(-1)">Ga terug</a><br><br>
            <?php
            }
        }                
    }
    else
    {
    echo "<META HTTP-EQUIV=refresh CONTENT=0; URL=forum.php>";
    }
}
elseif (isset($_GET['type']))
{
$begin= ($_GET['p'] >= 0) ? $_GET['p']*$nrtpp : 0;
$topics = mysql_query("SELECT id,subject,user,date FROM forum_topics WHERE `type`='{$_GET['type']}' ORDER by date DESC LIMIT $begin,$nrtpp") or die(mysql_error());
$aantal = mysql_num_rows($topics);
    if($aantal != 0)
    {
?>
<table width="100%" align=center>
  <tr>
        <td colspan="4" align="center"><b><a href=<?php echo $_SERVER['PHP_SELF'] ?>>Categorie&euml;n</a> - <?php echo"{$_GET['type']}"; ?></b></td>
  </tr>
  <tr> 
    <td colspan="4" align="center"> <? 
$nr = mysql_query("SELECT id FROM forum_topics WHERE `type`='{$_GET['type']}'") or die(mysql_error());
     if(mysql_num_rows($nr) <= $nrtpp)
    print "&#60; 1 &#62;";
  else {
    if($begin/$nrtpp == 0)
      print "&#60;&#60; ";
    else
      print "<a href=\"?type={$_GET['type']}&p=". ($begin/$nrtpp-1) ."\">&#60;&#60;</a> ";

    for($i=0; $i<mysql_num_rows($nr)/$nrtpp; $i++) {
      print "<a href=\"?type={$_GET['type']}&p=$i\">". ($i+1) ."</a> ";
    }

    if($begin+$nrtpp >= mysql_num_rows($nr))
      print "&#62;&#62; ";
    else
      print "<a href=\"?type={$_GET['type']}&p=". ($begin/$nrtpp+1) ."\">&#62;&#62;</a>";
  }
  ?>
     </td>
  </tr>
  <tr>
    <td width="40%" align="right">Onderwerp&nbsp;&nbsp;&nbsp;</td>
	<td width="20" align="right">Door&nbsp;&nbsp;&nbsp;</td>
	<td width="20" align="right">Posts&nbsp;&nbsp;&nbsp;</td>
    <td align="left">Datum</td>
  </tr>
<?php
        while($object = mysql_fetch_assoc($topics))
        {
		$posts = mysql_num_rows(mysql_query("SELECT id FROM `forum_reacties` WHERE `topic_id`='{$object['id']}'"));
		$user = $object['user'];
?>
  <tr>
    <td align="right"><a href="<?php echo $_SERVER['PHP_SELF']."?topic=".$object['id']; ?>"><?php echo stripslashes(htmlspecialchars($object['subject'])); ?></a>&nbsp;&nbsp;&nbsp;</td>
	<td align="right"><?php echo "<a href=user.php?x=$user>$user</a>";?> &nbsp;&nbsp;&nbsp;</td> 
	<td align="right"><? echo $posts ?>&nbsp;&nbsp;&nbsp;</td>
	<td align="left"><?php echo $object['date']; ?> &nbsp;&nbsp;&nbsp;<?php echo"<a href=message.php?p=new&to=$user><img border=0 src=http://members.lycos.nl/js6287/mail.gif height=11 width=11></a>&nbsp;"; if ($data->login == $object['user'] || $data->level >= 255){echo"<a href=?del=".$object['id']."><img border=0 src=http://members.lycos.nl/js6287/del.png height=11 width=11></a>&nbsp;&nbsp;<a href=?edit=".$object['id']."><img border=0 src=http://members.lycos.nl/js6287/edit.png height=11 width=11></a>";}?></td>
  </tr>
<?php
        }
?>
<tr><td>&nbsp;&nbsp;</td><td>&nbsp;&nbsp;</td></tr>
</table>
<?php
    if(mysql_num_rows($nr) <= $nrtpp)
    print "&#60; 1 &#62;";
  else {
    if($begin/$nrtpp == 0)
      print "&#60;&#60; ";
    else
      print "<a href=\"?type={$_GET['type']}&p=". ($begin/$nrtpp-1) ."\">&#60;&#60;</a> ";

    for($i=0; $i<mysql_num_rows($nr)/$nrtpp; $i++) {
      print "<a href=\"?type={$_GET['type']}&p=$i\">". ($i+1) ."</a> ";
    }

    if($begin+$nrtpp >= mysql_num_rows($nr))
      print "&#62;&#62; ";
    else
      print "<a href=\"?type={$_GET['type']}&p=". ($begin/$nrtpp+1) ."\">&#62;&#62;</a>";
  }
  }
    else
    {
?>
<br><br>Er zijn nog geen topics!<br><br>
<?php
    }
    if($_SERVER['REQUEST_METHOD'] != 'POST')   
    {
?>
<form method="post">
  <table width="100%">
  <tr><td>&nbsp;&nbsp;</td></tr>
    <tr>
    <td colspan="4" align="center"><b>Nieuw topic</b></td>
    </tr>
      <?php echo"<input name=user type=hidden size=50 maxlength=30 value='$data->login'>"; ?>
    <tr>
      <td align="right" width=30%>Onderwerp: </td>
      <td align="left"><input name="subject" type="text" size="50" maxlength="50"></td>
    </tr>
	<tr>
      <td align="right">Categorie: </td>
      <td align="left"><select name=type>
  <option value=algemeen>Algemeen</option>
  <option value=tip>TIP</option>
  <option value=bug>BUG</option>
  <option value=vragen>Vragen</option>
  <option value=route66>Route66</option>
  <option value=oc>Georganiseerde Misdaad</option>
  <option value=race>Race</option>
  <option value=familie>Familie</option>
<? if($data->famillie != ""){echo"<option value=$data->famillie>$data->famillie</option>"; }?>
  <option value=varia>Varia</option>
</select></td>
    </tr>
    <tr>
      <td colspan="4" align="center"><textarea name="message" cols="64" rows="5"></textarea></td>
    </tr>
    <tr>
      <td colspan="4" align="center"><input type="submit" name="Submit" value="Verzenden">
      <input type="reset" name="Reset" value="Wis velden"></td>
    </tr>
  </table>
</form>
<?php
    }
    else
    {
        if($_POST['user'] != "" AND $_POST['subject'] != "" AND $_POST['message'] != "")
        {
            if(strlen(str_replace(" ", "", $_POST['subject'])) < 2)
            {
            ?>
            <br><br>Vul een goed onderwerp in!<br>
            > <a href="javascript:history.go(-1)">Ga terug</a><br><br>
            <?php
            }
            elseif(strlen(str_replace(" ", "", $_POST['message'])) < 2)
            {
            ?>
            <br><br>Vul een goed bericht in!<br>
            > <a href="javascript:history.go(-1)">Ga terug</a><br><br>
            <?php
            }
            else
            {
            mysql_query("INSERT INTO forum_topics (user,type,subject,message,date) VALUES ('".addslashes($_POST['user'])."','".addslashes($_POST['type'])."','".addslashes($_POST['subject'])."','".addslashes($_POST['message'])."',NOW())") or die(mysql_error());
            echo "<META HTTP-EQUIV=refresh CONTENT=0; URL=forum.php>";
            }
        }
        else
        {
        ?>
        <br><br>Onderwerp en bericht zijn verplichte velden!<br>
        > <a href="javascript:history.go(-1)">Ga terug</a><br><br>
        <?php
        }
    }
}
else
{
?>
<table width="100%">
  <tr>
    <td colspan="2" align="center"><b>Categorie&euml;n</b></td>
  </tr>
  <tr>
    <td width="50%" align="right">Categorie&nbsp;&nbsp;&nbsp;</td>
    <td align="left">Aantal</td>
  </tr>
  <tr>
    <td align="right"><a href=?type=algemeen>Algemeen</a>&nbsp;&nbsp;&nbsp;</td>
    <td align="left"><?php 
	$topics = mysql_query("SELECT * FROM forum_topics WHERE `type`='algemeen'") or die(mysql_error());
    $aantal = mysql_num_rows($topics); 
	echo"$aantal";
	?></td>
  </tr>
  <tr>
    <td align="right"><a href=?type=tip>Tip</a>&nbsp;&nbsp;&nbsp;</td>
    <td align="left"><?php 
	$topics = mysql_query("SELECT * FROM forum_topics WHERE `type`='tip'") or die(mysql_error());
    $aantal = mysql_num_rows($topics); 
	echo"$aantal";
	?></td>
  </tr>
  <tr>
    <td align="right"><a href=?type=bug>Bug</a>&nbsp;&nbsp;&nbsp;</td>
    <td align="left"><?php 
	$topics = mysql_query("SELECT * FROM forum_topics WHERE `type`='bug'") or die(mysql_error());
    $aantal = mysql_num_rows($topics); 
	echo"$aantal";
	?></td>
  </tr>
  <tr>
    <td align="right"><a href=?type=vragen>Vragen</a>&nbsp;&nbsp;&nbsp;</td>
    <td align="left"><?php 
	$topics = mysql_query("SELECT * FROM forum_topics WHERE `type`='vragen'") or die(mysql_error());
    $aantal = mysql_num_rows($topics); 
	echo"$aantal";
	?></td>
  </tr>
  <tr>
    <td align="right"><a href=?type=route66>Route66</a>&nbsp;&nbsp;&nbsp;</td>
    <td align="left"><?php 
	$topics = mysql_query("SELECT * FROM forum_topics WHERE `type`='route66'") or die(mysql_error());
    $aantal = mysql_num_rows($topics); 
	echo"$aantal";
	?></td>
  </tr>
  <tr>
    <td align="right"><a href=?type=oc>Georganiseerde Misdaad</a>&nbsp;&nbsp;&nbsp;</td>
    <td align="left"><?php 
	$topics = mysql_query("SELECT * FROM forum_topics WHERE `type`='oc'") or die(mysql_error());
    $aantal = mysql_num_rows($topics); 
	echo"$aantal";
	?></td>
  </tr>
  <tr>
    <td align="right"><a href=?type=race>Race</a>&nbsp;&nbsp;&nbsp;</td>
    <td align="left"><?php 
	$topics = mysql_query("SELECT * FROM forum_topics WHERE `type`='race'") or die(mysql_error());
    $aantal = mysql_num_rows($topics); 
	echo"$aantal";
	?></td>
  </tr>
  <tr>
    <td align="right"><a href=?type=familie>Familie</a>&nbsp;&nbsp;&nbsp;</td>
    <td align="left"><?php 
	$topics = mysql_query("SELECT * FROM forum_topics WHERE `type`='familie'") or die(mysql_error());
    $aantal = mysql_num_rows($topics); 
	echo"$aantal";
	?></td>
  </tr>
  <tr>
    <td align="right"><a href=?type=rip>RIP</a>&nbsp;&nbsp;&nbsp;</td>
    <td align="left"><?php 
	$topics = mysql_query("SELECT * FROM forum_topics WHERE `type`='rip'") or die(mysql_error());
    $aantal = mysql_num_rows($topics); 
	echo"$aantal";
	?></td>
  </tr>
  <tr>
    <td align="right"><a href=?type=varia>Varia</a>&nbsp;&nbsp;&nbsp;</td>
    <td align="left"><?php 
	$topics = mysql_query("SELECT * FROM forum_topics WHERE `type`='varia'") or die(mysql_error());
    $aantal = mysql_num_rows($topics); 
	echo"$aantal";
	?></td>
  </tr>
<? if($data->famillie != ""){echo"
  <tr>
    <td align=right><a href=?type=$data->famillie>$data->famillie</a>&nbsp;&nbsp;&nbsp;</td>
    <td align=left>"; 
	$topics = mysql_query("SELECT * FROM forum_topics WHERE `type`='{$data->famillie}'") or die(mysql_error());
    $aantal = mysql_num_rows($topics); 
	echo"$aantal";
	echo"
	</td>
  </tr>"; }?>
<tr><td>&nbsp;&nbsp;</td><td>&nbsp;&nbsp;</td></tr>
</table>
<?php
    
    if($_SERVER['REQUEST_METHOD'] != 'POST')   
    {
?>
<form method="post">
  <table width="100%">
    <tr>
    <td colspan="2"  align="center"><b>Nieuw topic</b></td>
    </tr>
      <?php echo"<input name=user type=hidden size=50 maxlength=30 value='$data->login'>"; ?>
    <tr>
      <td align="right" width="30%">Onderwerp: </td>
      <td align="left"><input name="subject" type="text" size="50" maxlength="50"></td>
    </tr>
	<tr>
      <td align="right">Categorie: </td>
      <td align="left"><select name=type>
  <option value=algemeen>Algemeen</option>
  <option value=tip>TIP</option>
  <option value=bug>BUG</option>
  <option value=vragen>Vragen</option>
  <option value=route66>Route66</option>
  <option value=oc>Georganiseerde Misdaad</option>
  <option value=race>Race</option>
  <option value=familie>Familie</option>
<? if($data->famillie != ""){echo"<option value=$data->famillie>$data->famillie</option>"; }?>
  <option value=varia>Varia</option>
</select></td>
    </tr>
    <tr>
      <td colspan="2" align="center"><textarea name="message" cols="64" rows="5"></textarea></td>
    </tr>
    <tr>
      <td colspan="2" align="center"><input type="submit" name="Submit" value="Verzenden">
      <input type="reset" name="Reset" value="Wis velden"></td>
    </tr>
  </table>
</form>
<?php
    }
    else
    {
        if($_POST['user'] != "" AND $_POST['subject'] != "" AND $_POST['message'] != "")
        {
            if(strlen(str_replace(" ", "", $_POST['subject'])) < 2)
            {
            ?>
            <br><br>Vul een goed onderwerp in!<br>
            > <a href="javascript:history.go(-1)">Ga terug</a><br><br>
            <?php
            }
            elseif(strlen(str_replace(" ", "", $_POST['message'])) < 2)
            {
            ?>
            <br><br>Vul een goed bericht in!<br>
            > <a href="javascript:history.go(-1)">Ga terug</a><br><br>
            <?php
            }
            else
            {
            mysql_query("INSERT INTO forum_topics (user,type,subject,message,date) VALUES ('".addslashes($_POST['user'])."','".addslashes($_POST['type'])."','".addslashes($_POST['subject'])."','".addslashes($_POST['message'])."',NOW())") or die(mysql_error());
            echo "<META HTTP-EQUIV=refresh CONTENT=0; URL=forum.php>";
            }
        }
        else
        {
        ?>
        <br><br>Onderwerp en bericht zijn verplichte velden!<br>
        > <a href="javascript:history.go(-1)">Ga terug</a><br><br>
        <?php
        }
    }
}
?>
</body>
</html> 