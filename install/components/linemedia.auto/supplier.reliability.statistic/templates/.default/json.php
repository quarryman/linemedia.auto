<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/**
    ����� JSON � ������� ��� ���������� ��������.
    ������ ������ �� ����, �� ��� � ����� �������.
    ������ ������ �����. ����,��� ������� ����� ��� ��������.
*/

$APPLICATION->RestartBuffer();// ������ �� ������ ������

header('Content-Type: application/json');
/**
    �������� ���: pie -- �������� "��������-�������" � bars -- ����������� "������� �������� �� ����".
    *_opts --  ����������� ��������� ��������, ������� ����� ��� ������ �� ������� �� ����������� (�� ���� ���������, �� ����).

*/
        $data = array();
        $data['pie'] = array(
           array(GetMessage('LM_AUTO_SUPP_RELY_CHART_STATUS'), '%'),
           array(GetMessage('LM_AUTO_SUPP_RELY_CHART_STOCKED'),     $arResult['STAT']['completed']),
           array(GetMessage('LM_AUTO_SUPP_RELY_CHART_REJECTED'),    $arResult['STAT']['rejected'])
        );
       $data['pie_opts'] = array('title'=>GetMessage('CHART_TITLE'));

       
        /**
        *   ��� ���� ����� ��� ����,����� � ������ ���������� ������ ��� ����������� ��������� �� ������������ �������� ��������� �� ������� ����.
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
