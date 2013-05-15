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



global $DB;



/***********************************************************/
$sTableID = "b_lm_wordforms"; // ID таблицы
$oSort = new CAdminSorting($sTableID, "group", "asc"); // объект сортировки
$lAdmin = new CAdminList($sTableID, $oSort); // основной объект списка

// проверку значений фильтра дл€ удобства вынесем в отдельную функцию
function CheckFilter()
{
	global $FilterArr, $lAdmin;
	foreach ($FilterArr as $f) global $$f;
	
	return count($lAdmin->arFilterErrors) == 0; // если ошибки есть, вернем false;
}

// опишем элементы фильтра
$FilterArr = Array(
	"find_group",
	"find_brand_title",
);

// инициализируем фильтр
$lAdmin->InitFilter($FilterArr);

// если все значени€ фильтра корректны, обработаем его
if (CheckFilter()) {
	// создадим массив фильтрации дл€ выборки LinemediaautoBrand::GetList() на основе значений фильтра
	$arFilter = array(
		"group"  		=> $find_group,
		"brand_title"	=> $find_brand_title,
	);
}

// обработка одиночных и групповых действий
if (($arID = $lAdmin->GroupAction()) && $POST_RIGHT == "W") {
	// если выбрано "ƒл€ всех элементов"
	
	if ($_REQUEST['action_target']=='selected') {
		switch ($_REQUEST['action']) {
    		// удаление
    		case "delete":
    			@set_time_limit(0);
    			$DB->StartTransaction();
    			
    			$DB->Query('DELETE FROM b_lm_wordforms');
    			$wordform->clearGroup('');// clear cache
    			
    			$DB->Commit();
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
    			
    			
    			
    			$wordform = new LinemediaAutoWordForm;
    			$wordform->clearGroup($ID);
    			
    		  break;
    	}
	}
}


$where = array('1');
foreach($arFilter AS $code => $val)
{
	$val = trim($val);
	if($val != '')
	{
		$val = $DB->ForSQL($val);
		$where[] = "$code LIKE '%$val%'";
	}
}
$where_str = join(' AND ', $where);

$rsData = $DB->Query("SELECT `group`, GROUP_CONCAT(brand_title SEPARATOR ', ') AS brand_titles FROM b_lm_wordforms WHERE $where_str GROUP BY `group` LIMIT 500");


// преобразуем список в экземпл€р класса CAdminResult
$rsData = new CAdminResult($rsData, $sTableID);

// аналогично CDBResult инициализируем постраничную навигацию.
$rsData->NavStart();

// отправим вывод переключател€ страниц в основной объект $lAdmin
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("LM_AUTO_MAIN_BRANDS_NAV")));






$lAdmin->AddHeaders(array(
  array(  "id"    =>"group",
    "content"  =>GetMessage("LM_AUTO_MAIN_GROUP"),
    "sort"     =>"group",
    "default"  =>true,
  ),
  array(  "id"    =>"brand_titles",
    "content"  => GetMessage("LM_AUTO_MAIN_BRAND_TITLES"),
    "sort"     =>"brand_titles",
    "default"  =>true,
  ),
));



$lines = 0;
while ($arRes = $rsData->NavNext(true, "f_")) {
  $lines++;
  
  if($lines > 500)
  	break;
  
  // создаем строку. результат - экземпл€р класса CAdminListRow
  $row =& $lAdmin->AddRow($f_group, $arRes);
  

    
  // сформируем контекстное меню
  $arActions = Array();
  
  // удаление элемента
    $arActions[] = array(
      "ICON"=>"delete",
      "TEXT"=>GetMessage("LM_AUTO_MAIN_DEL"),
      "ACTION"=>"if(confirm('".GetMessage('LM_AUTO_MAIN_CONFIRM_DEL')."')) ".$lAdmin->ActionDoGroup($f_group, "delete")
    );
    // –едактирование элемента.
    $arActions[] = array(
      "ICON"    => "edit",
      "TEXT"    => GetMessage("LM_AUTO_MAIN_EDIT"),
      "ACTION"  => $lAdmin->ActionRedirect("/bitrix/admin/linemedia.auto_wordforms_add.php?ID=$f_group&lang=" . LANG),
      "DEFAULT" => true
    );
  
  // применим контекстное меню к строке
  $row->AddActions($arActions);
}





// групповые действи€
$lAdmin->AddGroupActionTable(Array(
  "delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"), // удалить выбранные элементы
  ));





// сформируем меню из одного пункта - добавление рассылки
$aContext = array(
  array(
    "TEXT"=>GetMessage("LM_AUTO_MAIN_ADD"),
    "LINK"=>"/bitrix/admin/linemedia.auto_wordforms_add.php?lang=" . LANG,
    "TITLE"=>GetMessage("LM_AUTO_MAIN_ADD"),
    "ICON"=>"btn_new",
  ),
);


// и прикрепим его к списку
$lAdmin->AddAdminContextMenu($aContext);





CUtil::InitJSCore(array('window'));


// альтернативный вывод
$lAdmin->CheckListMode();


// установим заголовок страницы
$APPLICATION->SetTitle(GetMessage('LM_AUTO_MAIN_WORDFORMS_TITLE'));


// не забудем разделить подготовку данных и вывод
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");






$APPLICATION->AddHeadScript('http://yandex.st/jquery/1.8.0/jquery.min.js');






// создадим объект фильтра
$oFilter = new CAdminFilter(
  $sTableID."_filter",
  array(
    GetMessage("LM_AUTO_MAIN_GROUP"),
    GetMessage("LM_AUTO_MAIN_BRAND_TITLE"),
  )
);
?>
<form name="find_form" method="get" action="<?= $APPLICATION->GetCurPage();?>">
<? $oFilter->Begin(); ?>
<tr>
  <td><?=GetMessage("LM_AUTO_MAIN_GROUP").":"?></td>
  <td><input type="text" name="find_group" size="25" value="<?= htmlspecialchars($find_group)?>" /></td>
</tr>
<tr>
  <td><?=GetMessage("LM_AUTO_MAIN_BRAND_TITLE").":"?></td>
  <td><input type="text" name="find_brand_title" size="30" value="<?= htmlspecialchars($find_brand_title)?>" /></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"find_form"));
$oFilter->End();
?>
</form>





<?
// выведем таблицу списка элементов
$lAdmin->DisplayList();

require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');

