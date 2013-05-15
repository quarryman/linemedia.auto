<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();


if(!CModule::IncludeModule('linemedia.auto')) {
    ShowError('No module "linemedia.auto"');
}

$suppliers = array();

$rs = LinemediaAutoSupplier::GetList();

foreach($rs as $id=>$val) {
    $suppliers[ $id ] = '['.$id.'] '.$val['NAME'];
}

$arComponentParameters = array(
    "PARAMETERS" => array(
        'SUPPLIER_ID'     =>  array(
            'TYPE'      =>  'LIST',
            'DEFAULT'   =>  '-1',
            'PARENT'    =>  'BASE',
            'NAME'      =>  GetMessage('LM_AUTO_SUPPLIER_ID'),
            'VALUES'    =>  $suppliers
        )
    )
);