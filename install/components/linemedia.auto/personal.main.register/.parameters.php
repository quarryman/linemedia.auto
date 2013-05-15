<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arFormFields = array(
	"NAME",
	"SECOND_NAME",
	"LAST_NAME",
	"AUTO_TIME_ZONE",
	"PERSONAL_PROFESSION",
	"PERSONAL_WWW",
	"PERSONAL_ICQ",
	"PERSONAL_GENDER",
	"PERSONAL_BIRTHDAY",
	"PERSONAL_PHOTO",
	"PERSONAL_PHONE",
	"PERSONAL_FAX",
	"PERSONAL_MOBILE",
	"PERSONAL_PAGER",
	"PERSONAL_STREET",
	"PERSONAL_MAILBOX",
	"PERSONAL_CITY",
	"PERSONAL_STATE",
	"PERSONAL_ZIP",
	"PERSONAL_COUNTRY",
	"PERSONAL_NOTES",
	"WORK_COMPANY",
	"WORK_DEPARTMENT",
	"WORK_POSITION",
	"WORK_WWW",
	"WORK_PHONE",
	"WORK_FAX",
	"WORK_PAGER",
	"WORK_STREET",
	"WORK_MAILBOX",
	"WORK_CITY",
	"WORK_STATE",
	"WORK_ZIP",
	"WORK_COUNTRY",
	"WORK_PROFILE",
	"WORK_LOGO",
	"WORK_NOTES",
);

if (!CTimeZone::Enabled()) {
	unset($arFormFields["AUTO_TIME_ZONE"]);
}
$arUserFields = array();
foreach ($arFormFields as $value) {
	$arUserFields[$value] = "[".$value."] ".GetMessage("REGISTER_FIELD_".$value);
}
$arRes = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", 0, LANGUAGE_ID);
$userProp = array();
if (!empty($arRes)) {
	foreach ($arRes as $key => $val) {
		$userProp[$val["FIELD_NAME"]] = (strLen($val["EDIT_FORM_LABEL"]) > 0 ? $val["EDIT_FORM_LABEL"] : $val["FIELD_NAME"]);
    }
}

/*
 * Свойства магазина.
 */
CModule::IncludeModule('sale');


// Список типов плательзиков.
$rs = CSalePersonType::GetList();
$personTypes = array();
while ($pt = $rs->Fetch()) {
    $personTypes[$pt['ID']] = $pt['NAME'];
}

// Свойства по плательщику.
$rs = CSaleOrderProps::GetList(array('SORT' => 'ASC'), array('ACTIVE' => 'Y', 'USER_PROPS' => 'Y'));
$saleProfileProps = array();
while ($prop = $rs->Fetch()) {
    $name = '['.$prop['ID'].'] '.$prop['NAME'].'('.$personTypes[$prop['PERSON_TYPE_ID']].')';
    if ($prop['REQUIED'] == 'Y') {
        $name .= '*';
    }
    $saleProfileProps[$prop['ID']] = $name;
}


// Подписки.
if (CModule::IncludeModule('subscribe')) {
    $rs = CRubric::GetList(array(), array('ACTIVE' => 'Y'));
    $rubrics = array();
    while ($rub = $rs->Fetch()) {
        $rubrics[$rub['ID']] = '['.$rub['ID'].'] '.$rub['NAME'];
    }
}

$arComponentParameters = array(
    "GROUPS" => array(
        "PROFILE" => array("NAME" => GetMessage('LM_AUTO_MAIN_PROP_GROUP_PROFILE')),
        'SUBSCRIBE'=> array("NAME" => GetMessage('LM_AUTO_MAIN_PROP_GROUP_SUBSCRIBE'))
    ),
	"PARAMETERS" => array(
	   
        "USE_EMAIL_AS_LOGIN" => array(
            "NAME" => GetMessage('LM_AUTO_MAIN_PROP_USE_EMAIL_AS_LOGIN'),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => 'N',
            "PARENT" => "BASE",
        ),
        
        "PERSON_SALE_PROFILE_FIELDS" => array(
            "PARENT" => "PROFILE",
            "NAME" => GetMessage('LM_AUTO_MAIN_PROP_PERSON_SALE_PROFILE_FIELDS'),
            "TYPE" => "LIST",
            "VALUES" => $saleProfileProps,
            "MULTIPLE" => "Y",
            "DEFAULT" => array(),
        ),

		"SHOW_FIELDS" => array(
			"NAME" => GetMessage("REGISTER_SHOW_FIELDS"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => $arUserFields,
			"PARENT" => "BASE",
		),

		"REQUIRED_FIELDS" => array(
			"NAME" => GetMessage("REGISTER_REQUIRED_FIELDS"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => $arUserFields,
			"PARENT" => "BASE",
		),

		"AUTH" => array(
			"NAME" => GetMessage("REGISTER_AUTOMATED_AUTH"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),

		"USE_BACKURL" => array(
			"NAME" => GetMessage("REGISTER_USE_BACKURL"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),

		"SUCCESS_PAGE" => array(
			"NAME" => GetMessage("REGISTER_SUCCESS_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),

		"SET_TITLE" => array(),
        
		"USER_PROPERTY" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("USER_PROPERTY"),
			"TYPE" => "LIST",
			"VALUES" => $userProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),
		),
		
		"GET_SUBSCRIBE" => array(
            "PARENT" => "SUBSCRIBE",
            "NAME" => GetMessage('LM_AUTO_MAIN_PROP_GET_SUBSCRIBE'),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
            "REFRESH" => "Y"
        )
	),
);

if ($arCurrentValues['GET_SUBSCRIBE'] == 'Y') {
    
    $arComponentParameters['PARAMETERS']['SUBSCRIBE_RUBRICS'] = array(
        "PARENT" => "SUBSCRIBE",
        "NAME" => GetMessage('LM_AUTO_MAIN_PROP_SUBSCRIBE_RUBRICS'),
        "TYPE" => "LIST",
        "VALUES" => $rubrics,
        "MULTIPLE" => "Y",
        "DEFAULT" => array(),
    );
}

?>