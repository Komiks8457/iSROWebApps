//아이템 검색 체크
function checkSearchForm()
{
	//country = getCookie('mlanguage1')
	switch (getCookie('mlanguage1'))
	{
		case 'us':
			var msg = 'Please enter the words for search.'
			break;
		case 'tr':
			var msg = 'Arama için kelimeleri giriniz.'
			break;
		case 'de':
			var msg = 'Bitte geben Sie das Suchwort ein.'
			break;
		case 'es':
			var msg = 'Por favor, introduzca las palabras para la busqueda.'
			break;
		case 'eg':
			var msg = '.رجاء ادخال الكلمة للبحث'
			break;
		default:
			var msg = 'Please enter the words for search.'
	}
	if (isField($('#searchWord').val()) == 0)
	{
		alert(msg);
		$('#searchWord').val('');
		$('#searchWord').focus();
		return false;
	}
	return true;
}

function isField(keyword) {

	var st_num,key_len;
	st_num = keyword.indexOf(" ");

	while (st_num != -1){
		keyword = keyword.replace(" ", "");
		st_num  = keyword.indexOf(" ");
	}
	key_len=keyword.length;
	return key_len;
}

//장바구니 추가 함수
function addReserved(package_id)
{
	dir = getCookie('dir');
	ext = getCookie('ext');
	//country = getCookie('mlanguage1')
	switch (getCookie('mlanguage1'))
	{
		//msg = 'Reserved에 추가 하시겠습니까?'
		//msg1 = "이미 Reserved에 추가 되어있습니다"
		//msg3 = "레벨 제한 아이템 이야~"
		case 'us':
			var msg = 'Added on the Reserved.\n\nDo you want to check?'
			var msg1 = 'Selected items are already on Reserved'
			var msg2 = 'You can keep maximum 20 items in the Reserved.\n\nIf you want to keep another item more. Please remove any other items.'
			var msg3 = 'It is not sufficient level for purchase items.\n\nPlease check the details by restriction of item description.'
			break;
		case 'de':
			var msg = 'Reserv litesine eklendi.Kontrol etmek istermisiniz?'
			var msg1 = 'Die ausgewählten Items sind bereits Reserviert'
			var msg2 = 'Sie können bis zu 20 Items in der Reservierung halten.\n\nWenn Sie zusätliche Items behalten wollen entfernen Sie bitte andere Items.'
			var msg3 = 'Nicht erhältlich für dieses Level.\n\nBitte prüfen Sie die Details der Einschränkungen für Items'
			break;
		case 'tr':
			var msg = 'Wurde zur überarbeiteten Liste hinzugefügt. \n\nWillst Du es überprüfen?'
			var msg1 = 'Seçilen öğeler zaten ayrılmış'
			var msg2 = 'Rezervde maksium 20 adet öğe tutabilirsiniz.\n\n Eğer diğer öğelerden daha fazla tutmak istiyorsanız,lütfen bazı öğeleri siliniz.'
			var msg3 = 'Mevcut seviyede satın alamaz.\n\nDetaylı bilgi için lütfen Öğe açıklamanın Sınırlama bölümü kontrol edin.'
			break;
		case 'es':
			var msg = 'Agregado en la lista de reservados.\n\n¿Quieres comprobar?'
			var msg1 = 'Los elementos seleccionados ya estan en Reservados'
			var msg2 = 'Se puede guardar un máximo de 20 items en Reservados.\n\n Si desea guardar un item más, por favor, quitar otro item.'
			var msg3 = 'Esto no está disponible para su compra en este nivel.\n\nPor favor, revise las restricciones en la descripción del artículo.'
			break;
		case 'eg':
			var msg = 'أضافة على لائحة المحفوظة.\n\n هل تريد أن تحقق؟'
			var msg1 = 'السلع المختارة تم حجزه'
			var msg2 = 'مكنك الاحتفاظ الحد الأقصى ٥٠ في الأدوات المحفوظة.\n\nإذا كنت تريد أن تبقي أدوات آخر، الرجاء إزالة أية أداة.'
			var msg3 = 'المستوى غير متوفرة للشراء.يرجى التحقق من التفاصيل عن طريق التقييد\n\nمن وصف السلعة.'
			break;
		default:
			var msg = 'Added on the Reserved.\n\nDo you want to check?'
			var msg1 = 'Selected items are already on Reserved'
			var msg2 = 'You can keep maximum 20 items in the Reserved.\n\nIf you want to keep another item more. Please remove any other items.'
			var msg3 = 'It is not sufficient level for purchase items.\n\nPlease check the details by restriction of item description.'
	}
	$.ajax({
		type:"POST",
		url:dir+"itemBuyGame_addReserved"+ext,
		data:"cart="+package_id,
		success:function(res)
		{
			if (res == '0')
			{
				confirmAction(msg,dir+'itemBuyGame_Reserved'+ext+'?st3=3')
				return;
			}
			else if (res == '-1')
			{
				alertmodal(msg1);
				return;
			}
			else if (res == '-2')
			{				
				alertmodal(msg2);
				return;
			}
			else if (res == '-3')
			{				
				alertmodal(msg3);
				return;
			}
			else
			{
				alertmodal('Error');
				return;
			}
		}
	});
}

function getCookie(name){
    var nameOfCookie = name + "=";
    var x = 0;
    while ( x <= document.cookie.length )
    {
            var y = (x+nameOfCookie.length);
            if ( document.cookie.substring( x, y ) == nameOfCookie ) {
                    if ( (endOfCookie=document.cookie.indexOf( ";", y )) == -1 )
                            endOfCookie = document.cookie.length;
                    return unescape( document.cookie.substring( y, endOfCookie ) );
            }
            x = document.cookie.indexOf( " ", x ) + 1;
            if ( x == 0 )
				break;
    }
    return "";
}

(function($) {
	var message = "";
	function clickIE() { if (document.all) { (message); return false; } }
	function clickNS(e) {
		if(document.layers || (document.getElementById && !document.all)) {
			if (e.which == 2 || e.which == 3) { (message); return false; }
		}
	}
	if (document.layers) {
		document.captureEvents(Event.MOUSEDOWN); document.onmousedown = clickNS;
	} else {
		document.onmouseup = clickNS; document.oncontextmenu = clickIE;
	}
	document.oncontextmenu = new Function("return false")
	document.onselectstart = new Function('return false');
	function dMDown(e) { return false; }
	function dOClick() { return true; }
	document.onmousedown = dMDown;
	document.onclick = dOClick;
});

function alertmodal(texts,title)
{
	if (title === undefined) {
		title = 'Confirmation Window';
	}
	if (texts.length > 145) {
		var contents = '<br><br>'+texts;
	} else {
		var contents = '<br><br><br>'+texts;
	}

	$('#alert_title').html(title).val();
	$('#alert_content').html(contents).val();
	$('#alert_modal').show();

	$('#alert_close').click(function(){
		$('#alert_modal').hide();
		return;
	});
}

function confirmAction(texts,url,title)
{
	if (title === undefined) {
		title = 'Confirmation Window';
	}
	if (texts.length > 145) {
		var contents = '<br><br>'+texts;
	} else {
		var contents = '<br><br><br>'+texts;
	}

	$('#alert_title').html(title).val();
	$('#alert_content').html(contents).val();
	$('#alert_modal').show();
	$('#alert_ok').show();

	$('#alert_ok').click(function(){
		$('#alert_modal').hide();
		$('#alert_ok').hide();
		if (url != undefined)
		location.href = url; //location.href = 'itemBuyGame_Reserved.asp?st3=3'
		return;
	});
	
	$('#alert_close').click(function(){
		$('#alert_modal').hide();
		$('#alert_ok').hide();
		return;
	});
}