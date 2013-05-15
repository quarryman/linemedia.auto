<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Блокнот");
?>
<?$APPLICATION->IncludeComponent(
    "linemedia.auto:personal.notepad",
    "",
    Array(
        "ADD_SECTION_CHAIN" => "Y",
        "INIT_JQUERY" => "Y",
    )
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>