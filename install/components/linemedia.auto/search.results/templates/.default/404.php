<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>


<h2><?=GetMessage('LM_AUTO_SEARCH_404')?></h2>

<?
if ($arParams['USE_REQUEST_FORM'] == 'Y' && CModule::IncludeModule('form')) {?>
    <?$APPLICATION->IncludeComponent("linemedia.auto:part.404.request", ".default", array(
        "IGNORE_CUSTOM_TEMPLATE" => "N",
        "USE_EXTENDED_ERRORS" => "N",
        "SEF_MODE" => "N",
        "SEF_FOLDER" => "/",
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "3600",
        "SUCCESS_URL" => "",
        "WHAT_FIND" => $_REQUEST['q'],
        "WHAT_BRAND" => $_REQUEST['brand_title'],
        "VARIABLE_ALIASES" => array(
            "WEB_FORM_ID" => "WEB_FORM_ID",
            "RESULT_ID" => "RESULT_ID",
        )
        ),
        false
    );?>
<?}?>