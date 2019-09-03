<?php

$MESS["TR_CA_DOCS_MODULE_NAME2"] = "КриптоАРМ Документы: бизнес-процессы";
$MESS["TR_CA_DOCS_MODULE_DESCRIPTION2"] = "Модуль работы с документами в Битрикс 24";
$MESS["TR_CA_DOCS_PARTNER_NAME"] = 'ООО "Цифровые технологии"';
$MESS["TR_CA_DOCS_PARTNER_URI"] = "https://trusted.ru";

$MESS["TR_CA_DOCS_CRM_MENU_TITLE"] = "КриптоАРМ Документы: бизнес-процессы";

// email by order
$MESS["TR_CA_DOCS_MAIL_EVENT_NAME"] = "КриптоАРМ Документы - рассылка документов по заказам";
$MESS["TR_CA_DOCS_MAIL_EVENT_DESCRIPTION"] = "
#EMAIL# - EMail получателя сообщения
#ORDER_USER# - имя пользователя, совершившего заказ
#ORDER_ID# - номер заказа
#FILE_NAMES# - список названий документов
";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_SUBJECT"] = "#SITE_NAME#: Документы по заказу №#ORDER_ID#";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_BODY"] = "
Документы: #FILE_NAMES# по заказу №#ORDER_ID# для #ORDER_USER#.
<img src=\"#SITE_URL#/bitrix/components/trusted/docs/email.php?order_id=#ORDER_ID#&rand=#RAND_UID#\" alt=\"\">
";

// email documents
$MESS["TR_CA_DOCS_MAIL_EVENT_TO_NAME"] = "КриптоАРМ Документы - отправка документов";
$MESS["TR_CA_DOCS_MAIL_EVENT_TO_DESCRIPTION"] = "
#EMAIL# - EMail получателя сообщения
#FILE_NAMES# - список названий документов
";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_TO_SUBJECT"] = "#SITE_NAME#: Документы";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_TO_BODY"] = "Документы: #FILE_NAMES#";

// email about share documents
$MESS["TR_CA_DOCS_MAIL_EVENT_SHARE_NAME"] = "КриптоАРМ Документы - уведомление о получении доступа к документу";
$MESS["TR_CA_DOCS_MAIL_EVENT_SHARE_DESCRIPTION"] = "
#EMAIL# - EMail получателя сообщения
#FILE_NAME# - название документа
#SHARE_FROM# - автор документа
";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_SHARE_SUBJECT"] = "#SITE_NAME#: получен доступ к #FILE_NAME#";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_SHARE_BODY"] = "#SITE_NAME#: #SHARE_FROM# поделился документом #FILE_NAME#";

// email completed form
$MESS["TR_CA_DOCS_MAIL_EVENT_FORM_NAME"] = "КриптоАРМ Документы - уведомление пользователя о форме";
$MESS["TR_CA_DOCS_MAIL_EVENT_FORM_DESCRIPTION"] = "
#EMAIL# - EMail получателя сообщения
#FILE_NAME# - название документа
";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_FORM_SUBJECT"] = "#SITE_NAME#: вы заполнили и подписали форму";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_FORM_BODY"] = "#SITE_NAME#: Вы успешно заполнили и подписали форму: #FILE_NAME#";

// email completed form and send it to admin
$MESS["TR_CA_DOCS_MAIL_EVENT_FORM_TO_ADMIN_NAME"] = "КриптоАРМ Документы - уведомление администратора о форме";
$MESS["TR_CA_DOCS_MAIL_EVENT_FORM_TO_ADMIN_DESCRIPTION"] = "
#EMAIL# - EMail получателя сообщения
#FILE_NAME# - название документа
#FORM_USER# - автор заполненной формы
";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_FORM_TO_ADMIN_SUBJECT"] = "#SITE_NAME#: Форма заполнена и подписана пользователем #FORM_USER#";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_FORM_TO_ADMIN_BODY"] = "#SITE_NAME#: Пользователь #FORM_USER# успешно заполнил форму и подписал все документы: #FILE_NAME#";

$MESS["TR_CA_DOCS_CANCEL_INSTALL"] = "Отменить установку";

$MESS["TR_CA_DOCS_BP_SIGN_TEMPLATE"] = "Подпись документа выбранными сотрудниками";
$MESS["TR_CA_DOCS_BP_AGREED_TEMPLATE"] = "Согласование документа выбранными сотрудниками";
$MESS["TR_CA_DOCS_BP_SERVICE_NOTE"] = "Служебная записка";
$MESS["TR_CA_DOCS_BP_ACQUAINTANCE"] = "Ознакомление с документом выбранными сотрудниками";
$MESS["TR_CA_DOCS_BP_MONEY_DEMAND"] = "Заявка на получение денежных средств";
$MESS["TR_CA_DOCS_BP_ORDER"] = "Приказ выбранным сотрудникам с прикреплением отчёта о выполнении";


