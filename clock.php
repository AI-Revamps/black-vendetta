<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Vendetta</title>
<link href="style.css" rel="stylesheet" type="text/css">
<meta name="keywords" content="Vendetta,Crimegame,crimegame,vendetta">
<meta name="language" content="english">
<META name="description" lang="nl" content="Vendetta crimegame met pit.">
</head>

<body leftmargin="0" topmargin="0" rightmargin="0" bottommargin="0">
<div align="right" id="clock" style="font-size:18px;font-family:Arial,Helvetica,sans-serif;color:#000;background:#87CEFA;width:140px;height:140px;display:flex;justify-content:center;align-items:center;"></div>
<script type="text/javascript">
function updateClock(){
  var now = new Date();
  var h = now.getHours().toString().padStart(2,'0');
  var m = now.getMinutes().toString().padStart(2,'0');
  var s = now.getSeconds().toString().padStart(2,'0');
  document.getElementById('clock').textContent = h+":"+m+":"+s;
}
updateClock();
setInterval(updateClock,1000);
</script>
</body>
</html>
