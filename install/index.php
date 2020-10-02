<?php

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ModuleManager,
    Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class dky_savebasket extends CModule {

    public $MODULE_ID = "dky.savebasket";
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_GROUP_RIGHTS = "N";

    function __construct() {
        $arModuleVersion = array();
        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path . "/version.php");
        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }
        $this->MODULE_NAME = "Save basket";
        $this->MODULE_DESCRIPTION = "Save basket shop";
        $this->PARTNER_NAME = "ИП Бреский Дмитрий Игоревич";
        $this->PARTNER_URI = "https://github.com/dimabresky/";
    }

    function DoInstall() {

        try {
            ModuleManager::registerModule($this->MODULE_ID);

            // install db tables
            $this->installDBTables();

            // install dependencies
            $this->installDependencies();
        } catch (Exception $ex) {
            $GLOBALS["APPLICATION"]->ThrowException($ex->getMessage());
            $this->DoUninstall();
            return false;
        }

        return true;
    }

    function DoUninstall() {

        Option::delete($this->MODULE_ID);

        // uninstall db tables
        $this->uninstallDBTables();

        // uninstall module dependecies
        $this->uninstallDependencies();
        ModuleManager::unRegisterModule($this->MODULE_ID);

        return true;
    }

    function installDBTables() {
        global $DB;

        // bonuses history table
        if (!$DB->Query('CREATE TABLE IF NOT EXISTS `savebasket`('
                        . 'ID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,'
                        . 'FUSER_ID INT UNSIGNED,'
                        . 'PRODUCT_ID INT UNSIGNED,'
                        . 'BASKET_ID INT UNSIGNED,'
                        . 'QUANTITY SMALLINT,'
                        . 'DATE DATETIME,'
                        . 'SHIPPING_DATE DATETIME'
                        . ')', true)) {
            throw new Exception('savebasket table creation error');
        }

        $DB->Query('CREATE INDEX fuser_id on `savebasket`(FUSER_ID)', true);
        $DB->Query('CREATE INDEX basket_id on `savebasket`(BASKET_ID)', true);

        return true;
    }

    function uninstallDBTables() {
        global $DB;

        $DB->Query('DROP TABLE IF EXISTS `savebasket`', true);

        return true;
    }

    function installDependencies() {
        
    }

    function uninstallDependencies() {
        
    }

}
