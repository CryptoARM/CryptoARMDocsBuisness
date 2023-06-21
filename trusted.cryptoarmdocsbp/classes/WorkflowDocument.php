<?php
namespace Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\GroupTable;
use Bitrix\Main\Loader;

// Loader::includeModule('trusted.cryptoarmdocsbp');

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/trusted.cryptoarmdocsbp/include.php";

if (isModuleInstalled(TR_CA_DOCS_CORE_MODULE)) {
    require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . TR_CA_DOCS_CORE_MODULE . "/include.php";
}

if (!Loader::IncludeModule('bizproc')) {
    return;
}

Loc::loadMessages(__FILE__);

class WorkflowDocument implements \IBPWorkflowDocument {

    public static $docType = 'TR_CA_DOC';

    public static function getDocumentType($id) {
        return self::$docType;
    }

    public static function getComplexDocumentType() {
        return [TR_CA_DOCS_BP_MODULE_ID, self::class, self::$docType];
    }

    public static function createDocument($parentDocumentId, $arFields) {
        $doc = Utils::createDocument($arFields['file'], array_diff_key($arFields, ['file' => true]));
        return $doc->getId();
    }

    public static function deleteDocument($documentId) {
        Database::getDocumentById($documentId)->getLastDocument()->remove();
    }

    public static function getDocument($documentId) {
        return Database::getDocumentById($documentId)->getLastDocument()->toArray();
    }

    public static function getDocumentFields($documentType) {
        return [
            'id' => [
                'Name' => 'ID',
                'Type' => FieldType::INT,
                'Editable' => false,
                'Required' => true,
            ],
            'name' => [
                'Name' => Loc::getMessage('TR_CA_DOC_NAME'),
                'Type' => FieldType::STRING,
                'Editable' => true,
                'Required' => true,
            ],
            'path' => [
                'Name' => Loc::getMessage('TR_CA_DOC_PATH'),
                'Type' => FieldType::STRING,
                'Editable' => false,
                'Required' => true,
            ],
            'type' => [
                'Name' => Loc::getMessage('TR_CA_DOC_TYPE'),
                'Type' => FieldType::STRING,
                'Editable' => false,
                'Required' => true,
            ],
            'author' => [
                'Name' => Loc::getMessage('TR_CA_DOC_AUTHOR'),
                'Type' => FieldType::STRING,
                'Editable' => false,
                'Required' => true,
            ],
            'status' => [
                'Name' => Loc::getMessage('TR_CA_DOC_STATUS'),
                'Type' => FieldType::STRING,
                'Editable' => false,
                'Required' => true,
            ],
            'signers' => [
                'Name' => Loc::getMessage('TR_CA_DOC_SIGNERS'),
                'Type' => FieldType::STRING,
                'Editable' => false,
                'Required' => true,
            ],
            'signatures' => [
                'Name' => Loc::getMessage('TR_CA_DOC_SIGNATURES'),
                'Type' => FieldType::STRING,
                'Editable' => false,
                'Required' => true,
            ],
            'hash' => [
                'Name' => Loc::getMessage('TR_CA_DOC_HASH'),
                'Type' => FieldType::STRING,
                'Editable' => false,
                'Required' => true,
            ],
        ];
    }

    public static function getComplexDocumentId($id) {
        return [TR_CA_DOCS_BP_MODULE_ID, self::class, $id];
    }

    public static function getDocumentAdminPage($documentId) {
        $doc = Database::getDocumentById($documentId);
        if (!$doc) {
            return '';
        }
        $lastDocId = $doc->getFirstParent()->getId();
        return "/bitrix/components/trusted/docs/ajax.php?command=content&id=$lastDocId&force=true";
    }

    public static function canUserOperateDocument($operation, $userId, $documentId, $arParameters = array()) {
        return true;
    }

    public static function canUserOperateDocumentType($operation, $userId, $documentType, $arParameters = array()) {
        // Can be used to disallow non-admins from accessing bp editor
        return true;
    }

    public static function updateDocument($documentId, $arFields, $modifiedById = null) {
    }

    public static function publishDocument($documentId) {
        return false;
    }

    public static function unpublishDocument($documentId) {
        return false;
    }

    public static function lockDocument($documentId, $workflowId) {
        return false;
    }

    public static function unlockDocument($documentId, $workflowId) {
        $doc = Database::getDocumentById($documentId)->getLastDocument();
        $doc->unblock();
        $doc->save();
        return true;
    }

    public static function isDocumentLocked($documentId, $workflowId) {
        return Database::getDocumentById($documentId)->getLastDocument()->getStatus() == DOC_STATUS_BLOCKED;
    }

    public static function getAllowableOperations($documentType) {
        return [];
    }

    public static function getAllowableUserGroups($documentType) {
        $dbAdminGroup = GroupTable::getById(1);
        $adminGroup = $dbAdminGroup->fetch();
        $author = Loc::getMessage('TR_CA_DOC_AUTHOR');
        $result = array(
            'group_1' => $adminGroup['NAME'],
            'author' => $author,
        );
        return $result;
    }

    public static function getUsersFromUserGroup($group, $documentId) {
        $group = strtolower($group);

        if ($group === 'author') {
            // No doc id in workflow editor
            if ($documentId !== self::$docType ) {
                $doc = Database::getDocumentById($documentId);
                return array($doc->getOwner());
            }
        }

        $groupId = intval(str_replace('group_', '', $group));
        if ($groupId <= 0) {
            return array();
        }

        return \CGroup::GetGroupUser($groupId);
    }

    public static function getDocumentForHistory($documentId, $historyIndex) {
        return false;
    }

    public static function recoverDocumentFromHistory($documentId, $arDocument) {
        return false;
    }

}

