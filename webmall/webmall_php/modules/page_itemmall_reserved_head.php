<script type="text/javascript">
function Purchase_all(formid)
{
	//전체구매
	//alert(if($(":checkbox").is(':checked')) $(":checkbox").attr('id'));
//	var cboxCnt = $(":checkbox").length
//	var checkCnt = 0

//	for (i=0; i <= cboxCnt -1 ; i++)
//	{
//		$(":checkbox")[i].checked = true
//		checkCnt = checkCnt + 1
//	}
	$(formid+' .details td.chk :checkbox').attr('checked',true);

//	if (checkCnt == 0)
	if ($(formid+' .details td.chk :checkbox').size() == 0)
	{
		alert('Please select the item to purchase');
		return false;
	}
	else
	{
		$(formid).submit();
	}
}

function checkForm(formid)
{
	var cboxCnt = $(formid+" :checkbox").length
	var iboxCnt = $(formid+" :text").length
	var checkCnt = 0

	for (i=0; i <= cboxCnt -1 ; i++)
	{
		if ($(formid+" :checkbox")[i].checked == true)
		{
			checkCnt = checkCnt + 1
		}
	}

	if (checkCnt == 0)
	{
		alert('Please select the item to purchase');
		return false;
	}

//	for (i=0; i <= iboxCnt -1 ; i++)
//	{
//		var cID = $(":text")[i].id
//		if (cID.indexOf('qty') >= 0)
//		{
//			if (isField($(":text")[i].value) == 0 || $(":text")[i].value == 0)
//			{
//				$(":text")[i].value = 1
//			}
//		}
//	}
	//return false;
	$(formid).submit();
}

//숫자외의 문자 입력 금지
$(function(){
	$('.val input').css('ime-mode','disabled');
	$('.val').keypress(function(event){
	  //alert(event.which);
	  if (event.which && (event.which  > 47 && event.which  < 58 || event.which == 8)) {
		//alert('숫자임!');
	  } else {
		//alert('숫자아님!');
		event.preventDefault();
	  }
	});

	// Check all of each category
	$('.details th.chk :checkbox').click(function(){
		if($(this).is(':checked')){
			$(this).parents('.details').find('td.chk :checkbox').attr('checked',true);
		} else {
			$(this).parents('.details').find('td.chk :checkbox').attr('checked',false);
		}
	});
});

function ItemAmountChange(qty)
{
	//수량 변경시 호출하는 함수에서 마지막에 이 함수를 호출하지만
	//Reserved 가아닌 단품구매에서만 사용하는 함수이므로 함수를 2개로 분리하지않고
	//빈 함수를 만들어서 스크립트 오류가 생기지 않도록 함
	//fckoff joymax
	var fnItemPrice = 0;
<?php if ($_reserved = $fn->getreserved($_jid)) {
	foreach($_reserved as $_index=>$_item) {
		if ($_item['discount_rate'] > 0)
		{
			$_bcmul = bcmul($_item['silk_price'],$_item['discount_rate']);
			$_price = bcsub($_item['silk_price'],bcdiv($_bcmul,'100'));
		}
		else $_price = $_item['silk_price']; 
?>
	fnItemPrice += parseInt($("#qty_<?=$_item['package_id']?>").val() * <?=$_price?>);
<?php } } ?>
	document.getElementById("itemAmount").innerHTML = fnItemPrice.toLocaleString(window.document.documentElement.lang).slice(0,-3)+' Silk';
}
$('#recipient').focus(function() {
	if ($(this).val()==$(this).attr("title")) { $(this).val(""); }
}).blur(function() {
	if ($(this).val()=="") { $(this).val($(this).attr("title")); }
});
</script>