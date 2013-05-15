<?php
require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';
$APPLICATION->IncludeComponent('linemedia.auto:supplier.reliability.statistic','', array('AJAX'=>'Y', 'SUPPLIER_ID'=>$_REQUEST['supplier_id']), false);
require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php';