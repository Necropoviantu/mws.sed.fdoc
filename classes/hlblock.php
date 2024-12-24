<?php
use Bitrix\Main\Loader;
use Bitrix\Highloadblock as HL;


class hlblock
{

    public static function hlblockAdd()
    {
        Loader::includeModule("highloadblock");

        $arLangs = array(
            'ru' => "Шаблоны Списание",
        );

        $result = HL\HighloadBlockTable::add(array(
            "NAME" => "TemplateDocSed",
            "TABLE_NAME" => "template_document_sed",
        ));
        if ($result->isSuccess()) {
            $id = $result->getId();

            $res = COption::SetOptionString("mws.sed.fdoc", "mws_sed_fdoc_template_document_sed", $id);

            foreach ($arLangs as $lang_key => $lang_value) {
                HL\HighloadBlockTable::add(array(
                    'ID' => $id,
                    'LID' => $lang_key,
                    'NAME' => $lang_value,
                ));
            }


        } else {
            $errors = $result->getErrorMessages();
            return $errors;
        }

        $UFOBject = 'HLBLOCK_' . $id;
        $arCartFields = array(


            "UF_TEMPLATE_CATEGORY" => array(
                'ENTITY_ID'=>$UFOBject,
                'FIELD_NAME'=>'UF_TEMPLATE_CATEGORY',
                'USER_TYPE_ID'=>'string',
                'MANDATORY'=>'Y',
                "EDIT_FORM_LABEL" => Array('ru'=>'Воронка в сделке', 'en'=>'Template name'),
                "LIST_COLUMN_LABEL" => Array('ru'=>'Воронка в сделке', 'en'=>'Template name'),
                "LIST_FILTER_LABEL" => Array('ru'=>'Воронка в сделке', 'en'=>'Template name'),
                "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
            ),

            "UF_TEMPLATE_TEMPLATES" => array(
                'ENTITY_ID'=>$UFOBject,
                'FIELD_NAME'=>'UF_TEMPLATE_TEMPLATES',
                'USER_TYPE_ID'=>'string',
                'MANDATORY'=>'Y',
                "EDIT_FORM_LABEL" => Array('ru'=>'Шаблоны', 'en'=>'ID Service'),
                "LIST_COLUMN_LABEL" => Array('ru'=>'Шаблоны', 'en'=>'ID Service'),
                "LIST_FILTER_LABEL" => Array('ru'=>'Шаблоны', 'en'=>'ID Service'),
                "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
            ),


        );

        $arSavedFieldsRes = Array();
        foreach($arCartFields as $arCartField){
            $obUserField  = new CUserTypeEntity;
            $ID = $obUserField->Add($arCartField);
            $arSavedFieldsRes[] = $ID;
        }

        return true;

    }


    public static function hlblockDelete()
    {
        Loader::includeModule("highloadblock");

        $LKtoUpdate = COption::GetOptionString("mws.sed.fdoc", "mws_sed_fdoc_template_document_sed", 0);

        if($LKtoUpdate) {
            $primary =[
                'ID'=>$LKtoUpdate,
                'LID'=>'ru'

            ];
            Bitrix\Highloadblock\HighloadBlockLangTable::delete($primary);
            Bitrix\Highloadblock\HighloadBlockTable::delete(['ID'=>$LKtoUpdate]);
            return true;
        }


    }


}