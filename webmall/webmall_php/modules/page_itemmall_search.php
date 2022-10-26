<?php
$_result_prem = $fn->getmallitems(1,12,3,0,0,1,$_xch);
$_result_silk = $fn->getmallitems(1,12,0,0,0,1,$_xch);
?>
						<h2><div><?=MESSAGE[$_loc][13]?></div></h2>
<?php if ($_result_prem) { ?>
						<h3><?=MESSAGE[$_loc][1]?></h3>
						<ul class="list">
<?php foreach($_result_prem as $_item) { ?>
							<li class="prem" style="padding-bottom: 5px;">
								<div class="intro">
									<a rel="#item-<?=$_item['package_id']?>" class="pic"><img src="/dist/images/itemlist_pac/<?=$_item['package_code']?>.jpg" alt="" /></a>
									<span class="name"><?=$_item['package_name']?></span>
									<div id="item-<?=$_item['package_id']?>" class="spec">
										<p class="spec-name"><strong><?=$_item['package_name']?></strong></p>
										<ul>
											<li class="first"><strong>Description</strong><br /><?=$_item[$_loc.'_explain']?></li>
											<li><strong>How to use</strong><br /><?=$_item[$_loc.'_use_method']?></li>
											<li><strong>Restriction</strong><br /><?=$_item[$_loc.'_use_restriction']?></li>
										</ul>
									</div>
								</div>
								<div class="price">
									<span class="type"><img src="/dist/images/item_img/ingame_img/<?=$_ico[3]?>" alt="" /></span>
                                    <strong class="val">
										<strong class="current"><?=$_item['silk_price']?>&nbsp;Silk</strong>
									</strong>
								</div>
								<div class="action">
									<span class="setter">
										<span class="btn-ga"><button type="button" onclick="location.href='/itemBuyGame<?=EXT?>?pid=<?=$_item['package_id']?>&buy=1'">Purchase</button></span>
									</span>
									<span class="pre-sel" >
										<button type="button" onclick="addReserved('<?=$_item['package_id']?>')"><img src="/dist/images/item_img/ingame_img/btn_presel.gif" alt="You can manage Pre-select'ed items that added on Reserved list." /></button>
									</span>
								</div>
							</li>
<?php } ?>
						</ul>
<?php } if ($_result_silk) { ?>
						<h3><?=MESSAGE[$_loc][2]?></h3>
						<ul class="list">
<?php foreach($fn->getmallitems(1,12,0,0,0,1,$_xch) as $_item) { ?>
							<li class="silk" style="padding-bottom: 5px;">
								<div class="intro">
									<a rel="#item-<?=$_item['package_id']?>" class="pic"><img src="/dist/images/itemlist_pac/<?=$_item['package_code']?>.jpg" alt="" /></a>
									<span class="name"><?=$_item['package_name']?></span>
									<div id="item-<?=$_item['package_id']?>" class="spec">
										<p class="spec-name"><strong><?=$_item['package_name']?></strong></p>
										<ul>
											<li class="first"><strong>Description</strong><br /><?=$_item[$_loc.'_explain']?></li>
											<li><strong>How to use</strong><br /><?=$_item[$_loc.'_use_method']?></li>
											<li><strong>Restriction</strong><br /><?=$_item[$_loc.'_use_restriction']?></li>
										</ul>
									</div>
								</div>
								<div class="price">
									<span class="type"><img src="/dist/images/item_img/ingame_img/<?=$_ico[0]?>" alt="" /></span>
                                    <strong class="val">
										<strong class="current"><?=$_item['silk_price']?>&nbsp;Silk</strong>
									</strong>
								</div>
								<div class="action">
									<span class="setter">
										<span class="btn-ga"><button type="button" onclick="location.href='/itemBuyGame<?=EXT?>?pid=<?=$_item['package_id']?>&buy=1'">Purchase</button></span>
									</span>
									<span class="pre-sel" >
										<button type="button" onclick="addReserved('<?=$_item['package_id']?>')"><img src="/dist/images/item_img/ingame_img/btn_presel.gif" alt="You can manage Pre-select'ed items that added on Reserved list." /></button>
									</span>
								</div>
							</li>
<?php } ?>
						</ul>
<?php } ?>