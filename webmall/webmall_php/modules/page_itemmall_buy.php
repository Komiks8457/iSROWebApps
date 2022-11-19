<?php
$_buythis = $_REQUEST['buythis'] ?? false;
$_itempid = $_REQUEST['itempid'] ?? null;
$_itemqty = $_REQUEST['itemqty'] ?? null;
$_rcpient = $_REQUEST['rcpient'] ?? null;
$_rcpient_jid = null;
$_nedsilk = null;
$_confirm = null;
$_success = null;
$_purchse = null;
$_totalrp = null;
$_price_prem = null;
$_price_silk = null;
$_silks_icon = null;
$_limit_itemx = null;
$_limit_reach = null;
$_limit_count = null;
$_limit = null;
$_price = null;

if ($_pid > 0 && ($_buy == 0 || $_buy == 1 || $_buy == 2))
{
	if ($fn->str_contains($_pid, "|"))
	{
		foreach(explode("|", $_pid) as $_index=>$_pidx)
		{
			$_itempid[$_index] = $_pidx;
			$_itemqty[$_index] = $_REQUEST['qty'] ?? 1;
		}
		$_POST['purchase'] = true;
	}
	else
	{
		$_itempid[] = $_pid;
		$_itemqty[] = $_REQUEST['qty'] ?? 1;
		if ($_buy == 0) $_POST['purchase'] = true;
	}
}

if (($_itempid != null && $_itemqty != null && $_buy == 2) || isset($_POST['purchase']))
{
	$_confirm = 1;
	
	foreach($_itempid as $_index=>$_pid)
	{
		if ($_pid == 0) continue;
		$_item = $fn->getpackagedetail($_pid);
		if ($_item == -1) break;
		if ($_item['discount_rate'] > 0)
		{
			$_bcmul = bcmul($_item['silk_price'], $_item['discount_rate']);
			$_price = bcsub($_item['silk_price'], bcdiv($_bcmul, '100'));
			if ($_item['silk_type'] == 0) $_price_silk += $_price * $_itemqty[$_index];
			if ($_item['silk_type'] == 3) $_price_prem += $_price * $_itemqty[$_index];
		}
		else
		{
			if ($_item['silk_type'] == 0) $_price_silk += $_item['silk_price'] * $_itemqty[$_index];
			if ($_item['silk_type'] == 3) $_price_prem += $_item['silk_price'] * $_itemqty[$_index];
		}
		if ($_item['month_limit'] > 0)
		{
			$_limit += $_itemqty[$_index];
			if ($_limit > $_item['month_limit'])
			{
				$_limit_reach = true;
				$_limit_count = $_item['month_limit'];
				$_limit_itemx = $_item['package_name'];
				break;
			}

			//check for user who already bought some of the limited item (purchase attemp)
			if ($fn->getboughtcount($_jid, $_item['package_code'])+$_limit > $_item['month_limit'])
			{
				$_limit_reach = true;
				$_limit_count = $_item['month_limit'];
				$_limit_itemx = $_item['package_name'];
			}
		}
	}

	if (GIFTING && !is_null($_rcpient) && $_rcpient != MESSAGE[$_loc][8][25])
	{
		$_rcpient_jid = $fn->getshardcharnamejid($_rcpient);
		if ($_rcpient_jid < 0) $_success=-65;
	}	
	if ($_cursilk[5]+$_cursilk[6] < $_price_prem+$_price_silk) $_nedsilk = true;
	if ($_cursilk[5] < $_price_prem) $_nedsilk = true;

	
}

if (isset($_POST['confirm']))
{
	$_confirm = 1;

	if (GIFTING && !is_null($_rcpient) && $_rcpient != MESSAGE[$_loc][8][25])
	{
		$_rcpient_jid = $fn->getshardcharnamejid($_rcpient);
		if ($_rcpient_jid < 0) $_success=-65;
	}
	else $_rcpient_jid = 0;

	foreach($_itempid as $_index=>$_pid)
	{
		if ($_pid == 0) continue;
		$_item = $fn->getpackagedetail($_pid);
		if ($_item == -1) break;
		if ($_item['discount_rate'])
		{
			$_bcmul = bcmul($_item['silk_price'], $_item['discount_rate']);
			$_price = bcsub($_item['silk_price'], bcdiv($_bcmul, '100'));
		}
		else $_price = $_item['silk_price'];

		$cp_invoice_id = rand(1111111111,2147483647);
		$pt_invoice_id = "JCASH".date('YmdHis').rand(111111,999999);
		
		if ($_itemqty[$_index] > 1)
		{
			for($i = 1; $i <= $_itemqty[$_index]; $i++)
			{
				//check and stop the loop if the user reached the limit (bulk buy)
				if ($_item['month_limit'] > 0)
				{
					if ($fn->getboughtcount($_jid, $_item['package_code']) > $_item['month_limit'])
					{
						$_limit_reach = true;
						$_limit_count = $_item['month_limit'];
						$_limit_itemx = $_item['package_name'];
						break;
					}
				}
				
				$_purchse = $fn->newitempurchase($_jid, $_item['silk_type'], $_price, $_pid, $pt_invoice_id, $cp_invoice_id, SERVERNAME, $_rcpient_jid);
				if ($_purchse < 0) break;
				$cp_invoice_id = rand(1111111111,2147483647);
				$pt_invoice_id = "JCASH".date('YmdHis').rand(111111,999999);
			}
		}
		else
		{
			//check and stop the loop if the user reached the limit (single buy)
			if ($_item['month_limit'] > 0)
			{
				if ($fn->getboughtcount($_jid, $_item['package_code']) > $_item['month_limit'])
				{
					$_limit_reach = true;
					$_limit_count = $_item['month_limit'];
					$_limit_itemx = $_item['package_name'];
					break;
				}
			}

			$_purchse = $fn->newitempurchase($_jid, $_item['silk_type'], $_price, $_pid, $pt_invoice_id, $cp_invoice_id, SERVERNAME, $_rcpient_jid);
			if ($_purchse < 0) break;
		}
	}
	
	$_success = $_purchse;
}
?>
						<h2><div><?=MESSAGE[$_loc][7]?></div></h2>
						<form action="/itemBuyGame<?=EXT?>?st3=6" method="post" id="buymenu">
							<div class="details">
								<table width="528" border="1">
									<col width="*" /><col width="130" /><col width="72" /><col width="55" /><col width="96" />
									<tr>
										<th class="item"><?=MESSAGE[$_loc][8][0]?></th>
										<th class="userid"><?=MESSAGE[$_loc][8][1]?></th>
										<th class="server"><?=MESSAGE[$_loc][8][2]?></th>
										<th class="qty"><?=MESSAGE[$_loc][8][3]?></th>
										<th class="price"><?=MESSAGE[$_loc][8][4]?></th>
									</tr>
<?php
foreach($_itempid as $_index=>$_pid) {
	if($_pid==0) continue;
	$_item = $fn->getpackagedetail($_pid);
	if ($_item == -1) break;
	if ($_item['discount_rate'])
	{
		$_bcmul = bcmul($_item['silk_price'], $_item['discount_rate']);
		$_price = bcsub($_item['silk_price'], bcdiv($_bcmul, '100'));
		$_totalrp += ($_itemqty[$_index]*$_price);
	}
	else $_totalrp += ($_itemqty[$_index]*$_item['silk_price']);
	$_silks_icon[$_index] = $_item['silk_type'];
?>
									<tr>
										<td class="item <?=($_item['silk_type'] == 0 ? "silk" : "prem")?>">
											<span class="pic"><img src="<?=CDN?>dist/images/itemlist_pac/<?=$_item['package_code']?>.jpg" alt="" /></span>
											<span class="name"><?=$_item['package_name']?></span>
<?php if ($_item['discount_rate'] > 0) { ?>
											<span class="tag"><img src="<?=CDN?>dist/images/item_img/ingame_img/item_sale_icon.png" alt="SALE" /></span>
<?php } ?>
										</td>
										<td class="userid"><?=$_uid?></td>
										<td class="server"><?=SERVERNAME?></td>
<?php if ($_confirm == 1) { ?>
										<td class="qty"><span class="qty"><?=$_itemqty[$_index]?></span></td>
<?php } else { ?>
										<td class="qty"><div class="val"><input type="text" name="itemqty[]" id="qty_<?=$_item['package_id']?>" size="5" maxlength="2" value="1" /></div></td>
<?php } ?>
										<td class="price">
											<span class="type"><img src="<?=CDN?>dist/images/item_img/ingame_img/<?=$_ico[$_item['silk_type']]?>" alt="Silk" /></span>
											<span class="val"><strong class="current"><?=($_item['discount_rate'] > 0 ? $_price : $_item['silk_price'])?>&nbsp;Silk</strong></span>
										</td>
										<input type="hidden" name="itempid[]" value="<?=$_item['package_id']?>" />
										<input type="hidden" name="itemqty[]" value="<?=$_itemqty[$_index]?>" />
									</tr>
<?php } ?>
									<input type="hidden" name="buythis" value="<?=$_buythis?>" />
									<tr class="total">
										<td>
<?php if (GIFTING && !$_buythis) { if ($_confirm == 1) {?>
										<span class="gift"><div class="val"><?=$_rcpient?></div></span>
										<input type="hidden" name="rcpient" maxlength="12" value="<?=$_rcpient?>" />
<?php } else { ?>
										<span class="gift"><input type="text" name="rcpient" maxlength="12" value="<?=MESSAGE[$_loc][8][25]?>" id="recipient" title="<?=MESSAGE[$_loc][8][25]?>" /></span>
<?php } } ?>
										</td>
										<th colspan="2"><strong>Total</strong></th>
										<td colspan="2" class="price">
											<span class="type"><img src="<?=CDN?>dist/images/item_img/ingame_img/<?=((in_array('0', $_silks_icon) && in_array('3', $_silks_icon)) ? $_ico[5] : $_ico[$_silks_icon[0]])?>" alt="Silk" /></span>
<?php if ($_confirm == 1) { ?>
											<span class="setter"><span class="val"><?=number_format($_totalrp)?>&nbsp;Silk</span></span>
<?php } else { ?>
											<span class="setter"><span id="itemAmount" class="val"><?=number_format($_totalrp)?>&nbsp;Silk</span></span>
<?php } ?>
										</td>
									</tr>
								</table>
							</div>
<?php if ($_confirm == 1 && $_nedsilk) { ?>
							<div class="msg msg-nesilk">
								<p class="remainging"><?=MESSAGE[$_loc][8][5]?> : <img src="<?=CDN?>dist/images/item_img/ingame_img/silk_premium.gif" alt="Prem" /> <?=$_cursilk[5]?> Silk <img src="<?=CDN?>dist/images/item_img/ingame_img/silk.gif" alt="Silk" /> <?=$_cursilk[6]?> Silk</p>
								<p><?=MESSAGE[$_loc][8][6]?></p>
							</div>
							<div class="ga">
								<span class="btn-ga btn-ga-cancel"><input type="button" onclick="location.href='<?=ROOTDIR?>itemBuyGame<?=EXT?>'" value="Back" /></span>
							</div>
<?php } else if ($_confirm == 1 && $_limit_reach) { ?>
							<div class="msg msg-nesilk">
								<p class="remainging"><?=sprintf(MESSAGE[$_loc][8][21], $_limit_itemx, $_limit_count)?></p>
								<p><?=MESSAGE[$_loc][8][22]?></p>
							</div>
							<div class="ga">
								<span class="btn-ga btn-ga-cancel"><input type="button" onclick="location.href='<?=ROOTDIR?>itemBuyGame<?=EXT?>'" value="Back" /></span>
							</div>
<?php } else if ($_confirm == 1 && $_success == 1) { ?>
							<div class="receiving">
								<h3 class="tit"><?=MESSAGE[$_loc][8][7]?></h3>
								<ul>
									<li><?=MESSAGE[$_loc][8][8]?></li>
									<li><?=MESSAGE[$_loc][8][9]?></li>
									<li><?=MESSAGE[$_loc][8][10]?></li>
								<ul>
								<div class="steps"><img src="<?=CDN?>dist/images/item_img/ingame_img/content_rpitems_img.gif" alt="" /></div>
							</div>
							<div class="ga">
								<span class="btn-ga btn-ga-cancel"><input type="button" onclick="location.href='<?=ROOTDIR?>itemBuyGame<?=EXT?>'" value="<?=MESSAGE[$_loc][8][19]?>" /></span>
								<span class="btn-ga btn-ga-history"><input type="button" onclick="location.href='<?=ROOTDIR?>itemBuyGame<?=EXT?>?st3=4'" value="<?=MESSAGE[$_loc][8][20]?>" /></span>
							</div>
<?php } else if ($_confirm == 1 && $_success == 0) { ?>
							<p class="msg msg-result">
								<strong><?=MESSAGE[$_loc][8][11]?></strong><br>
								<strong style="color:#fff200;"><?=MESSAGE[$_loc][8][12]?></strong>
							</p>
							<div class="ga">
<?php if ($_buythis) {?>
								<span class="btn-ga btn-ga-cancel"><button type="button" onclick="location.href='<?=ROOTDIR?>itemBuyGame<?=EXT?>'"><?=MESSAGE[$_loc][8][18]?></button></span>
<?php } else { ?>
								<span class="btn-ga btn-ga-cancel"><button type="button" onclick="history.back()"><?=MESSAGE[$_loc][8][18]?></button></span>
<?php } ?>
								<span class="btn-ga btn-ga-confirm"><input type="submit" name="confirm" value="<?=MESSAGE[$_loc][8][17]?>" /></span>
							</div>
<?php } else if ($_confirm == 1 && $_success == -39) { ?>
							<p class="msg msg-result">
								<strong style="color:red;"><?=MESSAGE[$_loc][8][23]?></strong>
							</p>
							<div class="ga">
								<span class="btn-ga btn-ga-cancel"><button type="button" onclick="location.href='<?=ROOTDIR?>itemBuyGame<?=EXT?>'"><?=MESSAGE[$_loc][8][19]?></button></span>
							</div>
<?php } else if ($_confirm == 1 && $_success == -65) { ?>
							<p class="msg msg-result">
								<strong style="color:red;"><?=sprintf(MESSAGE[$_loc][8][24], $_rcpient)?></strong>
							</p>
							<div class="ga">
								<span class="btn-ga btn-ga-cancel"><button type="button" onclick="history.back()"><?=MESSAGE[$_loc][8][19]?></button></span>
							</div>
<?php } else if ($_confirm == 1 && $_success < 0) { ?>
							<p class="msg msg-result">
								<strong><?=MESSAGE[$_loc][8][13]?></strong><br>
								<strong style="color:#fff200;"><?=MESSAGE[$_loc][8][14]?></strong>
							</p>
							<div class="ga">
								<span class="btn-ga btn-ga-cancel"><button type="button" onclick="location.href='<?=ROOTDIR?>itemBuyGame<?=EXT?>'"><?=MESSAGE[$_loc][8][19]?></button></span>
							</div>
<?php } else { ?>
							<p><?=MESSAGE[$_loc][8][15]?></p>
							<div class="ga">
								<span class="btn-ga btn-ga-cancel"><button type="button" onclick="history.back()"><?=MESSAGE[$_loc][8][18]?></button></span>
								<span class="btn-ga btn-ga-purchase"><input type="submit" name="purchase" value="<?=MESSAGE[$_loc][8][16]?>" /></span>
							</div>
<?php } if ($_confirm == 0 && count($_itempid) == 1) { ?>
							<div class="detail-view" id="detailimg">
								<div class="cont"><img onerror="document.getElementById('detailimg').style.display='none'" src="<?=CDN?>dist/images/itemlist_pac/<?=$_item['package_code']?>_detail.jpg" alt="" /></div>
							</div>
<?php } ?>
							<div class="scroll-helper2">&nbsp;</div>
						</form>