<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Запрос по VIN");
?>
<?$APPLICATION->IncludeComponent("linemedia.auto:personal.request.vin", "", array(
    "SEF_MODE" => "N",
    "SEF_FOLDER" => "#DEMO_FOLDER#/vin/",
    "TICKETS_PER_PAGE" => "50",
    "MESSAGES_PER_PAGE" => "20",
    "MESSAGE_MAX_LENGTH" => "70",
    "MESSAGE_SORT_ORDER" => "asc",
    "SET_PAGE_TITLE" => "Y",
    "TECDOC_NEW_URL" => "Y",
    "VARIABLE_ALIASES" => array(
        "ID" => "ID",
    )
    ),
    false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>