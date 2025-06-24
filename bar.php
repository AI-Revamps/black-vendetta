<?php
declare(strict_types=1);
require 'config.php';

function parseEmoticons(string $text, string $dir): string {
    $map = [
        ':-s' => 'smilie1.gif', ':-S' => 'smilie1.gif', ':s' => 'smilie1.gif', ':S' => 'smilie1.gif',
        ':cool:' => 'smilie2.gif', ':,(' => 'smilie3.gif', ':,-(' => 'smilie3.gif',
        ':-d' => 'smilie4.gif', ':-D' => 'smilie4.gif', ':d' => 'smilie4.gif', ':D' => 'smilie4.gif',
        ':@' => 'smilie5.gif', ':-@' => 'smilie5.gif',
        ':(' => 'smilie6.gif', ':-(' => 'smilie6.gif',
        ':|' => 'smilie7.gif', ':-|' => 'smilie7.gif',
        ':$' => 'smilie8.gif', ':-$' => 'smilie8.gif',
        ':-)' => 'smilie9.gif', ':)' => 'smilie9.gif',
        ':-p' => 'smilie10.gif', ':-P' => 'smilie10.gif', ':p' => 'smilie10.gif', ':P' => 'smilie10.gif',
        ';)' => 'smilie11.gif', ';-)' => 'smilie11.gif'
    ];
    foreach ($map as $code => $img) {
        $text = str_replace($code, '<img src="' . htmlspecialchars($dir, ENT_QUOTES) . '/' . $img . '" border="0">', $text);
    }
    $text = preg_replace('/\[b\](.*?)\[\/b\]/si', '<b>$1</b>', $text);
    $text = preg_replace('/\[u\](.*?)\[\/u\]/si', '<u>$1</u>', $text);
    $text = preg_replace('/\[i\](.*?)\[\/i\]/si', '<i>$1</i>', $text);
    $text = preg_replace('/\[color=(.*?)\](.*?)\[\/color\]/si', '<font color="$1">$2</font>', $text);
    $text = preg_replace('~[[:graph:]]+@[^<>[:space:]]+[[:alnum:]]~i', '<a href="mailto:$0">$0</a>', $text);
    $text = preg_replace('~(^|[>[:space:]\n])([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])([<[:space:]\n]|$)~i', '$1<a href="$2://$3$4" target="_blank">$2://$3$4</a>$5', $text);
    $text = preg_replace('/\[URL\](.+?)\[\/URL\]/si', '<a href="$1" target="_blank">$1</a>', $text);
    $text = preg_replace('~\[email\]([^\[]*)\[/email\]~i', '<a href="mailto:$1">$1</a>', $text);
    $text = preg_replace('~\[email=([^\[]*)\]([^\[]*)\[/email\]~i', '<a href="mailto:$1">$2</a>', $text);
    return $text;
}

$output = [];
for ($i = 0; $i <= $hits; $i++) {
    $output[] = parseEmoticons($getal[$i], $dir);
}
?>
<table width="100%" height="40" border="1" bordercolor="#000000">
  <tr>
    <td valign="center"><marquee>
<?php
if (!$bestand) {
    echo htmlspecialchars($leeg, ENT_QUOTES);
} else {
    if ($vooraan === 'oude') {
        foreach ($output as $msg) {
            echo $msg;
        }
    } elseif ($vooraan === 'nieuwe') {
        $start = $hits;
        $stop = max($hits - $max, 0);
        for ($i = $start; $i > $stop; $i--) {
            echo $output[$i];
        }
    }
}
?>
    </marquee></td>
  </tr>
</table>
