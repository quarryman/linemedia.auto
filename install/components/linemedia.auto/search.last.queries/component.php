<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule("linemedia.auto")){
	ShowError(GetMessage("LM_AUTO_MODULE_NOT_INSTALL"));
	return;
}

if (!function_exists('sortQueries')) {
    function sortQueries($a, $b)
    {
        if ($a['added'] == $b['added']) {
            return 0;
        }
        return ($a['added'] > $b['added']) ? -1 : 1;
    }
}


/*
 * Заполняется в компоненте search.results
 */
$arResult['QUERIES'] = (array) $_SESSION['LM_AUTO_MAIN']['QUERIES'];

uasort($arResult['QUERIES'], 'sortQueries');

$this->IncludeComponentTemplate();
