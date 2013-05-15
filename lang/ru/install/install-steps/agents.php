<?php

/**
 * install step 3 (final)
 *
 * @author  Linemedia
 * @since   01/08/2012
 *
 * @link    http://auto.linemedia.ru/
 */

$MESS['LM_AUTO_MAIN_IBLOCKS_STEP_SUCCESS']  = 'Инфоблоки установлены!'; 

$MESS['LM_AUTO_MAIN_AGENT_HEADER']  = 'Агент импорта прайслистов';
$MESS['LM_AUTO_MAIN_AGENT_DESC']  = 'В систему добавляется агент, который позволяет автоматически обновлять информацию в Вашей базе товаров.<br>
Принцип его работы таков:
<ol class="list">
    <li>Вы получаете прайслист от поставщика запчастей</li>
    <li>Прайслист конвертируется с помощью специальной программы, поставляемой вместе с этим модулем и загружается на Ваш сайт</li>
    <li>Агент проверяет наличие необработанных прайслистов и импортирует их в базу данных</li>
    <li>Товары из прайслиста появляются на сайте</li>
</ol>
<br>
<a href="http://auto.linemedia.ru/files/xls2csv.zip">Скачать программу-конвертер с документацией и примерами</a><br>
<br>
Поскольку прайслисты могут быть очень большими и их импорт занимает продолжительное время, настоятельно рекомендуется использовать <a target="_blank" href="http://dev.1c-bitrix.ru/community/webdev/user/8078/blog/implementation-of-all-agents-in-cron/">механизм запуска агентов через cron</a>.<br>
В противном случае агент может работать с ошибками!
';
$MESS['LM_AUTO_MAIN_AGENT_CRON_OK']  = 'Агенты работают через cron, ничего менять не требуется';
$MESS['LM_AUTO_MAIN_AGENT_CRON_ERROR']  = 'Внимание! Агенты работают не через <a target="_blank" href="http://dev.1c-bitrix.ru/community/webdev/user/8078/blog/implementation-of-all-agents-in-cron/">cron</a>, измените режим запуска агентов!';

$MESS['LM_AUTO_MAIN_AGENT_INSTRUCTIONS']  = 'Выполните указанные <a href="http://dev.1c-bitrix.ru/community/webdev/user/8078/blog/implementation-of-all-agents-in-cron/" target="_blank">здесь</a> инструкции!';




$MESS['LM_AUTO_MAIN_CONTINUE']  = 'Продолжить';

