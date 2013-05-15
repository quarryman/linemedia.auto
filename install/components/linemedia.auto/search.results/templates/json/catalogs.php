<?php

foreach ($arResult['CATALOGS'] as &$catalog) {
    unset($catalog['url']);
}
header('Content-type: application/json');
echo json_encode(array('catalogs' => $arResult['CATALOGS']));
exit();