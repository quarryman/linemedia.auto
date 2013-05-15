<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заказы");
?>

<?
    $APPLICATION->IncludeComponent("linemedia.auto:personal.orders", ".default", array(
	"COUNT_ON_PAGE" => "20",
	"USE_STATUS_COLOR" => "Y",
	"PATH_TO_PAYMENT" => "#DEMO_FOLDER#order/make/",
	"SET_TITLE" => "Y"
	),
	false
);
?>

<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php") ?>
