$(document).ready(function() {
    $('.applicability-firm').click(function() {
        var id = $(this).attr('rel');
        $('.applicability-models').hide();
        $('.applicability-modifications').hide();
        $('#applicability-model-' + id).show();
    });
    
    $('.applicability-model').click(function() {
        var id = $(this).attr('rel');
        $('.applicability-modifications').hide();
        $('#applicability-modification-' + id).show();
    });
});
