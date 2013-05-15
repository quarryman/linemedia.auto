<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("iblock"))
    return;

if (!CModule::IncludeModule("support"))
    return;

$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["IBLOCK_TYPE_ID"], "ACTIVE"=>"Y"));
while ($arr=$rsIBlock->Fetch())
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];

$rsCTicketDic = CTicketDictionary::GetList($by='s_name', $order='asc', Array("TYPE" => 'C'));
while ($arr=$rsCTicketDic->Fetch())
	$aTCategory[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];

$arYesNo = Array(
	"Y" => GetMessage("SUP_DESC_YES"),
	"N" => GetMessage("SUP_DESC_NO"),
);


$arComponentParameters = array(
	"PARAMETERS" => array(
                "IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("LM_GARAGE_IBLOCK_ID"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => $arIBlock,
			"REFRESH" => "N",
			"MULTIPLE" => "N",
		),
                "SUPPORT_CATEGORY" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("LM_SUPPORT_CATEGORY"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => $aTCategory,
			"REFRESH" => "N",
			"MULTIPLE" => "N",
		),
                
		"VARIABLE_ALIASES" => Array(
			"ID" => Array("NAME" => GetMessage("SUP_TICKET_ID_DESC"))
		),

		"SEF_MODE" => Array(
			"ticket_list" => Array(
				"NAME" => GetMessage("SUP_TICKET_LIST_DESC"),
				"DEFAULT" => "index.php",
				"VARIABLES" => array()
			),

			"ticket_edit" => Array(
				"NAME" => GetMessage("SUP_TICKET_EDIT_DESC"),
				"DEFAULT" => "#ID#.php",
				"VARIABLES" => array("ID")
			),
		),

		"TICKETS_PER_PAGE" => Array(
			"NAME" => GetMessage("SUP_LIST_TICKETS_PER_PAGE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT" => "50"
		),

		"MESSAGES_PER_PAGE" => Array(
			"NAME" => GetMessage("SUP_EDIT_MESSAGES_PER_PAGE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT" => "20"
		),
		
		"MESSAGE_MAX_LENGTH" => Array(
			"NAME" => GetMessage("SUP_MESSAGE_MAX_LENGTH"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT" => "70"
		),
			
		"MESSAGE_SORT_ORDER" => Array(
			"NAME" => GetMessage("SUP_MESSAGE_SORT_ORDER"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"VALUES" =>Array(
				"asc"=>GetMessage("SUP_SORT_ASC"),
				"desc"=>GetMessage("SUP_SORT_DESC")
			),
		),
		
		"SET_PAGE_TITLE" => Array(
			"NAME"=>GetMessage("SUP_SET_PAGE_TITLE"), 
			"TYPE"=>"LIST", 
			"MULTIPLE"=>"N", 
			"DEFAULT"=>"Y", 
			"PARENT" => "ADDITIONAL_SETTINGS",
			"VALUES"=>$arYesNo, 
			"ADDITIONAL_VALUES"=>"N"
		),
                /*
                'ADDITIONAL_FILTER' => Array(
                    "PARENT" => "ADDITIONAL_SETTINGS",
                    "NAME" => GetMessage("LM_CONDITION_SHOW"),
                    "TYPE" => "STRING",
                    "DEFAULT" => "",
                ),
                */
	)
);
?>
