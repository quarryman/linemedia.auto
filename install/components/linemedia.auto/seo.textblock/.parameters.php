<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("iblock"))
    return;

$arIBlocks = array();
$db_iblock = CIBlock::GetList(Array("SORT"=>"ASC"), Array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE"]!="-"?$arCurrentValues["IBLOCK_TYPE"]:"")));
$default_iblock_id = '';
while ($arRes = $db_iblock->Fetch()) {
    $arIBlocks[ $arRes["ID"] ] = $arRes["NAME"];
    if ($arRes['CODE'] == 'seo_anywhere') $default_iblock_id =  $arRes['ID'];
}


$arComponentParameters = array(
    "PARAMETERS" => array(
        "IBLOCK_TYPE" => Array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("LM_AUTO_IBLOCK_TYPE"),
            "TYPE" => "LIST",
            "VALUES" => CIBlockParameters::GetIBlockTypes(Array("-"=>" ")),
            "DEFAULT" => "linemedia_auto",
            "REFRESH" => "Y",
        ),
        'IBLOCK_ID'     =>  array(
            'TYPE'      =>  'LIST',
            'DEFAULT'   =>  $default_iblock_id,
            'PARENT'    =>  'BASE',
            'NAME'      =>  GetMessage('LM_AUTO_IBLOCK_ID'),
            'VALUES'=> $arIBlocks,
            "REFRESH" => "Y"
        ),
        'SET_META'=>array(
                'NAME'=>GetMessage('LM_AUTO_SET_META'),
                'TYPE'=>'CHECKBOX',
                'DEFAULT'=>'N',
                'PARENT'=>'ADDITIONAL'
        ),
        'SET_H1'=>array(
                'NAME'=>GetMessage('LM_AUTO_SET_H1'),
                'TYPE'=>'CHECKBOX',
                'DEFAULT'=>'N',
                'PARENT'=>'ADDITIONAL'
        ),
    )
);

if (intval($arCurrentValues["IBLOCK_ID"]) > 0 ) {
    $rs = CIBlockProperty::GetList(array('SORT'=>'ASC'), array('IBLOCK_ID'=>$arCurrentValues["IBLOCK_ID"],'CODE'=>'SEO_BLOCK_%'));
    $vals = array();
    while($item = $rs->Fetch()) {
        $val[ $item['CODE'] ] = $item['NAME'];
    }
     $arComponentParameters["PARAMETERS"]['WHAT_SHOW'] = array(
            'NAME'=>GetMessage('LM_AUTO_SEO_BLOCK_PROPERTY'),
            'TYPE'=>'LIST',
            'MULTIPLE'=>'N',
            'DEFAULT'=>'',
            'VALUES'=>$val
        );
}
