<?php

defined('B_PROLOG_INCLUDED') || die;

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;
use Bitrix\Main\UI;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

if (CModule::IncludeModuleEx('trusted.cryptoarmdocs') == MODULE_DEMO_EXPIRED) {
    echo GetMessage("TR_CA_DOCS_MODULE_DEMO_EXPIRED");
    return false;
};

Loader::includeModule('trusted.cryptoarmdocs');
CJSCore::Init('bp_starter');

UI\Extension::load("ui.buttons.icons");

$APPLICATION->SetTitle(Loc::getMessage('TR_CA_DOCS_CRM_LIST_TITLE'));

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/trusted.cryptoarmdocs/admin/trusted_cryptoarm_docs.php');

$schema = array(
    'GRID_ID' => 'crm_docs_grid',
    'SHOW_PAGINATION' => false,
    'SHOW_PAGESIZE' => false,
    'SHOW_TOTAL_COUNTER' => false,
    'ALLOW_SORT' => false,
    'STRUCTURE' => array(
        'ID' => array(
            'NAME' => 'ID',
            'TYPE' => 'int',
            'DEFAULT' => true,
            'SORT' => true,
            'FILTER_TYPE' => 'text',
        ),
        'NAME' => array(
            'NAME' => Loc::getMessage('TR_CA_DOCS_COL_FILENAME'),
            'TYPE' => 'text',
            'DEFAULT' => true,
            'SORT' => true,
            'FILTER_TYPE' => 'text',
        ),
        'SIGNATURES' => array(
            'NAME' => Loc::getMessage('TR_CA_DOCS_COL_SIGN'),
            'TYPE' => 'text',
            'DEFAULT' => true,
            'SORT' => true,
            'FILTER_TYPE' => 'text',
        ),
        'STATUS' => array(
            'NAME' => Loc::getMessage('TR_CA_DOCS_COL_STATUS'),
            'TYPE' => 'text',
            'DEFAULT' => true,
            'SORT' => true,
        ),
    ),
);

$gridBuilder = new Docs\GridBuilder($schema);

// print_r($gridBuilder->filter);
// print_r($gridBuilder->sort);
// print_r($gridBuilder->pagination);
$docs = Docs\Database::getDocumentsByUser($USER->GetID(), true);

$rows = array();

foreach ($docs->getList() as $doc) {
    $docId = $doc->getId();

    $signatures = $doc->getSignaturesToArray();

    $docStatus = $doc->getStatus();

    $docStatusString = Docs\Utils::GetTypeString($doc);
    if ($docStatus !== DOC_STATUS_NONE) {
        $docStatusString .= '<br>' .
            Loc::getMessage('TR_CA_DOCS_STATUS') .
            Docs\Utils::GetStatusString($doc);
    }

    $actions = array();
    if ($docStatus === DOC_STATUS_NONE) {
        $actions[] = array(
            'text' => Loc::getMessage('TR_CA_DOCS_ACT_SIGN'),
            'onclick' => "trustedCA.sign([$docId])",
            'default' => true,
        );
    }
    if ($docStatus === DOC_STATUS_BLOCKED) {
        $actions[] = array(
            'text' => Loc::getMessage('TR_CA_DOCS_ACT_UNBLOCK'),
            'onclick' => "trustedCA.unblock([$docId], {$gridBuilder->reloadGridJs})",
            'default' => true,
        );
    }
    $actions[] = array(
        'text' => Loc::getMessage('TR_CA_DOCS_ACT_PROTOCOL'),
        'onclick' => "trustedCA.protocol($docId)",
        'default' => false,
    );

    if (!empty($arResult['WORKFLOW_TEMPLATES'])) {
        $startWorkflowActions = array();
        foreach ($arResult['WORKFLOW_TEMPLATES'] as $workflowTemplate) {
            $starterParams = array(
                'moduleId' => TR_CA_DOCS_MODULE_ID,
                'entity' => Docs\WorkflowDocument::class,
                'documentType' => 'TR_CA_DOC',
                'documentId' => $docId,
                'templateId' => $workflowTemplate['ID'],
                'templateName' => $workflowTemplate['NAME'],
                'hasParameters' => $workflowTemplate['HAS_PARAMETERS']
            );
            $popupMessage = Loc::getMessage('TR_CA_DOCS_POPUP_MESSAGE_1') . $workflowTemplate['NAME'] . Loc::getMessage('TR_CA_DOCS_POPUP_MESSAGE_2');
            $startWorkflowActions[] = array(
                'TITLE' => $workflowTemplate['DESCRIPTION'],
                'TEXT' => $workflowTemplate['NAME'],
                'ONCLICK' => sprintf(
                    'BX.Bizproc.Starter.singleStart(%s, %s)',
                    Json::encode($starterParams),
                    $gridBuilder->reloadGridJs . ', trustedCA.showPopupMessage("' . $popupMessage . '", "check_circles", "positive")'
                )
            );
        }
        // $actions[] = array('SEPARATOR' => true);
        $actions[] = array(
            'TITLE' => Loc::getMessage('TR_CA_DOCS_CRM_START_WORKFLOW'),
            'TEXT' => Loc::getMessage('TR_CA_DOCS_CRM_START_WORKFLOW'),
            'MENU' => $startWorkflowActions
        );
    }

    $actions[] = array(
        'text' => Loc::getMessage('TR_CA_DOCS_ACT_REMOVE'),
        'onclick' => "trustedCA.remove([$docId], false, {$gridBuilder->reloadGridJs})",
        'default' => false,
    );

    $downloadJs = "trustedCA.download([$docId], true)";
    $docName = "<a style='cursor:pointer;' onclick='$downloadJs' ondblclick='event.stopPropagation()' title='" . Loc::getMessage('TR_CA_DOCS_DOWNLOAD_DOC') . "'>{$doc->getName()}</a>";
    $rows[] = array(
        'id' => $docId,
        'columns' => array(
            'ID' => $docId,
            'NAME' => $docName,
            'SIGNATURES' => $doc->getSignaturesToTable(),
            'STATUS' => $docStatusString,
        ),
        'actions' => $actions,
    );
}

$gridBuilder->showGrid($rows);

$maxSize  = Docs\Utils::maxUploadFileSize();
$onSuccess = "() => { $('#tr_ca_form_upload').submit() }";
$onFailure = "() => { $('#tr_ca_upload_input').val(null) }";
$accessFileJS = "() => { trustedCA.checkAccessFile(this.files[0], $onSuccess, $onFailure) }";
$sizeFileJS = "trustedCA.checkFileSize(this.files[0], $maxSize, $accessFileJS, $onFailure)";
ob_start();
?>
<form enctype="multipart/form-data" method="POST" id= "tr_ca_form_upload">
    <div class="ui-btn ui-btn-primary ui-btn-icon-add crm-btn-toolbar-add tr_ca_upload_wrapper">
        <input class="tr_ca_upload_input" id= "tr_ca_upload_input" name="tr_ca_upload_comp_crm" type="file"
               onchange="<?= $sizeFileJS ?>">
        <?= Loc::getMessage('TR_CA_DOCS_CRM_ADD_DOC') ?>
    </div>
</form>
<?
$output = ob_get_contents();
ob_end_clean();
$APPLICATION->AddViewContent('pagetitle', $output, 100);
$reloadDoc = "trustedCA.reloadGrid('crm_docs_grid')";
?>
<a id="trca-reload-doc" onclick="<?= $reloadDoc ?>"></a>

<?
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

if(!$arResult["NavShowAlways"])
{
	if ($arResult["NavRecordCount"] == 0 || ($arResult["NavPageCount"] == 1 && $arResult["NavShowAll"] == false))
		return;
}

$strNavQueryString = ($arResult["NavQueryString"] != "" ? $arResult["NavQueryString"]."&amp;" : "");
$strNavQueryStringFull = ($arResult["NavQueryString"] != "" ? "?".$arResult["NavQueryString"] : "");
?>

<font class="text"><?=$arResult["NavTitle"]?> 

<?if($arResult["bDescPageNumbering"] === true):?>

	<?=$arResult["NavFirstRecordShow"]?> <?=GetMessage("nav_to")?> <?=$arResult["NavLastRecordShow"]?> <?=GetMessage("nav_of")?> <?=$arResult["NavRecordCount"]?><br /></font>

	<font class="text">

	<?if ($arResult["NavPageNomer"] < $arResult["NavPageCount"]):?>
		<?if($arResult["bSavePage"]):?>
			<a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=$arResult["NavPageCount"]?>"><?=GetMessage("nav_begin")?></a>
			|
			<a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=($arResult["NavPageNomer"]+1)?>"><?=GetMessage("nav_prev")?></a>
			|
		<?else:?>
			<a href="<?=$arResult["sUrlPath"]?><?=$strNavQueryStringFull?>"><?=GetMessage("nav_begin")?></a>
			|
			<?if ($arResult["NavPageCount"] == ($arResult["NavPageNomer"]+1) ):?>
				<a href="<?=$arResult["sUrlPath"]?><?=$strNavQueryStringFull?>"><?=GetMessage("nav_prev")?></a>
				|
			<?else:?>
				<a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=($arResult["NavPageNomer"]+1)?>"><?=GetMessage("nav_prev")?></a>
				|
			<?endif?>
		<?endif?>
	<?else:?>
		<?=GetMessage("nav_begin")?>&nbsp;|&nbsp;<?=GetMessage("nav_prev")?>&nbsp;|
	<?endif?>

	<?while($arResult["nStartPage"] >= $arResult["nEndPage"]):?>
		<?$NavRecordGroupPrint = $arResult["NavPageCount"] - $arResult["nStartPage"] + 1;?>

		<?if ($arResult["nStartPage"] == $arResult["NavPageNomer"]):?>
			<b><?=$NavRecordGroupPrint?></b>
		<?elseif($arResult["nStartPage"] == $arResult["NavPageCount"] && $arResult["bSavePage"] == false):?>
			<a href="<?=$arResult["sUrlPath"]?><?=$strNavQueryStringFull?>"><?=$NavRecordGroupPrint?></a>
		<?else:?>
			<a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=$arResult["nStartPage"]?>"><?=$NavRecordGroupPrint?></a>
		<?endif?>

		<?$arResult["nStartPage"]--?>
	<?endwhile?>

	|

	<?if ($arResult["NavPageNomer"] > 1):?>
		<a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=($arResult["NavPageNomer"]-1)?>"><?=GetMessage("nav_next")?></a>
		|
		<a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=1"><?=GetMessage("nav_end")?></a>
	<?else:?>
		<?=GetMessage("nav_next")?>&nbsp;|&nbsp;<?=GetMessage("nav_end")?>
	<?endif?>

<?else:?>

	<?=$arResult["NavFirstRecordShow"]?> <?=GetMessage("nav_to")?> <?=$arResult["NavLastRecordShow"]?> <?=GetMessage("nav_of")?> <?=$arResult["NavRecordCount"]?><br /></font>

	<font class="text">

	<?if ($arResult["NavPageNomer"] > 1):?>

		<?if($arResult["bSavePage"]):?>
			<a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=1"><?=GetMessage("nav_begin")?></a>
			|
			<a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=($arResult["NavPageNomer"]-1)?>"><?=GetMessage("nav_prev")?></a>
			|
		<?else:?>
			<a href="<?=$arResult["sUrlPath"]?><?=$strNavQueryStringFull?>"><?=GetMessage("nav_begin")?></a>
			|
			<?if ($arResult["NavPageNomer"] > 2):?>
				<a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=($arResult["NavPageNomer"]-1)?>"><?=GetMessage("nav_prev")?></a>
			<?else:?>
				<a href="<?=$arResult["sUrlPath"]?><?=$strNavQueryStringFull?>"><?=GetMessage("nav_prev")?></a>
			<?endif?>
			|
		<?endif?>

	<?else:?>
		<?=GetMessage("nav_begin")?>&nbsp;|&nbsp;<?=GetMessage("nav_prev")?>&nbsp;|
	<?endif?>

	<?while($arResult["nStartPage"] <= $arResult["nEndPage"]):?>

		<?if ($arResult["nStartPage"] == $arResult["NavPageNomer"]):?>
			<b><?=$arResult["nStartPage"]?></b>
		<?elseif($arResult["nStartPage"] == 1 && $arResult["bSavePage"] == false):?>
			<a href="<?=$arResult["sUrlPath"]?><?=$strNavQueryStringFull?>"><?=$arResult["nStartPage"]?></a>
		<?else:?>
			<a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=$arResult["nStartPage"]?>"><?=$arResult["nStartPage"]?></a>
		<?endif?>
		<?$arResult["nStartPage"]++?>
	<?endwhile?>
	|

	<?if($arResult["NavPageNomer"] < $arResult["NavPageCount"]):?>
		<a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=($arResult["NavPageNomer"]+1)?>"><?=GetMessage("nav_next")?></a>&nbsp;|
		<a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=$arResult["NavPageCount"]?>"><?=GetMessage("nav_end")?></a>
	<?else:?>
		<?=GetMessage("nav_next")?>&nbsp;|&nbsp;<?=GetMessage("nav_end")?>
	<?endif?>

<?endif?>


<?if ($arResult["bShowAll"]):?>
<noindex>
	<?if ($arResult["NavShowAll"]):?>
		|&nbsp;<a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>SHOWALL_<?=$arResult["NavNum"]?>=0" rel="nofollow"><?=GetMessage("nav_paged")?></a>
	<?else:?>
		|&nbsp;<a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>SHOWALL_<?=$arResult["NavNum"]?>=1" rel="nofollow"><?=GetMessage("nav_all")?></a>
	<?endif?>
</noindex>
<?endif?>

</font>