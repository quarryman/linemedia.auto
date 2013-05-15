<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

define('STOP_STATISTICS', true);
define('NO_KEEP_STATISTIC', true);

global $USER;


/*
 * Языковой файл
 */
include('lang/' . LANGUAGE_ID . '/' . basename(__FILE__));

if (!$USER->IsAdmin()) {
    die('not admin');
}
if (!CModule::IncludeModule('linemedia.auto')) {
    die('no module');
}

/*
 * Какое действие надо выполнить?
 */
$action = (string) $_REQUEST['action'];



/*
 * Подключение к АПИ для получение исходных данных
 */
$api = new LinemediaAutoApiDriver();

/*
 * АПИ модификаци
 */
$modifications = new LinemediaAutoApiModifications();
$set_id = (string) $_REQUEST['set_id'];
$modifications->changeSetId($set_id);

/*
 * Сохранение страницы с чекбоксами
 */
if ($action == 'save_all') {
    
    $type       = (string) $_POST['type'];
    $parent_id  = (string) $_POST['parent_id'];
    
    /*
     * Список отмеченных чекбоксов = список видимых брендов
     */
    $visible_ids = array_map('strval', array_keys((array) $_POST[$type]));
    
    /*обрабатываем звёздочку("для всех") для групп*/
    $bUse4All = false;
    if (strpos($parent_id, ':*')!==false) {
        $parent_id = reset(explode(':', $parent_id, 2));
        $bUse4All = true;
    }

    /*
     * Все доступные элементы
     */
    $all_ids = array_filter($modifications->getItemsIds($type, $parent_id));
    
    
    /*
     * Вычислим невидимые бренды
     */
    $invisible_ids = array_diff($all_ids, $visible_ids);
    unset($all_ids);
    

    if ($bUse4All) {
        $parent_id = '*';
    }
    /*
     * Проставим значения
     */
    foreach ($visible_ids as $id) {
        $modifications->setVisibility($type, $id, $parent_id, true);
    }
    
    foreach ($invisible_ids as $id) {
        $modifications->setVisibility($type, $id, $parent_id, false);
    }
    
    die('OK');
}



/*
 * Сохранение всплывающего окна с редактированием одного вызова
 */
if ($action == 'save') {
    $type       = (string) $_POST['type'];
    $source_id  = (string) $_POST['source_id'];
    $parent_id  = (string) $_POST['parent_id'];
    $mod_id     = (string) $_POST['mod_id'];
    $out        = (array)  $_POST['out'];
    
    $custom_code = (array) $_POST['custom']['code'];
    $custom_vals = (array) $_POST['custom']['value'];
    
    foreach ($custom_code as $key => $code) {
        if (empty($code) || empty($custom_vals[$key])) {
            continue;
        }
        $out['user_fields'][$code] = $custom_vals[$key];
    }
    unset($custom_code, $custom_vals);
    
    /*
     * Уберём лишние значения
     */
    if ($out['sort'] == 500) {
        unset($out['sort']);
    }
    if ($out['hidden'] == 'N') {
        unset($out['hidden']);
    }
    $out['image'] = (string) $out['image_path'];
    unset($out['image_path']);
    
    
    /*
     * Удаление текущего изоюражения.
     */
    if (!empty($_POST['image_del']) && $_POST['image_del'] == 'Y') {
        CFile::Delete($out['image_id']);
        unset($out['image']);
        unset($out['image_id']);
    }
    
    /*
     * Сохраним изображение.
     */
    if (!empty($_FILES['image'])) {
        $image_id = CFile::SaveFile($_FILES['image'], '/linemedia.auto/images/upload/');

        $path = CFile::GetPath($image_id);
        if (!empty($path)) {
            $out['image']    = $path;
            $out['image_id'] = $image_id;
        }
    }
    
    /*
     * Обновление.
     */
    if ($mod_id != '') {
        $modifications->updateModificationById($mod_id, $out);
        closeJsPopup();
        exit();
    }

    /*
     * Сохраним только diff.
     * Для этого получим результат вызова из АПИ и сравним сохраняемое с элементом ответа, найденным по ключу.
     */
    $orig_data = $modifications->getItemById($type, $source_id, $parent_id, true);


    $diff = array_diff_assoc_recursive($out, $orig_data);

    /*
    switch ($type) {
        case 'modification':
            $parent_id = explode(':', $parent_id); // brand:model
            $parent_id = $parent_id[1];
            break;
    }
    */
    
    /*
     * А изменений-то нет теперь!
     */
    if (count($diff) == 0) {
        // Удалим изменения из базы.
        $modifications->delModification($type, $source_id, $parent_id);
    } else {
        // Запишем изменения в базу.
        try {
            $modifications->addModification($type, $source_id, $parent_id, $diff);
        } catch (Exception $e) {
            CHTTP::SetStatus('406 Not Acceptable');
            //возвращать "ОК" не подходит -- нам ещё нужно сбросить клиенту кусок js, который закроет диалог.
            // "непримлемо" вполне логичный ответ на ошибочные данные
            die($e->GetMessage());
        }
    }
    
    /*
     * Закроем попап.
     */
     closeJsPopup();
}



/*
 * Удаление созданного вручную элемента.
 */
if ($action == 'delete') {
    $id = (int) $_POST['id'];
    $modifications->deleteModificationById($id);
    die('OK');
}


/*
 * Окно редактирования изменения в АПИ.
 */
if ($action == 'edit_window') {
    
    /*
     * Что именно мы хотим менять?
     */
    $type       = (string) $_GET['type'];
    $source_id  = (string) $_POST['source_id'];
    $parent_id  = (string) $_POST['parent_id'];
    
    /*
     * Если элемент создан вручную, его придётся редактировать по ID, иначе его не отличить от остальных.
     */
    $mod_id  = (string) $_POST['mod_id'];
    
    /*
     * Получим пример данных, желательно из оригинала редактируемого элемента.
     * Для созданных вручную элементов нельзя игнорировать модификации, потому что в текдоке их нет.
     */
    $ignore_mods = ($mod_id == '');
    $data_example = $modifications->getTypeExample($type, $source_id, $parent_id, $ignore_mods);
    
    
    /*
     * Если добавляем новый элемент.
     */
    if ($source_id == '') {
        $result = array();
        $result['source_id'] = ''; // чтобы не прописался чужой ID
    } else {
        /*
         * Получим редактируемый объект.
         */
        $item = $modifications->getItemById($type, $source_id, $parent_id, false);
    }
    ?>
    <form action="/bitrix/components/linemedia.auto/tecdoc.catalog2/ajax.php" method="POST" id="lm-auto-tecdoc-popup-frm" enctype="multipart/form-data">
        <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>" />
        <input type="hidden" name="source_id" value="<?= htmlspecialchars($source_id) ?>" />
        <input type="hidden" name="parent_id" value="<?= htmlspecialchars($parent_id) ?>" />
        <input type="hidden" name="set_id" value="<?= htmlspecialchars($set_id) ?>" />
        <input type="hidden" name="mod_id" value="<?= htmlspecialchars($mod_id) ?>" />
        <input type="hidden" name="action" value="save" id="lm-auto-tecdoc-action" />
        <div><?=GetMessage('LM_AUTO_REQUIRED_FIELDS')?></div>
        <? // Печатаем форму из рекурсивного массива. ?>
        <?= recursivePrintField($data_example, $item, !empty($source_id)); ?>
        
        <div class="lm-auto-popup-edit">
            <label>
                <div><?= GetMessage('LM_AUTO_API_FIELD_IMAGE') ?><span></span></div>
                
                <input type="hidden" name="out[image_path]" value="<?= htmlspecialchars($item['image']) ?>" />
                <input type="hidden" name="out[image_id]" value="<?= htmlspecialchars($item['image_id']) ?>" />
                
                <div class="lm-auto-image-edit">
                    <?= (!empty($item['image'])) ? (CFile::ShowImage($item['image_id'], 50, 50)) : (''); ?>
                </div>
                <?= CFile::InputFile('image', 50, $item['image_id']) ?>
            </label>
        </div>
        <div class="lm-auto-popup-edit">
            <label>
                <div><?= GetMessage('LM_AUTO_API_FIELD_HIDDEN') ?></div>
                <input type="checkbox" name="out[hidden]" value="Y" <?= $item['hidden'] == 'Y' ? 'checked' : '' ?> />
            </label>
        </div>
        <div class="lm-auto-popup-edit">
            <label>
                <div><?= GetMessage('LM_AUTO_API_FIELD_SORT') ?></div>
                <input type="text" name="out[sort]" value="<?= $item['sort'] ? $item['sort'] : 500 ?>" />
            </label>
        </div>
    <?if ($type =='group') {?>
        <div class="lm-auto-popup-edit">
            <label>
                <input type="checkbox" name="" <?if($parent_id=='*')echo ' checked="checked" ';?> value="" onclick="var ___lm_v = $(this).closest('form').find('input[name=parent_id]');if(___lm_v.val()=='*'){___lm_v.val('<?=$parent_id?>');}else{___lm_v.val('*');}">
               <?=GetMessage('LM_AUTO_API_FOR_ALL')?>
            </label>
        </div>
    <?}?>
        <h4><?= GetMessage('LM_AUTO_API_CUSTOM_FIELDS') ?></h4>
        <?  // Добавление пользовательских полей.
            $events = GetModuleEvents("linemedia.auto", "OnTecdocItemShowCustomHtml");
            $html = '';
            while ($arEvent = $events->Fetch()) {
                $html .= ExecuteModuleEventEx($arEvent, array(&$item));
            }
            echo $html;
        ?>
        <? if (is_array($item['user_fields'])) {
            foreach ($item['user_fields'] as $code => $value) { ?>
                <div class="lm-auto-popup-edit">
                    <label style="display:inline-block;">
                        <div><?= GetMessage('LM_AUTO_API_CUSTOM_FIELD_CODE') ?></div>
                        <input type="text" name="custom[code][]" value="<?= $code ?>" />
                    </label>
                    <div style="display: inline-block;">=</div>
                    <label style="display: inline-block;">
                        <div><?= GetMessage('LM_AUTO_API_CUSTOM_FIELD_VALUE') ?></div>
                        <input type="text" name="custom[value][]" value="<?= $value ?>" />
                    </label>
                </div>
        <?  }
          }?>
        
        <div class="lm-auto-popup-edit">
            <a href="javascript:void(0);" onclick="$(this).closest('div').before($('#cloneable').clone().show());"><?= GetMessage('LM_AUTO_API_CUSTOM_FIELD_ADD') ?></a>
        </div>
        <div id="cloneable" style="display:none;">
            <label style="display:inline-block;">
                <div><?= GetMessage('LM_AUTO_API_CUSTOM_FIELD_CODE') ?></div>
                <input type="text" name="custom[code][]" value="" />
            </label>
            <div style="display:inline-block;">=</div>
            <label style="display:inline-block;"><div><?=GetMessage('LM_AUTO_API_CUSTOM_FIELD_VALUE')?></div><input type="text" name="custom[value][]" value=""></label>
        </div>
        
    </form>
    <?
    
}


/*
 * Печатаем форму из рекурсивного массива.
 */
function recursivePrintField($data_example, $result_modified, $edit = true, $prefix = '')
{
    $res = '';
    foreach ($data_example as $code => $value) {
        // Пропустим служебные данные и данные о изображении.+перестраховка от служебных полей
        if (in_array($code, array('image', 'image_id','source_id', 'parent_id','sortNumber', 'blockNumber','sort','hidden', 'user_fields'))) {
            continue;
        }
        
        // Выведем скрытые поля. если бренд текдочный, то не показываем поле редактирования id производителя.
        if (in_array($code, array('id', 'lm_mod_id'))) {
            $res .= '<input type="hidden"  name="out'.$prefix.'['.$code.']" value="'.$result_modified[$code].'" />';
            continue;
        }
        
        if (is_array($value)) {
            $res .= recursivePrintField($value, $result_modified[$code], $edit, $prefix.'['.$code.']');
        } else {
            $lang_id = $prefix.$code;
            $lang    = GetMessage($lang_id);
            $title   = ($lang == '') ? $lang_id : $lang;
            $help_title = GetMessage($lang_id.'_help');
/*
*   набор полей,что приходит от текдока, довольно непостоянен. поэтому для нужных и известных полей сообщение будет, а для остальных --нет
*/
            if(empty($lang)) continue;
/*
*   если это поле с некоторым id и у нас режим редактирования элемента текдока, то делаем поле readonly.
*   эти поля могут пригодиться при дальнейшем редактировании, поэтому показывать их надо, а вот давать редактировать -- нет.
* если же значение добавляется, то можно творить что угодно.
*/
            $res .= '
                <div class="lm-auto-popup-edit">
                <label>
                    <div>'.$title.'<span>' . $help_title . '</span></div>
                    <input type="text" '.($edit && in_array($code, array('manuId', 'modelId', 'carId', 'assemblyGroupNodeId')) && !isset($result_modified['lm_mod_id'])?'readonly':'').'  name="out'.$prefix.'['.$code.']" value="'.$result_modified[$code].'" /> ('.$data_example[$code].')
                </label>
                </div>';
        }
    }
    return $res;
}


/*
 * Многомерное ассоциативное сравнение.
 */
function array_diff_assoc_recursive($a, $b)
{
    $ret = array();
    foreach ($a as $k => $v) {
        if (!isset($b[$k]) && !is_null($v) && $v != '') {
            $ret[$k] = $v;
        } elseif (is_array($v)) {
            $sub = array_diff_assoc_recursive($v, $b[$k]);
            if ($sub) {
                $ret[$k] = $sub;
            }
        } else {
            $v      = (string) $v;
            $b[$k]  = (string) $b[$k];
            
            if ($v != $b[$k]) {
                $ret[$k] = $v;
            }
        }
    }
    return $ret; 
}


/*
 * Закрытие попапа.
 */
function closeJsPopup()
{
    require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
    require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_js.php');
    CUtil::JSPostUnescape();
    $obJSPopup = new CJSPopup();
    $obJSPopup->Close();
}

