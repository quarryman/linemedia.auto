<? include(dirname(__FILE__) . '/header.php'); IncludeTemplateLangFile(__FILE__);?>
<? define('FIRST_CAR_YEAR', '1986') ?>

<script type="text/javascript">
    var langs = {'LM_AUTO_EDIT_MODE': '<?= GetMessage('LM_AUTO_EDIT_MODE') ?>', 'LM_AUTO_SAVE': '<?= GetMessage('LM_AUTO_SAVE') ?>'};
</script>

<? $APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery.form.js'); ?>
<div class="tecdoc_year">
	<div class="left_side">      
        <label for="amount"><?= GetMessage('LM_AUTO_YEAR') ?>:</label>
        <div id="amount"></div>
    </div>      
    <div class="slider_placeholder">
        <div id="slider-range"></div>
    </div>
</div>

<div class="tecdoc models list">
    
    <?
        foreach ($arResult['MODELS'] as $model) {
        	
        	if ($arResult['EDIT_MODE'] == false && $model['hidden'] == 'Y') {
    			continue;
            }
            $out .= '<div class="model_card polaroid">';
            
            /*
             * Составим галерею
             */
            $gallery = '<div class="lm-auto-tecdoc-models-gallery" style="display:none;">';
            foreach ($model['images'] as $image) {
                $gallery .= '<img src="'.$image['url'].'" width="'.$image['width'].'" height="'.$image['height'].'" alt="'.htmlspecialcharsEx($arResult['brand_title']).' '.htmlspecialcharsEx($model['modelname']).'" />';
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
            $model['main_image']['url'] = ($model['image']) ? ($model['image']) : ($model['main_image']['url']);
            
            if ($model['main_image']['url'] != '') {
    	        $out .= '<a href="' . $arParams['SEF_FOLDER'] . $arResult['brand_id'] . '/' . $arResult['additional_url'] . htmlspecialcharsEx($model['modelId']) . '/"><img src="'.$model['main_image']['url'].'?w=190" alt="'.htmlspecialcharsEx($arResult['brand_title']).' '.htmlspecialcharsEx($model['modelname']).'" title="'.htmlspecialcharsEx($arResult['brand_title']).' '.htmlspecialcharsEx($model['modelname']).'" class="lm-auto-model-img grayscale" /></a>';
            } else {
    	        $out .= '<a href="' . $arParams['SEF_FOLDER'] . $arResult['brand_id'] . '/' . $arResult['additional_url'] . htmlspecialcharsEx($model['modelId']) . '/"><img src="'.$this->GetFolder().'/images/404model.png" alt="" class="lm-auto-model-img notfound" /></a>';
            }
            
            $out .= '</div>';
            
            /*
             * Режим правки
             */
            if ($arResult['EDIT_MODE']) {
                if ($model['lm_mod_id']) {
                    // Пользовательский элемент.
                    $out .= '<input type="checkbox" name="' . $arResult['type'] . '[' . $model['source_id'] . ']" value="Y" ' . ($model['hidden'] != 'Y' ? 'checked':'') . ' />';
                    $out .= '<a href="javascript:;" class="tecdoc-item-edit" data-id="' . $model['modelId'] . '" data-mod-id="' . $model['id'] . '"><img src="' . $this->GetFolder() . '/images/edit.png" alt="" /></a>';
                    $out .= '<a href="javascript:;" class="tecdoc-item-delete" data-id="' . $model['id'] . '"><img src="' . $this->GetFolder() . '/images/delete.png" alt=""/></a>';
                } else {
                    // Элемент TecDoc.
                    $out .= '<input type="checkbox" name="' . $arResult['type'] . '[' . $model['modelId'] . ']" value="Y" ' . ($model['hidden'] != 'Y' ? 'checked':'') . ' />';
                    $out .= '<a href="javascript:;" class="tecdoc-item-edit" data-id="' . $model['modelId'] . '" data-mod-id="' . $model['lm_mod_id'] . '"><img src="' . $this->GetFolder() . '/images/edit.png" alt="" /></a>';
                }
            }
            
            $out .= '<a class="m_select" href="' . $arParams['SEF_FOLDER'] . $arResult['brand_id'] . '/' . $arResult['additional_url'] . htmlspecialcharsEx($model['modelId']) . '/"> ' . htmlspecialcharsEx($model['modelname']) . '</a>';
            $out .= '<br />';
            
            $out .= '<div class="years">';
            
            if ($model['yearOfConstrFrom']) {
    	        $out .= '<span class="year_from" data-year="'.substr($model['yearOfConstrFrom'], 0, 4).'">' . substr($model['yearOfConstrFrom'], 0, 4) . ' &mdash; </span>';
    	    } else {
    	    	$out .= '<span class="year_from" data-year="'.FIRST_CAR_YEAR.'">' . GetMessage('YEAR_UNKNOWN') . ' &mdash; </span>';
            }
    	    
    	    if ($model['yearOfConstrTo']) {
    	        $out .= '<span class="year_to" data-year="'.substr($model['yearOfConstrTo'], 0, 4).'">' . substr($model['yearOfConstrTo'], 0, 4) . '</span>';
            } else {
    	    	$out .= '<span class="year_to" data-year="'.date('Y').'">' . GetMessage('YEAR_UNKNOWN') . '</span>';
            }
            
    	    $out .= '</div>';
    	    $out .= '</div>';
    	}
    	
    	foreach ($arResult['MODEL_GROUPS'] as $model_key => $model) {
            $out .= '<div class="model_card polaroid">';	        
            $out .= '<a class="m_select" href="' . $arParams['SEF_FOLDER'] . htmlspecialcharsEx($arResult['brand_id']) . '/?model_group=' . htmlspecialcharsEx($model_key) . '"> ' . htmlspecialcharsEx($model) . '</a>';
            $out .= '</div>';
    	}
    	
        echo $out;
    ?>
</div>


<script type="text/javascript">
    function getMinStartYear()
    {
        var from_years = [];
        $('.years').each(function() {
            var from = $(this).find('.year_from').data('year');
            from_years.push(from); 
        });
        var min = Math.min.apply(Math, from_years);
        return min;
    }
    
    function getMaxStartYear()
    {
        var to_years = [];
        $('.years').each(function() {
            var to = $(this).find('.year_from').data('year');
            to_years.push(to); 
        });
        var max = Math.max.apply(Math, to_years);
        return max;
    }

    function getMaxEndYear()
    {
        var to_years = [];
        $('.years').each(function() {
            var to = $(this).find('.year_to').data('year');
            to_years.push(to);
        });
        var max = Math.max.apply(Math, to_years);
        return max;
    }

    function hideYearRows(from, to)
    {
        $('.models .model_card').each(function() {
            var check_row = $(this);
            var year_from = check_row.find('.year_from').data('year');
            var year_to = check_row.find('.year_to').data('year');
    
            if ((year_from >= from) && (year_to <= to)) {
                check_row.show();              
            } else {            
                check_row.hide();   
            }
        });
    }

    function getMinDefaultYear(){
        hideYearRows(2000, getMaxEndYear());
        if( $('.model_card.polaroid').filter(':visible').length < 1 ) {
            hideYearRows(getMinStartYear(), getMaxEndYear());
            return getMinStartYear();
        }
        return 2000;
    }

    $("#slider-range").slider({
        range: true,
        min: getMinStartYear(),
        max: getMaxEndYear(),
        values: [getMinDefaultYear(), getMaxEndYear()],
        slide: function(event, ui) {
            hideYearRows(ui.values[0], ui.values[1]);
            $("#amount").html(ui.values[0] + " &mdash; " + ui.values[1]);
        }
    });
    $(function(){
        hideYearRows(getMinDefaultYear(), getMaxEndYear());
    });
    $("#amount").html($("#slider-range").slider("values", 0) + " &mdash; " + $("#slider-range" ).slider("values", 1));

</script>

<? include(dirname(__FILE__) . '/footer.php'); ?>
