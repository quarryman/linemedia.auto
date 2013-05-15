<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

__IncludeLang(dirname(__FILE__) . '/lang/' . LANGUAGE_ID . '/' . basename(__FILE__));

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError(GetMessage('LM_AUTO_NOTEPAD_ERROR_AUTO_MODULE'));
    return;
}

if (!check_bitrix_sessid('sessid')) {
    die(GetMessage('LM_AUTO_NOTEPAD_ERROR_SSID'));
}

global $USER;

if (!$user_id = $USER->GetID()) {
    die(GetMessage('LM_AUTO_NOTEPAD_ERROR_AUTH'));
};

$errors = array();

/**
* Обработка входящих пост запросов на создание и изменение деталей в блокноте.
*/

//если пришел part_id

/**
 * Add + part_id
 */
if ( $_POST['notepad'] == 'add' && !empty($_POST['part_id']) ) {

    $search = new LinemediaAutoSearchSimple();
    $part = (array) $search->searchLocalDatabaseForPart(
        array(
            'id' => (int)$_POST['part_id']
        )
    );

    $arFields = array(
        'user_id'     => (int)$_REQUEST['user_id']?:$user_id,
        'title'        => $part['title'],
        'article'      => $part['article'],
        'brand_title' => $part['brand_title']
    );

    $notepad_object = new LinemediaAutoNotepad();
    if (!$notepad_object->Add($arFields)) {
        die(GetMessage('LM_AUTO_NOTEPAD_ERROR_ADD'));
    };
    unset($notepad_object);
    unset($search);
    exit();
}

/**
 * Add
 */
if ( $_POST['notepad'] == 'add' && empty($_POST['part_id'])) {
    $arFields = array(
        'user_id'     => (int)$_REQUEST['user_id']?:$user_id,
        'title'        => htmlspecialchars(trim($_REQUEST['title'])),
        'article'      => htmlspecialchars(trim($_REQUEST['article'])),
        'brand_title' => htmlspecialchars(trim($_REQUEST['brand_title'])),
        'auto'        => htmlspecialchars(trim($_REQUEST['auto'])),
        'notes'       => trim(htmlspecialchars($_REQUEST['notes'])),
        'quantity'    => (int)$_REQUEST['quantity']
    );

    $notepad_object = new LinemediaAutoNotepad();

    if (!$new_id = $notepad_object->Add($arFields)) {
        die(GetMessage('LM_AUTO_NOTEPAD_ERROR_ADD'));
    }
}

/**
 * Update
 */
if ( $_POST['notepad'] == 'update' ) {
    $arFields = array(
        'title'        => htmlspecialchars(trim($_REQUEST['title'])),
        'brand_title' => htmlspecialchars(trim($_REQUEST['brand_title'])),
        'article'      => htmlspecialchars(trim($_REQUEST['article'])),
        'auto'        => htmlspecialchars(trim($_REQUEST['auto'])),
        'notes'       => trim(htmlspecialchars($_REQUEST['notes'])),
        'quantity'    => (int)$_REQUEST['quantity']
    );

    $notepad_object = new LinemediaAutoNotepad();
    if (!$notepad_object->update($_REQUEST['id'], $arFields)) {
        die(GetMessage('LM_AUTO_NOTEPAD_ERROR_UPDATE'));
    };
    unset($notepad_object);
}

/**
 * Delete
 */
if ( $_POST['notepad'] == 'delete' ) {
    if (!LinemediaAutoNotepad::deleteById((int)$_REQUEST['id'])) {
        die(GetMessage('LM_AUTO_NOTEPAD_ERROR_DELETE'));
    };
    echo "Deleted";
    exit();
}

/**
 * Get price
 */
if ( empty($errors) && $_POST['notepad'] == 'add' || $_POST['notepad'] == 'update' ) {
    $search = new LinemediaAutoSearchSimple();
    $parts = (array) $search->searchLocalDatabaseForPart(
        array(
            'article'      => htmlspecialchars(trim($_REQUEST['article'])),
            'brand_title' => htmlspecialchars(trim($_REQUEST['brand_title'])),
        ),
        true
    );

    $prices =array();
    $js_send = array();
    foreach ($parts as $part) {
        $part_obj = new LinemediaAutoPart($part['id']);

        /*
        * Посчитаем цену товара
        */
        $price = new LinemediaAutoPrice($part_obj);
        $price_calc = $price->calculate();
        $formatted = CurrencyFormat($price_calc, $price->getCurrency());
        $prices['PRICES'][$price_calc] = $formatted;
    }


    if (count($prices['PRICES']) > 0) {
        $prices['min_price'] = min(array_keys($prices['PRICES']));
        $prices['max_price'] = max(array_keys($prices['PRICES']));
    }

    $js_send['min'] = $prices['PRICES'][$prices['min_price']]?:0;
    $js_send['max'] = $prices['PRICES'][$prices['max_price']]?:0;
    $js_send['newid'] = $new_id?:'0';

    $findurl = LinemediaAutoUrlHelper::getPartUrl(
        array(
            'article' => htmlspecialchars(trim($_REQUEST['article'])),
            'brand_title' => htmlspecialchars(trim($_REQUEST['brand_title']))
        )
    );
    $js_send['findurl'] = $findurl;
    die(json_encode($js_send));
}
