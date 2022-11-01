<?php include_once("includes/connection.php");

$_st0 = null;
$_st1 = null;
$_st2 = null;
$_st3 = null;
$_pid = null;
$_uid = null;
$_jid = null;
$_ico = null;
$_buy = null;
$_xpt = null;
$_xpc = null;
$_xpn = null;
$_xps = null;
$_vip = null;

if ($_token = $fn->readtoken($_COOKIE['webmallkey'], SITE_PASS))
{
	if ($_token == -1)
	{
		header("Location: ".PAGE_ERROR."?code=C112");
		return;
	}
	
	if ($_token == -2)
	{
		header("Location: ".PAGE_ERROR."?code=C113");
		return;
	}
	
	if ($_genkey = $fn->certifykey($_token['jid']))
	{
		if (strtolower(md5($_token['jid'].$_genkey.VERIFYKEY)) == strtolower($_token['key']))
		{
			if(!$fn->sessionlog($_COOKIE['webmallkey'],$_token['key']))
			{
				header("Location: ".PAGE_ERROR."?code=C122");
				return;
			}

			$_st0 =	$_REQUEST['st0'] ?? 3;
			$_st1 =	$_REQUEST['st1'] ?? 0;
			$_st2 =	$_REQUEST['st2'] ?? 0;
			$_st3 =	$_REQUEST['st3'] ?? 1;
			$_pid =	$_REQUEST['pid'] ?? 0;
			$_buy = $_REQUEST['buy'] ?? 0;
			$_xpn =	$_REQUEST['page'] ?? 1;
			$_xch =	$_REQUEST['search'] ?? null;

			$_jid = $_token['jid'];
			$_loc =	$_token['loc'];

			$_vip = $fn->getvipinfo($_jid);

			$_cursilk = [
				0=>$fn->getusersilk($_jid,0),
				1=>$fn->getusersilk($_jid,1),
				3=>$fn->getusersilk($_jid,3),
				4=>$fn->getusersilk($_jid,4)
			];

			$_uid = $fn->tbuserinfo($_jid)['StrUserID'];	
			
			$_ico = [
				0=>"silk.gif",
				3=>"silk_premium.gif",
				5=>"silk_prem.gif"
			];
			
			if ($_pid != 0) $_st3 = 6;
			
			switch ($_st3)
			{
				case 0:
				case 1:
				case 2:
					$_xpt = "mall-list";
					break;
				case 3:
					$_xpt = "buyitem reserved";
					break;
				case 4:
					$_xpt = "buyitem history";
					break;
				case 5:
					$_xpt = "buyitem-guide";
					break;
				case 6:
					$_xpt = "buyitem";
					break;
				case 69:
					$_xpt = "mall-list item-search";
					break;
				default:
					$_xpt = "mall-list";
			}
			
			if ($_st1 > 0 && $_st2 > 0)
			{
				$_xps = 12;
				$_xpc = $fn->getitemscount($_st0,$_st1,$_st2);
				if ($_xpc > $_xps) $_xpc = round($_xpc / $_xps);
				if (!$fn->getmallitems($_xpc,$_xps,$_st0,$_st1,$_st2)) $_xpc-=1;
			}
		}
		else
		{
			header("Location: ".PAGE_ERROR."?code=C104");
			return;
		}
	}
	else
	{
		header("Location: ".PAGE_ERROR."?code=C106");
		return;
	}
}
else
{
	header("Location: ".PAGE_ERROR."?code=C115");
	return;
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Keywords" content="<?=SITE_KWRD?>" />
<meta name="Description" content="<?=SITE_DESC?>" />
<meta http-equiv='Page-Enter' content='blendTrans(Duration=0.2)'>
<meta http-equiv='Page-Exit' content='blendTrans(Duration=0.2)'>
<link rel="stylesheet" type="text/css" media="all" href="dist/css/droidarabickufi.css" />
<link rel="stylesheet" type="text/css" media="all" href="dist/css/itemmall_game.css" />
<title>Silkroad Online</title>
</head>
<body class="mig<?=null//(in_array($_COOKIE['mlanguage1'], ['tr','eg']) == true ? " lang_eg" : null)?>" ondragstart="return false" onselectstart="return false">
<div id="wrap" class="<?=$_xpt?>">
	<div id="header">
		<h1><?=MESSAGE[$_loc][0]?></h1>
		<ul id="gnb">
			<li class="prem<?=($_st3 == 1 ? " current" : null);?>"><a href="/itemBuyGame<?=EXT?>?st0=3&st3=1"><?=MESSAGE[$_loc][1]?></a></li>
			<li class="silk<?=($_st3 == 2 ? " current" : null);?>"><a href="/itemBuyGame<?=EXT?>?st0=0&st3=2"><?=MESSAGE[$_loc][2]?></a></li>
			<li class="res <?=($_st3 == 3 ? " current" : null);?>"><a href="/itemBuyGame<?=EXT?>?st3=3"><?=MESSAGE[$_loc][3]?></a></li>
			<li class="hist<?=($_st3 == 4 ? " current" : null);?>"><a href="/itemBuyGame<?=EXT?>?st3=4"><?=MESSAGE[$_loc][5]?></a></li>
		</ul>
	</div>
	<div id="developer">
		<div id="lead">
			<!-- Silk owned -->
			<div class="pod silkowned">
				<div class="run">
					<h2>Silk Owned<span style="position:absolute;color:yellow;padding-left:5px"><?=VIPTIER[$_vip[1]]?>&nbsp;<?=($_vip[0] > 0 ? "<img src=\"/dist/images/item_img/ingame_img/viplevel_".$_vip[1].".jpg\" style=\"width:16px;height:16px;margin-top:-2px;\" >" : null)?></span></h2>					
					<dl class="status">
						<dt>Premium Silk :</dt>
						<dd><img src="/dist/images/item_img/ingame_img/silk_premium.gif" alt="" /> <span id="silk_prem"><?=number_format($_cursilk[3]+$_cursilk[4])?></span> Silk</dd>
						<dt> -Month Usage : </dt>
						<dd><img src="/dist/images/item_img/ingame_img/silk_premium.gif" alt="" /> <?=number_format($fn->getsilkusage($_jid)[0])?> Silk</dd>
						<dt> -3Month Usage : </dt>
						<dd><img src="/dist/images/item_img/ingame_img/silk_premium.gif" alt="" /> <?=number_format($fn->getsilkusage($_jid)[1])?> Silk</dd>
						<dt>Silk :</dt>
						<dd><img src="/dist/images/item_img/ingame_img/silk.gif" alt="" /> <span id="silk_own"><?=number_format($_cursilk[0]+$_cursilk[1])?></span> Silk</dd>
					</dl>
					<p class="help" style="padding: 3px 12px 3px 12px"><a href="/itemBuyGame<?=EXT?>?st3=5"><?=MESSAGE[$_loc][12]?></a></p>
				</div>
			</div>
			<!-- Hot Shop -->
			<div class="pod hotshop jcarousel-skin-tango">
				<div class="run">
					<h2><?=MESSAGE[$_loc][9]?></h2>
					<div id="mycarousel" dir="ltr">
						<ul>
<?php foreach ($fn->popularitem() as $_item) { ?>
							<li class="prem" dir="ltr">
								<div class="intro">
									<a class="pic"><img src="/dist/images/itemlist_pac/<?=$_item['package_code'];?>.jpg" alt="" /></a>
									<span class="name" ><?=$_item['package_name'];?></span>
								</div>
								<div class="price" >
									<span class="type"><img src="/dist/images/item_img/ingame_img/<?=$_ico[$_item['silk_type']]?>" alt="Silk" /></span>
									<strong class="val"><?=$_item['silk_price'];?> Silk</strong>
								</div>
								<div class="action" >
									<span class="setter">
										<span class="btn-ga"><a href="#" onclick="location.href='itemBuyGame<?=EXT?>?st0=<?=$_st0?>&st3=6&pid=<?=$_item['package_id']?>&buy=1'"><?=MESSAGE[$_loc][11]?></a></span>
									</span>
									<span class="pre-sel" >
										<button type="button" onclick="addReserved('<?=$_item['package_id']?>'); return false;"><img src="/dist/images/item_img/ingame_img/btn_presel.gif" alt="You can manage Pre-select'ed items that added on Reserved list." /></button>
									</span>
								</div>
							</li>
<?php } ?>
						</ul>
					</div>
				</div>
			</div>
			<form action="/itemBuyGame<?=EXT?>" id="searchForm" name="searchForm" method="post" onsubmit="return checkSearchForm()">
				<div class="search">
					<h2>Search</h2>
					<span class="keyword">
						<input type="hidden" name="st3" value="69" />
						<input type="text" id="searchWord" name="search" size="10" value="" />
					</span>
					<span class="btn"><button type="submit"><?=MESSAGE[$_loc][10]?></button></span>
				</div>
			</form>
		</div>
		<div id="fol" class="setter">
			<!-- Content -->
			<div id="content">
<?php if($_st3==1 || $_st3==2) { include('page_itemmall_category.php'); } ?>
				<div id="screen">
					<div class="opener mold"></div>
					<div class="cropped">
<?php
switch ($_st3)
{
	case 0:
	case 1:
	case 2:
		include('page_itemmall_list.php');
		break;
	case 3:
		include('page_itemmall_reserved.php');
		break;
	case 4:
		include('page_itemmall_history.php');
		break;
	case 5:
		include('page_itemmall_guide.php');
		break;
	case 6:
		include('page_itemmall_buy.php');
		break;
	case 69:
		include('page_itemmall_search.php');
		break;
	default:
		include('page_itemmall_list.php');
		break;
}
?>
					</div>
					<div class="closer mold"></div>
				</div>
<?php if ($fn->getitemscount($_st0, $_st1, $_st2) > $_xps) { ?>
				<div class="pagex">
					<img src="/dist/images/item_img/ingame_img/btn_prev.gif" border="0" style="vertical-align: middle;" />&nbsp;<?php for($i = 1; $i <= $_xpc; $i++) { ?>&nbsp;<a href="/itemBuyGame<?=EXT?>?st0=<?=$_st0?>&st1=<?=$_st1?>&st2=<?=$_st2?>&page=<?=$i?>" title="Page <?=$i?>"><?=($_xpn == $i ? "<font color=#EB6F4D><b>".$i."</b></font>" : $i)?></a><?php } ?>&nbsp;&nbsp;<img src="/dist/images/item_img/ingame_img/btn_next.gif" border="0" style="vertical-align: middle;" />
				</div>
<?php } ?>
			</div>
		</div>
	</div>
</div>
/* TO BE ADDED LATER */
<div id="alert_modal" class="modal">
	<div class="alert_window">
		<div class="alert_title" id="alert_title"></div>
		<div class="alert_content">
			<p id="alert_content"></p>
		</div>
		<div class="alert_footer">
			<button id="alert_close" class="alert_close">Confirm</button>
		</div>
	</div>
</div>
<script type="text/javascript" src="dist/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="dist/js/jquery.jcarousel.min.js"></script>
<script type="text/javascript" src="dist/js/jquery.pngFix.js"></script>
<script type="text/javascript" src="dist/js/jquery.sexy-combo.min.js"></script>
<script type="text/javascript" src="dist/js/jquery.cluetip.js"></script>
<script type="text/javascript" src="dist/js/jquery.scroll.js"></script>
<script type="text/javascript" src="dist/js/ingame_shell.js"></script>
<script type="text/javascript" src="dist/js/_common.js>"></script>
<?php if ($_st3 == 3) { include('page_itemmall_reserved_head.php'); }?>
<?php if ($_st3 == 6) { include('page_itemmall_buy_head.php'); }?>
</body>
</html>