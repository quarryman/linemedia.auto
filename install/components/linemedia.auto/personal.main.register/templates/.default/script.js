$(document).ready(function() {
    $('.lm-auto-person-select').click(function() {
        var id = $(this).val();
        
        $('.lm-auto-person-select-container').hide();
        $('.lm-auto-person-select-container[rel="' + id + '"]').show();
    });
    
    $('.lm-auto-person-select[checked="checked"]').trigger('click');
});
