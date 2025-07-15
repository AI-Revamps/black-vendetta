<?php
include("config.php");
$message = '';

if (isset($_GET['id'], $_GET['code'])) {
    $id = $_GET['id'];
    $code = $_GET['code'];
    $dbres = mysql_query("SELECT * FROM `temp` WHERE `area`='signup' AND `id`='$id' AND `code`='$code'");
    if ($data = mysql_fetch_object($dbres)) {
        mysql_query("UPDATE `users` SET `activated`='1',`start`=NOW() WHERE `login`='{$data->login}'");
        mysql_query("DELETE FROM `temp` WHERE `id`='$id'");
        if ($data->forwerdedFor != '') {
            mysql_query("UPDATE `users` SET `respect`=`respect`+5 WHERE `id`='{$data->forwardedFor}'");
            mysql_query("INSERT INTO `logs`(`time`,`login`,`person`,`code`,`area`,`com`) values(NOW(),'{$data->login}','{$data->forwardedFor}','5','respect','RefererID')");
        }
        $message = 'De activatie is voltooid, je kunt nu inloggen.';
    } else {
        $message = 'Incorrecte activatie-code...';
    }
} elseif (isset($_POST['submit'])) {
    $gebruiker = $_POST['gebruiker'];
    $pass = $_POST['pass'];
    $refer = $_POST['refer'];
    $passconfirm = $_POST['passconfirm'];
    $email = $_POST['email'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $geslacht = $_POST['geslacht'];
    $steden = ["Brussel","Leuven","Gent","Brugge","Hasselt","Antwerpen","Amsterdam","Enschede"];
    $stad = $steden[rand(0,7)];
    $ipexist = mysql_num_rows(mysql_query("SELECT * FROM `users` WHERE `ip`='{$ip}' AND `status`='levend'"));
    $ipallowed = mysql_num_rows(mysql_query("SELECT * FROM `multiple` WHERE `ip`='{$ip}'"));
    $exist = mysql_num_rows(mysql_query("SELECT * FROM `users` WHERE `login`='{$gebruiker}'"));
    $eexist = mysql_num_rows(mysql_query("SELECT * FROM `users` WHERE `login`='{$gebruiker}' AND `status`='levend'"));
    $rexistRes = mysql_query("SELECT * FROM `users` WHERE `id`='{$refer}'");
    $rexistObj = mysql_fetch_object($rexistRes);
    $rexist = mysql_num_rows($rexistRes);
    $referlogin = $rexistObj->login ?? '';
    if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $gebruiker)) {
        $message = 'De opgegeven gebruikersnaam is ongeldig, je mag enkel letters of cijfers gebruiken.';
    } elseif (!$pass || $pass != $passconfirm) {
        $message = 'De opgegeven wachtwoorden zijn niet identiek.';
    } elseif (!preg_match('/^.+@.+\..+$/', $email)) {
        $message = 'Het opgegeven e-mailadres is ongeldig.';
    } elseif ($ipexist == 1 && $ipallowed != 1) {
        $message = 'Er is al een account gemaakt op dit IP adres.';
    } elseif ($exist == 1) {
        $message = 'De opgegeven gebruikersnaam is al in gebruik.';
    } elseif ($eexist == 1) {
        $message = 'Er is al iemand aangemeld met dit e-mailadres.';
    } elseif ($rexist != 1 && $refer != '') {
        $message = 'De opgegeven referrerID bestaat niet.';
    } else {
        $code = rand(100000,999999);
        mysql_query("INSERT INTO `users`(`start`,`login`,`pass`,`ip`,`email`,`stad`,`geslacht`,`activated`) values(NOW(),'{$gebruiker}',MD5('{$pass}'),'{$ip}','{$email}','{$stad}','{$geslacht}','1')");
        mysql_query("INSERT INTO `temp`(login,ip,code,area,time,forwardedFor) values('$gebruiker','$ip',$code,'signup',NOW(),'$referlogin')");
        $id = mysql_insert_id();
        mail($email,"Vendetta activatie-code","Hallo $gebruiker,\n\nBedankt voor het aanmelden op Vendetta (game 3-logd.nl).\nKlik hier om je account te activeren:\nhttp://logd.nl/game3/register.php?id=$id&code=$code","From: game3-logd.nl <noreply@logd.nl>\n");
        $message = "Je bent geregistreerd, er is een e-mail gestuurd naar $email met een activatie-code...";
    }
}
$refer = $_GET['refer'] ?? '';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title>Vendetta - Registreren</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="container py-4">
    <h1 class="mb-4">Registreren</h1>
    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="post" class="row g-3">
        <div class="col-md-6 offset-md-3">
            <label for="gebruiker" class="form-label">Login</label>
            <input type="text" name="gebruiker" id="gebruiker" class="form-control" maxlength="16" required>
        </div>
        <div class="col-md-6 offset-md-3">
            <label for="pass" class="form-label">Wachtwoord</label>
            <input type="password" name="pass" id="pass" class="form-control" maxlength="12" required>
        </div>
        <div class="col-md-6 offset-md-3">
            <label for="passconfirm" class="form-label">Herhaal wachtwoord</label>
            <input type="password" name="passconfirm" id="passconfirm" class="form-control" maxlength="12" required>
        </div>
        <div class="col-md-6 offset-md-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" name="email" id="email" class="form-control" maxlength="64" required>
        </div>
        <div class="col-md-6 offset-md-3">
            <label for="geslacht" class="form-label">Geslacht</label>
            <select name="geslacht" id="geslacht" class="form-select">
                <option value="Man">Man</option>
                <option value="Vrouw">Vrouw</option>
            </select>
        </div>
        <div class="col-md-6 offset-md-3">
            <label for="refer" class="form-label">ReferrerID (optioneel)</label>
            <input type="text" name="refer" id="refer" class="form-control" value="<?php echo htmlspecialchars($refer); ?>" maxlength="64">
        </div>
        <div class="col-md-6 offset-md-3">
            <button type="submit" name="submit" class="btn btn-primary">Aanmelden</button>
        </div>
    </form>
</body>
</html>
