<?php include_once("includes/connection.php");
if(!$fn->matchreferer($_SERVER['HTTP_REFERER']))
{
	header("Location: ".PAGE_ERROR."?code=C121");
	return;
}
if ($_token = $fn->readtoken($_COOKIE['webmallkey'], SITE_PASS))
{
	if ($_token == -1) die("-4");
	if ($_token == -2) die("-5");
	
	if ($_genkey = $fn->certifykey($_token['jid']))
	{
		if (strtolower(md5($_token['jid'].$_genkey.VERIFYKEY)) == strtolower($_token['key']))
		{
			echo $fn->getusersilk($_token['jid'], $_POST['type']);
		}
		else { die("-6"); }
	}
	else { die("-7"); }
}
else { die("-8"); }
?>