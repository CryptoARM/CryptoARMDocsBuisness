<?php

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Trusted\CryptoARM\Docs;
use Bitrix\Main\ModuleManager;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/trusted.cryptoarmdocsbp/install/index.php';

if (CModule::IncludeModuleEx('trusted.cryptoarmdocs') == MODULE_DEMO_EXPIRED) {
    echo GetMessage("TR_CA_DOCS_MODULE_DEMO_EXPIRED");
    return false;
};
if (!trusted_cryptoarmdocsbp::coreModuleInstalled()) {
    echo ShowMessage(Loc::getMessage("TR_CA_DOCS_NO_CORE_MODULE"));
    return false;
};
switch (trusted_cryptoarmdocsbp::CoreAndModuleAreCompatible()) {
    case "updateCore":
        echo ShowMessage(Loc::getMessage("TR_CA_DOCS_UPDATE_CORE_MODULE") . intval(ModuleManager::getVersion("trusted.cryptoarmdocsbp")) . Loc::getMessage("TR_CA_DOCS_UPDATE_CORE_MODULE2"));
        return false;
        break;
    case "updateModule":
        echo ShowMessage(Loc::getMessage("TR_CA_DOCS_UPDATE_MODULE"));
        return false;
        break;
    default:break;
};

if ($USER->IsAuthorized()) {

    $APPLICATION->IncludeComponent(
        'trusted:cryptoarm_docs_upload',
        '.default',
        array(
            'FILES' => array('tr_ca_upload_comp_crm'),
            'PROPS' => array(
                'USER' => $USER->GetID(),
            ),
        ),
        false
    );

    $APPLICATION->IncludeComponent(
        'trusted:cryptoarm_docs_crm',
        '.default',
        array(
            'SEF_MODE' => 'Y',
            'SEF_FOLDER' => '/tr_ca_docs/',
        ),
        false
    );

}

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';

