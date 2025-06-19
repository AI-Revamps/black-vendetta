<?php

declare(strict_types=1);
require 'config.php';

// Load current admin user
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
    <td class="subTitle"><b>Gevangenis Management</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr>
    <td class="mainTxt">
        <?php

if (isset($_POST['putin'])) {
    if ($data->level < 255) {
        echo "Je hebt niet genoeg rechten.";
        exit;
    }
    $victim = pdo_query("SELECT * FROM `users` WHERE `login` = ?", [$_POST['p']]);
    $vic = $victim->fetch();
    $vict = pdo_query("SELECT * FROM `jail` WHERE `login` = ?", [$_POST['p']]);
    $isin = $vict->rowCount();
    if ($isin != 0) {
        echo "Deze persoon zit al in de cel.";
    } else {
        echo "Je hebt deze persoon in de cel gestoken.";
        pdo_query(
            "INSERT INTO `jail`(`login`,`boete`,`stad`,`famillie`,`time`) VALUES(?,?,?,?,FROM_UNIXTIME(?))",
            [$vic->login, $boete, $vic->stad, $vic->famillie, $jailtime]
        );
    }
} elseif (isset($_GET['x'])) {
    pdo_query("DELETE FROM `jail` WHERE `login` = ?", [$_GET['x']]);
    print "Deze persoon is bevrijdt.";
}
        echo "<table align=center width=100%>
          <tr><td colspan='6'>
          <table width=100%><tr>
          <td width='100' align=center><b>Login</b></td>
      <td width='100' align=center><b>Familie</b></td>
          <td width='20%' align=center><b>Stad</b></td>
      <td width='20%' align=center><b>Boete</b></td>
      <td width='20%' align=center><b>Tijd</b></td>
      <td width='50' align=center><b>Bevrijden</b></td></tr>";

$query = "SELECT `bo`,`login`,`famillie`,`boete`,`stad`,UNIX_TIMESTAMP(`time`) AS `time` FROM jail ORDER BY `time` ASC";
$info = pdo_query($query);
$count = 0;
while (
    $gegeven = $info->fetch()
) {
    $name = htmlspecialchars($gegeven->login);
    $boeteVal = htmlspecialchars((string) $gegeven->boete);
    $stad = htmlspecialchars($gegeven->stad);
    $tim = $gegeven->time - time();
    $time = gmdate('i:s', $tim);
    $famillie = $gegeven->famillie;
    if (!$famillie) { $famillie = 'Geen'; }
    else { $famillie = "<a href=fam.php?x={$famillie}>{$famillie}</a>"; }
    $count++;
    echo "<tr>
        <td align=center><a href=\"user.php?x={$name}\">{$name}</a></td>
                <td align=center>{$famillie}</td>
                                <td align=center>{$stad}</td>
                <td align=center>&euro;{$boeteVal}</td>
                <td align=center>{$time}</td>";
    echo "<td width='50' align=center><a href=\"adm-prison.php?x={$name}\">[x]</a></td></tr>"; }
echo "</table></td></tr></table>";
echo "<table align=center width=100%><tr><td><div align='center'><b>S t e e k &nbsp;&nbsp; i n &nbsp;&nbsp; d e &nbsp;&nbsp; G e v a n g e n i s</b><br><br>";
echo "<form method=\"post\">Speler: <select name=\"p\">";
$dbres = pdo_query(
    "SELECT `login` FROM `users` WHERE `status`='levend' ORDER BY `login`"
);
while ($person = $dbres->fetch()) {
    $login = htmlspecialchars($person->login);
    print " <option value=\"{$login}\">{$login}</option>\n";
}
print "  </select> <input type=\"submit\" name=\"putin\" value=\"Steek in de bak\"></form></td></tr></table>";

?>
