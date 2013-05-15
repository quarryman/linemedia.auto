$(document).ready(function() {

    if (ajaxrecalc == 'Y') {
        // Пересчет количества.
        $('.cart-item-quantity input[name^="QUANTITY_"]').keyup(function() {
            var basket_id = parseInt($(this).attr('name').replace('QUANTITY_', ''));
            var quantity  = parseInt($(this).val());
            
            if ($(this).val().length > 0) {
                // Изменяем только при количестве большем нуля.
                if (quantity > 0) {
                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {'BASKET_ID': basket_id, 'QUANTITY': quantity, 'PARAMS': ajaxparams}
                    }).done(function(response, status) {
                        if (status == 'success') {
                            var data  = JSON.parse(response);
                            var price = data['TOTAL_PRICE'];
                            if (price.length > 0) {
                                $('.cart-item-price p b').html(price);
                            }
                        }
                    });
                } else {
                    $(this).val(1);
                }
            }
        });
    }
});
