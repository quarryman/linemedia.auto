<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle("Поиск");
?>
<?
$APPLICATION->IncludeComponent("linemedia.auto:tecdoc.catalog", ".default", array(
	"SEARCH_URL" => "#DEMO_FOLDER#search/#ARTICLE_ID#/?brand=#BRAND_ID#",
	"DETAIL_URL" => "#DEMO_FOLDER#part-detail/#ARTICLE_ID#/#ARTICLE_LINK_ID#/",
	"COLUMNS_COUNT" => "4",
	"ADD_SECTIONS_CHAIN" => "Y",
	"SHOW_ORIGINAL_ITEMS" => "Y",
	"ADD_SEO_DATA" => "Y",
	"TECDOC_NEW_URL" => "N",
	"SHOW_CAR_BRANDS_IN_URI" => "N",
	"SEF_FOLDER" => "#DEMO_FOLDER#tecdoc/",
	"SEF_MODE" => "Y"
	),
	false
);
?> 
<? require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php'); ?>
