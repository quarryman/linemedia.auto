<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError(GetMessage("LM_AUTO_MODULE_NOT_INSTALL"));
    return;
}

global $USER, $APPLICATION;

if (!$USER->GetID()) {
    ShowError(GetMessage('LM_AUTO_NOTEPAD_ERROR_AUTH'));
    return;
};



/**
 * ��������� ������� ����������.
 */

$arParams['TITLE'] = $arParams['TITLE'] ?: GetMessage('LM_AUTO_NOTEPAD_SET_TITILE');
$arParams['ADD_SECTION_CHAIN'] = $arParams['ADD_SECTION_CHAIN'] == 'Y' ? 'Y' : "N";
$arParams['SET_TITLE_NOTEPAD'] = $arParams['SET_TITLE_NOTEPAD'] == 'Y' ? 'Y' : "N";
$arParams['INIT_JQUERY'] = $arParams['INIT_JQUERY'] == 'Y' ? 'Y' : "N";



/*
* ��������� �� �������� jquery
*/
if ($arParams['INIT_JQUERY'] == 'Y') {
    CJSCore::Init(array('jquery'));
}

$arResult['DETAILS'] = array();



/**
 * �������� ������ �� �������� ��� ������� ������������
 */

$details = LinemediaAutoNotepad::getByUserId($USER->GetID());
while ($detail = $details->Fetch()) {
    $arResult['DETAILS'][] = $detail;
}



/**
 * �������� + search_url
 */
$search = new LinemediaAutoSearchSimple();

foreach ($arResult['DETAILS'] as $key => $detail) {

    /**
     * Url ��� ������ ������ (� �� ���� �����)
     */
    $findurl = LinemediaAutoUrlHelper::getPartUrl(
        array(
        'article' => $detail['article'],
        'brand_title' => $detail['brand_title'],
        )
    );

    $arResult['DETAILS'][$key]['search_url'] = $findurl;

    /**
     * ���� �-�� ������ ��� 0, �� ������ ��� ������ 1
     */
    $arResult['DETAILS'][$key]['quantity'] =  $detail['quantity']?:1;

    /*
    * ������ ��������� �������� � ��������� ����
    */
    $parts = (array) $search->searchLocalDatabaseForPart(
        array(
        'article' => $detail['article'],
        'brand_title' => $detail['brand_title']
        ),
        true
    );

    foreach ($parts as $part) {
        $part_obj = new LinemediaAutoPart($part['id']);

        /*
         * ��������� ���� ������
         */
        $price = new LinemediaAutoPrice($part_obj);
        $price_calc = $price->calculate();
        $formatted = CurrencyFormat($price_calc, $price->getCurrency());

        $arResult['DETAILS'][$key]['PRICES'][$price_calc] = $formatted;
    }

    foreach ($arResult['DETAILS'] as $part_num => $part) {
        if (count($part['PRICES']) > 0) {
            $arResult['DETAILS'][$part_num]['min_price'] = min(array_keys($part['PRICES']));
            $arResult['DETAILS'][$part_num]['max_price'] = max(array_keys($part['PRICES']));
        }
    }
}

$arResult['path'] = $this->GetPath();


/*
 *  ������� ������  + ���������
 */
if ($arParams['SET_TITLE_NOTEPAD'] == 'Y') {
    $APPLICATION->SetTitle($arParams['TITLE']);
}

if ($arParams['ADD_SECTION_CHAIN'] == 'Y') {
    $APPLICATION->AddChainItem(GetMessage("LM_AUTO_NOTEPAD_SET_TITILE"), $APPLICATION->GetCurPage());
}

$this->IncludeComponentTemplate();
