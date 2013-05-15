$(document).ready(function() {
    
    

	
	$('input#quick_search').quicksearch('.tecdoc ul li, .models .model_card, .tecdoc h2.letter, .tecdoc tbody tr, #lm-auto-tecdoc-catalog-groups li');
	
	if ($("#lm-auto-tecdoc-catalog-groups").length > 0){
		$("#lm-auto-tecdoc-catalog-groups").treeview({
		      animated: "false",
		      collapsed: true,
		      unique: false,
		      persist: "cookie"
		});
	}
	
	// form
	$('#tecdoc-items-edit').submit(function(){
		$.ajax({
			type:"POST",
			url:"/bitrix/components/linemedia.auto/tecdoc.catalog2/ajax.php?action=save_all",
			data: $(this).serialize(),
			success:function (html) {
				if(html == 'OK')
				{
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
	
	
	
	// link
	$('.tecdoc-item-edit').click(function(event){
		event.preventDefault();
		
		
		var type = $('input[name=type]').val();
		var source_id = $(this).data('id');
		var parent_id = $('input[name=parent_id]').val();
		var set_id 	  = $('input[name=set_id]').val();
		var mod_id 	  = $(this).data('mod-id');
		
		$.ajax({
			type:"POST",
			url:"/bitrix/components/linemedia.auto/tecdoc.catalog2/ajax.php?action=edit_window&type=" + type,
			data: {'source_id':source_id, 'parent_id':parent_id, 'set_id':set_id, 'mod_id':mod_id},
			success:function (html) {
				
				
				var params = {
					content : html,
					icon: 'head-block',
					resizable: true,
					draggable: true,
					content_url:'/bitrix/components/linemedia.auto/tecdoc.catalog2/ajax.php?action=save',
					buttons: [ 
						'', 
						BX.CDialog.btnSave, BX.CDialog.btnCancel//, BX.CDialog.btnClose 
					]
				};
				(new BX.CDialog(params)).Show()
				
				
			},
			error:function (data) {
				alert("window error");
			}
		});
	});
	
	
	
	
	$('.tecdoc-item-delete').click(function(event){
		event.preventDefault();
		
		if(!confirm('Delete?'))
			return;
		
		var mod_id 	  = $(this).data('mod-id');
		
		$.ajax({
			type:"POST",
			url:"/bitrix/components/linemedia.auto/tecdoc.catalog2/ajax.php?action=delete",
			data: {'mod_id':mod_id},
			success:function (html) {
				
				if(html == 'OK')
				{
					$(this).closest('tr').remove();
				} else {
					alert(html);
				}
				
				
			},
			error:function (data) {
				alert("window error");
			}
		});
	});
	
	
	
	/* Добавление нового элемента */
	$('#lm-auto-edit-add').click(function(event){
		var type = $('input[name=type]').val();
		var parent_id = $('input[name=parent_id]').val();
		var set_id 	  = $('input[name=set_id]').val();
		
		$.ajax({
			type:"POST",
			url:"/bitrix/components/linemedia.auto/tecdoc.catalog2/ajax.php?action=edit_window&type=" + type,
			data: {'parent_id':parent_id, 'set_id':set_id},
			success:function (html) {
				
				
				var params = {
					content : html,
					icon: 'head-block',
					resizable: true,
					draggable: true,
					content_url:'/bitrix/components/linemedia.auto/tecdoc.catalog2/ajax.php?action=save',
					buttons: [ 
						'', 
						BX.CDialog.btnSave, BX.CDialog.btnCancel//, BX.CDialog.btnClose 
					]
				};
				(new BX.CDialog(params)).Show()
				
				
			},
			error:function (data) {
				alert("window error");
			}
		});
	});
	
	
	
	
	
	// (de)select all
	$('#lm-auto-select-all').click(function(event){
		var checked = $(this).attr('checked') == 'checked' ? 'checked' : false;
		$('form#tecdoc-items-edit input[type=checkbox]').attr('checked', checked);
		//$(this).attr('checked', (checked) ? )
	});
});