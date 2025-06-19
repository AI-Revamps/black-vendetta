<?php
include("config.php");
$blah = mysql_query("SELECT * FROM `users` WHERE `status`='dood'");
while($user = mysql_fetch_object($blah)) {
mysql_query("DELETE FROM `garage` WHERE `login`='{$user->login}'");
mysql_query("DELETE FROM `iplog` WHERE `login`='{$user->login}'");
mysql_query("DELETE FROM `friends` WHERE `login`='{$user->login}'");
mysql_query("DELETE FROM `friends` WHERE `friend`='{$user->login}'");
mysql_query("DELETE FROM `hitlist` WHERE `login`='{$user->login}'");
}
?>