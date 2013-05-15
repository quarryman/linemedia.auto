<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arParams['SUPPLIER_ID'] = strval($arParams['SUPPLIER_ID']);


if(!CModule::IncludeModule('linemedia.auto')) {
    ShowError(GetMessage('LM_AUTO_SUPP_STAT_NO_MODULE'));
    return;
}
if ( empty($arParams['SUPPLIER_ID']) || !LinemediaAutoSupplier::existsSupplierId($arParams['SUPPLIER_ID'])) {
    global $USER;
    if ($USER->IsAdmin()) { // если пользователь не админ, то ему эта информация ничего полезного не скажет.
        ShowError(GetMessage('LM_AUTO_INVALID_SUPP_ID', array('#ID#'=>$arParams['SUPPLIER_ID'])));
    }
    return;
}

$arResult['STAT'] = LinemediaAutoSupplier::getStat($arParams['SUPPLIER_ID']);

$this->IncludeComponentTemplate();