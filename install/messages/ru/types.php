<?php

$arTypes = array(
    'LM_AUTO_USER_MSG_ITEM_ORDER' => array(
        array(
            'LID'           => 'ru',
            'EVENT_NAME'    => 'LM_AUTO_USER_MSG_ITEM_ORDER',
            'NAME'          => 'LINEMEDIA.AUTO: Сообщение менеджеру заказа',
            'DESCRIPTION'   => ""
        ),
        array(
            'LID'           => 'en',
            'EVENT_NAME'    => 'LM_AUTO_USER_MSG_ITEM_ORDER',
            'NAME'          => 'LINEMEDIA.AUTO: Сообщение менеджеру заказа',
            'DESCRIPTION'   => ""
        ),
    ),

    'LM_AUTO_SALE_STATUS_CHANGED' => array(
        array(
            'LID'           => 'ru',
            'EVENT_NAME'    => 'LM_AUTO_SALE_STATUS_CHANGED',
            'NAME'          => 'LINEMEDIA.AUTO: Изменение статуса товара',
            'DESCRIPTION'   => "#ORDER_ID# - код заказа\n#ORDER_DATE# - дата заказа\n#ORDER_STATUS# - статус заказа\n#EMAIL# - E-Mail пользователя\n#SALE_EMAIL# - E-Mail отдела продаж"
        ),
        array(
            'LID'           => 'en',
            'EVENT_NAME'    => 'LM_AUTO_SALE_STATUS_CHANGED',
            'NAME'          => 'LINEMEDIA.AUTO: Изменение статуса товара',
            'DESCRIPTION'   => "#ORDER_ID# - код заказа\n#ORDER_DATE# - дата заказа\n#ORDER_STATUS# - статус заказа\n#EMAIL# - E-Mail пользователя\n#SALE_EMAIL# - E-Mail отдела продаж"
        ),
    ),
    
    'LM_AUTO_SALE_ALLOW_PAYMENT' => array(
        array(
            'LID'           => 'ru',
            'EVENT_NAME'    => 'LM_AUTO_SALE_ALLOW_PAYMENT',
            'NAME'          => 'LINEMEDIA.AUTO: Возможность оплаты заказа',
            'DESCRIPTION'   => "#ORDER_ID# - код заказа\n#ORDER_DATE# - дата заказа\n#EMAIL# - E-Mail пользователя\n#SALE_EMAIL# - E-Mail отдела продаж\n#ORDER_LIST# - Состав заказа\n#PRICE# - Сумма заказа\n#ORDER_USER# - Заказчик"
        ),
        array(
            'LID'           => 'en',
            'EVENT_NAME'    => 'LM_AUTO_SALE_ALLOW_PAYMENT',
            'NAME'          => 'LINEMEDIA.AUTO: Возможность оплаты заказа',
            'DESCRIPTION'   => "#ORDER_ID# - код заказа\n#ORDER_DATE# - дата заказа\n#EMAIL# - E-Mail пользователя\n#SALE_EMAIL# - E-Mail отдела продаж\n#ORDER_LIST# - Состав заказа\n#PRICE# - Сумма заказа\n#ORDER_USER# - Заказчик"
        ),
    ),
    
    'LM_AUTO_ORDER_ITEM_CANCEL' => array(
        array(
            'LID'           => 'ru',
            'EVENT_NAME'    => 'LM_AUTO_ORDER_ITEM_CANCEL',
            'NAME'          => 'LINEMEDIA.AUTO: Отмена позиции в заказе',
            'DESCRIPTION'   => "#ORDER_ID# - код заказа\n#ITEM_NAME# - название позиции\n#EMAIL# - E-Mail пользователя\n#SALE_EMAIL# - E-Mail отдела продаж\n#QUANTITY# - Кол-во позиции\n#NEW_PRICE# - Новая сумма заказа\n#USER_NAME# - Заказчик"
        ),
        array(
            'LID'           => 'en',
            'EVENT_NAME'    => 'LM_AUTO_ORDER_ITEM_CANCEL',
            'NAME'          => 'LINEMEDIA.AUTO: Отмена позиции в заказе',
            'DESCRIPTION'   => "#ORDER_ID# - код заказа\n#ITEM_NAME# - название позиции\n#EMAIL# - E-Mail пользователя\n#SALE_EMAIL# - E-Mail отдела продаж\n#QUANTITY# - Кол-во позиции\n#NEW_PRICE# - Новая сумма заказа\n#USER_NAME# - Заказчик"
        ),
    ),
    
    'LM_AUTO_SALE_ORDER_ITEM_PAID' => array(
        array(
            'LID'           => 'ru',
            'EVENT_NAME'    => 'LM_AUTO_SALE_ORDER_ITEM_PAID',
            'NAME'          => 'LINEMEDIA.AUTO: Позиция в заказе оплачена',
            'DESCRIPTION'   => "#ORDER_ID# - код заказа\n#ORDER_DATE# - дата заказа\n#EMAIL# - E-Mail пользователя\n#SALE_EMAIL# - E-Mail отдела продаж\n#ITEM_NAME# - название позиции\n#ITEM_ART# - артикул позиции\n#ITEM_QUANTITY# - Кол-во позиции\n#ORDER_USER# - Заказчик"
        ),
        array(
            'LID'           => 'en',
            'EVENT_NAME'    => 'LM_AUTO_SALE_ORDER_ITEM_PAID',
            'NAME'          => 'LINEMEDIA.AUTO: Позиция в заказе оплачена',
            'DESCRIPTION'   => "#ORDER_ID# - код заказа\n#ORDER_DATE# - дата заказа\n#EMAIL# - E-Mail пользователя\n#SALE_EMAIL# - E-Mail отдела продаж\n#ITEM_NAME# - название позиции\n#ITEM_ART# - артикул позиции\n#ITEM_QUANTITY# - Кол-во позиции\n#ORDER_USER# - Заказчик"
        ),
    ),
    
    'LM_AUTO_SALE_ORDER_ITEM_DELIVERY' => array(
        array(
            'LID'           => 'ru',
            'EVENT_NAME'    => 'LM_AUTO_SALE_ORDER_ITEM_DELIVERY',
            'NAME'          => 'LINEMEDIA.AUTO: Разрешена доставка позиции в заказе',
            'DESCRIPTION'   => "#ORDER_ID# - код заказа\n#ORDER_DATE# - дата заказа\n#EMAIL# - E-Mail пользователя\n#SALE_EMAIL# - E-Mail отдела продаж\n#ITEM_NAME# - название позиции\n#ITEM_ART# - артикул позиции\n#ITEM_QUANTITY# - Кол-во позиции\n#ORDER_USER# - Заказчик"
        ),
        array(
            'LID'           => 'en',
            'EVENT_NAME'    => 'LM_AUTO_SALE_ORDER_ITEM_DELIVERY',
            'NAME'          => 'LINEMEDIA.AUTO: Разрешена доставка позиции в заказе',
            'DESCRIPTION'   => "#ORDER_ID# - код заказа\n#ORDER_DATE# - дата заказа\n#EMAIL# - E-Mail пользователя\n#SALE_EMAIL# - E-Mail отдела продаж\n#ITEM_NAME# - название позиции\n#ITEM_ART# - артикул позиции\n#ITEM_QUANTITY# - Кол-во позиции\n#ORDER_USER# - Заказчик"
        ),
    ),
    'LM_AUTO_VIN_SEND_MAIL' => array(
        array(
            'LID'           => 'ru',
            'EVENT_NAME'    => 'LM_AUTO_VIN_SEND_MAIL',
            'NAME'          => 'LINEMEDIA.AUTO: Запрос по VIN (по e-mail)',
            'DESCRIPTION'   => "#TITLE# - заголовок\n#MESSAGE# - сообщение"
        ),
         array(
            'LID'           => 'en',
            'EVENT_NAME'    => 'LM_AUTO_VIN_SEND_MAIL',
            'NAME'          => 'LINEMEDIA.AUTO: Запрос по VIN (по e-mail)',
            'DESCRIPTION'   => "#TITLE# - заголовок\n#MESSAGE# - сообщение"
        ),
    ),
    'LM_AUTO_VIN_IBLOCK_SEND_MAIL' => array(
        array(
            'LID'           => 'ru',
            'EVENT_NAME'    => 'LM_AUTO_VIN_IBLOCK_SEND_MAIL',
            'NAME'          => 'LINEMEDIA.AUTO: Запрос по VIN (инфоблок, уведомление пользователя)',
            'DESCRIPTION'   => "#ID# - ID запроса\n#VIN# - VIN\n#EMAIL# - Email пользователя\n#NAME# - Имя пользователя\n#LAST_NAME# - Фамилия пользователя"
        ),
         array(
            'LID'           => 'en',
            'EVENT_NAME'    => 'LM_AUTO_VIN_IBLOCK_SEND_MAIL',
            'NAME'          => 'LINEMEDIA.AUTO: Запрос по VIN (инфоблок, уведомление пользователя)',
            'DESCRIPTION'   => "#ID# - ID запроса\n#VIN# - VIN\n#EMAIL# - Email пользователя\n#NAME# - Имя пользователя\n#LAST_NAME# - Фамилия пользователя"
        ),
    ),
    'LM_AUTO_VIN_IBLOCK_SEND_MAIL_ADMIN' => array(
        array(
            'LID'           => 'ru',
            'EVENT_NAME'    => 'LM_AUTO_VIN_IBLOCK_SEND_MAIL_ADMIN',
            'NAME'          => 'LINEMEDIA.AUTO: Запрос по VIN (инфоблок, уведомление администратора)',
            'DESCRIPTION'   => "#ID# - ID запроса\n#USER_ID# - ID Пользователя\n#USER_NAME# - Имя пользователя\n#USER_LAST_NAME# - Фамилия пользователя\n#USER_EMAIL# - Email пользователя\n#DATE_CREATE# - Дата запроса\n#VIN# - VIN код\n#ADMIN_EDIT_URL# - Ссылка на просмотр запроса"
        ),
         array(
            'LID'           => 'en',
            'EVENT_NAME'    => 'LM_AUTO_VIN_IBLOCK_SEND_MAIL_ADMIN',
            'NAME'          => 'LINEMEDIA.AUTO: Запрос по VIN (инфоблок, уведомление администратора)',
            'DESCRIPTION'   => "#ID# - ID запроса\n#USER_ID# - ID Пользователя\n#USER_NAME# - Имя пользователя\n#USER_LAST_NAME# - Фамилия пользователя\n#USER_EMAIL# - Email пользователя\n#DATE_CREATE# - Дата запроса\n#VIN# - VIN код\n#ADMIN_EDIT_URL# - Ссылка на просмотр запроса"
        ),
    ),
    'LM_AUTO_VIN_IBLOCK_SEND_MAIL_MANAGER' => array(
        array(
            'LID'           => 'ru',
            'EVENT_NAME'    => 'LM_AUTO_VIN_IBLOCK_SEND_MAIL_MANAGER',
            'NAME'          => 'LINEMEDIA.AUTO: Запрос по VIN (инфоблок, уведомление менеджера)',
            'DESCRIPTION'   => "#ID# - ID запроса\n#USER_ID# - ID Пользователя\n#USER_NAME# - Имя пользователя\n#USER_LAST_NAME# - Фамилия пользователя\n#USER_EMAIL# - Email пользователя\n#DATE_CREATE# - Дата запроса\n#VIN# - VIN код\n#MANAGER_EMAIL# - Email ответственного менеджера\n#ADMIN_EDIT_URL# - Ссылка на просмотр запроса"
        ),
         array(
            'LID'           => 'en',
            'EVENT_NAME'    => 'LM_AUTO_VIN_IBLOCK_SEND_MAIL_MANAGER',
            'NAME'          => 'LINEMEDIA.AUTO: Запрос по VIN (инфоблок, уведомление менеджера)',
            'DESCRIPTION'   => "#ID# - ID запроса\n#USER_ID# - ID Пользователя\n#USER_NAME# - Имя пользователя\n#USER_LAST_NAME# - Фамилия пользователя\n#USER_EMAIL# - Email пользователя\n#DATE_CREATE# - Дата запроса\n#VIN# - VIN код\n#MANAGER_EMAIL# - Email ответственного менеджера\n#ADMIN_EDIT_URL# - Ссылка на просмотр запроса"
        ),
    ),
    'LM_AUTO_VIN_IBLOCK_SEND_MAIL_ANSWER' => array(
        array(
            'LID'           => 'ru',
            'EVENT_NAME'    => 'LM_AUTO_VIN_IBLOCK_SEND_MAIL_ANSWER',
            'NAME'          => 'LINEMEDIA.AUTO: Запрос по VIN (инфоблок, уведомление пользователя об ответе)',
            'DESCRIPTION'   => "#ID# - ID запроса\n#VIN# - VIN\n#EMAIL# - Email пользователя\n#NAME# - Имя пользователя\n#LAST_NAME# - Фамилия пользователя\n#ANSWER# - Ответ\n#MANAGER# - Менеджер, написавший ответ"
        ),
         array(
            'LID'           => 'en',
            'EVENT_NAME'    => 'LM_AUTO_VIN_IBLOCK_SEND_MAIL_ANSWER',
            'NAME'          => 'LINEMEDIA.AUTO: Запрос по VIN (инфоблок, уведомление пользователя об ответе)',
            'DESCRIPTION'   => "#ID# - ID запроса\n#VIN# - VIN\n#EMAIL# - Email пользователя\n#NAME# - Имя пользователя\n#LAST_NAME# - Фамилия пользователя\n#ANSWER# - Ответ\n#MANAGER# - Менеджер, написавший ответ"
        ),
    ),
    'LM_AUTO_NEW_USER' => array(
        array(
            'LID'           => 'ru',
            'EVENT_NAME'    => 'LM_AUTO_NEW_USER',
            'NAME'          => 'LINEMEDIA.AUTO: Регистрация',
            'DESCRIPTION'   => "#NAME# - имя\n#LAST_NAME# - фамилия\n#EMAIL# - электронная почта\n#LOGIN# - логин\n#PASSWORD# - пароль\n"
        ),
         array(
            'LID'           => 'en',
            'EVENT_NAME'    => 'LM_AUTO_NEW_USER',
            'NAME'          => 'LINEMEDIA.AUTO: Registration',
            'DESCRIPTION'   => "#NAME# - имя\n#LAST_NAME# - фамилия\n#EMAIL# - электронная почта\n#LOGIN# - логин\n#PASSWORD# - пароль\n"
        ),
    ),
    'LM_AUTO_SALE_NEW_ORDER'=> array(
        array(
            'LID'           => 'ru',
            'EVENT_NAME'    => 'LM_AUTO_SALE_NEW_ORDER',
            'NAME'          => 'LINEMEDIA.AUTO: Новый заказ',
            'DESCRIPTION'   => "#ORDER_ID# - код заказа\n#ORDER_DATE# - дата заказа\n#ORDER_USER# - заказчик\n#PRICE# - сумма заказа\n#EMAIL# - E-Mail заказчика\n#BCC# - E-Mail скрытой копии\n#ORDER_LIST# - состав заказа\n#SALE_EMAIL# - E-Mail отдела продаж\n"
        ),
         array(
            'LID'           => 'en',
            'EVENT_NAME'    => 'LM_AUTO_SALE_NEW_ORDER',
            'NAME'          => 'LINEMEDIA.AUTO: New order',
            'DESCRIPTION'   => "#ORDER_ID# - код заказа\n#ORDER_DATE# - дата заказа\n#ORDER_USER# - заказчик\n#PRICE# - сумма заказа\n#EMAIL# - E-Mail заказчика\n#BCC# - E-Mail скрытой копии\n#ORDER_LIST# - состав заказа\n#SALE_EMAIL# - E-Mail отдела продаж\n"
        )
    )
);
