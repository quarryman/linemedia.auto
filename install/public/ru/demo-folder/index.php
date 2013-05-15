<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Linemedia Autoexpert");?>

<?$APPLICATION->IncludeComponent("bitrix:menu", "tree", array(
        "ROOT_MENU_TYPE" => "auto",
        "MENU_CACHE_TYPE" => "A",
        "MENU_CACHE_TIME" => "3600",
        "MENU_CACHE_USE_GROUPS" => "Y",
        "MENU_CACHE_GET_VARS" => array(
        ),
        "MAX_LEVEL" => "4",
        "CHILD_MENU_TYPE" => "auto",
        "USE_EXT" => "Y",
        "DELAY" => "N",
        "ALLOW_MULTI_SELECT" => "N"
        ),
        false
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
