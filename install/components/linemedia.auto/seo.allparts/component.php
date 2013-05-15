<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError(GetMessage("LM_AUTO_MODULE_NOT_INSTALL"));
    return;
}

global $USER, $APPLICATION;


/**
 * ��������� ������� ����������.
 */

$arParams['ADD_SECTION_CHAIN'] = $arParams['ADD_SECTION_CHAIN'] == 'Y' ? 'Y' : "N";
$arParams['SET_TITLE_ALLPARTS'] = $arParams['SET_TITLE_ALLPARTS'] == 'Y' ? 'Y' : "N";
$arParams['SET_DESCRIPTION_ALLPARTS'] = $arParams['SET_DESCRIPTION_ALLPARTS'] == 'Y' ? 'Y' : "N";
$arParams['SET_KEYWORDS_ALLPARTS'] = $arParams['SET_KEYWORDS_ALLPARTS'] == 'Y' ? 'Y' : "N";
$arParams['INIT_JQUERY'] = $arParams['INIT_JQUERY'] == 'Y' ? 'Y' : "N";
$arParams['PARTS_PER_PAGE'] = $arParams['PARTS_PER_PAGE'] ?: "40";



/*
* ��������� �� �������� jquery
*/
if ($arParams['INIT_JQUERY'] == 'Y') {
    CJSCore::Init(array('jquery'));
}


/**
 * �������� �������� ��������
 */
$rsSite = CSite::GetByID(SITE_ID);
$arSite = $rsSite->Fetch();
$arResult['site_name'] = $arSite['SITE_NAME'];


/**
 * ������ ��������: ������������ + �������� �����
 */
$keywords = (GetMessage("LM_AUTO_MAIN_SEO_ALLPARTS_AUTOPARTS")) . ", {$arResult['site_name']}".', ';


/**
 * ���� ������ get ������, �� ���������� ������ ��������� ������, ���� ���, �� ������
 */

if (!empty($_GET['brand'])) {

    /**
     * �������� ��� ������ ��������� ������ �� �������� ����������� �� ���. ���� �������
     */
    $search_parts = new LinemediaAutoSearchSimple();

    $parts = (array) $search_parts->searchLocalDatabaseForPart(
        array(
            'brand_title' => trim(strip_tags($_REQUEST['brand']))
        ),
        true
    );

    /**
     * ��������� url ��� ������ ��������
     */
    foreach($parts as $key => $part) {
        $findurl = LinemediaAutoUrlHelper::getPartUrl(
            array(
                'article' => $part['article'],
                //'brand_title' => $part['brand_title'],
            )
        );
        $parts[$key]['url'] = $findurl ?: '';
    }

    /**
     * �������� ������ + �������� ����� ������
     */
    $arResult['brand']['title'] = trim(strip_tags($_REQUEST['brand'])) ? : '';
    $arResult['brand']['parts'] = $parts ?: array();

    /**
     * ��������� �������� ������ � keywords
     */
    $keywords .= $arResult['brand']['title'] . ', ';


} else {

    /**
     * �������� ��� ��������� � ���. ���� ������ �������� �����������
     */
    $start = microtime();
    $search = new LinemediaAutoBrands();
    $arResult['brands'] = array();

    $brands = (array) $search->getList();
    $end = microtime();
    echo $end - $start;
    $arResult['brands'] = $brands ?: array();


    /**
     * ��������� �������� �� ���� ���������� �������
     * � ������, ����� �� ����� �� ��������� 250
     */

    if (!empty($brands)) {
        foreach($brands as $key => $brand) {
            if (strlen($keywords) < 250) {
                $keywords_old = $keywords;
                $keywords .= $brand['brand_title'] . ', ';
            }
            if (strlen($keywords) > 249) {
                $keywords = $keywords_old;
                break;
            }
        }
    }
}

/**
 * ������� ��������� ������� � keywords
 */
$keywords = substr($keywords, 0, -2);
$arResult['keywords'] = $keywords ?: '';


/*
 *  ������� ������
 */
if ($arParams['ADD_SECTION_CHAIN'] == 'Y') {
    $APPLICATION->AddChainItem(GetMessage("LM_AUTO_MAIN_ALLPARTS_CHAIN"), $APPLICATION->GetCurPage());
}


$this->IncludeComponentTemplate();
