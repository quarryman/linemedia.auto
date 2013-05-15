<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (CModule::IncludeModule('linemedia.auto')) {
    if (!empty($arResult['ITEMS'])) {
        foreach ($arResult['ITEMS'] as &$item) {
            $item['PROPS'] = LinemediaAutoBasket::getProps($item['ID']);
            $item['SEARCH_URL'] = '/auto/search/'.$item['PROPS']['article']['VALUE'].'/?brand_title='.$item['PROPS']['brand_title']['VALUE'];
        }
    }
}
