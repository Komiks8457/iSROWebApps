<?php include_once("includes/connection.php");

if ($req = $_REQUEST['req'] ?? null)
{
	switch($req)
	{
		case stristr($req, (ROOTDIR=="/"?null:ROOTDIR)."itembuygame_getusersilk"):
			include('modules/api_getusersilk.php');
			break;
		case stristr($req, (ROOTDIR=="/"?null:ROOTDIR)."itembuygame_addreserved"):
			include('modules/api_addreserved.php');
			break;
		case stristr($req, (ROOTDIR=="/"?null:ROOTDIR)."itembuygame"):
			include('modules/page_itemmall.php');
			break;
		case stristr($req, (ROOTDIR=="/"?null:ROOTDIR)."gateway"):
			include('modules/page_gateway.php');
			break;
		case stristr($req, (ROOTDIR=="/"?null:ROOTDIR)."error"):
			include('modules/page_error.php');
			break;
		default:
		header("Location: ".PAGE_ERROR."?code=C116");
	}
} 
else header("Location: ".PAGE_ERROR."?code=C777");
?>