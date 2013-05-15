$(document).ready(function() {
    $('.applicability-firm').click(function() {
        
        var manuId 			= $(this).data('manuid');
        var article_id 		= $('#article_id').val();
        var article_link_id = $('#article_link_id').val();
        var sessid          = $('#sessid').val();
        var id              = $(this).attr('rel');
        
        $('#lm-auto-applicability').html('<img class="lm-auto-appl-loader" src="/bitrix/components/linemedia.auto/tecdoc.catalog.detail/images/ajax.gif" alt="">');
        
        
        $('.applicability-firm').removeClass('selected');
        $(this).addClass('selected');
        
        $.ajax({
		  url: "/bitrix/components/linemedia.auto/tecdoc.catalog.detail/ajax.php?applicability=Y",
		  data: {'article_id':article_id, 'article_link_id':article_link_id, 'manuId':manuId, 'sessid':sessid},
		  type:'post'
		}).done(function(html) {
		  $('#lm-auto-applicability').html(html);
		});
        
        
        
        
        
        $('.applicability-models').hide();
        $('.applicability-modifications').hide();
        $('#applicability-model-' + id).show();
    });
    
    
    $(".applicability-model").live('click', function(event) {
    	
    	$('.applicability-model').removeClass('selected');
        $(this).addClass('selected');
    	
	  	var id = $(this).attr('rel');
        $('.applicability-modifications').hide();
        $('#applicability-modification-' + id).show();
	});
    
});
