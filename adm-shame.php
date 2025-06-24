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
if (!$data || $data->level < 200) {
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
<table align=center width=100%>
  <tr>
    <td class="subTitle"><b>Wall Of Shame</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr>
    <td class="mainTxt">
<?php
if ($data->level < 1000) {
    echo 'Je hebt niet genoeg rechten';
    exit;
}
if (isset($_GET['del'])) {
    pdo_query('DELETE FROM `shame` WHERE `id` = ?', [$_GET['del']]);
    echo 'Verwijderd';
}

$dbres = pdo_query("SELECT `login` FROM `users` WHERE `status`='levend' ORDER BY `login`");
echo "<form method=\"post\">Speler: <select name=\"p\">";
while ($person = $dbres->fetch()) {
    $login = htmlspecialchars($person->login);
    print " <option value=\"{$login}\">{$login}</option>\n";
}
print "  </select> <br><input type=text name=com value=Reden><br><input type=\"submit\" name=\"post\" value=\"Spijker op schandpaal\"></form><br><br>";

if (isset($_POST['post'])) {
    $stmt = pdo_query('SELECT * FROM `shame` WHERE `cheater` = ?', [$_POST['p']]);
    if ($stmt->rowCount() != 0) {
        echo 'Deze persoon hangt al aan de schandpaal.';
    } elseif (!empty($_POST['com'])) {
        pdo_query(
            'INSERT INTO `shame`(`com`,`time`,`cheater`,`person`) VALUES (?, NOW(), ?, ?)',
            [$_POST['com'], $_POST['p'], $data->login]
        );
        echo 'De persoon is op de schandpaal gespijkerd!';
    } else {
        echo 'Je moet een bericht opgeven';
    }
}

print <<<ENDHTML
<table width=100%><tr>    <td align=center><b>Persoon</b></td>
      <td align=center><b>Tijd</b></td>
            <td align=center><b>Reden</b></td>
                        <td align=center><b>Door</b></td>
                        <td align=center><b>Delete</b></td> </tr>

ENDHTML;

$info = pdo_query('SELECT * FROM `shame` ORDER BY `id` ASC');
while ($gegeven = $info->fetch()) {
    $time = htmlspecialchars($gegeven->time);
    $cheater = htmlspecialchars($gegeven->cheater);
    $com = htmlspecialchars($gegeven->com);
    $door = htmlspecialchars($gegeven->person);
    $id = htmlspecialchars((string)$gegeven->id);
    print <<<ENDHTML
<tr>            <td align=center>$cheater</td>
      <td align=center>$time</td>
            <td align=center>$com</td>
                        <td align=center>$door</td>
                <td align=center><a href=adm-shame.php?del={$id}>[x]</a></td></tr>
ENDHTML;
}
?>

