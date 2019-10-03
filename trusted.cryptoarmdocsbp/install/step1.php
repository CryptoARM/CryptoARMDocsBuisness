<?php
use Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid()) {
    return;
}

Loc::loadMessages(__FILE__);

$APPLICATION->SetTitle(Loc::getMessage("TR_CA_DOCS_INSTALL_TITLE"));

?>

<form action="<?= $APPLICATION->GetCurPage() ?>">
<?=bitrix_sessid_post()?>
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="hidden" name="id" value="trusted.cryptoarmdocsbp">
    <input type="hidden" name="install" value="Y">
    <input type="submit" name="choice" value="<?= Loc::getMessage("TR_CA_DOCS_CONTINUE_INSTALL") ?>">
    <input type="submit" name="choice" value="<?= Loc::getMessage("TR_CA_DOCS_CANCEL_INSTALL") ?>">
</form>

