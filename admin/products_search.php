<?
$bAjaxMode = (isset($_GET['AJAX']));

if ($bAjaxMode == true) {
    define("NO_KEEP_STATISTIC", true); // �� �������� ����� �� ��������� AJAX
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LINEMEDIA AUTO MODULE NOT INSTALLED');
    return;
}

/*
 * ������� ����� ����������� � jQuery
 */
$APPLICATION->SetAdditionalCSS("/bitrix/modules/linemedia.auto/interface/style.css");
$APPLICATION->AddHeadScript("http://yandex.st/jquery/1.8.0/jquery.min.js");

IncludeModuleLangFile(__FILE__);

$arParams = array();

$arParams['QUERY']      = trim(strval($_REQUEST['q']));
$arParams['BRAND_TITLE']   = strval($_REQUEST['brand_title']);
$arParams['EXTRA']      = (array) $_REQUEST['extra'];

/*
 * ������ ������ ������
 */
try {
    $search = new LinemediaAutoSearch();
} catch (Exception $e) {
    $arResult['ERRORS'][] = $e->GetMessage();
}

/*
 * ������������� ��������� ������
 */
$search->setSearchQuery($arParams['QUERY']);

/*
 * ������������� �����
 */
if ($arParams['BRAND_TITLE'] != '') {
    $search->setSearchCondition('brand_title', $arParams['BRAND_TITLE']);
}

/*
 * �������������� ��������� ��������� �������
 */
if ($arParams['EXTRA'] > 0) {
    $search->setSearchCondition('extra', $arParams['EXTRA']);
}


/*
 * ��������� ������
 */

if (!empty($arParams['QUERY']) || !empty($arParams['BRAND_TITLE'])) {
    try {
        $search->execute();
    } catch (Exception $e) {
        $arResult['ERRORS'] []= $e->GetMessage();
    }
}

/*
 * ������ �� �������
 */
$modules_exceptions = $search->getThrownExceptions();
foreach ($modules_exceptions as $exception) {
    $arResult['ERRORS'][] = $exception->GetMessage();
}

$APPLICATION->SetTitle(GetMessage('SEARCH_PART_PAGE'));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");

/*
 * ��� ������ � �����?
 */
$template = null;
if (!empty($arParams['QUERY']) || !empty($arParams['BRAND_TITLE'])) {
    switch ($search->getResultsType()) {
        // ��������
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
            
            $template = 'products_search_catalogs.php';
            break;
        
        // ������
        case 'parts':
            $arResult['PARTS'] = $search->getResultsParts();
            
            // ������ �������������.
            $dbgroups = CGroup::GetList($by="c_sort", $order="asc", array());
            $groups = array();
            while ($group = $dbgroups->Fetch()) {
                $groups[$group['ID']] = $group;
            }
            $arResult['GROUPS'] = $groups;
            
            /*
             * ���������� �� ��������� � ...
             */
            foreach ($arResult['PARTS'] as $group_id => $parts) {
                foreach ($parts as $i => $part) {
                    
                    if ($part['supplier_id'] != '') {
                        $buy_url .= '&supplier_id=' . $part['supplier_id'];
                    }
                    $arResult['PARTS'][$group_id][$i]['buy_url'] = $buy_url;
                    
                    $opart = new LinemediaAutoPart($part['id'], $part);
                    
                    /*
                     * ��������� ���� ������
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
                     * ���������
                     */
                    $supplier = new LinemediaAutoSupplier($part['supplier_id']);
                    $arResult['PARTS'][$group_id][$i]['supplier'] = $supplier->getArray();
                    
                    /*
                     * ���
                     */
                    $arResult['PARTS'][$group_id][$i]['weight'] = (float) $arResult['PARTS'][$group_id][$i]['weight'];
                }
            }
            $template = 'products_search_parts.php';
            break;
        
        default:
            $arResult['ERRORS'] []= GetMessage('NOT_FOUND');
            break;
    }
}

?>

<form method="get">
    <p>
        <input type="text" name="q" value="<?= htmlspecialchars($arParams['QUERY']) ?>" />
        <input type="submit" value="<?= GetMessage('FIND') ?>" />
    </p>
</form>

<? if (!empty($arResult['ERRORS'])) { ?>
    <? foreach ($arResult['ERRORS'] as $error) { ?>
        <? ShowError($error) ?>
    <? } ?>
<? } ?>

<?  // ����������� ������� ������ �����������.
    if (!is_null($template)) {
        include ($template);
    } 
?>

