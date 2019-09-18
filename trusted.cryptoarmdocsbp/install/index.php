<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;
use Bitrix\Main\ModuleManager;
use Trusted\CryptoARM\Docs;
Loc::loadMessages(__FILE__);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/trusted.cryptoarmdocsbp/include.php';


Class trusted_cryptoarmdocsbp extends CModule
{
    // Required by the marketplace standards
    const MODULE_ID = "trusted.cryptoarmdocsbp";
    var $MODULE_ID = "trusted.cryptoarmdocsbp";
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $PARTNER_NAME;
    var $PARTNER_URI;

    function trusted_cryptoarmdocsbp()
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

        if (!self::d7Support() || !self::coreModuleInstalled()
        || !self::ModuleIsRelevant(ModuleManager::getVersion("trusted.cryptoarmdocs"), $arModuleVersion["VERSION"])
        || !self::ModuleIsRelevant($arModuleVersion["VERSION"], ModuleManager::getVersion("trusted.cryptoarmdocs"))) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage("MOD_INSTALL_TITLE"),
                 $DOCUMENT_ROOT . "/bitrix/modules/" . self::MODULE_ID . "/install/step_cancel.php"
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

        self::InstallFiles();
            //$this->CreateDocsDir();
            //$this->InstallModuleOptions();
            //$this->InstallDB();
            //$this->InstallIb();
            self::InstallMenuItems();
            //$this->InstallMailEvents();
        if (self::bizprocSupport()) {
            self::InstallBPTemplates();
        }

        ModuleManager::registerModule(self::MODULE_ID);

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
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . self::MODULE_ID . "/install/components/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/",
            true, true
        );

        if (self::bizprocSupport()) {
            CopyDirFiles(
                $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . self::MODULE_ID . "/install/activities/",
                $_SERVER["DOCUMENT_ROOT"] . "/bitrix/activities/custom/",
                true, true
            );
            CopyDirFiles(
                $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . self::MODULE_ID . "/install/crm_pub/",
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
        $siteInfo = self::getSiteInfo();
        if (self::crmSupport()) {
            self::AddMenuItem(
                $siteInfo["DIR"] . ".top.menu.php",
                array(
                    Loc::getMessage('TR_CA_DOCS_CRM_MENU_TITLE'),
                    $siteInfo["DIR"] . "tr_ca_docs/",
                    array(),
                    array(),
                    "IsModuleInstalled('" . self::MODULE_ID . "')"
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
                $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . self::MODULE_ID . "/install/unstep1.php"
            );
        }

        if ($request["uninst"] === Loc::getMessage("MOD_UNINST_DEL")) {
            self::UnInstallMenuItems();
            if (self::bizprocSupport()) {
                self::UninstallBPTemplates();
            }
            self::UnInstallFiles();
            ModuleManager::unRegisterModule(self::MODULE_ID);
            // $APPLICATION->IncludeAdminFile(
            //     Loc::getMessage("MOD_UNINSTALL_TITLE"),
            //     $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/unstep2.php"
            // );
        }
    }

    function UnInstallFiles()
    {
        DeleteDirFilesEx("/bitrix/components/trusted/cryptoarm_docs_crm/");

        DeleteDirFilesEx("/tr_ca_docs/");
        DeleteDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . self::MODULE_ID . "/install/activities/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/activities/custom/"
        );
        CUrlRewriter::Delete(
            array(
                'ID' => 'trusted:cryptoarm_docs_crm',
                'PATH' => '/tr_ca_docs/index.php',
            )
        );

        return true;
    }


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
        $templateIds[] = self::ImportBPTemplateFromFile('MoneyDemand.bpt', Loc::getMessage("TR_CA_DOCS_BP_MONEY_DEMAND"));
        $templateIds[] = self::ImportBPTemplateFromFile('Acquaintance.bpt', Loc::getMessage("TR_CA_DOCS_BP_ACQUAINTANCE"));
        $templateIds[] = self::ImportBPTemplateFromFile('SetSignResponsibility.bpt', Loc::getMessage("TR_CA_DOCS_BP_SIGN_TEMPLATE"));
        $templateIds[] = self::ImportBPTemplateFromFile('Order.bpt', Loc::getMessage("TR_CA_DOCS_BP_ORDER"));
        $templateIds[] = self::ImportBPTemplateFromFile('ServiceNote.bpt', Loc::getMessage("TR_CA_DOCS_BP_SERVICE_NOTE"));
        $templateIds[] = self::ImportBPTemplateFromFile('AgreedOn.bpt', Loc::getMessage("TR_CA_DOCS_BP_AGREED_TEMPLATE"));

        Option::set(TR_CA_DOCS_MODULE_ID, TR_CA_DOCS_TEMPLATE_ID, implode(" ", $templateIds));
    }

    function ImportBPTemplateFromFile ($filename, $templatename) {
        $file = fopen($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/trusted.cryptoarmdocsbp/resources/".$filename, 'r');
        $data = fread($file, filesize($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/trusted.cryptoarmdocsbp/resources/".$filename));
        fclose($file);
        $templateId = CBPWorkflowTemplateLoader::ImportTemplate(0, ["trusted.cryptoarmdocsbp", "Trusted\CryptoARM\Docs\WorkflowDocument", "TR_CA_DOC"], true, $templatename, "", $data);
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
