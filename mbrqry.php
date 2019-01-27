<?php 
session_start();
date_default_timezone_set('America/Los_Angeles');
include 'datautils.inc.php';
// include 'vardump.inc.php';
//$url = "http://apps.pacwilica.org/mbrquery";
$url = $_SERVER['HTTP_REFERER'];
// echo "url: $url<br>";
?>
<!DOCTYPE html>
<html>
<head>
<title>Membership Query</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Bootstrap -->
<link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
</head>
<body>
<script src="jquery.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>
$("document").ready(function() {
  $("#help").hide();
$("#helpbtn").click(function(e) {
  e.preventDefault();
  $("#help").toggle();
  });
});
</script>

<?php
$mcid = isset($_REQUEST['mcid']) ? $_REQUEST['mcid'] : "";
$qry = isset($_REQUEST['qry']) ? $_REQUEST['qry'] : "";
$submitdone = isset($_REQUEST['submit'])? TRUE : FALSE;
$ftime = date("F j, Y \a\\t g:i a", $stat['mtime']);
$hitcount = 0;
$mbrcnt = count($mbrarray);

if (isset($_REQUEST['u'])) {
	if (($_REQUEST['u']) != 'pelican') {
		echo "<head><meta http-equiv=\"refresh\" content=\"2; URL=$url\"></head>";		
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
		echo "<head><meta http-equiv=\"refresh\" content=\"2; URL=$url/index.php\"></head>";
		echo "<h1>Verification Failed.  Try again!</h1>";
		exit(0);
		}	
	}

?>
<head><title>Member Query</title>
<script>
function checkinput() {
	var inf = $("#qry").val();
	if (!inf.length) {
		alert("Nothing entered to search for.");
		return false;
		}
	return true;
	}
</script>
</head>
<body>
<h1>Membership Query</h1>
<div class="container">
<form action="mbrqry.php" method="post"  name="qryForm" onsubmit="return checkinput()" class=in-line>
Search for: <input autofocus autocomplete="off" type="input" id="qry" name="qry" value="<?=$qry?>">&nbsp;&nbsp;
<input type="submit" name="submit" value="Submit">
<button id="helpbtn">HELP</button>
</form>
<style>
table, th, td {
    border: 1px solid black;
    font-size: 15px;
}
</style>
<div id="help">
<h3>Tips for searching</h3>
<ul>
<li>Try using one or more characters or a number string without spaces.</li>
<li>Space characters are significant and are searched for if entered.</li>
<li>Fields searched include the Member Id, first name, last name, address, city, state, zip, phone number, email address and volunteer lists.</li>
<li>Usually searching for the first 3 characters of a persons last name is the most direct query method.</li></ul>
NOTE: results listed are ALL member records containing the target string entered in ANY of the listed fields.<br><br>
Following is a list of volunteer group codes that can also be used. Use the code, not the description.  CAUTION: results may include member records that contain the character string but are NOT in the volunteer group.<br><br>

<?php
$liststr = readdblist('EmailLists');
$listarray = formatdbrec($liststr);
// echo '<pre>liststr '; print_r($liststr); echo '</pre>';
// echo '<pre>listarray '; print_r($listarray); echo '</pre>';
echo '<ul><table><tr><th>List Code</th><th>List Name</th></tr>';
foreach ($listarray as $k => $v) {
	echo "<tr><td>$k</td><td>$v</td></tr>";
	}
echo '</table></ul></div>';

if (!isset($_REQUEST['qry'])) exit;

$reccount = 0;

$sql = "SELECT * FROM `members` 
WHERE `MCID` LIKE '%$qry%' 
	OR `FName` LIKE '%$qry%' 
	OR `LName` LIKE '%$qry%' 
	OR `NameLabel1stline` LIKE '%$qry%' 
	OR `AddressLine` LIKE '%$qry%' 
	OR `City` LIKE '%$qry%' 
	OR `ZipCode` LIKE '%$qry%'
	OR `PrimaryPhone` LIKE '%$qry%' 
	OR `EmailAddress` LIKE '%$qry%' 
	OR `Lists` LIKE '%$qry%'
ORDER BY `MCID` ASC;";

//	echo "SQL: $sql<br>";
	$res = doSQLsubmitted($sql);
	$hitcount = $res->num_rows;
	if ($hitcount > 0) {
		echo "Records found: $hitcount<br>";
		while ($r = $res->fetch_assoc()) {
//		  echo '<pre>'; print_r($r); echo '</pre>';
			format_record($r);
			$reccount++;
			}
		}
	echo "Records found: $reccount<br>";

	if ($reccount <= 0) {
		$dbrec = readdblist('EmailLists');
		$emarray = formatdbrec($dbrec);
//		echo '<pre>'; print_r($emarray); echo '</pre>';
		echo '<ul><table><tr><th>CODE</th><th>Description</th></tr>';
		foreach ($emarray as $k => $v) {
			echo "<tr><td>$k</td><td>$v</td></tr>";
			}
		echo '</table></ul>';
		}
echo '</div></body></html>';
exit(0);

function format_record($r) {
global $hitcount;
$label = "";
if (strlen($r[Organization]) > 0)  $label .= $r[Organization] . "<br>"; 
else $label .= '';
$label .= "$r[NameLabel1stline]<br>$r[AddressLine]<br>$r[City], $r[State]  $r[ZipCode]";
$em = "<a href=\"mailto:$r[EmailAddress]\">$r[EmailAddress]</a>";
$em2 = (strlen($r[EmailAddress2]) > 0) ? "<a href=\"mailto:$r[EmailAddress2]\">$r[EmailAddress2]</a>" : '';
 ;
$hitcount++;
print <<<rcdPage
<strong>MCID: $r[MCID]</strong><br>
<table border="1" width="100%">
<tr>
<td width="25%" valign="top"><u>Mailing Label:</u><br>
<table border="1" width="100%"><tr><td>&nbsp;</td></tr>
<tr><td valign="top" bgcolor="#E6E6FA">$label</td></tr>
</table></td>

<td width="25%" valign="top"><u>Contact Info:</u><br>FName: $r[FName]<br>LName: $r[LName]<br>
Email: $em<br>
rcdPage;

if (strlen($em2) > 0) echo "Email2: $em2<br>";
print <<<rcdPage1
Phone: $r[PrimaryPhone]</td>
<td valign="top"><u>Membership Info:</u><br>Type: $r[MCtype]<br>Date Joined: $r[MemDate]<br>Status: $r[MemStatus]<br>InActive: $r[Inactive]<br>Date Inactive: $r[Inactivedate]</td>
rcdPage1;

$listarray = explode(",", $r[Lists]);
echo '<td valign="top"><u>Vol Committees</u><br>';
foreach ($listarray as $v) {
  echo "$v<br>";
  }
echo '</td></tr></table>';

	return;
	}

?>
</div>

</body>
</html>
<script