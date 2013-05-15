<?php
/*
 * компонент выводит автокаталог текдока из нашего API
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();


/*
* Проверка наличия необходимых модулей
*/
if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError(GetMessage("LM_AUTOPORTAL_MODULE_NOT_INSTALL"));
    return;
}

/*
* Подключим на страницу jquery
*/
if ($arParams['INCLUDE_JQUERY'] == 'Y')
	CJSCore::Init(array('jquery'));

/*
* Добавим сборщик статистики Linemedia
*/
if($arParams['DISABLE_STATS'] != 'Y')
	$APPLICATION->AddHeadScript('http://api.auto.linemedia.ru/api.js');







/*
* Защита от парсинга каталога для авторизованного пользователя
*/
if(LinemediaAutoUserHelper::isRobot('LM_AUTO_ORIG_CAT_HIT', 60, 30))
{
	$arResult['ERROR'] = GetMessage('LM_AUTO_ORIGINAL_SCAN_ERROR'); 
	$this->IncludeComponentTemplate('error');
	return;
}






$arUrlTemplates = array(
	"part_details" => "part-info/#ARTICLE_ID#/",
    
    "models" => "index.php",
    "group_types" => "index.php?MODEL=#MODEL#",
    "groups" => "index.php?MODEL=#MODEL#&GROUP_TYPE=#GROUP_TYPE#",
    "group_sections" => "index.php?MODEL=#MODEL#&GROUP_TYPE=#GROUP_TYPE#&GROUP=#GROUP#",
    "parts" => "index.php?MODEL=#MODEL#&GROUP_TYPE=#GROUP_TYPE#&GROUP=#GROUP#&GROUP_SECTION=#GROUP_SECTION#",
);


$arUrlTemplatesSEF = array(
    "part_details" => "part-info/#ARTICLE_ID#/",
    
    "models" => "index.php",
    "group_types" => "#MODEL#/",
    "groups" => "#MODEL#/#GROUP_TYPE#/",
    "group_sections" => "#MODEL#/#GROUP_TYPE#/#GROUP#/",
    "parts" => "#MODEL#/#GROUP_TYPE#/#GROUP#/#GROUP_SECTION#/",
);

$arVariables = array();


/*
 * Обработка адресов.
 */
$url  = $APPLICATION->GetCurPage(true);
if($arParams['SEF_MODE'] == 'Y')
	$arUrlTemplates = $arUrlTemplatesSEF;

$page = CComponentEngine::ParseComponentPath($arParams['SEF_FOLDER'], $arUrlTemplates, $arVariables, $url);


/*
 * Если $page === false, то у нас ни один из $arUrlTemplates не подошёл.
 * Будем считать, что это возможно в случае, когда у нас нет завершающего слеша
 * Поэтому редиректим на страницу со слешем на конце (но только в случае включённого чпу).
 */
if ($page == false && $arParams['SEF_MODE'] == 'Y') {
    $uri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_DEFAULT);
    $path = parse_url($uri, PHP_URL_PATH);
    $path = str_replace('index.php', '', $path);
    if (strrpos($path, '/') != strlen($path)-1) {
        $q = parse_url($uri, PHP_URL_QUERY);
        if (strlen($q)) {
            LocalRedirect($path.'/?'.$q, 1, '301 Moved Permanently');
        } else {
            LocalRedirect($path.'/', 1, '301 Moved Permanently');
        }
        return;
    }
}


/*
* Подключаемся к API
*/
$api = new LinemediaAutoApiDriver();

$arVariables = array_map('intval', $arVariables);





if($page == 'part_details') {



	try {
		$data = $api->query('getOriginalOpelArticleDetails', array('article_id' => $arVariables['ARTICLE_ID']));
	} catch (Exception $e) {
		$arResult['ERROR'] = $e->GetMessage(); 
		$this->IncludeComponentTemplate('error');
		return;
	}
	
	$article = $data['data']['article'];
	
	$article['search_url'] = LinemediaAutoUrlHelper::getPartUrl(array(
        'article' => $article['Article'],
        'brand_title' => $article['Brand'],
    ));
		        
	
	/*
	* Если запчасть не найдена
	*/
	if($article['Article'] == '')
	{
		CHTTP::SetStatus(404);
	}  
	 
    /*
    * Поищем доступные варианты в локальной базе
    */
	$search = new LinemediaAutoSearchSimple();
    $parts = (array) $search->searchLocalDatabaseForPart(array(
        'article' => $article['Article'],
        'brand_title' => $article['Brand']
    ), true);
    
    
    foreach($parts AS $part) {
        $part_obj = new LinemediaAutoPart($part['id']);
        
        /*
         * Посчитаем цену товара
         */
        $price = new LinemediaAutoPrice($part_obj);
        $price_calc = $price->calculate();
        $formatted = CurrencyFormat($price_calc, $price->getCurrency());
        
        $article['PRICES'][$price_calc] = $formatted;
    }
	
	if(count($article['PRICES']) > 0) {
		$article['min_price'] = min(array_keys($article['PRICES']));
		$article['max_price'] = max(array_keys($article['PRICES']));
	}
	
	$arResult['ARTICLE'] = $article;
	$template = 'part_info';




} elseif($page == 'parts') {


	
	try {
		$data = $api->query('getOriginalOpelArticles', array('group_section_id' => $arVariables['GROUP_SECTION']));
	} catch (Exception $e) {
		$arResult['ERROR'] = $e->GetMessage(); 
		$this->IncludeComponentTemplate('error');
		return;
	}
	
	
	/*
	* Поищем товары в локальной базе
	*/
	$search = new LinemediaAutoSearchSimple();
    $brand_title = $data['data']['brand']['Name'];
    $articles = $data['data']['articles'];
    
    foreach ($articles as $key => $detail) {
        
        /*
        * Это не деталь, а группа
        */
        if($detail['is_group'] == 1)
        {
	        continue;
        }
        
        $articles[$key]['search_url'] = LinemediaAutoUrlHelper::getPartUrl(array(
            'article' => $detail['Article'],
            'brand_title' => $brand_title,
        ));
        
                
        /*
        * Поищем доступные варианты в локальной базе
        */
        $parts = (array) $search->searchLocalDatabaseForPart(array(
            'article' => $detail['Article'],
            'brand_title' => $brand_title
        ), true);
        
        
        
        /*
        * Скрываем товары, которых нет в локальной базе
        */
        if($arParams['HIDE_UNAVAILABLE'] == 'Y' AND count($parts) == 0) {
            unset($articles[$key]);
            continue;
        }
        	
        $articles[$key]['PARTS'] = $parts;
        
                
        foreach($parts AS $part) {
            $part_obj = new LinemediaAutoPart($part['id']);
            
            /*
             * Посчитаем цену товара
             */
            $price = new LinemediaAutoPrice($part_obj);
            $price_calc = $price->calculate();
            $formatted = CurrencyFormat($price_calc, $price->getCurrency());
            
            $articles[$key]['PRICES'][$price_calc] = $formatted;
        }
        
        /*
        * Ссылка на детальную страницу запчасти
        */        
        $articles[$key]['part_info_url'] = $arParams['SEF_FOLDER'] . 'part-info/' . $articles[$key]['ID_art'];
        
        
        
        if(CUser::GetId() < 1)
        {
	        $articles[$key]['Article'] 		= str_pad(substr($articles[$key]['Article'], 0, 2), strlen($articles[$key]['Article']) - 2, '*');
	        $articles[$key]['search_url']	= '?SHOW_AUTH_FORM=1';
	        $articles[$key]['part_info_url']= false;
        }
        
        
        
    }
	
	foreach($articles AS $key => $part)
    {
		if(count($part['PRICES']) > 0) {
    		$articles[$key]['min_price'] = min(array_keys($part['PRICES']));
    		$articles[$key]['max_price'] = max(array_keys($part['PRICES']));
    	}
    	
    }
	
	
	
	$arResult['ARTICLES'] = $articles;
	$arResult['MODEL'] = $data['data']['model'];
	$arResult['GROUP_TYPE'] = $data['data']['group_type'];
	$arResult['GROUP'] = $data['data']['group'];
	$arResult['GROUP_SECTION'] = $data['data']['group_section'];
	$arResult['images_prefix'] = $data['data']['images_prefix'];
	$template = 'articles';
	


	
} elseif($page == 'group_sections') {



	try {
		$data = $api->query('getOriginalOpelGroupSections', array('group_id' => $arVariables['GROUP']));
	} catch (Exception $e) {
		$arResult['ERROR'] = $e->GetMessage(); 
		$this->IncludeComponentTemplate('error');
		return;
	}
	$arResult['GROUP_SECTIONS'] = $data['data']['group_sections'];
	$arResult['MODEL'] = $data['data']['model'];
	$arResult['GROUP_TYPE'] = $data['data']['group_type'];
	$arResult['GROUP'] = $data['data']['group'];
	$arResult['images_prefix'] = $data['data']['images_prefix'];
	$template = 'group_sections';
	
	
	
} elseif($page == 'groups') {
	
	try {
		$data = $api->query('getOriginalOpelGroups', array('group_type_id' => $arVariables['GROUP_TYPE']));
	} catch (Exception $e) {
		$arResult['ERROR'] = $e->GetMessage(); 
		$this->IncludeComponentTemplate('error');
		return;
	}
	$arResult['GROUPS'] = $data['data']['groups'];
	$arResult['MODEL'] = $data['data']['model'];
	$arResult['GROUP_TYPE'] = $data['data']['group_type'];
	$template = 'groups';
	
	
	
	
} elseif($page == 'group_types') {
	
	try {
		$data = $api->query('getOriginalOpelGroupTypes', array('model_id' => $arVariables['MODEL']));
	} catch (Exception $e) {
		$arResult['ERROR'] = $e->GetMessage(); 
		$this->IncludeComponentTemplate('error');
		return;
	}
	$arResult['GROUP_TYPES'] = $data['data']['group_types'];
	$arResult['MODEL'] = $data['data']['model'];
	

	$template = 'group_types';
	
	
	
	
} else {
	
	try {
		$data = $api->query('getOriginalOpelModels', array('brand_id' => $arVariables['BRAND']));
	} catch (Exception $e) {
		$arResult['ERROR'] = $e->GetMessage(); 
		$this->IncludeComponentTemplate('error');
		return;
	}
	$arResult['MODELS'] = $data['data']['models'];

	$arResult['images_prefix'] = $data['data']['images_prefix'];
	$template = 'models';
}



/*
 *  Хлебные крошки.
 */
if ($arParams['ADD_SECTIONS_CHAIN'] == 'Y') {
    
    $APPLICATION->SetTitle(GetMessage('LM_AUTOPORTAL_TITLE_CATALOG'));
    $brand_title = 'Opel';
    
    /*
	* Присутствует выбор запчасти (детальная информация)
	*/
    if ($arVariables['ARTICLE_ID'] != '') {
    	$article_title = $data['data']['article']['Brand'] . ' ' .$data['data']['article']['Article'];
	    $APPLICATION->AddChainItem(GetMessage('LM_AUTOPORTAL_PART_INFO_FOR').$article_title, $arParams['SEF_FOLDER'].'part-info/'.$arVariables['ARTICLE_ID'].'/');
        $APPLICATION->SetTitle(GetMessage('LM_AUTOPORTAL_PART_INFO_FOR').$article_title);
	}
    
    
	
	/*
	* Присутствует выбор модели
	*/
	if($arVariables['MODEL'] != '') {
		$model_title = $data['data']['model']['Name'];
		$APPLICATION->AddChainItem($model_title, $arParams['SEF_FOLDER'].$arVariables['MODEL'].'/');
        $APPLICATION->SetTitle(GetMessage('LM_AUTOPORTAL_CATALOG_FOR').$brand_title.' '.$model_title);
	}
	
	
	/*
	* Присутствует выбор типа группы
	*/
	if($arVariables['GROUP_TYPE'] != '') {
		$group_type_title = $data['data']['group_type']['Name'];
		$APPLICATION->AddChainItem($group_type_title, $arParams['SEF_FOLDER'].$arVariables['MODEL'].'/'.$arVariables['GROUP_TYPE'].'/');
        $APPLICATION->SetTitle($group_type_title . ' ' .$brand_title.' '.$model_title);
	}
	
	/*
	* Присутствует выбор группы
	*/
	if($arVariables['GROUP'] != '') {
		$group_title = $data['data']['group']['Name'];
		$APPLICATION->AddChainItem($group_title, $arParams['SEF_FOLDER'].$arVariables['MODEL'].'/'.$arVariables['GROUP_TYPE'].'/'.$arVariables['GROUP'].'/');
        $APPLICATION->SetTitle($group_title . ' ' .$brand_title.' '.$model_title);
	}
	
	
	/*
	* Присутствует выбор секции группы
	*/
	if($arVariables['GROUP_SECTION'] != '') {
		$group_section_title = $data['data']['group_section']['Name'];
		$APPLICATION->AddChainItem($group_section_title, $arParams['SEF_FOLDER'].$arVariables['MODEL'].'/'.$arVariables['GROUP_TYPE'].'/'.$arVariables['GROUP'].'/'.$arVariables['GROUP_SECTION'].'/');
        $APPLICATION->SetTitle($group_section_title . ' ' .$brand_title.' '.$model_title);
	}
	    
    
}


/*
* Форма авторизации для незарегистрированных пользователей
*/
if(CUser::GetID() < 1 AND isset($_REQUEST['SHOW_AUTH_FORM']))
{
	$APPLICATION->AuthForm(GetMessage('LM_AUTO_ORIGINAL_NEED_AUTH'));
} else {
	/*
	* Подключение шаблона
	*/
	$this->IncludeComponentTemplate($template);
}