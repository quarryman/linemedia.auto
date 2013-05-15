<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

IncludeModuleLangFile(__FILE__);

/*
 * Установка шаблонов.
 */

 
/*
 * Получим список сайтов системы.
 */
$arTemplates = array();
$rsTemplates = CSiteTemplate::GetList();
while ($arTemplate = $rsTemplates->Fetch()) {
    $arTemplates []= $arTemplate;
}


/*
 * Копирование шаблонов.
 */