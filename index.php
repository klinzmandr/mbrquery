<!DOCTYPE html>
<html>
<head>
<title>Membership General Query</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Bootstrap -->
<link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
</head>
<body>
<script>
function validatekfld() {
// keystring?
ks = new String(document.kapform.kkeystring.value);
if (ks.length < 1) {
  alert("Please enter the letters on the left.");
  document.kapform.kkeystring.focus();
  return false; }
	}

</script>
<?php 
echo "<h1>Security Check</h1>";
session_start();
?>
<form action="mbrqry.php" method="post" name="kapform" onsubmit="return validatekfld();">
<table border="0" width="80%"><tr><td align="right">
<img src="../captcha/index.php?<?php echo session_name()?>=<?php echo session_id()?>" style="vertical-align:middle" /></td>
<td>Message Verification - enter letters at left.<br>
<input autofocus type="text" name="kkeystring">(Reload page for new letters)<br>
<input type="password" name="u">Enter usage password:<br>
</td></tr>
<tr><td align="right"><td>
<input type="reset" value="Reset">
<input name="check" type="submit" value="Submit"></td></tr>
</form>  
</table>
<script src="jquery.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>