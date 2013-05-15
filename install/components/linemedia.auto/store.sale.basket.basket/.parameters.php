<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arYesNo = Array(
	'Y' => GetMessage('SBB_DESC_YES'),
	'N' => GetMessage('SBB_DESC_NO'),
);

$arComponentParameters = Array(
	"PARAMETERS" => Array(
		"PATH_TO_ORDER" => Array(
			"NAME" => GetMessage("SBB_PATH_TO_ORDER"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/personal/order.php",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),

		"HIDE_COUPON" => Array(
			"NAME"=>GetMessage("SBB_HIDE_COUPON"),
			"TYPE"=>"LIST", "MULTIPLE"=>"N",
			"VALUES"=>array(
					"N" => GetMessage("SBB_DESC_NO"),
					"Y" => GetMessage("SBB_DESC_YES")
				),
			"DEFAULT"=>"N",
			"COLS"=>25,
			"ADDITIONAL_VALUES"=>"N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"COLUMNS_LIST" => Array(
			"NAME"=>GetMessage("SBB_COLUMNS_LIST"),
			"TYPE"=>"LIST",
			"MULTIPLE"=>"Y",
			"VALUES"=>array(
				"NAME" => GetMessage("SBB_BNAME"),
				"PROPS" => GetMessage("SBB_BPROPS"),
				"PRICE" => GetMessage("SBB_BPRICE"),
				"TYPE" => GetMessage("SBB_BTYPE"),
				"QUANTITY" => GetMessage("SBB_BQUANTITY"),
				"DELETE" => GetMessage("SBB_BDELETE"),
				"DELAY" => GetMessage("SBB_BDELAY"),
				"WEIGHT" => GetMessage("SBB_BWEIGHT"),
				"DISCOUNT" => GetMessage("SBB_BDISCOUNT"),
				"VAT" => GetMessage("SBB_BVAT"),
				),
				"DEFAULT"=>array("NAME", "PRICE", "TYPE", "DISCOUNT", "QUANTITY", "DELETE", "DELAY", "WEIGHT"),
				"COLS"=>25,
			"ADDITIONAL_VALUES"=>"N",
			"PARENT" => "VISUAL",
		),

		"QUANTITY_FLOAT" => array(
			"NAME" => GetMessage('SBB_QUANTITY_FLOAT'),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "N",
			"ADDITIONAL_VALUES"=>"N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
/*
		"PRICE_VAT_INCLUDE" => array(
			"NAME" => GetMessage('SBB_VAT_INCLUDE'),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "Y",
			"ADDITIONAL_VALUES"=>"N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
*/
		"PRICE_VAT_SHOW_VALUE" => array(
			"NAME" => GetMessage('SBB_VAT_SHOW_VALUE'),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "N",
			"ADDITIONAL_VALUES"=>"N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"COUNT_DISCOUNT_4_ALL_QUANTITY" => Array(
			"NAME"=>GetMessage("SBB_COUNT_DISCOUNT_4_ALL_QUANTITY"), 
			"TYPE"=>"LIST", "MULTIPLE"=>"N", 
			"VALUES"=>array(
					"N" => GetMessage("SBB_DESC_NO"), 
					"Y" => GetMessage("SBB_DESC_YES")
				), 
			"DEFAULT"=>"N", 
			"COLS"=>25, 
			"ADDITIONAL_VALUES"=>"N",
			"PARENT" => "BASE",
		),
        "AJAX_RECALC" => array(
            "NAME" => GetMessage('SBB_AJAX_RECALC'),
            "TYPE" => "CHECKBOX",
            "MULTIPLE" => "N",
            "DEFAULT" => "N",
            "ADDITIONAL_VALUES" => "N",
            "PARENT" => "ADDITIONAL_SETTINGS",
        ),
		//"SET_TITLE" => Array(),
		"AJAX_MODE" => Array(),
	)
);






$arProps = array();
/*
 * ID поставщика
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_SUPPLIER_ID'),
    "CODE" => "supplier_id",
);

/*
 * Название поставщика
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_SUPPLIER_TITLE'),
    "CODE" => "supplier_title",
);

/*
 * Артикул
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_ARTICLE'),
    "CODE" => "article",
);

/*
 * ID производителя
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_BRAND_ID'),
    "CODE" => "brand_id",
);

/*
 * Название производителя
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_BRAND_TITLE'),
    "CODE" => "brand_title",
);

/*
 * Закупочная цена
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_BASE_PRICE'),
    "CODE" => "brand_title",
);

/*
 * Оплата товара
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_PAYED'),
    "CODE" => "payed",
);

/*
 * Дата оплата товара
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_PAYED_DATE'),
    "CODE" => "payed_date",
);

/*
 * Кем изменена оплата
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_PAYED_ID'),
    "CODE" => "emp_payed_id",
);

/*
 * Отмена заказа
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_CANCELED'),
    "CODE" => "canceled",
);

/*
 * Дата отмены заказа
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_CANCELED_DATE'),
    "CODE" => "canceled_date",
);

/*
 * Кем отменен заказ
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_CANCELED_ID'),
    "CODE" => "emp_canceled_id",
);

/*
 * Статус товара
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_STATUS'),
    "CODE" => "status",
);

/*
 * Дата изменения статуса
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_STATUS_DATE'),
    "CODE" => "date_status",
);

/*
 * Кем изменен статус
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_STATUS_ID'),
    "CODE" => "emp_status_id",
);

/*
 * Возможность доставки
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_DELIVREY'),
    "CODE" => "delivery",
);

/*
 * Дата изменения статуса доставки
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_DELIVERY_DATE'),
    "CODE" => "date_delivery",
);

/*
 * Кем изменен статус доставки
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_DELIVERY_ID'),
    "CODE" => "emp_delivery_id",
);

foreach($arProps AS $prop)
{
    $VALUES[$prop['CODE']] = $prop['NAME'];
}

$arComponentParameters['PARAMETERS']['HIDE_PROPERTIES'] = array(
	"NAME" => GetMessage("LM_AUTO_MAIN_HIDE_PROPERTIES"),
	"TYPE" => "LIST",
	"MULTIPLE" => "Y",
	"DEFAULT" => "",
	"VALUES" => $VALUES,
	"ADDITIONAL_VALUES" => "Y",
);
