<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arDefaultUrlTemplates404 = array(
    "ticket_list" => "index.php",
    "ticket_edit" => "#ID#.php",
);

$arDefaultVariableAliases = array(
    "ID" => "ID"
);

$arDefaultVariableAliases404 = array(
);

$arComponentVariables = array('ID');

if (IsModuleInstalled('support')) {
    $arParams['CATEGORY_ID'] = (int) COption::GetOptionInt('linemedia.auto', 'LM_AUTO_SUPPORT_CATEGORY_REQUEST_VIN');
    
    if ($arParams['SEF_MODE'] == 'Y') {
        $arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
    	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);
    
    	$componentPage = CComponentEngine::ParseComponentPath(
    		$arParams['SEF_FOLDER'],
    		$arUrlTemplates,
    		$arVariables
    	);
    
    	if ($componentPage == "ticket_edit") {
    		$componentPage = "ticket_edit";
    	} elseif($_SERVER["REQUEST_METHOD"] == "POST" && (strlen($_REQUEST["save"]) > 0 || strlen($_REQUEST["apply"]) > 0)) {
    		$componentPage = "ticket_edit";
    	} else {
            if (!$USER->IsAuthorized()) {
                $componentPage = "ticket_edit";
            } else {
                $componentPage = "ticket_list";
            }
        }
    
    	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);
    
    	$arResult = array(
    		"FOLDER" => $arParams["SEF_FOLDER"],
    		"URL_TEMPLATES" => $arUrlTemplates, 
    		"VARIABLES" => $arVariables, 
    		"ALIASES" => $arVariableAliases
    	);
    
    	$this->IncludeComponentTemplate($componentPage);
    } else {
    	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
    	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);
        
    	$componentPage = "";
        
    	if (isset($arVariables["ID"]) && intval($arVariables["ID"]) >= 0) {
    		$componentPage = "ticket_edit";
    	} else {
            if (!$USER->IsAuthorized()) {
                $componentPage = "ticket_edit";
            } else {
                $componentPage = "ticket_list";
            }
        }
        
    	$arResult = array(
    		"FOLDER" => "",
    		"URL_TEMPLATES" => array(
    			"ticket_edit" => htmlspecialchars($APPLICATION->GetCurPage())."?".$arVariableAliases["ID"]."=#ID#",
    			"ticket_list" => htmlspecialchars($APPLICATION->GetCurPage()),
    		),
    		"VARIABLES" => $arVariables, 
    		"ALIASES" => $arVariableAliases
    	);
    
    	$this->IncludeComponentTemplate($componentPage);
    }
} else {
    $this->IncludeComponentTemplate('mail');
}

