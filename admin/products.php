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

/*
 * Максимальное количество строк для отображения в списке.
 */
define('LM_AUTO_SHOW_PRODUCTS_LIMIT', 500);


$database = new LinemediaAutoDatabase();


/***********************************************************/
$sTableID = "b_lm_products"; // ID таблицы
$oSort = new CAdminSorting($sTableID, "title", "asc"); // объект сортировки
$lAdmin = new CAdminList($sTableID, $oSort); // основной объект списка

// проверку значений фильтра для удобства вынесем в отдельную функцию
function CheckFilter()
{
	global $FilterArr, $lAdmin;
	foreach ($FilterArr as $f) global $$f;
	
	return count($lAdmin->arFilterErrors) == 0; // если ошибки есть, вернем false;
}

// опишем элементы фильтра
$FilterArr = Array(
	"find_article",
	"find_id",
	"find_original_article",
	"find_title",
	"find_brand_title",
	"find_price",
	"find_weight",
	"find_supplier_id",
);

// инициализируем фильтр
$lAdmin->InitFilter($FilterArr);

// если все значения фильтра корректны, обработаем его
if (CheckFilter()) {
	// создадим массив фильтрации для выборки LinemediaautoBrand::GetList() на основе значений фильтра
	$arFilter = array(
		"article"  		     => $find_article,
		"id"      		     => $find_id,
		"original_article"   => $find_original_article,
		"title"	  		     => $find_title,
		"brand_title"	     => $find_brand_title,
		"price"	  		     => $find_price,
		"weight"	  	     => $find_weight,
		"supplier_id"	     => $find_supplier_id,
	);
}

// обработка одиночных и групповых действий
if (($arID = $lAdmin->GroupAction()) && $POST_RIGHT == "W") {
	// если выбрано "Для всех элементов"
	
	if ($_REQUEST['action_target'] == 'selected') {
		switch ($_REQUEST['action']) {
    		// удаление
    		case "delete":
    			@set_time_limit(0);
    			$database->StartTransaction();
    			$database->Query('DELETE FROM `b_lm_products`;');
    			$database->Commit();
                break;
    	}
	}

	// пройдем по списку элементов
	foreach ($arID as $ID) {
		if (strlen($ID) <= 0) {
			continue;
        }
	   	$ID = IntVal($ID);
		
		// для каждого элемента совершим требуемое действие
		switch ($_REQUEST['action']) {
    		// удаление
    		case "delete":
    			@set_time_limit(0);
    			$database->StartTransaction();
    			
    			$database->Query('DELETE FROM `b_lm_products` WHERE `id` = ' . $ID . ' LIMIT 1;');
    			
    			$database->Commit();
    		  break;
    	}
	}
}


$where = array('1');
foreach ($arFilter as $code => $val) {
	$val = trim($val);
	if ($val != '') {
		$val = $database->ForSQL($val);
		$where[] = "$code = '$val'";
	}
}
$where_str = join(' AND ', $where);

$sort_by     = (!empty($_REQUEST['by'])) ? ((string) $_REQUEST['by']) : ('id');
$sort_order  = ($_REQUEST['order'] == 'desc') ? ('DESC') : ('ASC');

$dbData = $database->Query("SELECT * FROM `b_lm_products` WHERE $where_str ORDER BY `$sort_by` $sort_order;");

// преобразуем список в экземпляр класса CAdminResult
$rsData = new CAdminResult($dbData, $sTableID);

// аналогично CDBResult инициализируем постраничную навигацию.
$rsData->NavStart(array('bShowAll' => false), false);

// отправим вывод переключателя страниц в основной объект $lAdmin
$lAdmin->NavText($rsData->GetNavPrint(GetMessage('LM_AUTO_MAIN_BRANDS_NAV')));


$arHeaders = array(
  array(  "id"    =>"id",
    "content"  =>"ID",
    "sort"     =>"id",
    "default"  =>true,
  ),
  array(  "id"    =>"title",
    "content"  => GetMessage("LM_AUTO_MAIN_TITLE"),
    "sort"     =>"title",
    "default"  =>true,
  ),
  array(  "id"    =>"article",
    "content"  =>GetMessage("LM_AUTO_MAIN_ARTICLE"),
    "sort"     =>"article",
    "default"  =>true,
  ),
  array(  "id"    =>"original_article",
    "content"  =>GetMessage("LM_AUTO_MAIN_ORIGINAL_ARTICLE"),
    "sort"     =>"original_article",
    "default"  =>true,
  ),
  
  array(  "id"    =>"brand_title",
    "content"  => GetMessage("LM_AUTO_MAIN_BRAND_TITLE"),
    "sort"     =>"brand_title",
    "default"  =>true,
  ),
  array(  "id"    =>"price",
    "content"  => GetMessage("LM_AUTO_MAIN_PRICE"),
    "sort"     =>"price",
    "default"  =>true,
  ),
  array(  "id"    =>"quantity",
    "content"  => GetMessage("LM_AUTO_MAIN_QUANTITY"),
    "sort"     =>"quantity",
    "default"  =>true,
  ),
  array(  "id"    =>"group_id",
    "content"  => GetMessage("LM_AUTO_MAIN_GROUP"),
    "sort"     =>"group_id",
    "default"  =>false,
  ),
  array(  "id"    =>"weight",
    "content"  => GetMessage("LM_AUTO_MAIN_WEIGHT"),
    "sort"     =>"weight",
    "default"  =>true,
  ),
  array(  "id"    =>"supplier_id",
    "content"  => GetMessage("LM_AUTO_MAIN_SUPPLIER_ID"),
    "sort"     =>"supplier_id",
    "default"  =>true,
  ),
  array(  "id"    =>"modified",
    "content"  => GetMessage("LM_AUTO_MAIN_MODIFIED"),
    "sort"     =>"modified",
    "default"  =>true,
  ),
);


/*
 * Пользовательские поля.
 */
$lmfields = new LinemediaAutoCustomFields();
         
$custom_fields = $lmfields->getFields();

foreach ($custom_fields as $custom_field) {
    $arHeaders []= array(
        "id"        => $custom_field['code'],
        "content"   => $custom_field['name'],
        "sort"      => $custom_field['code'],
        "default"   => false,
    );
}


$lAdmin->AddHeaders($arHeaders);


/*
 * Поставщики
 */
$suppliers_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
$suppliers = array();
$suppliers_res = LinemediaAutoSupplier::GetList();
foreach ($suppliers_res as $supplier) {
	$suppliers[$supplier['PROPS']['supplier_id']['VALUE']] = $supplier;
}


$lines = 0;
while ($arRes = $rsData->NavNext(true, "f_")) {
    if (++$lines > LM_AUTO_SHOW_PRODUCTS_LIMIT) {
        break;
    }
    
    // Создаем строку. Результат - экземпляр класса CAdminListRow.
    $row =& $lAdmin->AddRow($f_id, $arRes);
    
    $supplier = $suppliers[$f_supplier_id];
    $row->AddViewField("supplier_id", "[<a href='/bitrix/admin/iblock_element_edit.php?ID=" . $supplier['ID'] . "&type=linemedia_auto&lang=ru&IBLOCK_ID=" . $suppliers_iblock_id . "&find_section_section=0'>$f_supplier_id</a>] " . $supplier['NAME']);
    
    // Сформируем контекстное меню.
    $arActions = array();
    
    // Редактирование элемента.
    $arActions []= array(
        'ICON' => 'edit',
        'DEFAULT' => true,
        'TEXT' => GetMessage('LM_AUTO_MAIN_EDIT'),
        'ACTION' => $lAdmin->ActionRedirect("linemedia.auto_part_edit.php?ID=".$f_id."&lang=".LANGUAGE_ID)
    );
    
    // Удаление элемента.
    $arActions []= array(
        "ICON"   => "delete",
        "TEXT"   => GetMessage("LM_AUTO_MAIN_DEL"),
        "ACTION" => "if(confirm('".GetMessage('LM_AUTO_MAIN_CONFIRM_DEL')."')) ".$lAdmin->ActionDoGroup($f_id, "delete")
    );
    
    
    // Применим контекстное меню к строке.
    $row->AddActions($arActions);
}


// сформируем меню из одного пункта - добавление рассылки
$aContext = array(
    array(
        "TEXT" => GetMessage("LM_AUTO_MAIN_ADD"),
        "LINK" => "/bitrix/admin/linemedia.auto_part_edit.php",
        "TITLE" => GetMessage("LM_AUTO_MAIN_ADD"),
        "ICON" => "btn_new",
    ),
);

// Прикрепим его к списку
$lAdmin->AddAdminContextMenu($aContext);


// Групповые действия.
$lAdmin->AddGroupActionTable(Array(
    "delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"), // удалить выбранные элементы
));

CUtil::InitJSCore(array('window'));


// Альтернативный вывод.
$lAdmin->CheckListMode();


// установим заголовок страницы
$APPLICATION->SetTitle(GetMessage('LM_AUTO_MAIN_PRODUCTS_TITLE'));


// не забудем разделить подготовку данных и вывод
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");



$APPLICATION->AddHeadScript('http://yandex.st/jquery/1.8.0/jquery.min.js');


// создадим объект фильтра
$oFilter = new CAdminFilter(
  $sTableID."_filter",
  array(
    GetMessage("LM_AUTO_MAIN_ARTICLE"),
    "ID",
    GetMessage("LM_AUTO_MAIN_ORIGINAL_ARTICLE"),
    GetMessage("LM_AUTO_MAIN_TITLE"),
    GetMessage("LM_AUTO_MAIN_BRAND_TITLE"),
    GetMessage("LM_AUTO_MAIN_PRICE"),
    GetMessage("LM_AUTO_MAIN_WEIGHT"),
    GetMessage("LM_AUTO_MAIN_SUPPLIER_ID"),
  )
);
?>
<form name="find_form" method="get" action="<?= $APPLICATION->GetCurPage();?>">
<? $oFilter->Begin(); ?>
<tr>
  <td><?=GetMessage("LM_AUTO_MAIN_ARTICLE").":"?></td>
  <td><input type="text" name="find_article" size="25" value="<?= htmlspecialchars($find_article)?>" /></td>
</tr>
<tr>
  <td>ID:</td>
  <td><input type="text" name="find_id" size="7" value="<?= htmlspecialchars($find_id)?>" /></td>
</tr>
<tr>
  <td><?=GetMessage("LM_AUTO_MAIN_ORIGINAL_ARTICLE").":"?></td>
  <td><input type="text" name="find_original_article" size="25" value="<?= htmlspecialchars($find_original_article)?>" /></td>
</tr>
<tr>
  <td><?=GetMessage("LM_AUTO_MAIN_TITLE").":"?></td>
  <td><input type="text" name="find_title" size="50" value="<?= htmlspecialchars($find_title)?>" /></td>
</tr>
<tr>
  <td><?=GetMessage("LM_AUTO_MAIN_BRAND_TITLE").":"?></td>
  <td><input type="text" name="find_brand_title" size="30" value="<?= htmlspecialchars($find_brand_title)?>" /></td>
</tr>
<tr>
  <td><?=GetMessage("LM_AUTO_MAIN_PRICE").":"?></td>
  <td><input type="text" name="find_price" size="15" value="<?= htmlspecialchars($find_price)?>" /></td>
</tr>
<tr>
  <td><?=GetMessage("LM_AUTO_MAIN_WEIGHT").":"?></td>
  <td><input type="text" name="find_weight" size="15" value="<?= htmlspecialchars($find_weight)?>" /></td>
</tr>
<tr>
  <td><?=GetMessage("LM_AUTO_MAIN_SUPPLIER_ID").":"?></td>
  <td>
	  <select name="find_supplier_id">
	  	<option value=""><?=GetMessage('NOT_SELECTED')?></option>
	  	<? foreach ($suppliers as $code => $supplier) { ?>
	  		<option value="<?= $code ?>"<?= (($code == $find_supplier_id) ? " selected" : "") ?>>
	  		   <?= $supplier['NAME'] ?>
            </option>
	  	<? } ?>
	  </select>
  </td>
</tr>

<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"find_form"));
$oFilter->End();
?>
</form>

<?
// выведем таблицу списка элементов
$lAdmin->DisplayList();
?>

<? require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');
