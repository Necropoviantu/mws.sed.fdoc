<?php
use Bitrix\Crm\CompanyTable;
use Bitrix\Crm\DealTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;

class RegistrateJs
{
   public static function  loadCustomJsCss()
    {
        global $USER;
        $res = CUser::GetUserGroup($USER->getId());

        if(in_array(36,$res) || $USER->IsAdmin() ){

        // if($USER->IsAdmin()) {
            \CJSCore::RegisterExt("mws.sed.fdoc.button", array(
                "js" => array(
                    "/local/modules/mws.sed.fdoc/js/addButtonSedFdoc.js"
                )
            ));
            \CJSCore::Init(array(
                "mws.sed.fdoc.button"
            ));
        // }
        }


    }


}