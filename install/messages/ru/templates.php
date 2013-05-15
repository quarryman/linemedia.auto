<?php

$arTemplates = array(
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_USER_MSG_ITEM_ORDER',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#EMAIL#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '#SERVER_NAME#: Вопрос по позиции заказа',
        'MESSAGE'       => "Вопрос по позиции заказа сайта #SITE_NAME#\n------------------------------------------\nУ пользователя [#USER_ID#] #USER_NAME# возник вопрос\nпо позиции [#ORDER_ITEM_ID#] #ORDER_ITEM_NAME#\nв заказе №#ORDER_ID#\n\nВопрос пользователя:\n#MSG#;"
    ),
    
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_SALE_STATUS_CHANGED',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#EMAIL#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '#SERVER_NAME#: Изменение статуса товара в заказе N#ORDER_ID#',
        'MESSAGE'       => "Информационное сообщение сайта #SITE_NAME#\n------------------------------------------\n\nСтатус товара \"#ITEM_NAME#\"\nв заказе номер №#ORDER_ID# от #ORDER_DATE# изменен.\n\nНовый статус товара: #ITEM_STATUS#\n\nАртикул: #ITEM_ART#\nБренд: #ITEM_BRAND#\nЦена: #ITEM_PRICE#\nКоличество: #ITEM_QUANTITY#\nСумма: #ITEM_AMOUNT#\n\n#SITE_NAME#"
    ),
    
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_SALE_ALLOW_PAYMENT',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#EMAIL#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '#SERVER_NAME#: Оплата заказа N#ORDER_ID#',
        'MESSAGE'       => "Вы можете оплатить свой заказ №#ORDER_ID# перейдя по ссылке\nhttp://#SERVER_NAME#/auto/personal/order/make/?ORDER_ID=#ORDER_ID#\n\nCостав заказа:\n#ORDER_LIST#"
    ),
    
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_ORDER_ITEM_CANCEL',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#EMAIL#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '#SERVER_NAME#: Отмена позиции заказа N#ORDER_ID#',
        'MESSAGE'       => "Позиция #ITEM_NAME# в количестве #QUANTITY# отменена в заказе №#ORDER_ID#\nНовая сумма заказа: #NEW_PRICE#руб"
    ),
    
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_ORDER_ITEM_CANCEL',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#EMAIL#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '#SERVER_NAME#: Отмена позиции заказа N#ORDER_ID#',
        'MESSAGE'       => "Позиция #ITEM_NAME# в количестве #QUANTITY# отменена в заказе №#ORDER_ID#\nНовая сумма заказа: #NEW_PRICE#руб"
    ),
    
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_SALE_ORDER_ITEM_PAID',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#EMAIL#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '#SERVER_NAME#: Оплата позиции заказа N#ORDER_ID#',
        'MESSAGE'       => "Позиция #ITEM_NAME# (#ITEM_ART#) в количестве #ITEM_QUANTITY#шт оплачена."
    ),
    
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_SALE_ORDER_ITEM_DELIVERY',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#EMAIL#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '#SERVER_NAME#: Доставка позиции заказа N#ORDER_ID#',
        'MESSAGE'       => "Позиция #ITEM_NAME# (#ITEM_ART#) в количестве #ITEM_QUANTITY#шт разрешена к доставке."
    ),
    
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_VIN_SEND_MAIL',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#DEFAULT_EMAIL_FROM#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '#SERVER_NAME#: Запрос по VIN - #TITLE#',
        'MESSAGE'       => "#MESSAGE#"
    ),

    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_VIN_IBLOCK_SEND_MAIL',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#EMAIL#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '[TID##ID#]#SITE_NAME#: Ваше обращение принято',
        'MESSAGE'       => "#NAME# #LAST_NAME#,\nВаш запрос принят, ему присвоен номер #ID#.\n\nВы не должны отвечать на это письмо. Это только подтверждение,\nчто менеджер получил ваш запрос и работает с ним.\n\nДля просмотра запроса воспользуйтесь ссылкой:\nhttp://#SERVER_NAME#/vin/auto/?ID=#ID#\n\nО получении ответа вы будете дополнительно уведомлены.\n\nПисьмо сгенерировано автоматически."
    ),
    
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_VIN_IBLOCK_SEND_MAIL_ADMIN',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#DEFAULT_EMAIL_FROM#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '[TID##ID#] #SITE_NAME#: Новый запрос по VIN',
        'MESSAGE'       => "Новый запрос по VIN ##ID# менджерам сайта #SITE_NAME#.\nОт кого: [#USER_ID#] #USER_NAME# #USER_LAST_NAME#\nСоздано: #DATE_CREATE#\nVIN: #VIN#\n\nДля просмотра и ответа на запрос воспользуйтесь ссылкой:\nhttp://#SERVER_NAME##ADMIN_EDIT_URL#\n\nПисьмо сгенерировано автоматически."
    ),
    
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_VIN_IBLOCK_SEND_MAIL_MANAGER',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#MANAGER_EMAIL#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '[TID##ID#] #SITE_NAME#: Новый запрос по VIN',
        'MESSAGE'       => "Новый запрос по VIN ##ID# менджерам сайта #SITE_NAME#.\nОт кого: [#USER_ID#] #USER_NAME# #USER_LAST_NAME#\nСоздано: #DATE_CREATE#\nVIN: #VIN#\n\nДля просмотра и ответа на запрос воспользуйтесь ссылкой:\nhttp://#SERVER_NAME##ADMIN_EDIT_URL#\n\nПисьмо сгенерировано автоматически."
    ),
    
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_VIN_IBLOCK_SEND_MAIL_ANSWER',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#EMAIL#',
        'BODY_TYPE'     => 'html',
        'SUBJECT'       => '[TID##ID#]#SITE_NAME#: Получен ответ на запрос по VIN',
        'MESSAGE'       => "#NAME# #LAST_NAME#,<br />\nна ваш запрос по VIN #VIN# (#ID#) поступил ответ<br />\nот вашего менеджера #MANAGER#:<br />\n<br />\n#ANSWER#<br />\nВы можете просмотреть ответ, перейдя по следующей ссылке:\nhttp://#SERVER_NAME#/vin/auto/?ID=#ID#<br />\n<br />\nСообщение сгенерировано автоматически.\n#SITE_NAME#"
    ),
    
    array(
        'ACTIVE'        => 'Y',
        'EVENT_NAME'    => 'LM_AUTO_NEW_USER',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#EMAIL#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '#SITE_NAME#: Поздравляем с успешной регистрацией!',
        'MESSAGE'       => "Вы успешно зарегистрированы на сайте #SITE_NAME#\n------------------------------------------\n\n#NAME# #LAST_NAME#,\n\nВаша регистрационная информация:\n\nВаш логин: #LOGIN#\nВаш пароль: #PASSWORD#\n\nВы можете изменить пароль, перейдя по следующей ссылке:\nhttp://#SERVER_NAME#/auth/index.php?change_password=yes&lang=ru&USER_CHECKWORD=#CHECKWORD#\n\nСообщение сгенерировано автоматически."
    ),
    
    array(
        'ACTIVE'        => 'N',
        'EVENT_NAME'    => 'LM_AUTO_SALE_NEW_ORDER',
        'LID'           => '',
        'EMAIL_FROM'    => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO'      => '#SALE_EMAIL#',
        'BODY_TYPE'     => 'text',
        'SUBJECT'       => '#SITE_NAME#: Новый заказ №#ORDER_ID#',
        'MESSAGE'       => "Информационное сообщение сайта #SITE_NAME#\n------------------------------------------\nНовый заказ #ORDER_ID# от #ORDER_DATE# пользователя  #ORDER_USER#.\nСтоимость заказа: #PRICE#.\nСостав заказа:\n#ORDER_LIST#\nСообщение сгенерировано автоматически.\n"
    ),
);