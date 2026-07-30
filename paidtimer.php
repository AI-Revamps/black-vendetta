<?php
$var1 = $data->crime - time();
$var2 = $data->ac - time();
$var3 = $data->transport - time();
$var4 = $data->pc - time();
$var5 = $data->bc - time();
?>
<script>
var a = 0
var b = 0
var c = 0
var d = 0
var e = 0
function funca(timeleft){
if(timeleft >= 0){
  //a new function started so check the time left.
  a = timeleft;
}
if(a <= 0){
  //time is lower then 0 so show Now
  a="Nu"
  //display
  document.getElementById('crime').innerHTML=a
}
else{
  //start calculating
  calculateTime(a)
  //display
  document.getElementById('crime').innerHTML=left
  //time left-1
  a=a-1
  //loop
  t=setTimeout("funca()",1000)
}
}

function funcb(timeleft){
if(timeleft >= 0){
  //a new function started so check the time left.
  b = timeleft;
}
if(b <= 0){
  //time is lower then 0 so show Now
  b="Nu"
  //display
  document.getElementById('car').innerHTML=b
}
else{
  //start calculating
  calculateTime(b)
  //display
  document.getElementById('car').innerHTML=left
  //time left-1
  b=b-1
  //loop
  t=setTimeout("funcb()",1000)
}
}

function funcc(timeleft){
if(timeleft >= 0){
  //a new function started so check the time left.
  c = timeleft;
}
if(c <= 0){
  //time is lower then 0 so show Now
  c="Nu"
  //display
  document.getElementById('trans').innerHTML=c
}
else{
  //start calculating
  calculateTime(c)
  //display
  document.getElementById('trans').innerHTML=left
  //time left-1
  c=c-1
  //loop
  t=setTimeout("funcc()",1000)
}
}

function funcd(timeleft){
if(timeleft >= 0){
  //a new function started so check the time left.
  d = timeleft;
}
if(d <= 0){
  //time is lower then 0 so show Now
  d="Nu"
  //display
  document.getElementById('heist').innerHTML=d
}
else{
  //start calculating
  calculateTime(d)
  //display
  document.getElementById('heist').innerHTML=left
  //time left-1
  d=d-1
  //loop
  t=setTimeout("funcd()",1000)
}
}

function funce(timeleft){
if(timeleft >= 0){
  //a new function started so check the time left.
  e = timeleft;
}
if(e <= 0){
  //time is lower then 0 so show Now
  e="Nu"
  //display
  document.getElementById('oc').innerHTML=e
}
else{
  //start calculating
  calculateTime(e)
  //display
  document.getElementById('oc').innerHTML=left
  //time left-1
  e=e-1
  //loop
  t=setTimeout("funce()",1000)
}
}

function checkTime(i)
{
if (i<10) {
  i="0" + i
}
return i
}

function calculateTime(z){
  togo = z;
  trash = togo/3600;
  h = Math.floor(trash);
  trash = togo-(h*3600);
  trash = trash/60;
  m = Math.floor(trash);
  trash = togo-((h*3600)+(m*60));
  s = Math.floor(trash);
  s=checkTime(s);
  m=checkTime(m);
  h=checkTime(h);
  
  left = h+":"+m+":"+s;
  return left
}
</script>

<body OnLoad="funca(<?php echo $var1; ?>);funcb(<?php echo $var2; ?>);funcc(<?php echo $var3; ?>);funcd(<?php echo $var4; ?>);funce(<?php echo $var5; ?>);">
<tr><td>
		  <table width=100% border=0 cellspacing=0 cellpadding=0>
		  <tr>
		    <td><a href=crime.php target=hoofd>Midaad: </a></td><td><div id='crime'></div></td>
		  </tr>
		  <tr>
		    <td><a href=nickacar.php target=hoofd>Auto: </a></td><td><div id='car'></div></td>
		  </tr>
		  <tr>
		    <td><a href=heist.php target=hoofd>Route 66: </a></td><td><div id='heist'></div></td>
		  </tr>
		  <tr>
		    <td><a href=oc.php target=hoofd>OC: </a></td><td><div id='oc'></div></td>
		  </tr>
		  <tr>
		    <td><a href=transport.php target=hoofd>Transport: </a></td><td><div id='trans'></div></td>
		  </tr>
		  </table>
		  </td>
</tr>
