<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;
use Bitrix\Main\ModuleManager;
// use Bitrix\Main\EventManager;
// use Bitrix\Main\Loader;
// use Trusted\CryptoARM\Docs;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/trusted.cryptoarmdocs/include.php';

Loc::loadMessages(__FILE__);

Class trusted_cryptoarmdocs_bitrix24 extends CModule
{
    // Required by the marketplace standards
    var $MODULE_ID = "trusted.cryptoarmdocsBP";
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $PARTNER_NAME;
    var $PARTNER_URI;

    function trusted_cryptoarmdocs_bitrix24()
    {
        self::__construct();
    }

    function __construct()
    {
        $arModuleVersion = array();
        include __DIR__ . "/version.php";
        $this->MODULE_NAME = Loc::getMessage("TR_CA_DOCS_MODULE_NAME2");
        $this->MODULE_DESCRIPTION = Loc::getMessage("TR_CA_DOCS_MODULE_DESCRIPTION2");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->PARTNER_NAME = GetMessage("TR_CA_DOCS_PARTNER_NAME");
        $this->PARTNER_URI = GetMessage("TR_CA_DOCS_PARTNER_URI");
    }

    function DoInstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;

        include __DIR__ . "/version.php";

        //$context = Application::getInstance()->getContext();
        //$request = $context->getRequest();
        //step = (int)$request["step"];

        if (!$this->d7Support() || !$this->coreModuleInstalled()
        || !$this->ModuleIsRelevant(ModuleManager::getVersion("trusted.cryptoarmdocs"), $arModuleVersion["VERSION"])
        || !$this->ModuleIsRelevant($arModuleVersion["VERSION"], ModuleManager::getVersion("trusted.cryptoarmdocs"))) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage("MOD_INSTALL_TITLE"),
                 $DOCUMENT_ROOT . "/bitrix/modules/" . $this->MODULE_ID . "/install/step_cancel.php"
            );
        }


        // if ($request["choice"] == Loc::getMessage("TR_CA_DOCS_CANCEL_INSTALL")) {
        //     $continue = false;
        // }
        // if ($step < 2 && $continue) {
        //     $APPLICATION->IncludeAdminFile(
        //         Loc::getMessage("MOD_INSTALL_TITLE"),
        //         $DOCUMENT_ROOT . "/bitrix/modules/" . $this->MODULE_ID . "/install/step1.php"

        //     );
        //     Docs\Utils::dump($this->MODULE_ID);
        // }
        // if ($step == 2 && $continue) {
        //     $APPLICATION->IncludeAdminFile(
        //         Loc::getMessage("MOD_INSTALL_TITLE"),
        //         $DOCUMENT_ROOT . "/bitrix/modules/" . $this->MODULE_ID . "/install/step2.php"
        //     );
        // }
        // if ($step == 3 && $continue) {
        //     $APPLICATION->IncludeAdminFile(
        //         Loc::getMessage("MOD_INSTALL_TITLE"),
        //         $DOCUMENT_ROOT . "/bitrix/modules/" . $this->MODULE_ID . "/install/step3.php"
        //     );
        // }
        // if ($step == 4 && $continue) {
        //     if ($request["dropDB"] == "Y") {
        //         $this->UnInstallDB();
        //         $this->UnInstallIb();
        //     } elseif ($request["dropLostDocs"]) {
        //         $lostDocs = unserialize($request["dropLostDocs"]);
        //         foreach ($lostDocs as $id) {
        //             $this->dropDocumentChain($id);
        //         }
        //     }
            $this->InstallFiles();
            //$this->CreateDocsDir();
            //$this->InstallModuleOptions();
            //$this->InstallDB();
            //$this->InstallIb();
            $this->InstallMenuItems();
            //$this->InstallMailEvents();
            if ($this->bizprocSupport()) {
                $this->InstallBPTemplates();
            }

            ModuleManager::registerModule($this->MODULE_ID);

        // if (!$continue) {
        //     $APPLICATION->IncludeAdminFile(
        //         Loc::getMessage("MOD_INSTALL_TITLE"),
        //         $DOCUMENT_ROOT . "/bitrix/modules/" . $this->MODULE_ID . "/install/step_cancel.php"
        //     );
        // }
    }

    function d7Support()
    {
        return CheckVersion(ModuleManager::getVersion("main"), "14.00.00");
    }

    function crmSupport()
    {
        return IsModuleInstalled("crm");
    }

    function bizprocSupport()
    {
        return IsModuleInstalled("bizproc");
    }

    function coreModuleInstalled()
    {
        return IsModuleInstalled("trusted.cryptoarmdocs");
    }

    function ModuleIsRelevant($module1, $module2)
    {
        $module1 = explode(".", $module1);
        $module2 = explode(".", $module2);
        if (intval($module2[0])>intval($module1[0])) return false;
            elseif (intval($module2[0])<=intval($module1[0])) return true;
    }


    function InstallFiles()
    {
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/components/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/",
            true, true
        );

        if ($this->bizprocSupport()) {
            CopyDirFiles(
                $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/activities/",
                $_SERVER["DOCUMENT_ROOT"] . "/bitrix/activities/custom/",
                true, true
            );
            CopyDirFiles(
                $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/crm_pub/",
                $_SERVER["DOCUMENT_ROOT"],
                true, true
            );
            CUrlRewriter::Add(
                array(
                    'CONDITION' => '#^/tr_ca_docs/#',
                    'RULE' => '',
                    'ID' => 'trusted:cryptoarm_docs_crm',
                    'PATH' => '/tr_ca_docs/index.php',
                )
            );
        }
        return true;
    }

    function InstallMenuItems() {
        $siteInfo = $this->getSiteInfo();

        if ($this->crmSupport()) {
            $this->AddMenuItem(
                $siteInfo["DIR"] . ".top.menu.php",
                array(
                    Loc::getMessage('TR_CA_DOCS_CRM_MENU_TITLE'),
                    $siteInfo["DIR"] . "tr_ca_docs/",
                    array(),
                    array(),
                    "IsModuleInstalled('" . $this->MODULE_ID . "')"
                ),
                $siteInfo["LID"]
            );
        }
    }


    function DoUninstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;

        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();
        $step = (int)$request["step"];

        if ($step < 2) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage("MOD_UNINSTALL_TITLE"),
                $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/unstep1.php"
            );
        }

        if ($request["uninst"] === Loc::getMessage("MOD_UNINST_DEL")) {
        // if ($step == 2) {

            // $this->UnInstallModuleOptions();
            // $deletedata = $request["deletedata"];
            // if ($deletedata == "Y") {
            //     $this->UnInstallDB();
            //     $this->UnInstallIb();
            // }
            self::UnInstallMenuItems();
            // $this->UnInstallMailEvents();
            if (self::bizprocSupport()) {
                self::UninstallBPTemplates();
            }
            self::UnInstallFiles();
            ModuleManager::unRegisterModule('trusted.cryptoarmdocsBP');
            // $APPLICATION->IncludeAdminFile(
            //     Loc::getMessage("MOD_UNINSTALL_TITLE"),
            //     $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/unstep2.php"
            // );
        } else {
            return false;
        }
    }

    function UnInstallFiles()
    {
        // DeleteDirFilesEx("/bitrix/components/trusted/cryptoarm_docs_by_user/");
        // DeleteDirFilesEx("/bitrix/components/trusted/cryptoarm_docs_by_order/");
        DeleteDirFilesEx("/bitrix/components/trusted/cryptoarm_docs_crm/");
        // DeleteDirFilesEx("/bitrix/components/trusted/cryptoarm_docs_form/");
        // DeleteDirFilesEx("/bitrix/components/trusted/cryptoarm_docs_upload/");
        // DeleteDirFilesEx("/bitrix/components/trusted/docs/");
        // DeleteDirFiles(
        //     $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/admin/",
        //     $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin"
        // );
        // DeleteDirFilesEx("/bitrix/js/" . $this->MODULE_ID);
        // DeleteDirFiles(
        //     $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/themes/.default/",
        //     $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes/.default/"
        // );
        // DeleteDirFilesEx("/bitrix/themes/.default/icons/" . $this->MODULE_ID);

        // CRM
        DeleteDirFilesEx("/tr_ca_docs/");
        DeleteDirFilesEx("/bitrix/activities/custom/trustedcasign/");
        DeleteDirFilesEx("/bitrix/activities/custom/trustedcaapprove/");
        DeleteDirFilesEx("/bitrix/activities/custom/trustedcashare/");
        DeleteDirFilesEx("/bitrix/activities/custom/trustedcaupload/");
        DeleteDirFilesEx("/bitrix/activities/custom/trustedcareview/");
        CUrlRewriter::Delete(
            array(
                'ID' => 'trusted:cryptoarm_docs_crm',
                'PATH' => '/tr_ca_docs/index.php',
            )
        );

        return true;
    }

    // function UnInstallModuleOptions()
    // {
    //     $options = array(
    //         // 'DOCUMENTS_DIR',
    //         'MAIL_EVENT_ID',
    //         'MAIL_TEMPLATE_ID',
    //         'MAIL_EVENT_ID_TO',
    //         'MAIL_TEMPLATE_ID_TO',
    //         'MAIL_EVENT_ID_SHARE',
    //         'MAIL_TEMPLATE_ID_SHARE',
    //         'MAIL_EVENT_ID_FORM',
    //         'MAIL_TEMPLATE_ID_FORM',
    //         'MAIL_EVENT_ID_FORM_TO_ADMIN',
    //         'MAIL_TEMPLATE_ID_FORM_TO_ADMIN',
    //     );
    //     foreach ($options as $option) {
    //         Option::delete(
    //             $this->MODULE_ID,
    //             array('name' => $option)
    //         );
    //     }
    // }

    // function UnInstallDB()
    // {
    //     global $DB;
    //     if (Loader::includeModule('bizproc')) {
    //         $docs = Docs\Database::getDocuments();
    //         foreach ($docs->getList() as $doc) {
    //             $doc->remove();
    //         }
    //     }
    //     $sql = "DROP TABLE IF EXISTS `tr_ca_docs`";
    //     $DB->Query($sql);
    //     $sql = "DROP TABLE IF EXISTS `tr_ca_docs_property`";
    //     $DB->Query($sql);
    // }

    // function UnInstallIb() {
    //     Docs\IBlock::uninstall();
    // }

    function UnInstallMenuItems() {
        $siteInfo = self::getSiteInfo();

        if (self::crmSupport()) {
            self::DeleteMenuItem(
                $siteInfo["DIR"] . ".top.menu.php",
                $siteInfo["DIR"] . "tr_ca_docs/",
                $siteInfo["LID"]
            );
        }
    }

    // function UnInstallMailEvents()
    // {
    //     $events = array(
    //         'TR_CA_DOCS_MAIL_BY_ORDER',
    //         'TR_CA_DOCS_MAIL_TO',
    //         'TR_CA_DOCS_MAIL_SHARE',
    //         'TR_CA_DOCS_MAIL_FORM',
    //         'TR_CA_DOCS_MAIL_FORM_TO_ADMIN',
    //     );
    //     foreach ($events as $event) {
    //         $eventMessages = CEventMessage::GetList(
    //             $by = 'id',
    //             $order = 'desc',
    //             array('TYPE' => $event)
    //         );
    //         $eventMessage = new CEventMessage;
    //         while ($template = $eventMessages->Fetch()) {
    //             $eventMessage->Delete((int)$template['ID']);
    //         }
    //         $eventType = new CEventType;
    //         $eventType->Delete($event);
    //     }
    // }

    // function dropDocumentChain($id)
    // {
    //     global $DB;
    //     // Try to find parent doc
    //     $sql = 'SELECT `PARENT_ID` FROM `tr_ca_docs` WHERE `ID`=' . $id;
    //     $res = $DB->Query($sql)->Fetch();
    //     $parentId = $res["PARENT_ID"];

    //     $sql = 'DELETE FROM `tr_ca_docs`'
    //         . 'WHERE ID = ' . $id;
    //     $DB->Query($sql);
    //     $sql = 'DELETE FROM `tr_ca_docs_property`'
    //         . 'WHERE DOCUMENT_ID = ' . $id;
    //     $DB->Query($sql);

    //     if ($parentId) {
    //         $this->dropDocumentChain($parentId);
    //     }
    // }

    function getSiteInfo() {
        $siteID = CSite::GetDefSite();
        return CSite::GetByID($siteID)->Fetch();
    }

    function AddMenuItem($menuFile, $menuItem,  $siteID, $pos = -1)
    {
        if (CModule::IncludeModule('fileman')) {
            $arResult = CFileMan::GetMenuArray(Application::getDocumentRoot() . $menuFile);
            $arMenuItems = $arResult["aMenuLinks"];
            $menuTemplate = $arResult["sMenuTemplate"];

            $bFound = false;
            foreach ($arMenuItems as $item) {
                if ($item[1] == $menuItem[1]) {
                    $bFound = true;
                    break;
                }
            }

            if (!$bFound) {
                if ($pos<0 || $pos>=count($arMenuItems)) {
                    $arMenuItems[] = $menuItem;
                } else {
                    for ($i=count($arMenuItems); $i>$pos; $i--) {
                        $arMenuItems[$i] = $arMenuItems[$i-1];
                    }
                    $arMenuItems[$pos] = $menuItem;
                }

                CFileMan::SaveMenu(array($siteID, $menuFile), $arMenuItems, $menuTemplate);
            }
        }
    }

    function DeleteMenuItem($menuFile, $menuLink, $siteID) {
        if (CModule::IncludeModule("fileman")) {
            $arResult = CFileMan::GetMenuArray(Application::getDocumentRoot() . $menuFile);
            $arMenuItems = $arResult["aMenuLinks"];
            $menuTemplate = $arResult["sMenuTemplate"];

            foreach($arMenuItems as $key => $item) {
                if($item[1] == $menuLink) unset($arMenuItems[$key]);
            }

            CFileMan::SaveMenu(array($siteID, $menuFile), $arMenuItems, $menuTemplate);
        }
    }

    function InstallBPTemplates() {
        CModule::IncludeModule('bizproc');
        CModule::IncludeModule('bizprocdesigner');

        $templateIds = array();
        $templateIds[] = $this->ImportBPTemplateFromFile('MoneyDemand.bpt', Loc::getMessage("TR_CA_DOCS_BP_MONEY_DEMAND"));
        $templateIds[] = $this->ImportBPTemplateFromFile('Acquaintance.bpt', Loc::getMessage("TR_CA_DOCS_BP_ACQUAINTANCE"));
        $templateIds[] = $this->ImportBPTemplateFromFile('SetSignResponsibility.bpt', Loc::getMessage("TR_CA_DOCS_BP_SIGN_TEMPLATE"));
        $templateIds[] = $this->ImportBPTemplateFromFile('Order.bpt', Loc::getMessage("TR_CA_DOCS_BP_ORDER"));
        $templateIds[] = $this->ImportBPTemplateFromFile('ServiceNote.bpt', Loc::getMessage("TR_CA_DOCS_BP_SERVICE_NOTE"));
        $templateIds[] = $this->ImportBPTemplateFromFile('AgreedOn.bpt', Loc::getMessage("TR_CA_DOCS_BP_AGREED_TEMPLATE"));

        Option::set(TR_CA_DOCS_MODULE_ID, TR_CA_DOCS_TEMPLATE_ID, implode(" ", $templateIds));
    }

    function ImportBPTemplateFromFile ($filename, $templatename) {
        $file = fopen(TR_CA_DOCS_MODULE_DIR . "resources/".$filename, 'r');
        $data = fread($file, filesize(TR_CA_DOCS_MODULE_DIR . "resources/".$filename));
        fclose($file);
        $templateId = CBPWorkflowTemplateLoader::ImportTemplate(0, ["trusted.cryptoarmdocs", "Trusted\CryptoARM\Docs\WorkflowDocument", "TR_CA_DOC"], true, $templatename, "", $data);
        return $templateId;
    }

    function UninstallBPTemplates () {
        $templateIds = preg_split('/ /', Option::get(TR_CA_DOCS_MODULE_ID, TR_CA_DOCS_TEMPLATE_ID), null, PREG_SPLIT_NO_EMPTY);
        global $DB;
        foreach ($templateIds as $id) {
            $dbResult = $DB->Query(
                "SELECT COUNT('x') as CNT ".
                "FROM b_bp_workflow_instance WI ".
                "WHERE WI.WORKFLOW_TEMPLATE_ID = ".intval($id)." "
            );

            if ($arResult = $dbResult->Fetch()) {
                $cnt = intval($arResult["CNT"]);
                if ($cnt > 0) {
                    $dbResult = $DB->Query("SELECT ID FROM b_bp_workflow_instance WI WHERE WORKFLOW_TEMPLATE_ID = ".intval($id)."");
                    while ($arResult = $dbResult->Fetch()) {
                        CBPAllTaskService::DeleteByWorkflow($arResult["ID"]);
                    }
                    $DB->Query("DELETE FROM b_bp_workflow_instance WHERE WORKFLOW_TEMPLATE_ID = ".intval($id)."");
                    $DB->Query("DELETE FROM b_bp_workflow_state WHERE WORKFLOW_TEMPLATE_ID = ".intval($id)."");
                }
            }
            CBPWorkflowTemplateLoader::delete($id);
        }
    }
}
