<?php
$_entries = 25;
$_entries_count = null;
$_page  = $_REQUEST['p'] ?? 1;
$_month = $_REQUEST['nMonth'] ?? 0;
$_year  = $_REQUEST['nYear'] ?? date("2022");
$_gethistoryspent = $fn->gethistoryspent($_jid);
$_gethistory = $fn->gethistory($_jid, $_year, $_month, $_entries, $_page);
if ($_gethistory[0] > $_entries) $_entries_count = round($_gethistory[0] / $_entries);
?>
						<h2><div><?=MESSAGE[$_loc][5]?></div></h2>
						<div class="lead">
							<h3><em><?=date("F")?> <?=date("Y")?></em> <?=MESSAGE[$_loc][5]?></h3>
							<form class="settings" action="itemBuyGame<?=EXT?>?st3=4" name="sroform" method="post">
								<label for="sel-history-year"><?=MESSAGE[$_loc][6][0]?></label>
								<select name="nYear" id="sel-history-year" onchange="sroform.submit();">
<?php for($_y = 2018; $_y <= date("Y"); $_y++) { ?>
									<option value='<?=$_y?>'<?=($_y == $_year ? " selected" : null)?>><?=$_y?></option>
<?php } ?>
								</select>
								<label for="sel-history-month"><?=MESSAGE[$_loc][6][1]?></label>
								<select name="nMonth" id="sel-history-month" onchange="sroform.submit();">
<?php for($_m = 1; $_m <= 12; $_m++) {?>
									<option value='<?=$_m?>'<?=($_m == $_month ? " selected" : null)?>><?=sprintf('%02d', $_m)?></option>
<?php } ?>
									<option value='0'<?=($_month == 0 ? " selected" : null)?>>All</option>
								</select>
							</form>
						</div>
						<div class="details details-remaining">
							<table width="528" border="3">
								<caption><?=MESSAGE[$_loc][6][2]?></caption>
								<col width="*" /><col width="110" /><col width="117" />
								<tr>
									<th colspan="2"><?=MESSAGE[$_loc][6][3]?></th>
									<td class="price">
										<span class="type"><img src="<?=CDN?>dist/images/item_img/ingame_img/silk_premium.gif" alt="Prem" /></span>
										<span class="val"><?=number_format($_gethistoryspent[1])?> Silk</span>
									</td>
								</tr>
								<tr>
									<th colspan="2"><?=MESSAGE[$_loc][6][4]?></th>
									<td class="price">
										<span class="type"><img src="<?=CDN?>dist/images/item_img/ingame_img/silk.gif" alt="Silk" /></span>
										<span class="val"><?=number_format($_gethistoryspent[0])?> Silk</span>
									</td>
								</tr>
								<tr class="total">
									<th colspan="1"><strong><?=MESSAGE[$_loc][6][5]?></strong></th>
									<td colspan="2" class="price">
										<span class="type"><img src="<?=CDN?>dist/images/item_img/ingame_img/<?=$_ico[5]?>" alt="Silk" /></span></span>
										<span class="setter"><span class="val"><?=number_format($_gethistoryspent[0]+$_gethistoryspent[1])?> Silk&nbsp;</span></span>
									</td>
								</tr>
							</table>
						</div>
						<p class="note-serves-details"><?=MESSAGE[$_loc][6][6]?></p>
						<div class="details details-buyitem">
							<table width="528" border="1">
								<caption><?=MESSAGE[$_loc][6][7]?></caption>
								<col width="80" /><col width="*" /><col width="60" /><col width="60" /><col width="110" />
								<tr>
									<th><?=MESSAGE[$_loc][6][8]?></th>
									<th><?=MESSAGE[$_loc][6][9]?></th>
									<th><?=MESSAGE[$_loc][6][10]?></th>
									<th><?=MESSAGE[$_loc][6][11]?></th>
									<th><?=MESSAGE[$_loc][6][12]?></th>
								</tr>
<?php if ($_gethistory[0] > 0) { foreach($_gethistory[1] as $_item) {?>
								<tr>
									<td><?=date("Y-m-d\r\nh:i A", strtotime($_item['reg_date']))?></td>
									<td><?=$_item['item_name_package']?></td>
									<td><?=SERVERNAME?></td>
									<td><?=($_item['message']=='$game_gift'?"Gift":($_item['silk_own']==0?$_item['silk_own_premium']:$_item['silk_own']))?></td>
									<td style="border-right-width:1px;"><?=($_item['character_id']==null?"Not Taken":$fn->getcharinfo($_item['character_id'])['CharName16'])?></td>
								</tr>
<?php }} else { ?>
								<tr>
									<td colspan="5"><?=MESSAGE[$_loc][6][13]?></td>
								</tr>
<?php } ?>
								<tr>
<?php if ($_gethistory[0] > $_entries) {?>
									<td colspan="5" style="border-right-width:1px;">
										<div >
											<img src='<?=CDN?>dist/images/item_img/ingame_img/btn_prev.gif' border='0' style='vertical-align: middle;' />&nbsp;<?php for($i = 1; $i <= $_entries_count; $i++) { ?>&nbsp;<a href="/itemBuyGame_history<?=EXT?>?&st3=4&p=<?=$i?>" title="Page <?=$i?>"><?=($_page == $i ? "<font color=#EB6F4D><b>".$i."</b></font>" : $i)?></a><?php } ?>&nbsp;&nbsp;<img src='<?=CDN?>dist/images/item_img/ingame_img/btn_next.gif' border='0' style='vertical-align: middle;' />
										</div>
									</td>
<?php } ?>
								</tr>
							</table>
						</div>