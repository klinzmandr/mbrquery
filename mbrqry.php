<!DOCTYPE html>
<html>
<head>
<title>Membership Query</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Bootstrap -->
<link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
</head>
<body>
<?php
/*
echo "<hr>Debug Info: dump of input array POST name and value pairs<br>";
foreach ($_REQUEST as $key => $value) { echo "Key: $key, Value: //$value<br>";  }
echo "<hr>Debug Info: dump of input array SESSION name and value pairs<br>";
foreach ($_SESSION as $key => $value) { echo "Key: $key, Value: $value<br>";  }
echo "<hr>";
*/
session_start();
date_default_timezone_set('America/Los_Angeles');
include 'datautils.inc';

$mcid = isset($_REQUEST['mcid']) ? $_REQUEST['mcid'] : "";
$qry = isset($_REQUEST['qry']) ? $_REQUEST['qry'] : "";
$submitdone = isset($_REQUEST['submit'])? TRUE : FALSE;
$ftime = date("F j, Y \a\\t g:i a", $stat['mtime']);
$hitcount = 0;
$mbrcnt = count($mbrarray);

if (isset($_REQUEST['u'])) {
	if (($_REQUEST['u']) != 'pelican') {
		echo "<head><meta http-equiv=\"refresh\" content=\"2; URL=http://www.pacificwildlifecare.org/mbrship/index.php\"></head>";		
		echo "<h1>Password Failed.  Try again!</h1>";
		echo "password entered: " . $_REQUEST['u'] . "<br>";
		exit(0);
		}
	}
//echo "entered key:" . $_REQUEST['kkeystring'] . ":<br>"; 
//echo "&nbsp;session key:" . $_SESSION['captcha_keystring'] . ":<br>"; 
//echo "check keystring<br>";
if (isset($_REQUEST['kkeystring'])) {
	//echo "checking keystring<br>";
	if (isset($_SESSION['captcha_keystring']) && 
				$_SESSION['captcha_keystring'] ==  $_REQUEST['kkeystring']) {
		//echo "<br>MATCHED";
		}
	else {
		echo "<head><meta http-equiv=\"refresh\" content=\"2; URL=http://www.pacificwildlifecare.org/mbrship/index.php\"></head>";
		echo "<h1>Verification Failed.  Try again!</h1>";
		exit(0);
		}	
	}

print <<<hdrPart
<head><title>Member Query</title>
<script>
function loadfocus() {
	document.qryForm.mcid.focus();
	}
</script>
</head>
<body onload="loadfocus()")>
<h1>Membership Query</h1>

hdrPart;

print <<<formPart
<div class="container">
<form action="mbrqry.php" method="post"  name="qryForm">
Search for: <input autofocus autocomplete="off" type="input" name="qry" value="$qry">&nbsp;&nbsp;
<input type="submit" name="submit" value="Submit"><br>
</form>
formPart;

$reccount = 0;
if ($submitdone) {
	$sql = "SELECT * FROM `members` WHERE `MCID` LIKE '%$qry%' OR `FName` LIKE '%$qry%' OR `LName` LIKE '%$qry%' OR `NameLabel1stline` LIKE '%$qry%' OR `City` LIKE '%$qry%' OR `PrimaryPhone` LIKE '%$qry%' OR `EmailAddress` LIKE '%$qry%' ORDER BY `MCID` ASC;";
	$res = doSQLsubmitted($sql);
	$hitcount = $res->num_rows;
	if ($hitcount > 0) {
		while ($r = $res->fetch_assoc()) {
			format_record($r);
			$reccount++;
			}
		}
	}
echo "Records found: $reccount<br>";
echo '</div><script src="jquery.js"></script><script src="js/bootstrap.min.js"></script>
</body></html>';
exit(0);

function format_record($r) {
global $hitcount;
$label = "";
if (strlen($r[Organization]) > 0)  $label .= $r[Organization] . "<br>"; 
else $label .= '<br>';
$label .= "$r[NameLabel1stline]<br>$r[AddressLine]<br>$r[City], $r[State]  $r[ZipCode]";
$em = "<a href=\"mailto:$r[EmailAddress]\">$r[EmailAddress]</a>";
$hitcount++;
print <<<rcdPage
<strong>MCID: $r[MCID]</strong><br>
<table cellpadding="0" cellspacing="0" border="1" width="100%">
<tr>
<td width="30%" valign="top"><u>Mailing Label:</u><br>
<table cellpadding="0" cellspacing="0" border="1" width="100%">
<tr><td valign="top" bgcolor="#E6E6FA">$label</td></tr>
</table></td>

<td width="40%" valign="top"><u>Contact Info:</u><br>FName: $r[FName]<br>LName: $r[LName]<br>Email: $em<br>Phone: $r[PrimaryPhone]</td>
<td><u>Membership Info:</u><br>Type: $r[MemType]<br>Date Joined: $r[MemDate]<br>Status: $r[MemStatus]<br>InActive: $r[Inactive]<br>Date Inactive: $r[Inactivedate]</td>
</tr>
</table><br>
</body></html>
rcdPage;

	return;
	}

?>
</div>
<script src="jquery.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
