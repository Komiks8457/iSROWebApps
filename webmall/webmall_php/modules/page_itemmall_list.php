<?php if ($_st1==0 && $_st2==0 && $fn->newbestcount('new',$_st0,3)[0]) { ?>
						<h3><div><?=$fn->category(0,0,$_loc)[1]?></div></h3>
						<ul class="list">
<?php foreach($fn->newbestcount('new',$_st0,3)[1] as $_item) { ?>
							<li class="<?=($_st0 == 0 ? "silk" : "prem")?>" style="padding-bottom: 5px;">
								<div class="intro">
									<a rel="#item-<?=$_item['package_id']?>" class="pic"><img src="<?=CDN?>dist/images/itemlist_pac/<?=$_item['package_code']?>.jpg" alt="" /></a>
									<span class="name"><?=$_item['package_name']?></span>
									<span class="tag"><img src="<?=CDN?>dist/images/item_img/ingame_img/item_new_icon.png" alt="NEW" /></span>
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
                                    <strong class="val">
										<strong class="current"><?=$_item['silk_price']?>&nbsp;Silk</strong>
									</strong>
								</div>
								<div class="action">
									<span class="setter">
										<span class="btn-ga"><button type="button" onclick="location.href='<?=ROOTDIR?>itemBuyGame<?=EXT?>?st3=6&pid=<?=$_item['package_id']?>&buy=1'">Purchase</button></span>
									</span>
									<span class="pre-sel" >
										<button type="button" onclick="addReserved('<?=$_item['package_id']?>')"><img src="<?=CDN?>dist/images/item_img/ingame_img/btn_presel.gif" alt="You can manage Pre-select'ed items that added on Reserved list." /></button>
									</span>
								</div>
							</li>
<?php } ?>
						</ul>
<?php } if ($_st1==0 && $_st2==0 && $fn->newbestcount('best',$_st0,3)[0]) { ?>
						<h3 style="margin-top:15px;"><div><?=$fn->category(0,0,$_loc)[2]?></div></h3>
						<ul class="list">
<?php foreach($fn->newbestcount('best',$_st0,3)[1] as $_item) { ?>
							<li class="<?=($_st0 == 0 ? "silk" : "prem")?>" style="padding-bottom: 5px;">
								<div class="intro">
									<a rel="#item-<?=$_item['package_id']?>" class="pic"><img src="<?=CDN?>dist/images/itemlist_pac/<?=$_item['package_code']?>.jpg" alt="" /></a>
									<span class="name"><?=$_item['package_name']?></span>
									<span class="tag"><img src="<?=CDN?>dist/images/item_img/ingame_img/item_best_icon.png" alt="BEST" /></span>
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
                                    <strong class="val">
										<strong class="current"><?=$_item['silk_price']?>&nbsp;Silk</strong>
									</strong>
								</div>
								<div class="action">
									<span class="setter">
										<span class="btn-ga"><button type="button" onclick="location.href='<?=ROOTDIR?>itemBuyGame<?=EXT?>?st3=6&pid=<?=$_item['package_id']?>&buy=1'">Purchase</button></span>
									</span>
									<span class="pre-sel" >
										<button type="button" onclick="addReserved('<?=$_item['package_id']?>')"><img src="<?=CDN?>dist/images/item_img/ingame_img/btn_presel.gif" alt="You can manage Pre-select'ed items that added on Reserved list." /></button>
									</span>
								</div>
							</li>
<?php } ?>
						</ul>
<?php } if ($fn->nbetween($_st1,10,1) && $_st2 >= 1) { if ($_mallitems = $fn->getmallitems($_xpn,$_xps,$_st0,$_st1,$_st2)) { ?>
						<h3><div><?=$fn->category($_st1,$_st2,$_loc)[1]?><span style="float:right;margin-top:-13px;font-size:smaller"><?=($fn->i_am_gm($_jid) ? "<a href=\"".ROOTDIR."itemBuyGame".EXT."?st0=".$_st0."&st1=".$_st1."&st2=".$_st2."&st3=7&nameidx=1\">Edit List</a>" : null)?></span></div></h3>
						<ul class="list">
<?php foreach($_mallitems as $_item) { ?>
							<li class="<?=($_st0 == 0 ? "silk" : "prem")?>" style="padding-bottom: 5px;">
								<div class="intro">
									<a rel="#item-<?=$_item['package_id']?>" class="pic"><img src="<?=CDN?>dist/images/itemlist_pac/<?=$_item['package_code']?>.jpg" alt="" /></a>
									<?=(($_st0==3 && $_st1==7) && $_item['month_limit'] >= 1 ? "<div style=\"position:absolute;margin:33px 0 0;color:yellow\">Limited to ".$fn->getboughtcount($_jid, $_item['package_code'])."/{$_item['month_limit']}</div>" : null)?>
									<span class="name"><?=$_item['package_name']?></span>
<?php if ($_item['discount_rate'] > 0) { ?>
									<span class="tag"><img src="<?=CDN?>dist/images/item_img/ingame_img/item_sale_icon.png" alt="SALE" /></span>
<?php } ?>
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
<?php } ?>
								</div>
								<div class="action">
<?php if($_vip[1] >= $_item['vip_level']) { if ($_st1==7) { ?>
									<span class="setter">
										<span class="btn-ga vip <?=($_item['month_limit'] > 0 && $fn->getboughtcount($_jid, $_item['package_code']) >= $_item['month_limit'] ? "disabled" : null)?>"><button type="button" onclick="location.href='<?=ROOTDIR?>itemBuyGame<?=EXT?>?st3=6&pid=<?=$_item['package_id']?>&buy=1'" <?=($_item['month_limit'] > 0 && $fn->getboughtcount($_jid, $_item['package_code']) >= $_item['month_limit'] ? "disabled" : null)?>>Purchase</button></span>
									</span>
<?php } else { ?>
									<span class="setter">
										<span class="btn-ga"><button type="button" onclick="location.href='<?=ROOTDIR?>itemBuyGame<?=EXT?>?st3=6&pid=<?=$_item['package_id']?>&buy=1'">Purchase</button></span>
									</span>
									<span class="pre-sel" >
										<button type="button" onclick="addReserved('<?=$_item['package_id']?>')"><img src="<?=CDN?>dist/images/item_img/ingame_img/btn_presel.gif" alt="You can manage Pre-select'ed items that added on Reserved list." /></button>
									</span>
<?php } } else {?>
									<div class="setter" style="color:#CC1A30;margin-top:3px;height:16px;;">Required <?=VIPTIER[$_item['vip_level']]?> Rank</div>
<?php } ?>
								</div>
							</li>
<?php } } ?>
						</ul>
						<div class="scroll-helper2">&nbsp;</div>
<?php } ?>
