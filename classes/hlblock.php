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
            "UF_TEMPLATE_NAME" => array(
                'ENTITY_ID'=>$UFOBject,
                'FIELD_NAME'=>'UF_TEMPLATE_NAME',
                'USER_TYPE_ID'=>'string',
                'MANDATORY'=>'Y',
                "EDIT_FORM_LABEL" => Array('ru'=>'Имя шаблона', 'en'=>'Template name'),
                "LIST_COLUMN_LABEL" => Array('ru'=>'Имя шаблона', 'en'=>'Template name'),
                "LIST_FILTER_LABEL" => Array('ru'=>'Имя шаблона', 'en'=>'Template name'),
                "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
            ),

            "UF_TEMPLATE_SERVICE_ID" => array(
                'ENTITY_ID'=>$UFOBject,
                'FIELD_NAME'=>'UF_TEMPLATE_SERVICE_ID',
                'USER_TYPE_ID'=>'string',
                'MANDATORY'=>'Y',
                "EDIT_FORM_LABEL" => Array('ru'=>'ID Услуги', 'en'=>'ID Service'),
                "LIST_COLUMN_LABEL" => Array('ru'=>'ID Услуги', 'en'=>'ID Service'),
                "LIST_FILTER_LABEL" => Array('ru'=>'ID Услуги', 'en'=>'ID Service'),
                "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
            ),

            "UF_TEMPLATE_PRODUCT_IDS" => array(
                'ENTITY_ID'=>$UFOBject,
                'FIELD_NAME'=>'UF_TEMPLATE_PRODUCT_IDS',
                'USER_TYPE_ID'=>'string',
                'MANDATORY'=>'N',
                "EDIT_FORM_LABEL" => Array('ru'=>'Шаблон Товаров', 'en'=>'Template products'),
                "LIST_COLUMN_LABEL" => Array('ru'=>'Шаблон Товаров', 'en'=>'Template products'),
                "LIST_FILTER_LABEL" => Array('ru'=>'Шаблон Товаров', 'en'=>'Template products'),
                "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
            ),

            'UF_TEMPLATE_COMMENT'=>array(
                'ENTITY_ID'=>$UFOBject,
                'FIELD_NAME'=>'UF_TEMPLATE_COMMENT',
                'USER_TYPE_ID'=>'string',
                'MANDATORY'=>'Y',
                "EDIT_FORM_LABEL" => Array('ru'=>'Комментарий', 'en'=>'Comment'),
                "LIST_COLUMN_LABEL" => Array('ru'=>'Комментарий', 'en'=>'Comment'),
                "LIST_FILTER_LABEL" => Array('ru'=>'Комментарий', 'en'=>'Comment'),
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

        $LKtoUpdate = COption::GetOptionString("mws.deal.entity", "mws_deal_entity_template_write_off", 0);;

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