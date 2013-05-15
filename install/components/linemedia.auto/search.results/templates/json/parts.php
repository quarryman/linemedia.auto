<?php


foreach ($arResult['PARTS'] as $y => $parts) {
    foreach ($parts as $i => $part) {
        
        $arResult['PARTS'][$y][$i] = array(
        	'id' => $part['id'],
        	'title' => $part['title'],
        	'article' => ($part['original_article']) ? $part['original_article'] : $part['article'],
        	'brand_title' => $part['brand_title'],
        	'price_src' => $part['price_src'],
        	'quantity' => $part['quantity'],
        	'delivery' => $part['delivery'],
        	'modified' => $part['modified'],
        	'weight' => $part['weight'],
        );
        
    }
}
header('Content-type: application/json');
echo json_encode(array('parts' => $arResult['PARTS']));
exit();
