<div class="lm-auto-catalog-original index">

	<?if(count($arResult['catalogs']) == 0){
		ShowError(GetMessage('LM_AUTO_ORIG_NO_CATALOGS'));
	}?>

    <?foreach($arResult['catalogs'] AS $catalog){?>
    
    <div class="catalog <?=$catalog['brand_code']?>">
	    <div class="orig-logo">
	    	<a href="<?=$catalog['URL']?>"><img src="<?=$catalog['logo']?>" alt="<?=GetMessage('LM_AUTO_ORIG_CATALOG_PREF')?> <?=$catalog['brand_title']?>" title="<?=GetMessage('LM_AUTO_ORIG_CATALOG_PREF')?> <?=$catalog['brand_title']?>" /></a>
	    </div>
	    <div class="orig-title">
		    <a href="<?=$catalog['URL']?>"><?=$catalog['brand_title']?></a>
	    </div>
    </div>
    
    <?}?>
</div>
<div style="clear:both"></div><!-- bitrix edit mode -->