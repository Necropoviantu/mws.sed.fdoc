<?php

namespace Mywebstor\Fdoc;

use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\Query\Join;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\UserTable;

Loc::loadMessages(__FILE__);

class MwsSedFdocUsersTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */

    public static function getTableName()
    {
        return "mws_sed_fdoc_users";
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            'ID' => (new IntegerField('ID'))
                ->configurePrimary()
                ->configureRequired()
                ->configureDefaultValue(0)
                ->configureTitle(Loc::getMessage('MWS_SED_FDOC_USER_ID_FIELD')),
            'BASE' => (new StringField('BASE'))
                ->configureTitle(Loc::getMessage('MWS_SED_FDOC_USER_BASE_FIELD')),
            'USER_TITLE' => (new ExpressionField(
                'USER_TITLE',
                'CONCAT_WS(" ", IF(LENGTH(%s), %s, NULL), IF(LENGTH(%s), %s, NULL), IF(LENGTH(%s), %s, NULL))',
                array(
                    'USER.LAST_NAME',
                    'USER.LAST_NAME',
                    'USER.NAME',
                    'USER.NAME',
                    'USER.SECOND_NAME',
                    'USER.SECOND_NAME',
                )
            ))
                ->configureTitle(Loc::getMessage('MWS_SED_FDOC_USER_TITLE_FIELD')),
            'USER' => (new ReferenceField(
                'USER',
                UserTable::class,
                Join::on("this.ID", "ref.ID")
            ))
        );
    }

}