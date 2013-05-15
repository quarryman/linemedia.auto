<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * Linemedia API
 * Sphinx module
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
$methods["CAPILinemediaAutoSearch"] = array(
	'LinemediaAuto_Search' => array(
		"type"      => "public",
		"name"      => "Search",
		"input"     => array(
			'query' 		=> array("varType" => "string", "strict" => "strict"),
			'brand_title' 	=> array("varType" => "string", "strict" => "no"),
			'extra' 		=> array("varType" => "ArrayOfStruct_AssocArray", "arrType" => "Struct_AssocArray", "strict" => "no"),
			'type' 			=> array("varType" => "string", "strict" => "no"),
		),
		"output"    => array(
			"search_results" => array("varType" => "Struct_LinemediaAuto_SearchResults")
		),
		'desc' => GetMessage('LM_API_FNC_SEARCH_DESCR'),
	)
);
