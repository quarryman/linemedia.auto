<? require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php'); ?>
<?$APPLICATION->IncludeComponent("linemedia.auto:tecdoc.catalog2", "brands", array(
    "DETAIL_URL" => "/auto/part-detail/#ARTICLE_ID#/#ARTICLE_LINK_ID#/",
    "COLUMNS_COUNT" => "4",
    "ADD_SECTIONS_CHAIN" => "Y",
    "SHOW_ORIGINAL_ITEMS" => "Y",
    "GROUP_MODELS" => "N",
    "TECDOC_BRAND_TYPES" => array(
        0 => "1",
        1 => "2",
        2 => "3",
    ),
    "MODIFICATIONS_SET" => "default",
    "HIDE_UNAVAILABLE" => "N",
    "DISABLE_STATS" => "N",
    "INCLUDE_PARTS_IMAGES" => "Y",
    "SEF_FOLDER" => "/auto/tecdoc/",
    "SEF_MODE" => "N"
    ),
    false
);?>

<p>
    Мы работаем для вас уже несколько лет. Быстрые поставки и полный ассортимент автозапчастей - вот наш принцип.
</p>
 
<p>
    У нас покупают многие интернет-магазины, частные клиенты и крупные компании.
    Знающие люди говорят, что сложно найти более заманчивые цены и сроки, чем на нашем интернет-складе.
</p>
 
<p>
    Мы работаем по принципу минимальных затрат, что позволяет предложить такие выгодные условия.
    Никаких курьеров, логистических компаний и других транспортных издержек — только самовывоз в удобное для вас время. 
    <br />
</p>
<blockquote> 
    <p><b>Те, кто знает толк в запчастях — покупают запчасти у нас</b></p>
</blockquote>

<hr/>
<img src="/images/importlogos.png" alt="Бренды автозапчастей на интернет-складе Ангар13" />
<hr/>

<? require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php'); ?>