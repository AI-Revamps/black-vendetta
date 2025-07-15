<?php
include("config.php");
$dbres = mysql_query("SELECT * FROM `users` WHERE `login`='{$_SESSION['login']}'");
$data = mysql_fetch_object($dbres);
$pp = 10;
$start = (isset($_GET['p']) && $_GET['p'] >= 0) ? $_GET['p']*$pp : 0;
$total = mysql_num_rows(mysql_query("SELECT id FROM `news`"));
if ($start > $total) $start = 0;
$rows = mysql_query("SELECT * FROM `news` ORDER BY time DESC LIMIT $start,$pp");
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title>Vendetta Nieuws</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="container py-3">
    <h1 class="mb-3">Nieuws</h1>
    <div class="mb-4">
        <?php
        while ($news = mysql_fetch_object($rows)) {
            $time = date('d/m/Y', $news->time);
            echo '<div class="mb-3"><strong>' . $news->title . '</strong> <span class="text-muted">' . $time . '</span><p>' . $news->text . '</p></div>';
        }
        ?>
    </div>
    <div class="text-center">
        <?php
        if ($total <= $pp) {
            echo "&laquo; &lt; 1 &gt; &raquo;";
        } else {
            if ($start/$pp == 0) {
                echo "&laquo; &lt; ";
            } else {
                echo '<a href="?p=0">&laquo;</a> <a href="?p=' . ($start/$pp-1) . '">&lt;</a> ';
            }
            for ($i=0; $i<$total/$pp; $i++) {
                if ($i == $start/$pp) {
                    echo '<u>' . ($i+1) . '</u> ';
                } else {
                    echo '<a href="?p=' . $i . '">' . ($i+1) . '</a> ';
                }
            }
            if ($start+$pp >= $total) {
                echo ' &gt; &raquo; ';
            } else {
                echo '<a href="?p=' . ($start/$pp+1) . '">&gt;</a> <a href="?p=' . (ceil($total/$pp)-1) . '">&raquo;</a>';
            }
        }
        ?>
    </div>
</body>
</html>
