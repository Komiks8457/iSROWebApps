<?php
$_totalrp = null;
$_silks_icon = null;
if (isset($_REQUEST['delete'])) $fn->delreserved($_jid, $_REQUEST['delete']);
?>
						<h2><div><?=MESSAGE[$_loc][3]?></div></h2>
						<form action="itemBuyGame<?=EXT?>?st3=6&buy=2" method="post" id="gsroForm">
							<div class="details">
								<table width="528" border="1">
									<col width="24" /><col width="*" /><col width="72" /><col width="55" /><col width="96" /><col width="98" />
									<tr>
										<th class="chk"><input type="checkbox" id="allcheck_p" name="allcheck_p" title="All"></th>
										<th class="item"><?=MESSAGE[$_loc][4][0]?></th>
										<th class="server"><?=MESSAGE[$_loc][4][1]?></th>
										<th class="qty"><?=MESSAGE[$_loc][4][2]?></th>
										<th class="price"><?=MESSAGE[$_loc][4][3]?></th>
										<th class="action"><?=MESSAGE[$_loc][4][4]?></th>
									</tr>
<?php
if ($_reserved = $fn->getreserved($_jid))
{
	foreach($_reserved as $_index=>$_item) { 
		if ($_item['discount_rate'])
		{
			$_bcmul = bcmul($_item['silk_price'], $_item['discount_rate']);
			$_price = bcsub($_item['silk_price'], bcdiv($_bcmul, '100'));
			$_totalrp += $_price;
		}
		else $_totalrp += $_item['silk_price'];
		$_silks_icon[$_index] = $_item['silk_type'];
?>
									<tr>
										<td class="chk"><input type="checkbox" name="itempid[]" value="<?=$_item['package_id']?>"></td>
										<td class="item <?=($_item['silk_type'] == 0 ? "silk" : "prem")?>">
											<span class="pic"><img src="<?=CDN?>dist/images/itemlist_pac/<?=$_item['package_code']?>.jpg" alt="" /></span>
											<span class="name"><?=$_item['package_name']?></span>
<?php if ($_item['discount_rate'] > 0) { ?>
											<span class="tag"><img src="<?=CDN?>dist/images/item_img/ingame_img/item_sale_icon.png" alt="SALE" /></span>
<?php } ?>
										</td>
										<td class="server"><?=SERVERNAME?></td>
										<td class="qty">
											<div class="val"><input type="text" name="itemqty[]" id="qty_<?=$_item['package_id']?>" size="5" maxlength="2" class="itemqty_<?=$_item['package_id']?>" value="1" /></div>
										</td>
										<td class="price">
											<span class="type"><img src="<?=CDN?>dist/images/item_img/ingame_img/<?=$_ico[$_item['silk_type']]?>" alt="Premium" /></span>
											<span class="val"><strong class="current"><?=($_item['discount_rate'] > 0 ? $_price : $_item['silk_price'])?>&nbsp;Silk</strong></span>
										</td>
										<td class="action">
											<span class="btn-ga"><a href="#" onclick="location.href='<?=ROOTDIR?>itemBuyGame<?=EXT?>?st3=6&pid=<?=$_item['package_id']?>&qty='+$('.itemqty_<?=$_item['package_id']?>').val()+'&rcpient='+$('#recipient').val()+'&buy=2';"><?=MESSAGE[$_loc][4][4]?></a></span>
											<span class="btn-ga btn-ga-cancel"><a href="<?=ROOTDIR?>itemBuyGame<?=EXT?>?st3=3&delete=<?=$_item['idx']?>"><?=MESSAGE[$_loc][4][5]?></a></span>
										</td>
									</tr>
<?php } ?>
									<tr class="total">
										<td colspan=3>
<?php if (GIFTING) { ?>											
											<span class="gift"><input type="text" name="rcpient" maxlength="12" value="<?=MESSAGE[$_loc][8][25]?>" id="recipient" title="<?=MESSAGE[$_loc][8][25]?>" /></span>
<?php } ?>
										</td>
										<th colspan="1"><strong>Total</strong></th>
										<td colspan="2" class="price">
										<span class="type"><img src="<?=CDN?>dist/images/item_img/ingame_img/<?=((in_array('0', $_silks_icon) && in_array('3', $_silks_icon)) ? $_ico[5] : $_ico[$_silks_icon[0]])?>" alt="Silk" /></span>
											<span class="setter"><span id="itemAmount" class="val"><?=number_format($_totalrp)?>&nbsp;Silk</span></span>
										</td>
									</tr>
<?php } else { ?>
									<tr>
										<td colspan="6">
											<?=MESSAGE[$_loc][4][9]?>
										</td>
									</tr>
<?php } ?>
								</table>
							</div>
<?php if ($_reserved) {?>
							<div class="ga">
								<span class="btn-ga"><a href="<?=ROOTDIR?>itemBuyGame<?=EXT?>?st3=3&delete=all"><?=MESSAGE[$_loc][4][6]?></a></span>
								<span class="btn-ga"><a href="#" onclick="checkForm('#gsroForm'); return false;"><?=MESSAGE[$_loc][4][7]?></a></span>
								<span class="btn-ga"><a href="#" onclick="Purchase_all('#gsroForm'); return false;"><?=MESSAGE[$_loc][4][8]?></a></span>
							</div>
<?php } ?>
						</form>
						<div class="scroll-helper2">&nbsp;</div>