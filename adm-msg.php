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
<html>
<head>
<title>Vendetta</title>
<link rel="stylesheet" type="text/css" href="style.css">
<meta name="keywords" content="Vendetta,Crimegame,crimegame,vendetta">
<meta name="language" content="english">
<META name="description" lang="nl" content="Vendetta crimegame met pit.">
</head>
<table align=center width=100%>
  <tr>
    <td class="subTitle"><b>Admin bericht</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr>
    <td class="mainTxt">
<?php
if (isset($_POST['submit'])) {
    $to = $_POST['to'] ?? '';
    $subject = preg_replace('/</', '&#60;', $_POST['subject'] ?? '');
    $message = preg_replace('/</', '&#60;', $_POST['message'] ?? '');
    if ($to !== '') {
        pdo_query(
            "INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Admin',?,?,?)",
            [$to, $subject, $message]
        );
        print "Bericht verzonden.";
    } else {
        if ($data->level < 255) { echo "Je hebt niet genoeg rechten."; exit; }
        $dbres = pdo_query("SELECT `login` FROM `users` WHERE `status`='levend' AND `activated`='1'");
        while ($member = $dbres->fetch()) {
            pdo_query(
                "INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Admin',?,?,?)",
                [$member->login, $subject, $message]
            );
        }
        print "Bericht naar alle members verzonden.";
    }
}
print <<<ENDHTML
        <form name=\"form1\" method=\"POST\" action=\"adm-msg.php\"><table>
        <input type=\"hidden\" name=\"id\" value=\"$id\">
        <input type=\"hidden\" name=\"code\" value=\"$code\">
        <tr><td width=100>Van:</td>             <td>Admin</td></tr>
        <tr><td width=100>Naar:</td>            <td><input type=\"text\" name=\"to\" value=\"{$_REQUEST['to']}\" maxlength=16> (leeg is iedereen)</td></tr>
        <tr><td width=100>Onderwerp:</td>       <td><input type=\"text\" name=\"subject\" value=\"{$_REQUEST['subject']}\" maxlength=25></td></tr>
        <tr><td width=100 valign=\"top\">Bericht:</td>
                                                <td><textarea name=\"message\" cols=40 rows=10>{$_REQUEST['message']}</textarea></td></tr>
        <tr><td width=100></td>                 <td align=\"right\"><input type=\"submit\" name=\"submit\" value=\"Verzenden\"></td></tr>
  </td></tr>
ENDHTML;
?>
