<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arYesNo = Array(
	"Y" => GetMessage("SPO_DESC_YES"),
	"N" => GetMessage("SPO_DESC_NO"),
);


$arComponentParameters = array(
	"PARAMETERS" => array(
		"SEF_MODE" => Array(
			"list" => Array(
				"NAME" => GetMessage("SPO_LIST_DESC"),
				"DEFAULT" => "index.php",
				"VARIABLES" => array()
			),
			"detail" => Array(
				"NAME" => GetMessage("SPO_DETAIL_DESC"),
				"DEFAULT" => "order_detail.php?ID=#ID#",
				"VARIABLES" => array("ID")
			),
			"cancel" => Array(
				"NAME" => GetMessage("SPO_CANCEL_DESC"),
				"DEFAULT" => "order_cancel.php?ID=#ID#",
				"VARIABLES" => array("ID")
			),

		),

		"ORDERS_PER_PAGE" => Array(
			"NAME" => GetMessage("SPO_ORDERS_PER_PAGE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "20",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PATH_TO_PAYMENT" => Array(
			"NAME" => GetMessage("SPO_PATH_TO_PAYMENT"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "payment.php",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PATH_TO_BASKET" => Array(
			"NAME" => GetMessage("SPO_PATH_TO_BASKET"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "basket.php",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),

		"SET_TITLE" => Array(),
		"SAVE_IN_SESSION" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SPO_SAVE_IN_SESSION"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"NAV_TEMPLATE" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SPOL_NAV_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
	)
);

if (CModule::IncludeModule("sale")) {
	$dbPerson = CSalePersonType::GetList(Array("SORT" => "ASC", "NAME" => "ASC"));
	while ($arPerson = $dbPerson->GetNext()) {
		$arPers2Prop = Array("" => GetMessage("SPO_SHOW_ALL"));
		$bProp = false;
		$dbProp = CSaleOrderProps::GetList(Array("SORT" => "ASC", "NAME" => "ASC"), Array("PERSON_TYPE_ID" => $arPerson["ID"]));
		while ($arProp = $dbProp -> GetNext()) {
			$arPers2Prop[$arProp["ID"]] = $arProp["NAME"];
			$bProp = true;
		}
		
		if ($bProp) {
			$arComponentParameters["PARAMETERS"]["PROP_".$arPerson["ID"]] =  Array(
							"NAME" => GetMessage("SPO_PROPS_NOT_SHOW")." \"".$arPerson["NAME"]."\" (".$arPerson["LID"].")", 
							"TYPE"=>"LIST", "MULTIPLE"=>"Y", 
							"VALUES" => $arPers2Prop,
							"DEFAULT"=>"", 
							"COLS"=>25, 
							"ADDITIONAL_VALUES"=>"N",
							"PARENT" => "BASE",
				);
		}
	}
}


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

foreach ($arProps as $prop) {
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
