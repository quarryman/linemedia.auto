/*
 * Добавление в корзину.
 */
function add2cart(hash, url)
{
    var qi = $('input[rel="quantity"][data-part-hash="' + hash + '"]');
    var quantity = parseInt(qi.val());

    var quantity = qi.val(), mf = Math.max(1, parseInt(qi.data('step')));
    if (quantity < mf || (quantity % mf > 0)) {
        if (!confirm(langs['LM_AUTO_SEARCH_QUANTITY_SIZE_CONFIRM'] + ' (' + mf + ').')) {
            return false;
        }
    }
    url = url + '&quantity=' + quantity;

    document.location = url;
}


/*
 * Замена раскладки.
 */
function remapping(string)
{
    var rus = 'йцукенгшщзхъфывапролджэячсмитьбюЙЦУКЕНГШЩЗХЪФЫВАПРОЛДЖЭЯЧСМИТЬБЮ';
    var eng = 'qwertyuiop[]asdfghjkl;\'zxcvbnm,.QWERTYUIOP{}ASDFGHJKL:"ZXCVBNM<>';

    rusLetters = rus.split('');
    engLetters = eng.split('');

    for (var i in rusLetters) {
        string = string.replace(new RegExp(rusLetters[i], 'g'), engLetters[i]);
    }
    return string;
}

$(document).ready(function() {

    $('.int').live('keyup', function(e) {
        var val = $(this).val();
        $(this).val(val.replace(/\D/g, ''));
    });

    $('.int').live('blur', function() {
        var val = parseInt($(this).val());
        var multiplication_factor = Math.max(1, parseInt($(this).data('step')));
        if (isNaN(val) || val <= 0) {
            $(this).val(multiplication_factor);
        } else {
            var mod = val % multiplication_factor;
            if (mod >= multiplication_factor / 2) {
                val += (val % multiplication_factor);
            } else {
                val -= (val % multiplication_factor);
            }
            val = Math.max(val, multiplication_factor);
            if (!isNaN(val)) {
                $(this).val(val);
            }
        }
    });

    $('.maxvalue').live('keyup', function() {
        var max = Math.max(1, parseInt($(this).data('max')));
        var val = parseInt($(this).val());
        if (val > max) {
            $(this).val(max);
        }
    });

    $('#lm-auto-main-search-form-id').submit(function() {
        var input = $('#lm-auto-main-search-query-id');
        if (input.data('remapping')) {
            $('#lm-auto-main-search-query-id').val(remapping($('#lm-auto-main-search-query-id').val()));
        }
    });



    /*
     * При нажатии на кнопку "добавить" в колонке "блокнот" формируется post запрос из параметров
     * данного товара в таблице или если задан part_id, то отправляется только он с дальнейшим
     * поиском по локальной базе
     */

	 /*$(".lm-auto-search-parts").tablesorter({
		 headers: { 
		 		3: {sorter: false},
		 		8: {sorter: false}, 
				9: {sorter: false},
				10: {sorter: false} 
			}
	});
*/

});


function AddToNotepad(_this, event) {

    // появление всплывающего сообщения
    var Dialog = new BX.CDialog({
        title: popup_title,
        content: lang_go_to_notepad_body + '<br /> <a target="_blank" href="'+path_notepad+'">'+lang_go_to_notepad+'</a>',

        icon: '',
        resizable: true,
        draggable: true,
        height: '70',
        width: '220',
        buttons: []
    });

    //this.parentWindow.Close();


    var path_to_ajax = '/bitrix/components/linemedia.auto/personal.notepad/ajax.php';
    var tr = $(_this).closest("tr");

    var part_id = parseInt(tr.find(".notepad_part_id").val());
    var api_value = tr.find(".part_api_value").val();
    var title = tr.find(".fn").text();
    var brand_title = tr.find(".brand").text();
    var article = tr.find(".sku").text();
    var quantity = tr.find(".int").val();
    var status = 'add';

    // проверка на существование $part['id'] осуществляется на основе данных о $part['supplier']['PROPS']['api']['VALUE']
    if (!api_value) {
        $.post(path_to_ajax, { notepad: status, part_id: part_id, sessid: sessid})
            .done( function(data,status) {
                if (status == 'success') {
                    Dialog.Show();
                }
            });
    } else {

        $.post(path_to_ajax, { notepad: status, title: title, brand_title: brand_title, article: article, quantity: quantity,
            sessid: sessid})
            .done( function(data,status) {
                if (status == 'success') {
                    Dialog.Show();
                }
            });
    }
    event.preventDefault();
    event.stopPropagation();
}
