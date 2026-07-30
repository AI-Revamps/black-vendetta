<?PHP
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
<script>
<!-- hide
function openNewWindow() {
  popupWin = window.open('http://members.lycos.nl/js6287/chat/index.php',
  'open_window',
  'menubar, toolbar, location, directories, status, scrollbars, resizable, dependent, width=800, height=550, left=0, top=0')
}
// done hiding -->
</script>
<body topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
<table width="100%" border="0">
  <tr>
    <td width="170">&nbsp;</td>
    <td align="center"><img src="images/header.gif"></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>
	<?
	if ($data->status != levend) {
	?>
	<table border="0" align="center">
        <tr>
          <td class="topmenuOffl" onMouseOver="className='topmenuOverl';" onMouseOut="className='topmenuOffl';" onClick="top.frames['hoofd'].location.href='home.php';">Home&nbsp;&nbsp;&nbsp;&nbsp;</td>
          <td class="topmenuOff" onMouseOver="className='topmenuOver';" onMouseOut="className='topmenuOff';" onClick="top.frames['hoofd'].location.href='login.php';">&nbsp;&nbsp;Login&nbsp;&nbsp;</td>
		  <td class="topmenuOff" onMouseOver="className='topmenuOver';" onMouseOut="className='topmenuOff';" onClick="top.frames['hoofd'].location.href='news.php';">&nbsp;&nbsp;Nieuws&nbsp;&nbsp;</td>
          <td class="topmenuOff" onMouseOver="className='topmenuOver';" onMouseOut="className='topmenuOff';" onClick="top.frames['hoofd'].location.href='register.php';">&nbsp;&nbsp;Registreer&nbsp;&nbsp;</td>
          <td class="topmenuOffr" onMouseOver="className='topmenuOverr';" onMouseOut="className='topmenuOffr';" onClick="top.frames['hoofd'].location.href='help.php';">&nbsp;&nbsp;&nbsp;&nbsp;FAQ</td>
        </tr>
      </table>
	  <?
	  }
	  else {
	?>
	<table border="0" align="center">
        <tr>
          <td class="topmenuOffl" onMouseOver="className='topmenuOverl';" onMouseOut="className='topmenuOffl';" onClick="top.frames['hoofd'].location.href='news.php';">Nieuws&nbsp;&nbsp;&nbsp;&nbsp;</td>
          <td class="topmenuOff" onMouseOver="className='topmenuOver';" onMouseOut="className='topmenuOff';" onClick="top.frames['hoofd'].location.href='forum.php';">&nbsp;&nbsp;Forum&nbsp;&nbsp;</td>
	      <td class="topmenuOff" onMouseOver="className='topmenuOver';" onMouseOut="className='topmenuOff';" onClick="top.frames['hoofd'].location.href='poll.php';">&nbsp;&nbsp;Poll&nbsp;&nbsp;</td>
		  <td class="topmenuOff" onMouseOver="className='topmenuOver';" onMouseOut="className='topmenuOff';" onClick="top.frames['hoofd'].location.href='tip.php';">&nbsp;&nbsp;Ticket&nbsp;&nbsp;</td>
          <td class="topmenuOff" onMouseOver="className='topmenuOver';" onMouseOut="className='topmenuOff';" onClick="top.frames['hoofd'].location.href='login.php?x=logout';">&nbsp;&nbsp;Logout&nbsp;&nbsp;</td>
		  <td class="topmenuOff" onMouseOver="className='topmenuOver';" onMouseOut="className='topmenuOff';" onClick="top.frames['hoofd'].location.href='/';">&nbsp;&nbsp;logd.nl&nbsp;&nbsp;</td>
          <td class="topmenuOffr" onMouseOver="className='topmenuOverr';" onMouseOut="className='topmenuOffr';" onClick="top.frames['hoofd'].location.href='help.php';">&nbsp;&nbsp;&nbsp;&nbsp;FAQ</td>
        </tr>
      </table>
	  <?
	  }
	  ?>
    </td>
  </tr>
</table>
</body>
</html>