<?php
include("config.php");

$stmt = db_query("SELECT `login` FROM `users` WHERE `status`='dood'");
$res  = $stmt->get_result();
while ($user = $res->fetch_object()) {
    db_query("DELETE FROM `garage` WHERE `login`=?", array($user->login));
    db_query("DELETE FROM `iplog` WHERE `login`=?", array($user->login));
    db_query("DELETE FROM `friends` WHERE `login`=?", array($user->login));
    db_query("DELETE FROM `friends` WHERE `friend`=?", array($user->login));
    db_query("DELETE FROM `hitlist` WHERE `login`=?", array($user->login));
}
?>