<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = Array(
	"PARAMETERS" => Array(
		"PATH_TO_BASKET" => Array(
			"NAME" => GetMessage("SOA_PATH_TO_BASKET"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "basket.php",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PATH_TO_PERSONAL" => Array(
			"NAME" => GetMessage("SOA_PATH_TO_PERSONAL"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "index.php",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PATH_TO_PAYMENT" => Array(
			"NAME" => GetMessage("SOA_PATH_TO_PAYMENT"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "payment.php",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PATH_TO_AUTH" => Array(
			"NAME" => GetMessage("SOA_PATH_TO_AUTH"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/auth/",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PAY_FROM_ACCOUNT" => Array(
			"NAME"=>GetMessage("SOA_ALLOW_PAY_FROM_ACCOUNT"), 
			"TYPE" => "CHECKBOX",
			"DEFAULT"=>"Y", 
			"PARENT" => "BASE",
		),
		"COUNT_DELIVERY_TAX" => Array(
			"NAME"=>GetMessage("SOA_COUNT_DELIVERY_TAX"), 
			"TYPE" => "CHECKBOX",
			"DEFAULT"=>"N", 
			"PARENT" => "BASE",
		),
		"COUNT_DISCOUNT_4_ALL_QUANTITY" => Array(
			"NAME"=>GetMessage("SOA_COUNT_DISCOUNT_4_ALL_QUANTITY"), 
			"TYPE" => "CHECKBOX",
			"DEFAULT"=>"N", 
			"PARENT" => "BASE",
		),
		"ONLY_FULL_PAY_FROM_ACCOUNT" => Array(
			"NAME"=>GetMessage("SOA_ONLY_FULL_PAY_FROM_ACCOUNT"), 
			"TYPE" => "CHECKBOX",
			"DEFAULT"=>"N", 
			"PARENT" => "BASE",
		),
		"ALLOW_AUTO_REGISTER" => Array(
			"NAME"=>GetMessage("SOA_ALLOW_AUTO_REGISTER"), 
			"TYPE" => "CHECKBOX",
			"DEFAULT"=>"N", 
			"PARENT" => "BASE",
		),
		"SEND_NEW_USER_NOTIFY" => Array(
			"NAME"=>GetMessage("SOA_SEND_NEW_USER_NOTIFY"), 
			"TYPE" => "CHECKBOX",
			"DEFAULT"=>"Y", 
			"PARENT" => "BASE",
		),
		"DELIVERY_NO_AJAX" => Array(
			"NAME" => GetMessage("SOA_DELIVERY_NO_AJAX"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "Y",
			"PARENT" => "BASE",
		),
		"SET_TITLE" => Array(),
	)
);

if(CModule::IncludeModule("sale"))
{
	$dbPerson = CSalePersonType::GetList(Array("SORT" => "ASC", "NAME" => "ASC"));
	while($arPerson = $dbPerson->GetNext())
	{
	
		$arPers2Prop = Array("" => GetMessage("SOA_SHOW_ALL"));
		$bProp = false;
		$dbProp = CSaleOrderProps::GetList(Array("SORT" => "ASC", "NAME" => "ASC"), Array("PERSON_TYPE_ID" => $arPerson["ID"]));
		while($arProp = $dbProp -> GetNext())
		{
			
			$arPers2Prop[$arProp["ID"]] = $arProp["NAME"];
			$bProp = true;
		}
		
		if($bProp)
		{
			$arComponentParameters["PARAMETERS"]["PROP_".$arPerson["ID"]] =  Array(
							"NAME" => GetMessage("SOA_PROPS_NOT_SHOW")." \"".$arPerson["NAME"]."\" (".$arPerson["LID"].")", 
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
 * ID ����������
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_SUPPLIER_ID'),
    "CODE" => "supplier_id",
);

/*
 * �������� ����������
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_SUPPLIER_TITLE'),
    "CODE" => "supplier_title",
);

/*
 * �������
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_ARTICLE'),
    "CODE" => "article",
);

/*
 * ID �������������
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_BRAND_ID'),
    "CODE" => "brand_id",
);

/*
 * �������� �������������
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_BRAND_TITLE'),
    "CODE" => "brand_title",
);

/*
 * ���������� ����
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_BASE_PRICE'),
    "CODE" => "base_price",
);

/*
 * ������ ������
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_PAYED'),
    "CODE" => "payed",
);

/*
 * ���� ������ ������
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_PAYED_DATE'),
    "CODE" => "payed_date",
);

/*
 * ��� �������� ������
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_PAYED_ID'),
    "CODE" => "emp_payed_id",
);

/*
 * ������ ������
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_CANCELED'),
    "CODE" => "canceled",
);

/*
 * ���� ������ ������
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_CANCELED_DATE'),
    "CODE" => "canceled_date",
);

/*
 * ��� ������� �����
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_CANCELED_ID'),
    "CODE" => "emp_canceled_id",
);

/*
 * ������ ������
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_STATUS'),
    "CODE" => "status",
);

/*
 * ���� ��������� �������
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_STATUS_DATE'),
    "CODE" => "date_status",
);

/*
 * ��� ������� ������
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_STATUS_ID'),
    "CODE" => "emp_status_id",
);

/*
 * ����������� ��������
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_DELIVREY'),
    "CODE" => "delivery",
);

/*
 * ���� ��������� ������� ��������
 */
$arProps[] = array(
    "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_DELIVERY_DATE'),
    "CODE" => "date_delivery",
);

/*
 * ��� ������� ������ ��������
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
