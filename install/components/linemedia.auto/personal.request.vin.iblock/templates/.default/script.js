$(document).ready(function(){
    $('.lm-auto-vin-extra-header a').click(function() {
        if($('#lm-auto-vin-extra-tbody').css('display') == 'none'){
            $('#lm-auto-vin-show-extra-info').val('Y');
        }else{
            $('#lm-auto-vin-show-extra-info').val('N');
        }
        $('#lm-auto-vin-extra-tbody').toggle('slow');
    });
    
    $('.lm-auto-vin-row-add').click(function() {
        var copy_tr = $('#lm-auto-vin-table-request tbody tr:first').clone();
        $(copy_tr).find('td:first').html('<a href="javascript: void(0);" class="lm-auto-vin-row-del"></a>');
        $(copy_tr).find('input').val('');
        $('#lm-auto-vin-table-request tbody tr:last').before(copy_tr);
    });
    
    $('.lm-auto-vin-row-del').live('click', function() {
        $(this).parents('tr').remove();
    });
});