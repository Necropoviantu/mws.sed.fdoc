<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Mywebstor\Fdoc\MwsSedFdocUsersTable;

class MwsSedFdocRest extends IRestService
{
    public static function OnRestServiceBuildDescription()
    {
        return array(
            "mwssedfdoc" => array(
                "mwssedfdoc.getUsersBitrix" => array(__CLASS__, "getUsersBitrix"),
                "mwssedfdoc.setUsersBitrix" => array(__CLASS__, "setUsersBitrix"),
                "mwssedfdoc.getConnectOptions" => array(__CLASS__, "getConnectOptions"),
                "mwssedfdoc.setConnectOptions" => array(__CLASS__, "setConnectOptions"),
                "mwssedfdoc.getAllUsersFdoc" => array(__CLASS__, "getAllUsersFdoc"),
                "mwssedfdoc.deleteUsers" => array(__CLASS__, "deleteUsers"),
                //TODO Запросы в Fdoc;
                "mwssedfdoc.getCorpId" => array(__CLASS__, "getCorpId"),

            ),
        );
    }

    public static function getUsersBitrix($query, $nav, \CRestServer $server)
    {
        $result = \Bitrix\Main\UserTable::getList([
            'filter' => [
                'ACTIVE' => 'Y',
            ]
        ]);
        $usersBitrix = [];
        while ($user = $result->fetch()) {
            $usersBitrix[] = [
                'ID' => $user['ID'],
                'FULL_NAME' => $user['LAST_NAME'] . ' ' . $user['NAME'],
            ];
        }
        return $usersBitrix;
    }

    public static function getAllUsersFdoc($query, $nav, \CRestServer $server)
    {
        \Bitrix\Main\Loader::includeModule('mws.sed.fdoc');


        $currentUserObject = MwsSedFdocUsersTable::getList([
            "runtime" => [

                new \Bitrix\Main\Entity\ReferenceField(
                    'USER',
                    Bitrix\Main\UserTable::getEntity(),
                    ['=this.ID' => 'ref.ID']
                ),


            ],
            'select' => ['ID', 'BASE', 'lastNAME' => 'USER.LAST_NAME', 'firstNAME' => 'USER.NAME'],

        ]);

        $users = [];
        while ($res = $currentUserObject->fetch()) {
//            $creds = explode(':', base64_decode($res['BASE']));

            $users[] = [

                'ID' => $res['ID'],
                'FULL_NAME' => $res['lastNAME'] . ' ' . $res['firstNAME'],
                'BASE' => $res['BASE'],
//                'login' => $creds[0],
//                'password' => $creds[1],
            ];


        }

        return $users;
    }

    public static function deleteUsers($query, $nav, \CRestServer $server)
    {
        \Bitrix\Main\Loader::includeModule('mws.sed.fdoc');

        $rows = $query['USERS'];

            foreach ($rows as $row) {
            $currentUserObject = MwsSedFdocUsersTable::delete($row['ID']);
            }
        return 'ok';
    }


    public static function setUsersBitrix($query, $nav, \CRestServer $server)
    {
        \Bitrix\Main\Loader::includeModule('mws.sed.fdoc');
        $user = $query['USER'];

        $currentUserObject = MwsSedFdocUsersTable::createObject(array("ID" => $user['bxuser']['ID']));

        $currentUserObject
            ->set('BASE', base64_encode($user["login"] . ":" . $user["password"]));

        $saveResult = $currentUserObject->save();

        if (!$saveResult->isSuccess()) {

            return 'error';

        } else {

            return 'save';

        }
    }

    public static function getConnectOptions($query, $nav, \CRestServer $server)
    {
        $credentials = [
            'urlApi' => Option::get('mws.sed.fdoc', 'credentials_fdoc_urlApi', ''),
            'keyApi' => Option::get('mws.sed.fdoc', 'credentials_fdoc_keyApi', ''),
            'loginApi'=> Option::get('mws.sed.fdoc', 'credentials_fdoc_loginApi', ''),
            'passwordApi'=> Option::get('mws.sed.fdoc', 'credentials_fdoc_passwordApi', ''),
            'login' => Option::get('mws.sed.fdoc', 'credentials_fdoc_login', ''),
            'password' => Option::get('mws.sed.fdoc', 'credentials_fdoc_password', ''),
            'base64' => base64_encode(Option::get('mws.sed.fdoc', 'credentials_fdoc_login', '') . ":" . Option::get('mws.sed.fdoc', 'credentials_fdoc_password', ''))
        ];

        return $credentials;
    }

    public static function setConnectOptions($query, $nav, \CRestServer $server)
    {
        $set = $query['credentials'];

        Option::set('mws.sed.fdoc', 'credentials_fdoc_urlApi', $set['urlApi']);
        Option::set('mws.sed.fdoc', 'credentials_fdoc_keyApi', $set['keyApi']);
        Option::set('mws.sed.fdoc', 'credentials_fdoc_loginApi', $set['loginApi']);
        Option::set('mws.sed.fdoc', 'credentials_fdoc_passwordApi', $set['passwordApi']);
        Option::set('mws.sed.fdoc', 'credentials_fdoc_login', $set['login']);
        Option::set('mws.sed.fdoc', 'credentials_fdoc_password', $set['password']);

        return 'save';
    }

    public static function getCorpId($query, $nav, \CRestServer $server){

        \Bitrix\Main\Loader::includeModule('mws.sed.fdoc');

        $credentials = [
            'urlApi' => Option::get('mws.sed.fdoc', 'credentials_fdoc_urlApi', ''),
            'keyApi' => Option::get('mws.sed.fdoc', 'credentials_fdoc_keyApi', ''),
            'loginApi'=> Option::get('mws.sed.fdoc', 'credentials_fdoc_loginApi', ''),
            'passwordApi'=> Option::get('mws.sed.fdoc', 'credentials_fdoc_passwordApi', ''),
            'login' => Option::get('mws.sed.fdoc', 'credentials_fdoc_login', ''),
            'password' => Option::get('mws.sed.fdoc', 'credentials_fdoc_password', ''),
            'base64' => base64_encode(Option::get('mws.sed.fdoc', 'credentials_fdoc_login', '') . ":" . Option::get('mws.sed.fdoc', 'credentials_fdoc_password', ''))

        ];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $credentials['urlApi'].'api/v1/verifyApiKey?apiKey='.$credentials['keyApi'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic '. base64_encode($credentials['loginApi'].":".$credentials['passwordApi']),
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response)['corpId'];
    }

    public static function getAuthToken($query, $nav, \CRestServer $server)
    {





    }


}