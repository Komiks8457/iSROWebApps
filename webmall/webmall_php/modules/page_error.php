<?php 
if (isset($_REQUEST['code']))
{
	switch(strtolower($msg = $_REQUEST['code']))
	{
		case "c101": // debugging mode is active
			$errormsg = "This webpage is currently under maintainance";
			break;
		case "c102": // calendar is current close, will be open next month
			$errormsg = "Attendance event of this month has ended, will be open again in next month.";
			break;
		case "c103": // not-used
			$errormsg = "An error [".strtoupper($msg)."] occured.<br>Please contact administrator immediately.";
			break;
		case "c104": // key not matched in fingerprint
			$errormsg = "An error [".strtoupper($msg)."] occured.<br>Please contact administrator immediately.";
			break;
		case "c105": // passed jid is not a number and passed key is not md5 hash
			$errormsg = "An error [".strtoupper($msg)."] occured.<br>Please contact administrator immediately.";
			break;
		case "c106": // ***CRITICAL*** $genkey return -1
			$errormsg = "An error [".strtoupper($msg)."] occured.<br>Please try reopening this page, if error still occur contact an administrator ASAP.";
			break;
		case "c107": // no jid,key,day or token parameter was pass
			$errormsg = "An error [".strtoupper($msg)."] occured.<br>Please contact administrator immediately.";
			break;
		case "c108": // someone is trying to direct access the event.asp
			$errormsg = "An error [".strtoupper($msg)."] occured.<br>Please contact administrator immediately.";
			break;
		case "c109": // someone is trying to sqli
			$errormsg = "An error [".strtoupper($msg)."] occured.<br>Please contact administrator immediately.";
			break;
		case "c110": // fingerprint key already existed
			$errormsg = "An error [".strtoupper($msg)."] occured.<br>Please contact administrator immediately.";
			break;
		case "c111": // fingerprint insert error
			$errormsg = "An error [".strtoupper($msg)."] occured.<br>Please contact administrator immediately.";
			break;
		case "c112": // unknown token
			$errormsg = "An error [".strtoupper($msg)."] occured.<br>Please contact administrator immediately.";
			break;
		case "c113": // expired token
			$errormsg = "An error [".strtoupper($msg)."] occured.<br>Please contact administrator immediately.";
			break;
		case "c114": // token encryption failed
			$errormsg = "An error [".strtoupper($msg)."] occured.<br>Please contact administrator immediately.";
			break;
		case "c115": // token decryption failed
			$errormsg = "An error [".strtoupper($msg)."] occured.<br>Please contact administrator immediately.";
			break;
		case "c116": // requested module not found
			$errormsg = "An error [".strtoupper($msg)."] occured.<br>Please contact administrator immediately.";
			break;
		case "c117": // no module requested
			$errormsg = "An error [".strtoupper($msg)."] occured.<br>Please contact administrator immediately.";
			break;
		case "c118": // ***CRITICAL*** $calendar_info returned false
			$errormsg = "An error [".strtoupper($msg)."] occured.<br>Please try reopening this page, if error still occur contact an administrator ASAP.";
			break;
		case "c119": // ***CRITICAL*** $calendar did not return number.
			$errormsg = "An error [".strtoupper($msg)."] occured.<br>Please try reopening this page, if error still occur contact an administrator ASAP.";
			break;
		case "c120": // ***CRITICAL*** $logintime returned -1
			$errormsg = "An error [".strtoupper($msg)."] occured.<br>Please try reopening this page, if error still occur contact an administrator ASAP.";
			break;
		case "c121": // session key already exists
			$errormsg = "An error [".strtoupper($msg)."] occured.<br>Please try reopening this page, if error still occur contact an administrator ASAP.";
			break;
		case "c777": // 
			$errormsg = "<b>WEBMALL PHP BY Komiks👀#8457</b><br><a href=\"https://github.com/Komiks8457/iSROWebApps\">https://github.com/Komiks8457/iSROWebApps</a>";
			break;
		default: // nothng is match from the above cases
			$errormsg = "An error [".strtoupper($msg)."] occured.<br>Please contact administrator immediately.";
			break;
	}
}
else
{
	$errormsg = "An error [C300] occured.<br>Please contact administrator immediately.";
}
$fn->writelog($errormsg, "errors.log");
?>
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Keywords" content="silkroad, silkroadonline, joymax, onlinesilkroad, silkroad-online" />
<META http-equiv='Page-Enter' content='blendTrans(Duration=0.2)'>
<META http-equiv='Page-Exit' content='blendTrans(Duration=0.2)'>
<link rel="stylesheet" type="text/css" media="all" href="<?=ROOTDIR?>dist/css/itemmall_game.css" />
<title>Silkroad Online</title>
</head>
<body class="mig " ondragstart="return false" onselectstart="return false">
<div id="wrap" class="error">
	<h1>Error</h1>
	<div id="screen">
		<div class="opener mold"></div>
		<div class="cropped">
			<p class="msg"><?=$errormsg?></p>
		</div>
		<div class="closer mold"></div>
	</div>
</div>
</body>
</html>