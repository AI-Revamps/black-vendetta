<?
include("config.php");
?>
<script language="JavaScript">
<!--
function addEmoticon(emoticon)
{
	form.bericht.value += " " + emoticon;
}
//-->
</script>
<form name="form" method="post" action="berichtenbalk.php">
	<img src="<? echo "$dir"; ?>/new.gif">
    <input name="naam" type="text" value="Je naam" maxlength="<? echo "$maxn"; ?>">
    <input name="bericht" type="text" value="Je bericht" maxlength="<? echo "$maxb"; ?>">
    <input type="submit" name="Submit" value="toevoegen">
    <br>
<?
echo"
<a href=javascript: onclick=addEmoticon('[color=][/color]')><img border=0 src=\"$dir/color.gif\"></a> 
<a href=javascript: onclick=addEmoticon('[b][/b]')><img border=0 src=\"$dir/bold.gif\"></a> 
<a href=javascript: onclick=addEmoticon('[u][/u]')><img border=0 src=\"$dir/underline.gif\"></a> 
<a href=javascript: onclick=addEmoticon('[i][/i]')><img border=0 src=\"$dir/italic.gif\"></a><br>
<a href=javascript: onclick=addEmoticon(':s')><img border=0 src=\"$dir/smilie1.gif\" width=15 height=15></a>
<a href=javascript: onclick=addEmoticon(':cool:')><img border=0 src=\"$dir/smilie2.gif\" width=15 height=15></a>
<a href=javascript: onclick=addEmoticon(':,(')><img border=0 src=\"$dir/smilie3.gif\" width=15 height=15></a>
<a href=javascript: onclick=addEmoticon(':d')><img border=0 src=\"$dir/smilie4.gif\" width=15 height=15></a>
<a href=javascript: onclick=addEmoticon(':@')><img border=0 src=\"$dir/smilie5.gif\" width=15 height=15></a>
<a href=javascript: onclick=addEmoticon(':(')><img border=0 src=\"$dir/smilie6.gif\" width=15 height=15></a>
<a href=javascript: onclick=addEmoticon(':|')><img border=0 src=\"$dir/smilie7.gif\" width=15 height=15></a>
<a href=javascript: onclick=addEmoticon(':$')><img border=0 src=\"$dir/smilie8.gif\" width=15 height=15></a>
<a href=javascript: onclick=addEmoticon(':)')><img border=0 src=\"$dir/smilie9.gif\" width=15 height=15></a>
<a href=javascript: onclick=addEmoticon(':p')><img border=0 src=\"$dir/smilie10.gif\" width=15 height=15></a>
<a href=javascript: onclick=addEmoticon(';)')><img border=0 src=\"$dir/smilie11.gif\" width=15 height=15></a>";

?>
  </form>