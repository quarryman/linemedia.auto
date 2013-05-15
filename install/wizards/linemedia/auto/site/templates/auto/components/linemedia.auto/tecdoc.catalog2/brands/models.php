<? include(dirname(__FILE__) . '/header.php'); IncludeTemplateLangFile(__FILE__);?>



<div class="tecdoc_year">        
    <label for="amount"><?=GetMessage('LM_AUTO_YEAR')?>:</label>
    <div id="amount"></div>    
    <div class="slider_placeholder"><div id="slider-range"></div></div>
    
</div>

<div class="tecdoc models list">

<?
	    foreach($arResult['MODELS'] AS $model)
	    {
	    	
	    	if($arResult['EDIT_MODE'] == false AND $model['hidden'] == 'Y')
				continue;
	    	
	        $out .= '<div class="model_card polaroid">';
	        
            /*
	        * Составим галерею
	        */
	        $gallery = '<div class="lm-auto-tecdoc-models-gallery" style="display:none">';
	        foreach($model['images'] AS $image)
	        {
	            $gallery .= '<img src="'.$image['url'].'" width="'.$image['width'].'" height="'.$image['height'].'" alt="'.htmlspecialchars($arResult['brand_title']).' '.htmlspecialchars($model['modelname']).'" />';
	        }
	        $gallery .= '</div>';
	        $out .= $gallery;
	        
	        
	        
	        $out .= '<div class="main_img_place">';
	        
	        /*
	        * Главная картинка
	        *
	        * http://images.api.auto.linemedia.ru/BRANDS/1139/9856/main.jpg?info
	        * http://images.api.auto.linemedia.ru/BRANDS/1139/9856/main.jpg?w=100
	        * http://images.api.auto.linemedia.ru/BRANDS/1139/9856/main.jpg?w=300&h=400
	        */
	        if($model['main_image']['url'] != '')
	        {
		        $out .= '<a href="' . $arParams['SEF_FOLDER'] . $arResult['brand_id'] . '/' . $arResult['additional_url'] . htmlspecialchars($model['modelId']) . '/"><img src="'.$model['main_image']['url'].'?w=128" alt="'.htmlspecialchars($arResult['brand_title']).' '.htmlspecialchars($model['modelname']).'" title="'.htmlspecialchars($arResult['brand_title']).' '.htmlspecialchars($model['modelname']).'" class="lm-auto-model-img grayscale" /></a>';
	        } else {
		        $out .= '<a href="' . $arParams['SEF_FOLDER'] . $arResult['brand_id'] . '/' . $arResult['additional_url'] . htmlspecialchars($model['modelId']) . '/"><img src="'.$this->GetFolder().'/images/404model.png" alt="" class="lm-auto-model-img notfound" /></a>';
	        }
            
            $out .= '</div>';
	        
	        /*
	        * Режим правки
	        */
	        if($arResult['EDIT_MODE'])
	        {
		        $out .= '<input type="checkbox" name="' . $arResult['type'] . '[' . $model['modelId'] . ']" value="Y" ' . ($model['hidden'] != 'Y' ? 'checked':'') . ' />';
		        $out .= '<a href="javascript:;" class="tecdoc-item-edit" data-id="' . $model['modelId'] . '" data-mod-id="' . $model['lm_mod_id'] . '"><img src="' . $this->GetFolder() . '/images/edit.png" alt=""/></a>';
		        
		        if($model['lm_mod_id'])
					$out .=  '<a href="javascript:;" class="tecdoc-item-deletet" data-mod-id="' . $model['lm_mod_id'] . '"><img src="' . $this->GetFolder() . '/images/delete.png" alt=""/></a>';
	        }
	        
	        
	        $out .= '<a class="m_select" href="' . $arParams['SEF_FOLDER'] . $arResult['brand_id'] . '/' . $arResult['additional_url'] . htmlspecialchars($model['modelId']) . '/"> ' . htmlspecialchars($model['modelname']) . '</a>';
	        
	        
	        
	        
	    
	        
	        
	        $out .= '<br />';
	        
	        $out .= '<div class="years">';
	        
	        if($model['yearOfConstrFrom'])
		        $out .= '<span class="year_from">' . substr($model['yearOfConstrFrom'], 0, 4) . '</span>';
		    else
		    	$out .= '<span class="year_from">' . GetMessage('YEAR_UNKNOWN') . '</span>';
		    
		    $out .= ' - ';
		    
		    if($model['yearOfConstrTo'])
		        $out .= '<span class="year_to">' . substr($model['yearOfConstrTo'], 0, 4) . '</span>';
	        else
		    	$out .= '<span class="year_to">' . GetMessage('YEAR_UNKNOWN') . '</span>';
		    $out .= '</div>';
		    
	        $out .= '</div>';
		}
		
		
		
		foreach($arResult['MODEL_GROUPS'] AS $model_key => $model)
	    {
	        $out .= '<div class="model_card polaroid">';	        
	        $out .= '<a class="m_select" href="' . $arParams['SEF_FOLDER'] . htmlspecialchars($arResult['brand_id']) . '/?model_group=' . htmlspecialchars($model_key) . '"> ' . htmlspecialchars($model) . '</a>';
	        $out .= '</div>';
		}
		
	    echo $out;
	    ?>




</div>









<hr />













<script>

function getMinStartYear(){
    var from_years = [];
    $('.years').each(function(){
        var from = $(this).find('.year_from').text();
        from_years.push(from); 
    });
    var min = Math.min.apply(Math, from_years)     
    return min;
}

function getMaxStartYear(){
    var to_years = [];
    $('.years').each(function(){
        var to = $(this).find('.year_to').text();
        to_years.push(to); 
    });
    var max = Math.max.apply(Math, to_years)     
    return max;
}

function hideYearRows(from, to){
    
    $('.models .model_card').each(function(){
        var check_row = $(this);
        var year_from = check_row.find('.year_from').text();
        var year_to = check_row.find('.year_to').text();

        if((year_from >= from) && (year_to <= to)){   
            check_row.show();               
        } else {            
            check_row.hide();   
        }
        
        
    });
}
  
$( "#slider-range" ).slider({
    range: true,
    min: getMinStartYear(),
    max: getMaxStartYear(),
    values: [ getMinStartYear(), getMaxStartYear() ],
    slide: function( event, ui ) {
        hideYearRows(ui.values[ 0 ], ui.values[ 1 ]);
        $( "#amount" ).text( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
    }
});

$( "#amount" ).text( $( "#slider-range" ).slider( "values", 0 ) +
" - " + $( "#slider-range" ).slider( "values", 1 ) );
</script>

<? include(dirname(__FILE__) . '/footer.php'); ?>
