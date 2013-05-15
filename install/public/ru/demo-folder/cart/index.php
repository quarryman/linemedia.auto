<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Корзина");
?>
<?$APPLICATION->IncludeComponent("linemedia.auto:store.sale.basket.basket", ".default", array(
	"COUNT_DISCOUNT_4_ALL_QUANTITY" => "N",
	"COLUMNS_LIST" => array(
		0 => "NAME",
		1 => "PRICE",
		2 => "QUANTITY",
		3 => "DELETE",
		4 => "DELAY",
	),
	"AJAX_MODE" => "N",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"PATH_TO_ORDER" => "#DEMO_FOLDER#order/",
	"HIDE_COUPON" => "Y",
	"QUANTITY_FLOAT" => "N",
	"PRICE_VAT_SHOW_VALUE" => "N",
	"SET_TITLE" => "Y",
	"HIDE_PROPERTIES" => array(
		0 => "",
		1 => "",
	),
	"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
