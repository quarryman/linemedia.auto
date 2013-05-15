<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

if ($arParams['SET_TITLE_ALLPARTS'] == 'Y') {
    $APPLICATION->SetTitle(GetMessage("LM_AUTO_MAIN_SEO_ALLPARTS_AUTOPARTS") ." {$arResult['brand']['title']} " . GetMessage("LM_AUTO_MAIN_SEO_ALLPARTS_IN_SHOP") . " {$arResult['site_name']}");
}

if ($arParams['SET_DESCRIPTION_ALLPARTS'] == 'Y') {
    $APPLICATION->SetPageProperty("description",GetMessage("LM_AUTO_MAIN_SEO_ALLPARTS_AUTOPARTS") ." {$arResult['brand']['title']} " . GetMessage("LM_AUTO_MAIN_SEO_ALLPARTS_IN_SHOP") . " {$arResult['site_name']}");
}

if ($arParams['SET_KEYWORDS_ALLPARTS'] == 'Y') {
    $APPLICATION->SetPageProperty("keywords", $arResult['keywords']);
}


if (!empty($arResult)) { ?>

    <? if(!empty($arResult['brand'])) {?>

    <p class="lm-seo-allparts-parts-text"><?=GetMessage("LM_AUTO_MAIN_SEO_ALLPARTS_GOODS")?> <span class="lm-seo-parts-brand-title"> <?=$arResult['brand']['title']?></span></p>

    <?
        $rs_parts = new CDBResult;
        $rs_parts->InitFromArray($arResult['brand']['parts']);
        $rs_parts->NavStart($arParams['PARTS_PER_PAGE']);?>



        <div class="lm-seo-allparts-parts">
            <? while($part = $rs_parts->NavNext(false)) {?>
                <p class="part"><a href="<?=$part['url']?>">
                    <span class="title"><?=$part['title'];?></span>
                    <span class="brand-title"> <?=$part['brand_title'];?></span>
                    <span class="article"> <?=$part['article'];?></span>
                </a></p>
            <?}?>
        </div>

        <p class="lm-seo-allparts-nav"><?=$rs_parts->NavPrint(GetMessage("LM_AUTO_MAIN_SEO_ALLPARTS_GOODS"))?></p>

    <?php  } else { ?>
        <div class="lm-seo-allparts-brands">
            <?foreach($arResult['brands'] as $key => $brand) {?>
                <a href="?brand=<?=$brand['brand_title']?>"><?=$brand['brand_title'];?></a>
            <?php } ?>
        </div>
    <?
    }
}

