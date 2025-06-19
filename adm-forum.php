<?php
declare(strict_types=1);
require 'config.php';

$stmt = pdo_query(
    "SELECT *,UNIX_TIMESTAMP(`pc`) AS `pc`,UNIX_TIMESTAMP(`transport`) AS `transport`," .
    "UNIX_TIMESTAMP(`bc`) AS `bc`,UNIX_TIMESTAMP(`slaap`) AS `slaap`," .
    "UNIX_TIMESTAMP(`kc`) AS `kc`,UNIX_TIMESTAMP(`start`) AS `start`," .
    "UNIX_TIMESTAMP(`crime`) AS `crime`,UNIX_TIMESTAMP(`ac`) AS `ac` " .
    "FROM `users` WHERE `login` = ?",
    [$_SESSION['login']]
);
$data = $stmt->fetch();
if (!$data || $data->level < 200) { exit; }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Vendetta Forum</title>
<link href="style.css" rel="stylesheet" type="text/css">
<meta name="keywords" content="Vendetta,Crimegame,crimegame,vendetta">
<meta name="language" content="english">
<META name="description" lang="nl" content="Vendetta crimegame met pit.">
</head>

<body>
<?php
error_reporting(E_ALL);
?>
<table width=100% align=center>
  <tr> 
    <td class="subTitle"><b>Forum</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
<?php
if($data->level < 255) {echo"Je hebt niet genoeg rechten.";exit;}
if (isset($_GET['del'])) {
    $id = $_GET['del'];
    pdo_query("DELETE FROM `forum_topics` WHERE `id` = ?", [$id]);
    pdo_query("DELETE FROM `forum_reacties` WHERE `topic_id` = ?", [$id]);
    echo "<br><br>Topic verwijderd!<br>
                > <a href=javascript:history.go(-1)>Ga terug</a><br><br>";
}
elseif(isset($_GET['delr']))
{
    $id = $_GET['delr'];
    pdo_query("DELETE FROM `forum_reacties` WHERE `id` = ?", [$id]);
    echo "<br><br>Reactie verwijderd!<br>
                > <a href=javascript:history.go(-1)>Ga terug</a><br><br>";
}
elseif(isset($_GET['topic']))
{
// Voor de topic
$topic = pdo_query(
    "SELECT id,type,user,subject,message,DATE_FORMAT(date,'%d-%m-%Y om %H:%i') AS date FROM forum_topics WHERE id = ?",
    [$_GET['topic']]
);
$aantal_topics = $topic->rowCount();
    if ($aantal_topics == 1)
    {
        while ($object = $topic->fetch(PDO::FETCH_ASSOC))
        {
        $id = $object['id'];
        $subject = stripslashes($object['subject']);
		$user = $object['user'];
?>
<table width="50%" align="center" >
  <tr> 
    <td colspan="2"><b><a href=<?php echo $_SERVER['PHP_SELF'] ?>>Categorie&euml;n</a> - <a href=<?php echo $_SERVER['PHP_SELF']."?type=".$object['type']; ?>><?php echo"{$object['type']}"; ?></a> - <?php echo stripslashes(htmlspecialchars($object['subject'])); ?></b></td>
  </tr>  
  <tr> 
    <td width="35%">&nbsp;</td>
    <td width="65%">&nbsp;</td>
  </tr>
  <tr> 
    <td>Door: <?php echo "<a href=user.php?x=$user>$user</a>";?></td>
    <td width="65%" rowspan="3"><i><?php echo nl2br(stripslashes(htmlspecialchars($object['message']))); ?></i></td>
  </tr>
  <tr> 
    <td>Tijd: <?php echo $object['date']; ?></td>
  </tr>
  <tr> 
    <td>Bericht:</td>
  </tr>
</table>
<?php
        }
    // Voor de reacties
    $message = pdo_query(
        "SELECT id,user,subject,message,DATE_FORMAT(date,'%d-%m-%Y om %H:%i') AS date FROM forum_reacties WHERE topic_id = ?",
        [$_GET['topic']]
    );
    $aantal_messages = $message->rowCount();
        if ($aantal_messages != 0)
        {
            while ($object = $message->fetch(PDO::FETCH_ASSOC))
            {
			$user = $object['user'];
?>
<br>
<table width="50%" align="center">
    <tr> 
    <td colspan="2"><b><?php echo stripslashes(htmlspecialchars($object['subject'])); ?></b></td>
  </tr>  
  <tr> 
    <td width="35%">&nbsp;</td>
    <td width="65%">&nbsp;</td>
  </tr>
  <tr> 
    <td>Door: <?php echo "<a href=user.php?x=$user>$user</a>";?> &nbsp;&nbsp;&nbsp;<?php if ($data->login == $object['user']){echo"<a href=".$_SERVER['PHP_SELF']."?delr=".$object['id'].">[Del]</a>";}?></td>
    <td width="65%" rowspan="3"><i><?php echo nl2br(stripslashes(htmlspecialchars($object['message']))); ?></i></td>
  </tr>
  <tr> 
    <td>Tijd: <?php echo $object['date']; ?></td>
  </tr>
  <tr> 
    <td>Bericht:</td>
  </tr>
</table>
<?php
            }
        }
        else
        {
?>
<br><br>Er zijn geen reacties!<br><br>
<?php
        }                
    }
    else
    {
    echo "<script language=\"JavaScript\">top.location.href='".$_SERVER['PHP_SELF']."';</script>";
    }
}
elseif (isset($_GET['type']))
{
$topics = pdo_query(
    "SELECT id,subject,user,DATE_FORMAT(date,'%d-%m-%Y om %H:%i') AS date FROM forum_topics WHERE `type` = ? ORDER by id DESC",
    [$_GET['type']]
);
$aantal = $topics->rowCount();
    if ($aantal != 0)
    {
?>
<table width="100%">
  <tr>
        <td colspan="2"><b><a href=<?php echo $_SERVER['PHP_SELF'] ?>>Categorie&euml;n</a> - <?php echo"{$_GET['type']}"; ?></b></td>
  </tr>
  <tr>
    <td width="50%" class="blok_tekst" align="right">Onderwerp&nbsp;&nbsp;&nbsp;</td>
    <td align="left">Datum</td>
  </tr>
<?php
        while ($object = $topics->fetch(PDO::FETCH_ASSOC))
        {
?>
  <tr>
    <td align="right"><a href="<?php echo $_SERVER['PHP_SELF']."?topic=".$object['id']; ?>"><?php echo stripslashes(htmlspecialchars($object['subject'])); ?></a>&nbsp;&nbsp;&nbsp;</td>
    <td align="left"><?php echo $object['date']; ?> &nbsp;&nbsp;&nbsp;<?php if ($data->login == $object['user']){echo"<a href=".$_SERVER['PHP_SELF']."?del=".$object['id'].">Delete</a>";}?></td>
  </tr>
<?php
        }
?>
<tr><td>&nbsp;&nbsp;</td><td>&nbsp;&nbsp;</td></tr>
</table>
<?php
    }
    else
    {
?>
<br><br>Er zijn nog geen topics!<br><br>
<?php
    }
}
else
{
?>
<table width="100%">
  <tr>
    <td colspan="2"><b>Categorie&euml;n</b></td>
  </tr>
  <tr>
    <td width="50%" align="right">Categorie&nbsp;&nbsp;&nbsp;</td>
    <td align="left">Aantal</td>
  </tr>
  <tr>
    <td align="right"><a href=?type=algemeen>Algemeen</a>&nbsp;&nbsp;&nbsp;</td>
    <td align="left"><?php 
        $aantal = pdo_query("SELECT COUNT(*) AS c FROM forum_topics WHERE `type`='algemeen'")->fetch()->c;
        echo $aantal;
	?></td>
  </tr>
  <tr>
    <td align="right"><a href=?type=tip>Tip</a>&nbsp;&nbsp;&nbsp;</td>
    <td align="left"><?php 
        $aantal = pdo_query("SELECT COUNT(*) AS c FROM forum_topics WHERE `type`='tip'")->fetch()->c;
        echo $aantal;
	?></td>
  </tr>
  <tr>
    <td align="right"><a href=?type=route66>Route66</a>&nbsp;&nbsp;&nbsp;</td>
    <td align="left"><?php 
        $aantal = pdo_query("SELECT COUNT(*) AS c FROM forum_topics WHERE `type`='route66'")->fetch()->c;
        echo $aantal;
	?></td>
  </tr>
  <tr>
    <td align="right"><a href=?type=oc>Georganiseerde Misdaad</a>&nbsp;&nbsp;&nbsp;</td>
    <td align="left"><?php 
        $aantal = pdo_query("SELECT COUNT(*) AS c FROM forum_topics WHERE `type`='oc'")->fetch()->c;
        echo $aantal;
	?></td>
  </tr>
  <tr>
    <td align="right"><a href=?type=race>Race</a>&nbsp;&nbsp;&nbsp;</td>
    <td align="left"><?php 
        $aantal = pdo_query("SELECT COUNT(*) AS c FROM forum_topics WHERE `type`='race'")->fetch()->c;
        echo $aantal;
	?></td>
  </tr>
  <tr>
    <td align="right"><a href=?type=familie>Familie</a>&nbsp;&nbsp;&nbsp;</td>
    <td align="left"><?php 
        $aantal = pdo_query("SELECT COUNT(*) AS c FROM forum_topics WHERE `type`='familie'")->fetch()->c;
        echo $aantal;
	?></td>
  </tr>
  <tr>
    <td align="right"><a href=?type=rip>RIP</a>&nbsp;&nbsp;&nbsp;</td>
    <td align="left"><?php 
        $aantal = pdo_query("SELECT COUNT(*) AS c FROM forum_topics WHERE `type`='rip'")->fetch()->c;
        echo $aantal;
	?></td>
  </tr>
  <tr>
    <td align="right"><a href=?type=varia>Varia</a>&nbsp;&nbsp;&nbsp;</td>
    <td align="left"><?php 
        $aantal = pdo_query("SELECT COUNT(*) AS c FROM forum_topics WHERE `type`='varia'")->fetch()->c;
        echo $aantal;
	?></td>
  </tr>
<tr><td>&nbsp;&nbsp;</td><td>&nbsp;&nbsp;</td></tr>
</table>
<?php
}
?>
</body>
</html> 