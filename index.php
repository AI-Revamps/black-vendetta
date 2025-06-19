<?php
declare(strict_types=1);
session_start();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title>Vendetta</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <iframe src="menu.php" name="menu" class="w-100" style="height:100vh;border:0;"></iframe>
        </div>
        <div class="col-md-7 p-0">
            <iframe src="home.php" name="hoofd" class="w-100" style="height:100vh;border:0;"></iframe>
        </div>
        <div class="col-md-3 p-0">
            <iframe src="right.php" name="right" class="w-100" style="height:100vh;border:0;"></iframe>
        </div>
    </div>
</div>
</body>
</html>
