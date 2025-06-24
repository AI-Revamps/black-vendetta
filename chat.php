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
if (!check_login()) {
    $data->login = 'Guest';
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
  <tr> 
    <td class="subTitle"><b>Chat</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class="mainTxt">
      <form method="POST" action="http://www.chat4all.net/jirc26/chat-v1.cgi" target="myWindow" onsubmit="window.open('http://www.chat4all.net/blank.htm','myWindow','width=758,height=490,statusbar=no,location=no,toolbar=no,scrollbar=no,resize=yes,resizable=yes');" >
<center>
<input type="submit" value="Start de chat"></center>
<input type=hidden name="pagetitle" value="Vendettagame Chat"><p></p>
<input type=hidden name="tabletitle" value="Vendettagame chat"><p></p>
<input type=hidden name="roomname" value="vendetta"><p></p>
<input type=hidden name="tabletopcolour" value="#CC0000"><p></p>
<input type=hidden name="tablebodycolour" value="#CCCCCC"><p></p>
<input type=hidden name="language" value="ned"><p></p>
<input type=hidden name="rgb1" size="3" value="243">
<input type=hidden name="rgb2" size="3" value="243">
<input type=hidden name="rgb3" size="3" value="243"><p></p>
<input type=hidden name="smiley" value="3">
<input type="hidden" name="nickname" size="20" value="<?php echo $data->login;?>">
<input type="hidden" value="2" name="size">
</form>
    </td>  
  </tr>  
</table>

</body>


</html>