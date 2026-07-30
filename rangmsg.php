<?php
if ($data->xp < 10 && $data->xp >= 5) { mysql_query("UPDATE `users` SET `gstart`='1' WHERE `login`='$data->login'"); }
elseif ($data->xp < 20 && $data->xp >= 15) { mysql_query("UPDATE `users` SET `gstart`='1' WHERE `login`='$data->login'"); }
elseif ($data->xp < 50 && $data->xp >= 40) { mysql_query("UPDATE `users` SET `gstart`='1' WHERE `login`='$data->login'"); }
elseif ($data->xp < 150 && $data->xp >= 100) { mysql_query("UPDATE `users` SET `gstart`='1' WHERE `login`='$data->login'"); }
elseif ($data->xp < 500 && $data->xp >= 450) { mysql_query("UPDATE `users` SET `gstart`='1' WHERE `login`='$data->login'"); }
elseif ($data->xp < 1000 && $data->xp >= 750) { mysql_query("UPDATE `users` SET `gstart`='1' WHERE `login`='$data->login'"); }
elseif ($data->xp < 2000 && $data->xp >= 1500) { mysql_query("UPDATE `users` SET `gstart`='1' WHERE `login`='$data->login'"); }
elseif ($data->xp < 3000 && $data->xp >= 2500) { mysql_query("UPDATE `users` SET `gstart`='1' WHERE `login`='$data->login'"); }
elseif ($data->xp < 4500 && $data->xp >= 4000) { mysql_query("UPDATE `users` SET `gstart`='1' WHERE `login`='$data->login'"); }
elseif ($data->xp < 6000 && $data->xp >= 5500) { mysql_query("UPDATE `users` SET `gstart`='1' WHERE `login`='$data->login'"); }
elseif ($data->xp < 8000 && $data->xp >= 7500) { mysql_query("UPDATE `users` SET `gstart`='1' WHERE `login`='$data->login'"); }
elseif ($data->xp < 11000 && $data->xp >= 10500) { mysql_query("UPDATE `users` SET `gstart`='1' WHERE `login`='$data->login'"); }
elseif ($data->xp < 15000 && $data->xp >= 14500) { mysql_query("UPDATE `users` SET `gstart`='1' WHERE `login`='$data->login'"); }
elseif ($data->xp < 20000 && $data->xp >= 19500) { mysql_query("UPDATE `users` SET `gstart`='1' WHERE `login`='$data->login'"); }

$msgnum  = rand(0,4);
$message = Array(
"Je bent $rang geworden, doe zo door en heers over de straten.",
"Misdaad loont, je bent $rang geworden.",
"Gefeliciteerd, je bent $rang geworden.",
"Die laatste auto die je gestolen hebt was echt meesterwerk, je kan je nu $rang noemen.",
"Je bent $rang geworden, knallen met die champagne.");
$msg  = "{$message[$msgnum]}";
if ($vordering <= 20 && $data->gstart > 0) { mysql_query("INSERT INTO `messages`(`time`,`from`,`to`,`subject`,`message`) values(NOW(),'Notificatie','{$data->login}','Rank','{$msg}')"); mysql_query("UPDATE `users` SET `gstart`='0' WHERE `login`='$data->login'"); }

?>