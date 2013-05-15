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
    $('#lm-auto-main-search-form-id').submit(function() {
        var input = $('#lm-auto-main-search-query-id');
        if (input.data('remapping')) {
            $('#lm-auto-main-search-query-id').val(remapping($('#lm-auto-main-search-query-id').val()));
        }
    });
});