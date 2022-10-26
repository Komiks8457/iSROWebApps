<?php include_once("includes/connection.php");

if ($req = $_REQUEST['req'] ?? null)
{
	switch($req)
	{
		case stristr($req, "itembuygame_getusersilk"):
			include('modules/api_getusersilk.php');
			break;
		case stristr($req, "itembuygame_addreserved"):
			include('modules/api_addreserved.php');
			break;
		case stristr($req, "itembuygame"):
			include('modules/page_itemmall.php');
			break;
		case stristr($req, "gateway"):
			include('modules/page_gateway.php');
			break;
		case stristr($req, "error"):
			include('modules/page_error.php');
			break;
		default:
		header("Location: ".PAGE_ERROR."?code=C116");
	}
} 
else
{
	$fn->writelog("[Unkown Parameter]\t({$fn->getipvisitor()})\t{$_SERVER['QUERY_STRING']}",'samtingwong.log');
	echo "Hello World?";
}
?>