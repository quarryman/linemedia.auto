<?php
// require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/iblock.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");

define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", true);

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/iblock/admin/iblock_element_edit_compat.php");
__IncludeLang(dirname(__FILE__).'/lang/ru/edit.php');
global $USER;

if (!$USER->IsAdmin())
    die('not admin');

if (!CModule::IncludeModule("linemedia.auto"))
    die('no module');

CModule::IncludeModule('iblock');
CModule::IncludeModule('fileman');

$block = (string)$_REQUEST['block'];
$iblock_id = intval($_REQUEST['iblock_id']);
$id = (int)$_REQUEST['id'];
$url = strval($_REQUEST['url']);

$show_h1 = strval($_REQUEST['h1']) == 'Y';
$show_meta =strval($_REQUEST['meta']) == 'Y';

if(check_bitrix_sessid()) {

	$text = strval($_REQUEST[ $block ]);
	$type =(string)$_REQUEST[$block.'_TYPE'];
	if ($action == 'add') {
		$arFields = array(
							'NAME'=>$url,
							'ACTIVE'=>'Y',
							'IBLOCK_ID'=>$iblock_id,
							'PROPERTY_VALUES'=>array(
								$block => array('VALUE'=>array('TYPE'=>$type,'TEXT'=>$text)),
								'title'=> strval($_REQUEST['title']),
								'description'=> strval($_REQUEST['description']),
								'h1'=> strval($_REQUEST['h1']),
								'keywords'=> strval($_REQUEST['keywords'])
							)
						);
		$elem = new CIBlockElement;
		$id = $elem->Add($arFields);
		if (!$id) {
			$error = $elem->LAST_ERROR;
		} else {
			$close_and_reload = true;
		}

	} else if ($action=='update') {
		CIBlockElement::SetPropertyValues($id, $iblock_id, array('VALUE'=>array('TYPE'=>$type,'TEXT'=>$text)), $block);
		if ($show_meta) {
			CIBlockElement::SetPropertyValues($id, $iblock_id, strval($_REQUEST['title']), 'title');
			CIBlockElement::SetPropertyValues($id, $iblock_id, strval($_REQUEST['description']), 'description');
			CIBlockElement::SetPropertyValues($id, $iblock_id, strval($_REQUEST['keywords']), 'keywords');
		}
		if ($show_h1) {
			CIBlockElement::SetPropertyValues($id, $iblock_id, strval($_REQUEST['h1_value']), 'h1');
		}
 		$close_and_reload = true;
	}
}


if($id > 0) {
	$rs = CIBlockElement::GetByID($id);
	$obElement = $rs->GetNextElement();
	$props = $obElement->GetProperties();
	$text = $props[ $block ];
	$type = $text['VALUE']['TYPE'];
	$text = $text['VALUE']['TEXT'];
} else {
	$text = '';
	$type = 'text';
}
// var_dump($id, $text);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
if ($close_and_reload) {
	$obJSPopup = new CJSPopup;
	$obJSPopup->Close();
}
?>
<style>
#lm-auto-seo-block-edit label,
#lm-auto-seo-block-edit input[type=radio]{display:inline-block !important;}
#lm-auto-seo-block-edit .w40p {
    margin-right: 10px;
    text-align: right;
    width: 40%;
}
</style>
<?if(isset($error)) {ShowError($error);}?>
<form action="" method="post" id="lm-auto-seo-block-edit">
<?=bitrix_sessid_post();?>
	<input type="hidden" name="meta" value="<?=$show_meta?'Y':'N'?>">
	<input type="hidden" name="h1" value="<?=$show_h1?'Y':'N'?>">
	<input type="hidden" name="action" value="<?=$id?'update':'add'?>">
	<input type="hidden" name="block" value="<?=$block?>">
	<input type="hidden" name="url" value="<?=$url?>">
	<input type="hidden" name="iblock_id" value="<?=$iblock_id?>">
	<input type="hidden" name="id" value="<?=$id?>">
<table style="width:100%;">
	<tr>
		<td><?=GetMessage('LM_AUTO_BLOCK_TYPE')?>
			<label for="<?=$block?>_TYPE_text"><input type="radio" name="<?=$block?>_TYPE" id="<?=$block?>_TYPE_text" value="text"<?if($type !="html")echo " checked"?>>
			<?echo GetMessage("IBLOCK_DESC_TYPE_TEXT")?></label> /
			<label for="<?=$block?>_TYPE_html"><input type="radio" name="<?=$block?>_TYPE" id="<?=$block?>_TYPE_html" value="html"<?if ($type =="html")echo " checked"?>>
			<?echo GetMessage("IBLOCK_DESC_TYPE_HTML")?></label>
		</td>
	</tr>
	<?/*<tr>
		<td>
			<?CFileMan::AddHTMLEditorFrame(
			$block,
			$text,
			$_REQUEST[$block."_TYPE"],
			'html',
			array(
					'height' => 150,
					'width' => '100%'
				),
			"N",
			0,
			"",
			"",
			SITE_ID,
			true,
			false,
			array(
				'toolbarConfig' => 'public',
				'saveEditorKey' => $iblock_id
			)
			);?>
		</td>
	</tr>*/?>
	<tr>
		<td>
			<?=GetMessage('LM_SEOBLOCK_TEXT')?>
		</td>
	</tr>
		<tr>
			<td>
				<textarea style="width:100%;" rows="10" name="<?=$block?>"><?=$text?></textarea>
			</td>
		</tr>
	<?if($show_meta) {?>
		<tr>
			<td><label class="w40p"><?=GetMessage('LM_AUTO_SEO_TITLE')?></label>
				<input type="text" name="title" value="<?=$props['title']['VALUE']?>">
			</td>
		</tr>
		<tr>
			<td><label class="w40p"><?=GetMessage('LM_AUTO_SEO_DESCRIPTION')?></label>
				<input type="text" name="description" value="<?=$props['description']['VALUE']?>">
			</td>
		</tr>
		<tr>
			<td><label class="w40p"><?=GetMessage('LM_AUTO_SEO_KEYWORDS')?></label>
				<input type="text" rows="10" name="keywords" value="<?=$props['keywords']['VALUE']?>">
			</td>
		</tr>
	<?}?>
	<?if($show_h1) {?>
		<tr>
			<td><label class="w40p"><?=GetMessage('LM_AUTO_SEO_H1')?></label>
				<input type="text" name="h1_value" value="<?=$props['h1']['VALUE']?>">
			</td>
		</tr>
	<?}?>
	</table>
</form>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");?>
