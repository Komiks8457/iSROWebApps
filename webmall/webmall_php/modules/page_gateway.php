<?php
if (isset($_GET['jid']) && isset($_GET['key']))
{
	if (file_exists($_SERVER['DOCUMENT_ROOT']."\\sessions\\".$_GET['key']))
	{
		$_iframesrc = PAGE_ERROR."?code=C122";
	}
	else
	{
		$fn->gentoken(time()."|".$_GET['jid']."|".$_GET['key']."|".$_GET['loc'], SITE_PASS);
		$_iframesrc = (!isset($_GET['pid'])?PAGE_ITEMMALL:PAGE_ITEMMALL."?pid=".$_GET['pid']."&buythis=true");
	}
}
else
{
	$fn->writelog("[Unkown Parameter]\t(".$fn->getipvisitor().")\t".$_SERVER['QUERY_STRING'],'direct_access.log');
	$_iframesrc = PAGE_ERROR."?code=C107";
}
?>
<!DOCTYPE html>
<head>
<title><?=SITE_NAME?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Keywords" content="<?=SITE_KWRD?>" />
<meta name="Description" content="<?=SITE_DESC?>" />
<style>
html, body, div, span, applet, object, iframe,
h1, h2, h3, h4, h5, h6, p, blockquote, pre,
a, abbr, acronym, address, big, cite, code,
del, dfn, em, font, img, ins, kbd, q, s, samp,
small, strike, strong, sub, sup, tt, var,
dl, dt, dd, ol, ul, li,
fieldset, form, label, legend,
table, caption, tbody, tfoot, thead, tr, th, td {
	margin:0;
	padding:0;
	font-size:100%;
	vertical-align:baseline;
	overflow:hidden;
}
</style>
</head>
<body ondragstart="return false" onselectstart="return false" style="color:#7E755C; width:800px; height:569px;">
<div><iframe style="width:800px; height:569px; border:0;" src="<?=$_iframesrc?>" scrolling="no"></iframe></div>
</body>
</html>