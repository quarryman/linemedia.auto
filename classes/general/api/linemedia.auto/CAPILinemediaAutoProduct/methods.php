<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * Linemedia API
 * Main auto module
 * Module events
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://www.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);


/*
* Методы работы с поиском
*/
$methods["CAPILinemediaAutoProduct"] = array(
	'LinemediaAutoProduct_Add' => array(
		"type"      => "public",
		"name"      => "Add",
		"input"     => array(
			'products' 		 => array("varType" => "ArrayOfStruct_LinemediaAuto_Product", "arrType" => "Struct_LinemediaAuto_Product"),
		),
		"output"    => array(
			"ok" => array("varType" => "bool")
		),
		'desc' => GetMessage('LM_API_FNC_ADD_DESCR'),
	),
	'LinemediaAutoProduct_Delete' => array(
		"type"      => "public",
		"name"      => "Delete",
		"input"     => array(
			'filter'		 => array("varType" => "ArrayOfStruct_AssocArray", "arrType" => "Struct_AssocArray"),
		),
		"output"    => array(
			"ok" => array("varType" => "bool")
		),
		'desc' => GetMessage('LM_API_FNC_DEL_DESCR'),
	),
	
);
