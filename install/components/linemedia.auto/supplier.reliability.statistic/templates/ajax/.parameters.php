<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arTemplateParameters = array(
            'WIDTH'=>array(
                            'NAME'=>GetMessage('LM_AUTO_SRS_WIDTH'),
                            'TYPE'=>'STRING',
                            'DEFAULT'=>'300px',
                            ),
            'HEIGHT'=>array(
                            'NAME'=>GetMessage('LM_AUTO_SRS_HEIGHT'),
                            'TYPE'=>'STRING',
                            'DEFAULT'=>'300px',
                            ),
);