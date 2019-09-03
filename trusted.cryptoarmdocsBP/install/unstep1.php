<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

if (!check_bitrix_sessid()) {
    return;
}

Loc::loadMessages(__FILE__);

$APPLICATION->SetTitle(Loc::getMessage("TR_CA_DOCS_UNINSTALL_TITLE"));
?>

<form action="<?= $APPLICATION->GetCurPage() ?>">
    <?= bitrix_sessid_post() ?>
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="hidden" name="id" value="trusted.cryptoarmdocsBP">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="step" value="2">
    <? echo CAdminMessage::ShowMessage(Loc::getMessage("MOD_UNINST_WARN")) ?>
    <?
    //check on active workflows, based on installed Cryptoarm templates. Sends warning, if founds some
    if (IsModuleInstalled("bizproc")) {
        $templateIds = preg_split('/ /', Option::get(TR_CA_DOCS_MODULE_ID, TR_CA_DOCS_TEMPLATE_ID), null, PREG_SPLIT_NO_EMPTY);
        global $DB;
        $found = false;
        foreach ($templateIds as $id) {
            $dbResult = $DB->Query(
                "SELECT COUNT('x') as CNT ".
                "FROM b_bp_workflow_instance WI ".
                "WHERE WI.WORKFLOW_TEMPLATE_ID = ".intval($id)." "
            );

            if ($arResult = $dbResult->Fetch()) {
                $count = intval($arResult["CNT"]);
                if ($count > 0) {$found = true;}
            }
        }

        if ($found) {echo CAdminMessage::ShowMessage(Loc::getMessage("TR_CA_DOCS_UNINST_TEMPLATES")); }
    }
    ?>
    <!-- <div style="border-top: 1px solid;
                border-bottom: 1px solid;
                border-color: #BDCADB;
                margin: 16px 0;
                display: inline-block;
                padding: 15px 30px 15px 18px;">
        <p>
            <input type="checkbox" name="deletedata" id="deletedata" value="Y">
            <label for="deletedata"><? //echo Loc::getMessage("TR_CA_DOCS_UNINST_DELETE_DATA") ?></label>
        </p>
        <p><? //echo nl2br(Loc::getMessage("TR_CA_DOCS_UNINST_SAVE_PROMPT")) ?></p>
    </div> -->
    <br/>
    <input type="submit" name="choice" value="<?= Loc::getMessage("MOD_BACK") ?>">
    <input type="submit" name="uninst" value="<?= Loc::getMessage("MOD_UNINST_DEL") ?>">
</form>
