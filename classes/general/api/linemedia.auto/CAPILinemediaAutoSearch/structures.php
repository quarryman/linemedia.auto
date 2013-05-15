<?php


/*
* Запчасть
*/
$structures["Struct_LinemediaAuto_SearchPart"] = array(
    "article" 		=> array("varType" => "string"),
    "brand_title" 	=> array("varType" => "string"),
    "price" 		=> array("varType" => "float"),
    "delivery_time" => array("varType" => "integer"),
    "id" 			=> array("varType" => "integer"),
    "title" 		=> array("varType" => "string"),
    "quantity" 		=> array("varType" => "float"),
    "part_search_url" => array("varType" => "string"),
);


/*
* Список аналогов в поисковом ответе
*/
$structures["Struct_LinemediaAuto_SearchAnalogs"] = array(
    "analog_type" 	=> array("varType" => "string"),
    "parts" 		=> array("varType" => "ArrayOfStruct_LinemediaAuto_SearchPart", "arrType" => "Struct_LinemediaAuto_SearchPart"),
);


/*
* Каталог в поисковом ответе
*/
$structures["Struct_LinemediaAuto_SearchCatalog"] = array(
    "title" 		=> array("varType" => "string"),
    "brand_title" 	=> array("varType" => "string"),
    "data-source" 	=> array("varType" => "string"),
    "extra" 		=> array("varType" => "ArrayOfStruct_AssocArray", "arrType" => "Struct_AssocArray"),
);

/*
* Поисковый ответ
*/
$structures["Struct_LinemediaAuto_SearchResults"] = array(
    "type"		=> array("varType" => "string"),
    "parts"		=> array("varType" => "ArrayOfStruct_LinemediaAuto_SearchAnalogs", "arrType" => "Struct_LinemediaAuto_SearchAnalogs"),
    "catalogs"	=> array("varType" => "ArrayOfStruct_LinemediaAuto_SearchCatalog", "arrType" => "Struct_LinemediaAuto_SearchCatalog"),
);