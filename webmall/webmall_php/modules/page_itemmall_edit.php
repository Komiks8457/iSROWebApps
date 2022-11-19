<?php
echo "coming soon";
return;
$_nameidx = $_REQUEST['nameidx'] ?? 1;
$_package_items = $fn->mallpackageitems($_st0, $_st1, $_st2);
?>
                        <h3><div><?=$fn->category($_st1,$_st2,$_loc)[$_nameidx]?><span style="float:right;margin-top:-13px;font-size:smaller"><?=($fn->i_am_gm($_jid) ? "<a href=\"".ROOTDIR."itemBuyGame".EXT."?st0=".$_st0."&st1=".$_st1."&st2=".$_st2."&st3=7&nameidx=".$_nameidx."\">Edit List</a>" : null)?></span></div></h3>
                        <ul class="list">
<?php foreach($_package_items as $_item) { ?>
							<li class="<?=($_st0 == 0 ? "silk" : "prem")?>" style="padding-bottom: 5px;">
								<div class="intro">
									<a rel="#item-<?=$_item['package_id']?>" class="pic"><img src="<?=CDN?>dist/images/itemlist_pac/<?=$_item['package_code']?>.jpg" alt="" /></a>
									<span class="name"><?=$_item['package_name']?></span>
									<div id="item-<?=$_item['package_id']?>" class="spec">
										<p class="spec-name"><strong><?=$_item['package_name']?></strong></p>
										<ul>
											<li class="first"><strong>Description</strong><br /><?=$_item[$_loc.'_explain']?></li>
											<li><strong>How to use</strong><br /><?=$_item[$_loc.'_use_method']?></li>
											<li><strong>Restriction</strong><br /><?=$_item[$_loc.'_use_restriction']?></li>
											<li><strong>Quantity</strong><br />x<?=($_item['item_quantity']==0?"1":$_item['item_quantity'])?></li>
										</ul>
									</div>
								</div>
								<div class="price">
									<span class="type"><img src="<?=CDN?>dist/images/item_img/ingame_img/<?=$_ico[$_item['silk_type']]?>" alt="" /></span>
<?php if ($_item['discount_rate'] > 0) { ?>
									<strong class="val">
										<strong class="normal"><?=$_item['silk_price']?>&nbsp;Silk</strong>
									</strong>
									<strong class="val">
										<strong class="current"><?=bcsub($_item['silk_price'], bcdiv(bcmul($_item['silk_price'], $_item['discount_rate']), '100'))?> Silk (<?=$_item['discount_rate']?>% off)</strong>
									</strong>
<?php } else { ?>
									<strong class="val">
										<strong class="current"><?=$_item['silk_price']?>&nbsp;Silk</strong>
									</strong>
                                    <strong class="val">
										<strong class="current">No Discount</strong>
									</strong>
<?php } ?>
								</div>
								<div class="action">
                                <span class="btn-ga btn-ga-purchase"><input type="submit" name="purchase" value="<?=MESSAGE[$_loc][8][16]?>" /></span>
								</div>
							</li>
<?php } ?>
                        </ul>