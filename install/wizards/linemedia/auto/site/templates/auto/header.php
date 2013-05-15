<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= LANGUAGE_ID ?>" lang="<?= LANGUAGE_ID ?>">
<head>
    <title><? $APPLICATION->ShowTitle() ?></title>
    
    <link rel="stylesheet" type="text/css" href="<?= SITE_TEMPLATE_PATH ?>/bootstrap/css/bootstrap.css" />
    
    <link rel="stylesheet" type="text/css" href="http://yandex.st/jquery-ui/1.8.15/themes/humanity/jquery.ui.all.min.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?= SITE_TEMPLATE_PATH ?>/css/smoothness/jquery.fancybox-1.3.4.css" />
    
    <link rel="shortcut icon" type="image/x-icon" href="/bitrix/templates/bootstrap/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="<?=SITE_TEMPLATE_PATH?>/css/smoothness/jquery-ui-1.8.13.custom.css" />
    
    <script src="<?= SITE_TEMPLATE_PATH ?>/js/jquery-1.5.1.min.js" type="text/javascript"></script>
    <script src="http://yandex.st/jquery-ui/1.8.16/jquery-ui.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="<?= SITE_TEMPLATE_PATH ?>/js/script.js"></script>
    <script type="text/javascript" src="http://yandex.st/jquery/fancybox/1.3.4/jquery.fancybox.min.js"></script>
    
    <? $APPLICATION->ShowHead(); ?>
</head>
<body>

<? IncludeTemplateLangFile(__FILE__); ?>

<div id="panel"><? $APPLICATION->ShowPanel(); ?></div>

<div class="bg_tile">
    <div class="navbar">
      <div class="navbar-inner">
        <div class="container">
            <?  // Меню.
                $APPLICATION->IncludeComponent(
                    "bitrix:menu",
                    "horizontal",
                    array(
                        "ROOT_MENU_TYPE" => "topauto",
                        "MENU_CACHE_TYPE" => "Y",
                        "MENU_CACHE_TIME" => "36000000",
                        "MENU_CACHE_USE_GROUPS" => "Y",
                        "MENU_CACHE_GET_VARS" => array(),
                        "MAX_LEVEL" => "1",
                        "USE_EXT" => "N",
                        "ALLOW_MULTI_SELECT" => "N"
                    )
                );
            ?>
            <?  // Ссылки пользователя.
                $APPLICATION->IncludeFile(
                    SITE_DIR."include/user_links.php",
                    array(),
                    array("MODE" => "text")
                );
            ?>
        </div>
      </div>
    </div>
    
    <div class="container">
        <div class="row">
            <div class="span6">
                <?  // Название компании.
                    $APPLICATION->IncludeComponent(
                        "bitrix:main.include",
                        "",
                        array(
                            "AREA_FILE_SHOW" => "file",
                            "PATH" => SITE_DIR."include/logo.php"
                        ),
                        false
                    );
                ?>
            </div>
            
            <div class="span3">
                <div class="box">
                    <?  // Телефон
                        $APPLICATION->IncludeComponent(
                            "bitrix:main.include",
                            "",
                            array(
                                "AREA_FILE_SHOW" => "file",
                                "PATH" => SITE_DIR."include/phone.php"
                            ),
                            false
                        );
                    ?>
                </div>
            </div>
            
            <div class="span3">
                <div class="box">
                    <?  // Режим работы.
                        $APPLICATION->IncludeComponent(
                            "bitrix:main.include",
                            "",
                            array(
                                "AREA_FILE_SHOW" => "file",
                                "PATH" => SITE_DIR."include/schedule.php"
                            ),
                            false
                        );
                    ?>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="span3">
                <div class="row">
                    <div class="span3">
                        <div class="box">
                            <?  // Форма авторизации.
                                $APPLICATION->IncludeComponent(
                                    "bitrix:system.auth.form",
                                    "left",
                                    array(
                                        "REGISTER_URL" => SITE_DIR."login/",
                                        "FORGOT_PASSWORD_URL" => "",
                                        "PROFILE_URL" => SITE_DIR."personal/",
                                        "SHOW_ERRORS" => "N"
                                   ),
                                   false
                                );
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="span3">
                        <div class="box basket_small">                          
                            <?  // Корзина.
                                $APPLICATION->IncludeComponent("bitrix:sale.basket.basket.small", "left", array(
    "PATH_TO_BASKET" => "/auto/cart/",
    "PATH_TO_ORDER" => "/auto/order/"
    ),
    false
);
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="span3">
                        <div class="h3"><?= GetMessage('CATALOGS') ?></div>
                        <?  
                            $APPLICATION->IncludeComponent(
                                "bitrix:menu",
                                "tabs-left",
                                array(
                                    "ROOT_MENU_TYPE" => "auto.catalogs",
                                    "MENU_CACHE_TYPE" => "Y",
                                    "MENU_CACHE_TIME" => "36000000",
                                    "MENU_CACHE_USE_GROUPS" => "Y",
                                    "MENU_CACHE_GET_VARS" => array(),
                                    "MAX_LEVEL" => "1",
                                    "CHILD_MENU_TYPE" => "catalogs",
                                    "USE_EXT" => "N",
                                    "DELAY" => "N",
                                    "ALLOW_MULTI_SELECT" => "N"
                                ),
                                false
                            );
                        ?>
                    </div>
                </div>
                
                <? if ($USER->IsAuthorized()) { ?>
                    <div class="row">
                        <div class="span3">
                            <div class="h3"><?= GetMessage('PERSONAL_INFO') ?></div>
                            <?  
                                $APPLICATION->IncludeComponent(
                                    "bitrix:menu",
                                    "tabs-left",
                                    array(
                                        "ROOT_MENU_TYPE" => "auto.personal",
                                        "MENU_CACHE_TYPE" => "Y",
                                        "MENU_CACHE_TIME" => "36000000",
                                        "MENU_CACHE_USE_GROUPS" => "Y",
                                        "MENU_CACHE_GET_VARS" => array(),
                                        "MAX_LEVEL" => "1",
                                        "CHILD_MENU_TYPE" => "catalogs",
                                        "USE_EXT" => "N",
                                        "DELAY" => "N",
                                        "ALLOW_MULTI_SELECT" => "N"
                                    ),
                                    false
                                );
                            ?>
                        </div>
                    </div>
                <? } ?>
                
            </div>
            
            <div class="span9">
                <div class="row">
                    <div class="span9 search_box">
                        <div class="box">
                            <?  // Поиск в шапке сайта
                                $APPLICATION->IncludeComponent("linemedia.auto:search.results", ".default", array(
    "QUERY" => $_REQUEST["q"],
    "PART_ID" => $_REQUEST["part_id"],
    "BRAND_TITLE" => $_REQUEST["brand_title"],
    "EXTRA" => $_REQUEST["extra"],
    "BASKET_URL" => "/auto/cart/",
    "VIN_URL" => "/auto/vin/",
    "TITLE" => "Поиск запчасти #QUERY#",
    "REMOTE_SUPPLIERS_AJAX" => array(
    ),
    "HIDE_FIELDS" => array(
    ),
    "REMAPPING" => "N",
    "SHOW_BLOCKS" => "form",
    "SET_TITLE" => "Y"
    ),
    false
);
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div id="workarea" class="span9">
                        <?  // Хлебные крошки.
                            $APPLICATION->IncludeComponent(
                                "bitrix:breadcrumb",
                                ".default",
                                array(
                                    "START_FROM" => "1",
                                    "PATH" => "",
                                    "SITE_ID" => "-"
                                ),
                                false,
                                array('HIDE_ICONS' => 'Y')
                            );
                        ?>
