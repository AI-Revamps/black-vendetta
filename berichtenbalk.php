<?php
declare(strict_types=1);
require 'config.php';
session_start();

function htmlparse(string $string): string {
    return htmlspecialchars(trim($string), ENT_QUOTES);
}

$status = 'Je kan nu een bericht intypen!';

if (isset($_POST['Submit'])) {
    $naam = $_POST['naam'] ?? '';
    $bericht = $_POST['bericht'] ?? '';

    if (($_SESSION['tijd'] ?? 0) + $verlooptijd > time()) {
        $status = $flood;
    } elseif ($naam === '' || $bericht === '' || $naam === 'Je naam' || $bericht === 'Je bericht') {
        $status = $ongeldig;
    } else {
        $_SESSION['tijd'] = time();
        $naam = htmlparse($naam);
        $bericht = htmlparse($bericht);
        $open = fopen($file, 'a');
        fwrite($open, "<?php\n\$getal[\$hits] = \"<b>$naam:</b> $bericht <b>$tussen</b> \";\n?>\n");
        fclose($open);
        $status = $post;
    }
}
?>
<iframe src="<?php echo htmlspecialchars($bar, ENT_QUOTES); ?>" frameborder="0" width="100%" height="*" name="main" scrolling="no"></iframe>
<table height="*" width="*" align="center" valign="middle" style="font-family: verdana">
  <tr>
    <td align="center"> <table style="border: 1px solid black; font-size: 10pt;" cellpadding="2" cellspacing="5">
        <tr>
          <td align="center">Status:<br><b><?php echo htmlspecialchars($status, ENT_QUOTES); ?></b>
<?php include $form; ?> <a href="bar.php" target="main"><img src="<?php echo htmlspecialchars($dir, ENT_QUOTES); ?>/www.gif" border="0">Vernieuwen</a>
            / <a href="admin.php"><img src="<?php echo htmlspecialchars($dir, ENT_QUOTES); ?>/admin.gif" border="0">Admin</a></TD>
        </TR></TABLE></TD></TR></TABLE></TD></TR></TABLE></TD></TR></TABLE>
