<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/**
    Отдаём JSON с данными для построения диаграмм.
    Больше ничего не надо, за сим в конце умираем.
    формат данных соотв. тому,что требует гугль для диаграмм.
*/

$APPLICATION->RestartBuffer();// защита от режима правки

header('Content-Type: application/json');
/**
    диаграмм две: pie -- круговая "доставил-отказал" и bars -- столбиковая "процент поставок по дням".
    *_opts --  минимальные настройки диаграмм, которые слабо или совсем не зависят от отображения (то есть заголовки, по сути).

*/
        $data = array();
        $data['pie'] = array(
           array(GetMessage('LM_AUTO_SUPP_RELY_CHART_STATUS'), '%'),
           array(GetMessage('LM_AUTO_SUPP_RELY_CHART_STOCKED'),     $arResult['STAT']['completed']),
           array(GetMessage('LM_AUTO_SUPP_RELY_CHART_REJECTED'),    $arResult['STAT']['rejected'])
        );
       $data['pie_opts'] = array('title'=>GetMessage('CHART_TITLE'));

       
        /**
        *   это поле нужно для того,чтобы в случае отсутствия данных для столбиковой диаграммы не показывалось страшное сообщение на красном фоне.
        *   
        */
       $data['bars_exists'] = (is_array($arResult['STAT']['delivery_time']) && !empty($arResult['STAT']['delivery_time']));

        if ($data['bars_exists']) {
            $data['bars'] = array(array(GetMessage('LM_AUTO_SUPP_RELY_DAYS'), GetMessage('LM_AUTO_SUPP_RELY_SUPP')));
            foreach ($arResult['STAT']['delivery_time'] as $days=>$percent) {
                $data['bars'][] = array(declension($days, array(GetMessage('LM_AUTO_SUPP_RELY_DAY_1'),GetMessage('LM_AUTO_SUPP_RELY_DAY234'), GetMessage('LM_AUTO_SUPP_RELY_DAY5ORMORE'))), (int)$percent);
            }
        }

       $data['bars_opts'] =  array(
          'title'=>GetMessage('LM_AUTO_SUPP_RELY_READY'),
          'vAxis'=>array('title'=> GetMessage('LM_AUTO_SUPP_RELY_SUPP')),
          'hAxis'=>array('title'=>GetMessage('LM_AUTO_SUPP_RELY_DAYS')),
          'seriesType'=>"bars"
        );
die(json_encode($data));
