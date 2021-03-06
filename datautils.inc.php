<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
// connect to the database for all pages
global $mysqli;

include "../.DBParamInfo";

if (!isset($_SESSION['TEST_MODE'])) {
	// production database
	$db = ProdDBName;
		}
else {
	// test database
	$db = TestDBName;
	}
$mysqli = new mysqli("localhost", DBUserName, DBPassword, $db);

if ($mysqli->connect_errno) {
		$errno = $mysqli->connect_errno;
    echo "Failed to connect to MySQL: (" . $errno . ") " . $mysqli->connect_error."<br>";
    $_SESSION['DB_ERROR'] = $db;
    }
// auto returns to code following the 'include' statement
//echo "Initial Connection Info: ".$mysqli->host_info . "<br><br>";

// ------------------ submit sql statement provided by calling script ----------
// submit sql statement provided in call
function doSQLsubmitted($sql) { 
global $mysqli;

if (isset($_SESSION['DB_ERROR'])) return(FALSE);
//echo "sql: ".$sql."<br>";
$res = $mysqli->query($sql);
if (substr_compare($sql,"DELETE",0,6,TRUE) == 0) {
	//echo "<br>Delete command seen - return affected_rows<br>";
	$rowsdeleted = $mysqli->affected_rows;
	//echo "delete count: $rowsdeleted<br>";	
	addlogentry($sql);
	return($rowsdeleted);
	}
// NOTE:  could do a check to see if DELETE or REPLACE was done and 
//        return 'affected_rows' instead of select results 
if (!$res) {
    showError($res);
		}
addlogentry($sql);
return($res);
}

// --------- update existing row in table from assoc array provided -------------
function sqlupdate($table, $fields, $where) {
global $mysqli;

$nowdate = date('Y-m-d');					// now date if needed
$sql = "UPDATE `$table` SET ";
$f = ""; 
foreach ($fields as $k => $v) {
	if (strlen($v) > 0) {
		$vv = urldecode($v);
		$vv = addslashes($vv);
		$f .= "`$k`='$vv', ";
		}
	else {
		$f .= "`$k`=NULL, ";
		}
 	}
$f = rtrim($f, ', ');
$sql .= $f . ' WHERE ' . $where;
//echo "Update SQL: $sql<b>";
$res = $mysqli->query($sql);
addlogentry($sql);
if (!$res) {
 	showError($res);	
	}
return;
}

// ----------- add new row into table from assoc array-------------
function sqlinsert($table,$fields) {
global $mysqli;

$nowdate = date('Y-m-d');					// now date if needed
$sql = "INSERT INTO $table (";
foreach ($fields as $k => $v) {		// field names for sql statement
	$fieldnames .= "`$k`, ";
	}
foreach ($fields as $k => $v) {		// field values for sql statement
	if (strlen($v) == 0) {
		$fieldvalues .= "NULL, ";
		}
	else {	
		$vv = urldecode($v);
		$vv = addslashes($vv);
		$fieldvalues .= "'$vv', ";
		}
	}
$sql .= rtrim($fieldnames, ', ');
$sql .= ") VALUES (";
$sql .= rtrim($fieldvalues,', ');
$sql .= ");";

$res = $mysqli->query($sql);
$rows = $mysqli->affected_rows;
if (!$res) {
	$err= showError($res);
	return($err);
	}
addlogentry($sql);
//echo "Insert SQL: $sql<br>";
//echo "affected rows: $rows<br>";
return($rows);
}

// --------------------- generalized error display for all DB functions ----------
function showError($res) {
global $mysqli;
	$errno = $mysqli->errno;
	$errmsg = $mysqli->error;
	if ($errno == 1049) {
		$db = $_SESSION['DB_ERROR'];
		print <<<errNoDB
<div class="alert">
<button type="button" class="close" data-dismiss="alert">&times;</button>
<strong>DB ERROR: database $db is not available</strong>
</div>
errNoDB;
  return(FALSE);
  }
	if ($errno == 1062) {
		$errmsg .= "<br>A record already exists for the unique key provided.";
		}
	print <<<errMsg
<div class="alert">
<button type="button" class="close" data-dismiss="alert">&times;</button>
<strong>DB ERROR $errno</strong>: $errmsg
</div>
errMsg;
  return(FALSE);
	}	

// ------------------------- add new log entry ----------------------------
function addlogentry($text) {
	global $mysqli;
	if (isset($_SESSION['DB_ERROR'])) return(FALSE);
	$user = $_SESSION['SessionUser'];
	$seclevel = $_SESSION['SecLevel'];
	$page = $_SERVER['PHP_SELF'];
	$txt = addslashes($text);
	$sql = "INSERT INTO `log` (`User`, `SecLevel`, `Page`, `Text`) VALUES ('$user', '$seclevel', '$page', '$txt');";
	//echo "Log: $sql<br>";
	$res = $mysqli->query($sql);
	if (!$res) {
		$errno = $mysqli->errno;
		$errmsg = $mysqli->error;
		echo "LOGGING ERROR: $errno -> $errmsg<br>";
		}
	return($err);
	}


// ---------------------------- text file utils ---------------------------------------------
function loadlist($listname) {
	$listitems = file("$listname");
	foreach ($listitems as $p) {
		$p = rtrim($p);
		if (strlen($p) <=0) { continue; } 
		if (substr_compare($p,'//',0,2) == 0) { continue; }
		printf("%s",$p);
		}
	return(TRUE);
}

// load text file
function loadmaintlist($listname) {
	$listitems = file("$listname");
	foreach ($listitems as $p) {
		printf("%s",$p);
		}
	return(TRUE);
}

// write text file
function writemaintlist($filename,$content) {
	file_put_contents("$filename", $content);
	}

// --------------------- db configtable utilities ----------------------------------------
// 'configtable' column names: CFGId, CfgName, CfgText
// read db table item
function readdblist($listname) {
	$sqldb = "SELECT * FROM `configtable` WHERE `CfgName` = '$listname';";
	//echo "sql: $sqldb\n<br>";
	$res = doSQLsubmitted($sqldb);
	$r = $res->fetch_assoc();
	$rc = $res->num_rows;
	//echo "rc: $rc<br>";
	//echo "<pre> cfg "; print_r($r); echo '</pre>';
	return($r[CfgText]);
	}

// format text blob from db into an array
function formatdbrec($txt) {
	$res = array();
	$lines = explode("\n",$txt); 
	foreach ($lines as $l) {
		$l = rtrim($l);
		if (strlen($l) <= 0) { continue; } 
		if (substr_compare($l,'//',0,2) == 0) { continue; }
		list($tla,$desc) = explode(":", $l);
		$res[$tla] = $desc;
		//echo "tla:$tla, desc:$desc<br>";
		}
	return($res);
	}


// ------------------- calc and return expiration date -----------------
function calcexpirationdate() {
	return(date('Y-m-01', strtotime('-11 months')));									// this is the expiration period
	}

// ------------------ check login credentials --------------------------
function checkcredentials($userid, $password) {
	$sql = "SELECT * FROM `adminusers` WHERE `UserID` = '$userid'";
	$res = doSQLsubmitted($sql);
	$nbrofrows = $res->num_rows;
	if ($nbrofrows == 0) {
		echo "ERROR: userid not valid<br>";
		addlogentry("Login attempt by $userid");
		return(false);
		}
	else {
		$res->data_seek(0);
		$r = $res->fetch_assoc();
		}
	if (($r[UserID] == $userid) && ($r[Password] == $password)) {
		//echo "found match - user: $uid, pw: $pw<br>";
		$_SESSION['SessionTimer'] = time() + $_SESSION['SessionLength'];
		$_SESSION['SecLevel'] = $r[Role];
		$_SESSION['SessionUser'] = $userid;
		return(true);
		}
	echo "ERROR: userid and/or password provided not valid.<br>";
	addlogentry("Password failure by $userid");
	return(false);
	}


?>