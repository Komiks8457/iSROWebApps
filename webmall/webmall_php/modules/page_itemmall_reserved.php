<?php if (isset($_REQUEST['delete'])) $fn->delreserved($_jid, $_REQUEST['delete']);?>
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
<?php if ($_reserved = $fn->getreserved($_jid)){foreach($_reserved as $_item) { ?>
									<tr>
										<td class="chk"><input type="checkbox" name="itempid[]" value="<?=$_item['package_id']?>"></td>
										<td class="item <?=($_item['silk_type'] == 0 ? "silk" : "prem")?>">
											<span class="pic"><img src="/dist/images/itemlist_pac/<?=$_item['package_code']?>.jpg" alt="" /></span>
											<span class="name"><?=$_item['package_name']?></span>
										</td>
										<td class="server"><?=SERVERNAME?></td>
										<td class="qty">
											<div class="val"><input type="text" name="itemqty[]" id="qty" size="5" maxlength="2" class="itemqty_<?=$_item['package_id']?>" value="1" /></div>
										</td>
										<td class="price">
											<span class="type"><img src="/dist/images/item_img/ingame_img/<?=$_ico[$_item['silk_type']]?>" alt="Premium" /></span>
											<span class="val"><strong class="current"><?=$_item['silk_price']?> Silk</strong></span>
										</td>
										<td class="action">
											<span class="btn-ga"><a href="#" onclick="location.href='/itemBuyGame<?=EXT?>?st3=6&pid=<?=$_item['package_id']?>&qty='+$('.itemqty_<?=$_item['package_id']?>').val()+'&buy=2';"><?=MESSAGE[$_loc][4][4]?></a></span>
											<span class="btn-ga btn-ga-cancel"><a href="/itemBuyGame<?=EXT?>?st3=3&delete=<?=$_item['idx']?>"><?=MESSAGE[$_loc][4][5]?></a></span>
										</td>
									</tr>
<?php }} else { ?>
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
								<span class="btn-ga"><a href="itemBuyGame<?=EXT?>?st3=3&delete=all"><?=MESSAGE[$_loc][4][6]?></a></span>
								<span class="btn-ga"><a href="#" onclick="checkForm('#gsroForm'); return false;"><?=MESSAGE[$_loc][4][7]?></a></span>
								<span class="btn-ga"><a href="#" onclick="Purchase_all('#gsroForm'); return false;"><?=MESSAGE[$_loc][4][8]?></a></span>
							</div>
<?php } ?>
						</form>
						<div class="scroll-helper2">&nbsp;</div>