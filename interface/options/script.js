$(document).ready(function() {
    $('#LM_AUTO_MAIN_USE_BITRIX_DB').change(function() {
        if ($(this).is(':checked')) {
            $('input.db-settings').attr('disabled', 'disabled');
        } else {
            $('input.db-settings').removeAttr('disabled');
        }
    });
});
