<?php
namespace Mywebstor\Fdoc;

use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\Query\Join;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\DateField;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\UserTable;

Loc::loadMessages(__FILE__);

class MwsSedFdocSendTable extends DataManager
{
    public static function getTableName()
    {
        return 'mws_sed_fdoc_send';
    }
    public static function getMap()
    {
        return array(
            new IntegerField('ID',
                array(
                    'primary' => true,
                    'autocomplete' => true,
                    'title' => Loc::getMessage('MWS_SED_FDOC_SEND_ID_FIELD'),
                )
            ),
            new IntegerField('ENTITY_ID',
                array(
                    'required' => true,
                    'default_value' => '',
                    'title' => Loc::getMessage('MWS_SED_FDOC_SEND_ENTITY_ID_FIELD'),
                )
            ),
            new IntegerField('DEAL_ID',
                array(
                    'required' => true,
                    'default_value' => '',
                    'title' => Loc::getMessage('MWS_SED_FDOC_SEND_DEAL_ID_FIELD'),
                )
            ),
            new IntegerField('SEND_ID',
                array(
                    'required' => true,
                    'default_value' => '',
                    'title' => Loc::getMessage('MWS_SED_FDOC_SEND_SEND_ID_FIELD'),
                ),
            ),
            new StringField(
                'DOC_NAME',
                array(
                    'required' => true,
                    'default_value' => '',
                    'title' => Loc::getMessage('MWS_SED_FDOC_SEND_DOC_NAME_FIELD'),
                )
            ),
            new StringField(
                'PACKAGE_URL',
                array(
                    'required' => true,
                    'default_value' => '',
                    'title' => Loc::getMessage('MWS_SED_FDOC_SEND_PACKAGE_URL_FIELD'),
                )
            ),
            new DateField(
                'DATE_CREATE',
                array(
                    'required' => false,
                    'default_value' => '',
                    'title'=>Loc::getMessage('MWS_SED_FDOC_SEND_DATE_CREATE_FIELD'),
                )
            ),

        );
    }
}