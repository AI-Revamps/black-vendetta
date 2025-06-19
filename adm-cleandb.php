<?php
declare(strict_types=1);
require 'config.php';

$stmt = pdo_query("SELECT `login` FROM `users` WHERE `status`='dood'");
while ($user = $stmt->fetch()) {
    pdo_query("DELETE FROM `garage` WHERE `login`=?", [$user->login]);
    pdo_query("DELETE FROM `iplog` WHERE `login`=?", [$user->login]);
    pdo_query("DELETE FROM `friends` WHERE `login`=?", [$user->login]);
    pdo_query("DELETE FROM `friends` WHERE `friend`=?", [$user->login]);
    pdo_query("DELETE FROM `hitlist` WHERE `login`=?", [$user->login]);
}
?>