<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if (CModule::IncludeModule('compression')) {
    CCompress::DisableCompression();
    CCompress::Disable2048Spaces();
}

$CATALOG_RIGHT = $APPLICATION->GetGroupRight("catalog");
if ($CATALOG_RIGHT == "D") {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/include.php");
IncludeModuleLangFile(__FILE__);


/*
 * Добавим стили установщика и jQuery
 */
$APPLICATION->SetAdditionalCSS("/bitrix/modules/linemedia.auto/interface/style.css");
$APPLICATION->AddHeadScript("http://yandex.st/jquery/1.8.0/jquery.min.js");

if (!CModule::IncludeModule('linemedia.auto')) {
    ShowError('LINEMEDIA AUTO MODULE NOT INSTALLED');
    return;
}

$saleModulePermissions = $APPLICATION->GetGroupRight("linemedia.auto");
if ($sMGRight == "D" || $sMGRight == "R") {
    $APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

$arParams = array();

$arParams['QUERY']       = trim(strval($_REQUEST['q']));
$arParams['BRAND_TITLE'] = strval($_REQUEST['brand_title']);
$arParams['EXTRA']       = (array) $_REQUEST['extra'];

/*
 * Создаём объект поиска
 */
try {
    $search = new LinemediaAutoSearch();
} catch (Exception $e) {
    $arResult['ERRORS'][] = $e->GetMessage();
}

/*
 * Устанавливаем поисковый запрос
 */
$search->setSearchQuery($arParams['QUERY']);

/*
 * Устанавливаем бренд
 */
if ($arParams['BRAND_TITLE'] != '') {
    $search->setSearchCondition('brand_title', $arParams['BRAND_TITLE']);
}

/*
 * Дополнительные параметры поисковых модулей
 */
if ($arParams['EXTRA'] > 0) {
    $search->setSearchCondition('extra', $arParams['EXTRA']);
}


/*
 * Определение типа поиска.
 */
$arParams['TYPE'] = LinemediaAutoSearch::SEARCH_SIMPLE;
if (strpos($arParams['QUERY'], ',') !== false) {
    $arParams['TYPE'] = LinemediaAutoSearch::SEARCH_GROUP;
}
if (isset($_REQUEST['partial']) && $_REQUEST['partial'] == 'Y') {
    $arParams['TYPE'] = LinemediaAutoSearch::SEARCH_PARTIAL;
}

$search->setType($arParams['TYPE']);


/*
 * Выполняем запрос
 */
if (!empty($arParams['QUERY']) || !empty($arParams['BRAND_TITLE'])) {
    try {
        $search->execute();
    } catch (Exception $e) {
        $arResult['ERRORS'] []= $e->GetMessage();
    }
}

/*
 * Ошибки от модулей
 */
$modules_exceptions = $search->getThrownExceptions();
foreach ($modules_exceptions as $exception) {
    $arResult['ERRORS'][] = $exception->GetMessage();
}

$APPLICATION->SetTitle(GetMessage('SEARCH_PART_PAGE'));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");

/*
 * Что пришло в ответ?
 */
$template = null;
if (!empty($arParams['QUERY']) || !empty($arParams['BRAND_TITLE'])) {
    switch ($search->getResultsType()) {
        // Каталоги
        case 'catalogs':
            $arResult['CATALOGS'] = $search->getResultsCatalogs();
            
            foreach ($arResult['CATALOGS'] as $id => &$catalog) {
                $formdata = array(
                        'article' => $arParams['QUERY'], 
                        'brand_title' => $catalog['brand_title'],
                        'extra' => $catalog['extra'],
                    );
                
                $catalog['find'] = '/bitrix/admin/linemedia.auto_products_search.php?q='.$arParams['QUERY'].'&'.http_build_query($formdata);
            }
            
            $template = 'catalogs.php';
            break;
        
        // Товары
        case 'parts':
            $arResult['PARTS'] = $search->getResultsParts();
            
            // Группы пользователей.
            $dbgroups = CGroup::GetList($by="c_sort", $order="asc", array());
            $groups = array();
            while ($group = $dbgroups->Fetch()) {
                $groups[$group['ID']] = $group;
            }
            $arResult['GROUPS'] = $groups;
            
            /*
             * Пробежимся по запчастям и ...
             */
            foreach ($arResult['PARTS'] as $group_id => $parts) {
                foreach ($parts as $i => $part) {
                    
                    if ($part['supplier_id'] != '') {
                        $buy_url .= '&supplier_id=' . $part['supplier_id'];
                    }
                    $arResult['PARTS'][$group_id][$i]['buy_url'] = $buy_url;
                    
                    $opart = new LinemediaAutoPart($part['id'], $part);
                    
                    /*
                     * Посчитаем цену товара
                     */
                    $price = new LinemediaAutoPrice($opart);
                    
                    $arResult['PARTS'][$group_id][$i]['price'] = $price->calculate();
                    
                    $odiscount = new LinemediaAutoCustomDiscount($opart);
                    foreach ($groups as $group => $arGroup) {
                        $odiscount->setGroups(array($group));
                        $discount_price = $odiscount->calculate($arResult['PARTS'][$group_id][$i]['price']);
                        $arResult['PARTS'][$group_id][$i]['prices'][$group] = (float) $discount_price;
                    }
                    
                    /*
                     * Поставщик
                     */
                    $supplier = new LinemediaAutoSupplier($part['supplier_id']);
                    $arResult['PARTS'][$group_id][$i]['supplier'] = $supplier->getArray();
                    
                    /*
                     * Вес
                     */
                    $arResult['PARTS'][$group_id][$i]['weight'] = (float) $arResult['PARTS'][$group_id][$i]['weight'];
                }
            }
            $template = 'parts.php';
            break;
        
        default:
            $arResult['ERRORS'] []= GetMessage('NOT_FOUND');
            break;
    }
}

// Получены ли резальтаты?
$hasResults = (!empty($arResult['PARTS']) || !empty($arResult['CATALOGS']));

?>

<script type="text/javascript">
    function SelEl(id, params)
    {
        var item = $('#price-' + params['hash'] + ' option:selected');
        
        // Выбранная цена.
        var price = item.val();
        
        // Тип цены (группа).
        params['priceType'] = item.attr('rel');
        
        window.opener.FillProductFields(<?= intval($index) ?>, params, price);
        window.close();
    }
</script>

<div class="adm-detail-toolbar">
    <span style="position:absolute;"></span>
    <form name="find_form" method="GET" action="<?= $APPLICATION->GetCurPage() ?>?">
        <input type="hidden" name="lang" value="<?= LANG ?>" />
        <input type="hidden" name="LID" value="<?= $LID ?>" />
        <table>
            <tr>
                <td><?= GetMessage("NEWO_SEARCH") ?>:</td>
                <td><input type="text" name="q" size="30" value="<?= $arParams['QUERY'] ?>"></td>
                <td><input type="submit" value="<?= GetMessage("NEWO_FIND") ?>" /></td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="checkbox" name="partial" value="Y" id="partial-search-is" <?= ($arParams['TYPE'] == LinemediaAutoSearch::SEARCH_PARTIAL) ? ('checked') : ('') ?> />
                    <label for="partial-search-is"><?= GetMessage('NEWO_PARTIAL_SEARCH') ?></label>
                </td>
                <td></td>
            </tr>
        </table>
        <br/>
    </form>
</div>

<? if (!empty($_REQUEST['QUERY']) && !$hasResults) { ?>
    <?= GetMessage("NEWO_EMPTY_RESULT") ?>
<? } else { ?>
    <? if ($_REQUEST['from'] == 'catalogs' && !empty($_SERVER['HTTP_REFERER'])) { ?>
        <a href="<?= $_SERVER['HTTP_REFERER'] ?>" class="adm-detail-toolbar-btn" id="btn_list">
            <span class="adm-detail-toolbar-btn-l"></span><span class="adm-detail-toolbar-btn-text"><?= GetMessage("NEWO_BACK") ?></span><span class="adm-detail-toolbar-btn-r"></span>
        </a>
        <br/><br/><br/>
    <? } ?>
    <? if ($hasResults) { ?>
        <? include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/linemedia.auto/admin/order/'.$template); ?>
    <? } else { ?>
        <? if (isset($arResult)) { ?>
            <?= GetMessage("NEWO_EMPTY_RESULT") ?>
        <? } ?>
    <? } ?>
<? } ?>

<br/><br/>
<input type="button" class="typebutton" value="<?= GetMessage("NEWO_CLOSE") ?>" onClick="window.close();" />
<br/>

<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php"); ?>