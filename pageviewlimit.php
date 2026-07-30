<?php  
Function PageViewLimit(){ 
   $dbres = mysql_query("SELECT paid FROM `users` WHERE `login`='{$_SESSION['login']}'");
   $data = mysql_fetch_object($dbres); 
   $paid = $data->paid;
   $clicks = array(15,25,35,50,100);  // Aantal clicks per rang (gewoon, donateur, silver, gold, admin)
   $PvlViews=$clicks[$paid]; 
   $error="
<html>
<head>
<title>Vendetta</title>
<link rel=\"stylesheet\" type=\"text/css\" href=\"style.css\">
</head>
  <table width=100%>
    <tr> 
    <td class=\"subTitle\"><b>Kliklimiet</b></td>
  </tr>
  <tr><td>&nbsp;&nbsp;</td></tr>
  <tr> 
    <td class=\"mainTxt\">
	Je mag om de server niet te belasten maar $PvlViews pagina's per minuut bekijken.<br>Je kan je kliklimiet verhogen door donateur te worden.
    </td></tr>
  </table>
</body>
</html>"; // error 

   session_start();  
   if(!isset($_SESSION['Pvl'])){  
      $_SESSION['Pvl']['Time']=time();  
      $_SESSION['Pvl']['Views']=1;  
   }  
   else{  
      // timer 
      if((time()-$_SESSION['Pvl']['Time']) >= 60){  
     $_SESSION['Pvl']['Time']=time();  
     $_SESSION['Pvl']['Views']=1;  
      }  
      else{  
         $_SESSION['Pvl']['Views']++;  

     if($_SESSION['Pvl']['Views']>=$PvlViews){  
           exit($error);  
         }  
      }  
   }  
}  
?> 
