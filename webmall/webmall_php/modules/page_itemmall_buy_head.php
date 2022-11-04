<script type="text/javascript">
function ItemAmountChange(qty)
{
	var fnItemPrice = 0;
<?php foreach($_itempid as $_index=>$_pid) {
	$_item = $fn->getpackagedetail($_pid);
	if ($_item['discount_rate'] > 0)
	{
		$_bcmul = bcmul($_item['silk_price'],$_item['discount_rate']);
		$_price = bcsub($_item['silk_price'],bcdiv($_bcmul,'100'));
	}
	else $_price = $_item['silk_price'];
?>
	fnItemPrice += parseInt($("#qty_<?=$_item['package_id']?>").val() * <?=$_price?>);
<?php } ?>
	document.getElementById("itemAmount").innerHTML = fnItemPrice.toLocaleString(window.document.documentElement.lang).slice(0,-3)+' Silk';
}
$('#recipient').focus(function() {
	if ($(this).val()==$(this).attr("title")) { $(this).val(""); }
}).blur(function() {
	if ($(this).val()=="") { $(this).val($(this).attr("title")); }
});
</script>