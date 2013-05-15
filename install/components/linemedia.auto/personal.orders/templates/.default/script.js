$(document).ready(function() {
    $('.lm-auto-order-toggle').click(function() {
        var order = $(this).attr('rel');
        
        if ($(this).hasClass('lm-auto-order-toggle-expand')) {
            $('#lm-auto-orders-table-id tr[rel="order-' + order + '"]').hide('fast');
            $(this).removeClass('lm-auto-order-toggle-expand');
            $(this).addClass('lm-auto-order-toggle-turn');
        } else {
            $('#lm-auto-orders-table-id tr[rel="order-' + order + '"]').show('fast');
            $(this).removeClass('lm-auto-order-toggle-turn');
            $(this).addClass('lm-auto-order-toggle-expand');
        }
    });
});
