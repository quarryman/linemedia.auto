<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Покупка");
?><?$APPLICATION->IncludeComponent("linemedia.auto:sale.order.ajax", ".default", array(
    "PAY_FROM_ACCOUNT" => "Y",
    "COUNT_DELIVERY_TAX" => "N",
    "COUNT_DISCOUNT_4_ALL_QUANTITY" => "N",
    "ONLY_FULL_PAY_FROM_ACCOUNT" => "N",
    "ALLOW_AUTO_REGISTER" => "Y",
    "SEND_NEW_USER_NOTIFY" => "Y",
    "DELIVERY_NO_AJAX" => "Y",
    "PROP_3" => array(
    ),
    "PROP_4" => array(
    ),
    "PATH_TO_BASKET" => "#DEMO_FOLDER#cart/",
    "PATH_TO_PERSONAL" => "#DEMO_FOLDER#orders/",
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
        6 => "base_price",
        7 => "payed",
        8 => "payed_date",
        9 => "emp_payed_id",
        10 => "canceled",
        11 => "canceled_date",
        12 => "emp_canceled_id",
        13 => "status",
        14 => "date_status",
        15 => "emp_status_id",
        16 => "delivery",
        17 => "date_delivery",
        18 => "emp_delivery_id",
        19 => "",
        20 => "",
    )
    ),
    false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
