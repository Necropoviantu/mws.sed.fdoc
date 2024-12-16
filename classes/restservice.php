<?php
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;


class MwsSedFdocRest extends IRestService
{
    public static function OnRestServiceBuildDescription()
    {
        return array(
            "mwssedfdoc"=>array(
                "mwssedfdoc.getUsersBitrix"=>array(__CLASS__,"getUsersBitrix"),
                "mwssedfdoc.setUsersBitrix"=>array(__CLASS__,"setUsersBitrix"),
            ),
        );
    }

    public static function getUsersBitrix($query, $nav, \CRestServer $server){
        $result =  \Bitrix\Main\UserTable::getList([
            'filter'=>[
                'ACTIVE' => 'Y',
            ]
        ]);
        $usersBitrix = [];
        while ($user = $result->fetch()) {
            $usersBitrix[] = [
                'ID'=>$user['ID'],
                'FULL_NAME' => $user['LAST_NAME'] . ' ' . $user['NAME'],
            ];
        }
        return $usersBitrix;
    }
    public static function setUsersBitrix($query, $nav, \CRestServer $server){

        return $query;

    }


}