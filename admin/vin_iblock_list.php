<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);
$saleModulePermissions = $APPLICATION->GetGroupRight("linemedia.auto");

if ($saleModulePermissions == 'D') {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$POST_RIGHT = 'W';

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO MODULE NOT INSTALLED');
    return;
}

if (!CModule::IncludeModule("iblock")) {
    ShowError('IBLOCK MODULE NOT INSTALLED');
    return;
}
global $DB;

/***********************************************************/
$sTableID = "b_lm_vin_iblock"; // ID таблицы
$oSort = new CAdminSorting($sTableID, "ID", "desc"); // объект сортировки
$lAdmin = new CAdminList($sTableID, $oSort); // основной объект списка


// проверку значений фильтра дл€ удобства вынесем в отдельную функцию
function CheckFilter(){
	global $FilterArr, $lAdmin;
	foreach ($FilterArr as $f) global $$f;
	
	return count($lAdmin->arFilterErrors) == 0; // если ошибки есть, вернем false;
}

// об€зательный и посто€нный фильтр
$arFilterMain = Array('ACTIVE' => 'Y', 'IBLOCK_CODE' => 'lm_auto_vin');

// опишем элементы фильтра
$FilterArr = Array(
	"find_ID",
        "find_VIN",
        "find_MANAGER",
);

// инициализируем фильтр
$lAdmin->InitFilter($FilterArr);

// если все значени€ фильтра корректны, обработаем его
if (CheckFilter()) {
	// создадим массив фильтрации дл€ выборки на основе значений фильтра
	$arFilter = array(
		"ID"  		    => $find_ID,
                "PROPERTY_VIN"      => $find_VIN,
                "PROPERTY_MANAGER"  => $find_MANAGER,
	);
}

$arFilter = array_merge($arFilterMain, $arFilter);
// обработка одиночных и групповых действий
if (($arID = $lAdmin->GroupAction()) && $POST_RIGHT == "W") {
	// если выбрано "ƒл€ всех элементов"
	if ($_REQUEST['action_target']=='selected') {
	    switch ($_REQUEST['action']) {
    		// удаление всех позиций
    		case "delete":
    			@set_time_limit(0);
                        $rsData = CIBlockElement::GetList(Array(), $arFilterMain, false, false, Array('ID'));
    			while($aRow = $rsData->Fetch()){
                            $DB->StartTransaction();
                            if(!CIBlockElement::Delete($aRow['ID'])){
                                $DB->Rollback();
                            }else{
                                $DB->Commit();
                            }
                        }
    		break;
	    }
	}
        
	// пройдем по списку элементов
	foreach ($arID as $ID) {
		if (strlen($ID) <= 0) {
			continue;
		}

		// дл€ каждого элемента совершим требуемое действие
		switch ($_REQUEST['action']) {
		    // удаление
		    case "delete":
                        
			    @set_time_limit(0);
			    $DB->StartTransaction();
			    if(!CIBlockElement::Delete($ID)){
				$DB->Rollback();
			    }else{
				$DB->Commit();
			    }
		    break;
		}
	}
}


$where = array('1');
foreach($arFilter AS $code => $val){
	$val = trim($val);
	if($val != ''){
		$val = $DB->ForSQL($val);
		$where[] = "$code = '$val'";
	}
}
$where_str = join(' AND ', $where);

$rsData = CIBlockElement::GetList(Array($by => $order), $arFilter, false, false, Array('ID', 'IBLOCK_ID', 'CREATED_BY', 'DATE_CREATE', 'PROPERTY_VIN', 'PROPERTY_ANSWER', 'PROPERTY_ANSWER_DATE', 'PROPERTY_MANAGER'));

// преобразуем список в экземпл€р класса CAdminResult
$rsData = new CAdminResult($rsData, $sTableID);

// аналогично CDBResult инициализируем постраничную навигацию.
$rsData->NavStart();

// отправим вывод переключател€ страниц в основной объект $lAdmin
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("LM_AUTO_MAIN_VIN_NAV")));

$arHeaders = array(
  array(  "id"    =>"ID",
    "content"  =>GetMessage("LM_AUTO_MAIN_ID"),
    "sort"     =>"ID",
    "default"  =>true,
  ),
  array(  "id"    =>"CREATED_BY",
    "content"  => GetMessage("LM_AUTO_MAIN_CREATED_BY"),
    "sort"     =>"CREATED_BY",
    "default"  =>true,
  ),
  array(  "id"    =>"PROPERTY_VIN_VALUE",
    "content"  => GetMessage("LM_AUTO_MAIN_VIN"),
    "sort"     =>"PROPERTY_VIN",
    "default"  =>true,
  ),
  array(  "id"    =>"DATE_CREATE",
    "content"  => GetMessage("LM_AUTO_MAIN_DATE"),
    "sort"     =>"DATE_CREATE",
    "default"  =>true,
  ),
  array(  "id"    =>"LM_AUTO_MAIN_ANSWER_ISSET",
    "content"  => GetMessage("LM_AUTO_MAIN_ANSWER_ISSET"),
    "sort"     => false,
    "default"  =>true,
  ),
  array(  "id"    =>"PROPERTY_ANSWER_DATE_VALUE",
    "content"  => GetMessage("LM_AUTO_MAIN_ANSWER_DATE"),
    "sort"     => 'PROPERTY_ANSWER_DATE',
    "default"  =>true,
  )
);

if(CModule::IncludeModule('linemedia.autobranches')){
    $arHeaders[] = Array(
    "id"    => "PROPERTY_MANAGER_VALUE",
    "content"  => GetMessage("LM_AUTO_MAIN_MANAGER"),
    "sort"     => 'PROPERTY_MANAGER',
    "default"  => true,
    );
}
$lAdmin->AddHeaders($arHeaders);
unset($arHeaders);

while ($arRes = $rsData->NavNext(true, "f_")) {  
  // создаем строку. результат - экземпл€р класса CAdminListRow
  $row =& $lAdmin->AddRow($arRes['ID'], $arRes);

  $row->AddField("LM_AUTO_MAIN_ANSWER_ISSET", ((!empty($arRes['PROPERTY_ANSWER_VALUE']))?GetMessage("LM_AUTO_MAIN_ANSWER_ISSET_YES"):GetMessage("LM_AUTO_MAIN_ANSWER_ISSET_NO")));
  
  $arUser = CUser::GetByID($arRes['CREATED_BY'])->Fetch();
  $fieldValue = "[<a href=\"/bitrix/admin/user_edit.php?ID=".$arRes['CREATED_BY']."&lang=".LANG."\">".$arRes['CREATED_BY']."</a>] ";
  $fieldValue .= htmlspecialcharsEx($arUser["NAME"].((strlen($arUser["NAME"])<=0 || strlen($arUser["LAST_NAME"])<=0) ? "" : " ").$arUser["LAST_NAME"]." (".$arUser["LOGIN"].")");
  $row->AddField("CREATED_BY", $fieldValue);

  if(empty($arRes['PROPERTY_ANSWER_DATE_VALUE'])){
    $row->AddField("PROPERTY_ANSWER_DATE_VALUE", '---');
  }
    
  if(CModule::IncludeModule('linemedia.autobranches') && !empty($arRes['PROPERTY_MANAGER_VALUE'])){
    $arUser = CUser::GetByID($arRes['PROPERTY_MANAGER_VALUE'])->Fetch();
    $fieldValue = "[<a href=\"/bitrix/admin/user_edit.php?ID=".$arRes['PROPERTY_MANAGER_VALUE']."&lang=".LANG."\">".$arRes['PROPERTY_MANAGER_VALUE']."</a>] ";
    $fieldValue .= htmlspecialcharsEx($arUser["NAME"].((strlen($arUser["NAME"])<=0 || strlen($arUser["LAST_NAME"])<=0) ? "" : " ").$arUser["LAST_NAME"]." (".$arUser["LOGIN"].")");
    $row->AddField("PROPERTY_MANAGER_VALUE", $fieldValue);
  }else{
    $row->AddField("PROPERTY_MANAGER_VALUE", '---');
  }
  
  // сформируем контекстное меню
  $arActions = Array();
  
  // –едактирование элемента.
    $arActions[] = array(
      "ICON"    => "edit",
      "TEXT"    => GetMessage("LM_AUTO_MAIN_SHOW"),
      "ACTION"  => $lAdmin->ActionRedirect("/bitrix/admin/linemedia.auto_vin_iblock_show.php?ID=$f_ID&lang=" . LANG),
      "DEFAULT" => true
    );
  
  // удаление элемента
    $arActions[] = array(
      "ICON"=>"delete",
      "TEXT"=>GetMessage("LM_AUTO_MAIN_DEL"),
      "ACTION"=>"if(confirm('".GetMessage('LM_AUTO_MAIN_CONFIRM_DEL')."')) " . $lAdmin->ActionDoGroup($f_ID, "delete")
    );
  
  // применим контекстное меню к строке
  $row->AddActions($arActions);
}

// групповые действи€
$lAdmin->AddGroupActionTable(Array(
  "delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"), // удалить выбранные элементы
  ));

CUtil::InitJSCore(array('window'));

// альтернативный вывод
$lAdmin->CheckListMode();

// установим заголовок страницы
$APPLICATION->SetTitle(GetMessage('LM_AUTO_MAIN_VIN_TITLE'));

// не забудем разделить подготовку данных и вывод
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$APPLICATION->AddHeadScript('http://yandex.st/jquery/1.8.0/jquery.min.js');

$arFilterNames = Array(
    GetMessage("LM_AUTO_MAIN_ID"),
    GetMessage("LM_AUTO_MAIN_VIN"),
);

if(CModule::IncludeModule('linemedia.autobranches')){
    $arFilterNames[] = GetMessage("LM_AUTO_MAIN_MANAGER");
}

// создадим объект фильтра
$oFilter = new CAdminFilter(
  $sTableID."_filter",
  $arFilterNames
);
?>
<form name="find_form" method="get" action="<?= $APPLICATION->GetCurPage();?>">
<? $oFilter->Begin(); ?>
<tr>
  <td><?=GetMessage("LM_AUTO_MAIN_ID").":"?></td>
  <td><input type="text" name="find_ID" size="25" value="<?= htmlspecialchars($find_group)?>" /></td>
</tr>
<tr>
  <td><?=GetMessage("LM_AUTO_MAIN_VIN").":"?></td>
  <td><input type="text" name="find_VIN" size="30" value="<?= htmlspecialchars($find_brand_title)?>" /></td>
</tr>
<?if(CModule::IncludeModule('linemedia.autobranches')){?>
<tr>
  <td><?=GetMessage("LM_AUTO_MAIN_MANAGER").":"?></td>
  <td>
    <input type="text" name="find_MANAGER" id="find_PROPERTY_MANAGER" size="30" value="<?= htmlspecialchars($find_brand_title)?>" />
    <input type="button" value="..." onClick="window.open('/bitrix/admin/user_search.php?lang=ru&FC=find_MANAGER&set_filter=Y', '', 'scrollbars=yes,resizable=yes,width=760,height=500,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 760)/2-5));">
  </td>
</tr>
<?}?>
<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"find_form"));
$oFilter->End();
?>
</form>

<?
// выведем таблицу списка элементов
$lAdmin->DisplayList();

require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');