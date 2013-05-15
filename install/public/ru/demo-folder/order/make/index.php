<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заказы");
?><?$APPLICATION->IncludeComponent("linemedia.auto:sale.order.ajax", "", array(
	"PAY_FROM_ACCOUNT" => "Y",
	"COUNT_DELIVERY_TAX" => "N",
	"COUNT_DISCOUNT_4_ALL_QUANTITY" => "N",
	"ONLY_FULL_PAY_FROM_ACCOUNT" => "N",
	"ALLOW_AUTO_REGISTER" => "Y",
	"SEND_NEW_USER_NOTIFY" => "Y",
	"DELIVERY_NO_AJAX" => "Y",
	"PROP_1" => array(
	),
	"PROP_2" => array(
	),
	"PATH_TO_BASKET" => "#DEMO_FOLDER#cart/",
	"PATH_TO_PERSONAL" => "#DEMO_FOLDER#order/",
	"PATH_TO_PAYMENT" => "#DEMO_FOLDER#order/payment/",
	"PATH_TO_AUTH" => "#DEMO_FOLDER#auth/",
	"SET_TITLE" => "Y",
	"HIDE_PROPERTIES" => array(
		0 => "",
		1 => "supplier_id",
		2 => "supplier_title",
		3 => "article",
		4 => "brand_id",
		5 => "brand_title",
		6 => "payed",
		7 => "payed_date",
		8 => "emp_payed_id",
		9 => "canceled",
		10 => "canceled_date",
		11 => "emp_canceled_id",
		12 => "status",
		13 => "date_status",
		14 => "emp_status_id",
		15 => "delivery",
		16 => "date_delivery",
		17 => "emp_delivery_id",
		18 => "",
	)
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
