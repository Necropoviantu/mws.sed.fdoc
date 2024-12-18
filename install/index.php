<?php
 use Bitrix\Main\Localization\Loc;

 Loc::loadLanguageFile(__FILE__);
class mws_sed_fdoc extends CModule{
    public $MODULE_ID = "mws.sed.fdoc";

    public $errors = "";

    static $events = array(
        array(
            "FROM_MODULE" => "rest",
            "FROM_EVENT" => "OnRestServiceBuildDescription",
            "TO_CLASS" => "MwsSedFdocRest",
            "TO_FUNCTION" => "OnRestServiceBuildDescription",
            "VERSION" => "1"
        ),
        array(
            "FROM_MODULE" => "main",
            "FROM_EVENT" => "OnEpilog",
            "TO_CLASS" => "RegistrateJs",
            "TO_FUNCTION" => "loadCustomJsCss",
            "VERSION" => "1"
        ),
    );

    public function __construct()
    {
        $this->MODULE_GROUP_RIGHTS = "N";
        $this->MODULE_NAME = Loc::getMessage("MWS_SED_FDOC_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("MWS_SED_FDOC_MODULE_DESCRIPTION");
        $this->PARTNER_NAME = Loc::getMessage("MWS_SED_FDOC_MODULE_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("MWS_SED_FDOC_MODULE_PARTNER_URI");

        $arModuleVersion = array();
        include __DIR__ . "/version.php";
        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }
    }

    public function DoInstall()
    {
        $this->InstallDB();
        $this->installFiles();
        $this->InstallEvents();
        \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
        return true;
    }

    public function DoUninstall()
    {
        global $APPLICATION, $USER, $DB, $step;
        $step = intval($step);
        if ($step < 2) {
            $APPLICATION->IncludeAdminFile(Loc::getMessage("MWS_SED_FDOC_MODULE_UNINSTALL_TITLE", array("#MODULE_NAME#" => $this->MODULE_NAME)), __DIR__ . "/unstep1.php");
        } elseif ($step === 2) {
            if (!array_key_exists('savedata', $_REQUEST) || $_REQUEST['savedata'] != 'Y') {
                $this->UnInstallDB();
            }
            $this->UnInstallFiles();
            $this->UnInstallEvents();
            \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
           // \Bitrix\Main\Config\Option::delete("mws.sed.fdoc");
            return true;
        }


        return true;
    }

    //TODO Копируем файлы

    public function InstallFiles()
    {
        CopyDirFiles(
            __DIR__ . "/local/admin/",
            \Bitrix\Main\Application::getDocumentRoot() . "/bitrix/admin/",
            true,
            true
        );
        return true;

    }
    //TODO Удаляем файлы
    public function UnInstallFiles()
    {
        DeleteDirFilesEx("/bitrix/admin/mwssedfdocsettings.php");
        return true;
    }

    //TODO регистрируем Эвенты
    public function installEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        foreach (static::$events as $event)
            switch ($event["VERSION"]) {
                case "2":
                    $eventManager->registerEventHandler($event["FROM_MODULE"], $event["FROM_EVENT"], $this->MODULE_ID, $event["TO_CLASS"], $event["TO_FUNCTION"]);
                    break;
                case "1":
                default:
                    $eventManager->registerEventHandlerCompatible($event["FROM_MODULE"], $event["FROM_EVENT"], $this->MODULE_ID, $event["TO_CLASS"], $event["TO_FUNCTION"]);
                    break;
            }
        return true;
    }

    //TODO Удаляем Эвенты
    public function UnInstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        foreach (static::$events as $event) {
            $eventManager->unregisterEventHandler($event["FROM_MODULE"], $event["FROM_EVENT"], $this->MODULE_ID, $event["TO_CLASS"], $event["TO_FUNCTION"]);
        }
        return true;
    }

    //TODO добавляем Таблицу в базу
    public function InstallDB()
    {
        global $DB, $APPLICATION;
        $this->errors = $DB->RunSQLBatch(__DIR__ . '/local/db/install.sql');
        if (is_array($this->errors)) {
            $APPLICATION->ThrowException(implode('<br />', $this->errors));
            return false;
        }
        return true;
    }
    //TODO Удаляем таблицу
    public function UnInstallDB()
    {
        /** @var \CMain $APPLICATION */
        /** @var \CDatabase $DB */
        global $DB, $APPLICATION;
        $this->errors = $DB->RunSQLBatch(__DIR__ . '/db/uninstall.sql');
        if (is_array($this->errors)) {
            throw new \Exception(implode('<br />', $this->errors));
            return false;
        }


        return true;
    }

}