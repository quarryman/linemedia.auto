$(document).ready(function() {
	
	
	
	
	
	if ($(".treeview").length > 0){
		$(".treeview").treeview({
		      animated: "false",
		      collapsed: true,
		      unique: false,
		      persist: "cookie"
		});
	}
	
	
	
	
	$('input#quick_search').quicksearch('.treeview li, .lm-auto-catalog-original tbody tr, .lm-auto-catalog-original .group_section');
	
	
    /*
    * Поиск по номеру на картинке
    */
    $('input#quick_search_img').quicksearch('.lm-auto-catalog-original.kia.articles tbody tr .img_num', {
        'show': function () {
            var this_parents_tr = $(this).parents('tr');
            
            this_parents_tr.show();
            
            /*
            * Сколько строк в текущей группе видимы
            * Если ,больше 0 - то покажем заголовок
            */
            var current_group = this_parents_tr.attr('class');
            var visible_count = $('tr.' + current_group).filter(function() {
              return $(this).css('display') !== 'block';
            }).length;
            
            if(visible_count > 0) {
                $('.' + current_group + '_header').show();
            }           
            
        },
        'hide': function () {
            var this_parents_tr = $(this).parents('tr');
            this_parents_tr.hide();
            
            /*
            * Сколько строк в текущей группе видимы
            * Если 0 - то прячем заголовок
            */
            var current_group = this_parents_tr.attr('class');
            var visible_count = $('tr.' + current_group).filter(function() {
              return $(this).css('display') !== 'none';
            }).length;
            
            if(visible_count == 0) {
                $('.' + current_group + '_header').hide();
            }           
        }
    });
    
    
    /*
    * ФИЛЬТР по типам авто
    * Соберем все типы авто
    */
    var car_types = Array();
    $('.lm-auto-catalog-original.models .lm-car-type').each(function(){
        var car_type = $.trim($(this).text());
        if (car_types.indexOf(car_type) == -1){
            car_types.push(car_type);
        }
    });
    
    /*
    * Заполним фильтр
    */
    for (var key in car_types) {
        var val = car_types [key];
        var car_button = '<span class="lm-filter-button">' + val + '</span>';
        $('.lm_car_type_filter').append(car_button);
    } 
    
    $('.lm_car_type_filter span').click(function(){
        $('.lm_car_type_filter span').removeClass('lm-active');
        $(this).addClass('lm-active');
        var active = $(this).text();
        $('.lm-auto-catalog-original.models tr').show();
        
        if(!$(this).hasClass('show-all')){
            $('.lm-auto-catalog-original.models .lm-car-type').each(function(){            
                var car_type = $.trim($(this).text());
                var compare = active;
                if(car_type != compare){                
                    $(this).parents('tr').hide();
                }
            });
        }
    });

});


    
    
