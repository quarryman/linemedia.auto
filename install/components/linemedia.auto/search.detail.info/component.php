<?php

/*
 * Компонент выводит детальную информацию о запчасти текдока.
 */
 
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule('linemedia.auto')) {
    ShowError(GetMessage('LM_AUTO_MAIN_MODULE_NOT_INSTALL'));
    return;
}

$arParams['AJAX'] = ($arParams['AJAX'] == 'Y');


//if (empty($arParams['SEARCH_URL'])) {
//    $arParams['SEARCH_URL'] = '/auto/search/';
//}

//if (empty($arParams['ADD_SECTIONS_CHAIN'])) {
//    $arParams['ADD_SECTIONS_CHAIN'] = 'Y';
//}

if (empty($arParams['SHOW_ORIGINAL_ITEMS'])) {
    $arParams['SHOW_ORIGINAL_ITEMS'] = 'Y';
}

if (empty($arParams['SHOW_APPLICABILITY'])) {
    $arParams['SHOW_APPLICABILITY'] = 'Y';
}

if (empty($arParams['SHOW_SEARCH_FORM'])) {
    $arParams['SHOW_SEARCH_FORM'] = 'Y';
}

if (empty($arParams['CACHED'])) {
    $arParams['CACHED'] = 'Y';
}

if (empty($arParams['CACHE_TIME'])) {
    $arParams['CACHE_TIME'] = 3600;
}
$arParams['CACHE_TIME'] = (int) $arParams['CACHE_TIME'];


$arParams['BRAND']      = trim((string) $arParams['BRAND']);
$arParams['ARTICLE']    = trim((string) $arParams['ARTICLE']);
$arParams['ARTICLE_ID'] = trim((string) $arParams['ARTICLE_ID']);


/* 
 * Для ajax-вызова не делаем преждевременный запрос в api.
 */
if ($arParams['AJAX']) {
    $this->IncludeComponentTemplate();
    return;
}


if (empty($arParams['BRAND']) && empty($arParams['ARTICLE']) && empty($arParams['ARTICLE_ID'])) {
    // CHTTP::SetStatus('404 Not Found');
    ShowError(GetMessage('LM_AUTO_MAIN_DETAIL_NOT_FOUND'));
    return;
}


$arResult = array();

/*
 * Подключение к API.
 */
$api = new LinemediaAutoApiDriver();

/*
 * Информация о детали.

$cache      = new CPHPCache();
$cache_time = $arParams['CACHE_TIME'];
$cache_id   = 'LM_AUTO_MAIN_SDI_'.md5($arParams['BRAND'].$arParams['ARTICLE'].$arParams['ARTICLE_ID']);
if ($arParams['CACHED'] == 'Y' && $cache->InitCache($cache_time, $cache_id)) {
       $cached = $cache->GetVars();
       $arResult['DATA']        = $cached['DATA'];
       $arResult['DETAIL']      = $cached['DETAIL'];
       $arResult['ARTICLE_ID']  = $cached['ARTICLE_ID'];
} else { */
    try {
        // Если не передан article_id - попытаемся получить его.
        if (empty($arParams['ARTICLE_ID'])) {
            // Получение ID бренда.
            $arParams['BRAND_ID'] = LinemediaAutoBrand::getTecdocBrandID($arParams['BRAND']);
            
            // DEBUG: Не нашелся бренд.
            if ($arParams['BRAND_ID'] <= 0) {
                LinemediaAutoDebug::add('Tecdoc not found brand '.$arParams['BRAND'], false, LM_AUTO_DEBUG_ERROR);
            }
            
            
            // Получение ID артикула.
            $response = $api->query(
                'getArticleId',
                $data = array('art' => $arParams['ARTICLE'], 'brand_id' => $arParams['BRAND_ID'])
            );
            
            $arParams['ARTICLE_ID'] = $response['data'];
            
            // DEBUG: Не нашлась деталь.
            if ($arParams['ARTICLE_ID'] <= 0) {
                LinemediaAutoDebug::add('Tecdoc not found article ['.$arParams['BRAND'].'] '.$arParams['ARTICLE'], false, LM_AUTO_DEBUG_ERROR);
            }
        }
        
        // Запрос данных из API.
        $args = array(
            array(
                'article_id' => $arParams['ARTICLE_ID'],
                'article_link_id' => null,
            )
        );
        
        $response = $api->query('getArticleDetailsMultiple', $args);
        $arResult['DATA'] = $arResult['DETAIL'] = $response['data'][0];
        /*
        if (empty($arResult['DATA'])) {
            $cache->AbortDataCache();
        } else {
            $cached = array('ARTICLE_ID' => $arParams['ARTICLE_ID'], 'DATA' => $arResult['DATA'], 'DETAIL' => $arResult['DETAIL']);
            $cache->StartDataCache($cache_time, $cache_id);
            $cache->EndDataCache($cached);
        }*/
    } catch (Exception $e) {
        $arResult['ERRORS'] []= $e->getMessage();
    }
//}


/*
 * Деталь не найдена или ошибка.
 */
if (!empty($arResult['ERRORS']) || empty($arResult['DATA'])) {
    // CHTTP::SetStatus("404 Not Found");
    
    // DEBUG: Не нашелся бренд.
    LinemediaAutoDebug::add('Tecdoc not found detail ['.$arParams['BRAND'].'] '.$arParams['ARTICLE'], false, LM_AUTO_DEBUG_ERROR);
    
    ShowError(GetMessage('LM_AUTO_MAIN_DETAIL_NOT_FOUND'));
    return;
}

/*
 * Изображение.
 */
$arResult['IMAGE'] = $arResult['DETAIL']['articleDocuments']['array'][0]['image'];

/*
 * Применимость.
 */
$arResult['APPLICABILITY'] = $arResult['DETAIL']['applicability'];


/*
 * Установить название в заголовок.
 */
if ($arParams['SET_TITLE'] == 'Y') {
    $APPLICATION->SetTitle($arResult['DATA']['directArticle']['articleName'].' '.$arResult['DATA']['directArticle']['articleNo']);
}


/*
 * Добавлять в цепочку навигации.
 */
if ($arParams['ADD_SECTIONS_CHAIN'] == 'Y') {
    $APPLICATION->AddChainItem($arResult['DATA']['directArticle']['articleName'].' '.$arResult['DATA']['directArticle']['articleNo'], null);
}


$this->IncludeComponentTemplate();
