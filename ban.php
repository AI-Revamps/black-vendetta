<?
$host = gethostbyaddr($_SERVER['REMOTE_ADDR']);  
$parts = explode('.', $host);
if ($parts[count($parts)-1] != "be" && $parts[count($parts)-1] != "nl"){
echo"Proxys zijn niet toegelaten.";exit;
}
?>
