$(document).ready(function() {

    $('#lm-auto-edit-apply-for-all').click(function(){
        var input = $('#tecdoc-items-edit').find("#lm-td-parent-id");
        var saved_val = input.val();
        input.val(input.val()+':*');
        $.ajax({
            type:"POST",
            url:"/bitrix/components/linemedia.auto/tecdoc.catalog2/ajax.php?action=save_all",
            data: $('#tecdoc-items-edit').serialize(),
            success:function (html) {
                    alert(html);
            },
            error:function (data) {
                alert("save error");
            }
        });
        input.val(saved_val);
    });
    // Отправка формы с изменениями и изображением.
    $('#tecdoc-item-save').live('click', function() {
        $('#lm-auto-tecdoc-popup-frm').ajaxForm({
            type: 'post',
            success: function(text, status) {
                if (status == 'success') {
                     document.location = document.location;
                } else {
                    alert(text);
                }
            },
            error:function(xhr, ajaxOptions, thrownError) {
                alert(xhr.responseText);
            }
        });
        $('#lm-auto-tecdoc-popup-frm').trigger('submit');

        return false;
    });
    
    $('input#quick_search').quicksearch('.tecdoc ul li, .models .model_card, .tecdoc h2.letter, .tecdoc tbody tr, #lm-auto-tecdoc-catalog-groups li');

    if ($("#lm-auto-tecdoc-catalog-groups").length > 0){
        $("#lm-auto-tecdoc-catalog-groups").treeview({
              animated: "false",
              collapsed: true,
              unique: false,
              persist: "cookie"
        });
    }

    // Отправка формы
    $('#tecdoc-items-edit').submit(function() {
        $.ajax({
            type: "POST",
            url: "/bitrix/components/linemedia.auto/tecdoc.catalog2/ajax.php?action=save_all",
            data: $(this).serialize(),
            success:function (html) {
                if (html == 'OK') {
                    alert('Ok');
                } else {
                    alert(html);
                }
            },
            error:function (data) {
                alert("save error");
            }
        });

        return false;
    })


    // Изменение элемента.
    $('.tecdoc-item-edit').click(function(event) {
        event.preventDefault();
        
        var type      = $('input[name="type"]').val();
        var source_id = $(this).data('id');
        var parent_id = $('input[name="parent_id"]').val();
        var set_id    = $('input[name="set_id"]').val();
        var mod_id    = $(this).data('mod-id');

        $.ajax({
            type: "POST",
            url: "/bitrix/components/linemedia.auto/tecdoc.catalog2/ajax.php?action=edit_window&type=" + type,
            data: {'source_id': source_id, 'parent_id': parent_id, 'set_id': set_id, 'mod_id': mod_id},
            success: function (html) {
                var params = {
                    title: langs['LM_AUTO_EDIT_MODE'],
                    content: html,
                    icon: 'head-block',
                    resizable: true,
                    draggable: true,
                    content_url:'/bitrix/components/linemedia.auto/tecdoc.catalog2/ajax.php?action=save',
                    buttons: [
                        '<input type="button" value="' + langs['LM_AUTO_SAVE'] + '" id="tecdoc-item-save" class="adm-btn-save">',
                        BX.CDialog.btnCancel
                    ]
                };
                (new BX.CDialog(params)).Show();
            },
            error: function (data) {
                alert("window error");
            }
        });
    });

    // Удаление элемента.
    $('.tecdoc-item-delete').click(function(event) {
        event.preventDefault();

        if (!confirm('Delete?')) {
            return;
        }
        var id = $(this).data('id');
        
        var element = $(this).closest('li,td,div');

        $.ajax({
            type: "POST",
            url: "/bitrix/components/linemedia.auto/tecdoc.catalog2/ajax.php?action=delete",
            data: {'id': id},
            success: function (html) {
                if (html == 'OK') {
                    element && element.remove();
                } else {
                    alert(html);
                }
            },
            error: function (data) {
                alert("window error");
            }
        });
    });

    
    // Добавление нового элемента.
    $('#lm-auto-edit-add').click(function(event){
        var type        = $('input[name="type"]').val();
        var parent_id   = $('input[name="parent_id"]').val();
        var set_id      = $('input[name="set_id"]').val();

        $.ajax({
            type: "POST",
            url: "/bitrix/components/linemedia.auto/tecdoc.catalog2/ajax.php?action=edit_window&type=" + type,
            data: {'parent_id':parent_id, 'set_id':set_id},
            success: function (html) {
                var params = {
                    content : html,
                    icon: 'head-block',
                    resizable: true,
                    draggable: true,
                    content_url: '/bitrix/components/linemedia.auto/tecdoc.catalog2/ajax.php?action=save',
                    buttons: [
                        '',
                        '<input type="button" value="' + langs['LM_AUTO_SAVE'] + '" id="tecdoc-item-save" class="adm-btn-save">',
                        BX.CDialog.btnCancel
                    ]
                };
                (new BX.CDialog(params)).Show()
            },
            error: function (data) {
                alert("window error");
            }
        });
    });
    
    
    // Выделение всех
    $('#lm-auto-select-all').click(function(event) {
        var checked = $(this).attr('checked') == 'checked' ? 'checked' : false;
        $('form#tecdoc-items-edit input[type=checkbox]').attr('checked', checked);
    });
    
    
    // Добавление подгруппы
    $('.tecdoc-item-add-child').click(function(event) {
        event.preventDefault();
        var parent_group_id = $(this).data('id');
        var type            = $('input[name="type"]').val();
        var parent_id       = $('input[name="parent_id"]').val();
        var set_id          = $('input[name="set_id"]').val();

        $.ajax({
            type: "POST",
            url: "/bitrix/components/linemedia.auto/tecdoc.catalog2/ajax.php?action=edit_window&type=" + type,
            data: {'parent_group_id': parent_group_id, 'parent_id':parent_id, 'set_id':set_id},
            success:function (html) {
                var params = {
                    content : html,
                    icon: 'head-block',
                    resizable: true,
                    draggable: true,
                    content_url:'/bitrix/components/linemedia.auto/tecdoc.catalog2/ajax.php?action=save',
                    buttons: [
                        '',
                        BX.CDialog.btnSave, BX.CDialog.btnCancel
                    ]
                };
                (new BX.CDialog(params)).Show()
                $('input[name = "out[parentNodeId]"]').val(parent_group_id);
            },
            error: function (data) {
                alert("window error");
            }
        });
    });

});

