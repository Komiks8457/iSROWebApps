<script type="text/javascript">
function ItemAmountChange(qty)
{
	var fnItemPrice = 0;
<?php foreach($_itempid as $_index=>$_pid) { $_item = $fn->getpackagedetail($_pid); ?>
	fnItemPrice += parseInt($("#qty_<?=$_item['package_id']?>").val() * <?=$_item['silk_price']?>);
<?php } ?>
	document.getElementById("itemAmount").innerHTML = fnItemPrice.toLocaleString(window.document.documentElement.lang).slice(0,-3)+' Silk';
}
</script>