<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Mywebstor\Fdoc\MwsSedFdocUsersTable;
use \Bitrix\Main\Type\DateTime;

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
                "mwssedfdoc.sendDealFilesFdoc" => array(__CLASS__, "sendDealFilesFdoc"),
                "mwssedfdoc.sendWebHookFdoc" => array(__CLASS__, "sendWebHookFdoc"),


                //TODO Запросы для фронта
                "mwssedfdoc.getAllFiles" => array(__CLASS__, "getAllFiles"),
                "mwssedfdoc.sendSelectedFiles" => array(__CLASS__, "sendSelectedFiles"),
                "mwssedfdoc.webhook" => array(__CLASS__, "webhook"),
            ),
        );
    }

    public static  function webhook($query, $nav, \CRestServer $server)
    {
        \Bitrix\Main\Diag\Debug::writeToFile(print_r($query,true),"","_webhook_log.log");
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
        $corpId = Option::get('mws.sed.fdoc', 'credentials_fdoc_corpId', "");

        if($corpId){
            return $corpId;
        }


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
        $corpId =json_decode($response,true)['corpId'];
        Option::set('mws.sed.fdoc', 'credentials_fdoc_corpId', $corpId);
        return $corpId;
    }
    public static function sendDealFilesFdoc($query, $nav, \CRestServer $server)
    {
        Bitrix\Main\Loader::includeModule('DocumentGenerator');

          $dealId = $query['DEAL_ID'];
          $templateIds = $query['TEMPLATE_IDS'];


        Bitrix\Main\Loader::includeModule('DocumentGenerator');


        $credentials = [
            'urlApi' => Option::get('mws.sed.fdoc', 'credentials_fdoc_urlApi', ''),
            'keyApi' => Option::get('mws.sed.fdoc', 'credentials_fdoc_keyApi', ''),
            'loginApi' => Option::get('mws.sed.fdoc', 'credentials_fdoc_loginApi', ''),
            'passwordApi' => Option::get('mws.sed.fdoc', 'credentials_fdoc_passwordApi', ''),
            'login' => Option::get('mws.sed.fdoc', 'credentials_fdoc_login', ''),
            'password' => Option::get('mws.sed.fdoc', 'credentials_fdoc_password', ''),
            'base64' => base64_encode(Option::get('mws.sed.fdoc', 'credentials_fdoc_login', '') . ":" . Option::get('mws.sed.fdoc', 'credentials_fdoc_password', ''))
        ];

        $corpId = Option::get('mws.sed.fdoc', 'credentials_fdoc_corpId', "");


        $dateTime = new \Bitrix\Main\Type\DateTime();
        $dateTime = $dateTime->add('+1 day');

        $doc = Bitrix\DocumentGenerator\Model\DocumentTable::getlist(
            [
                'filter' => [
                    '=PROVIDER' => mb_strtolower(Bitrix\Crm\Integration\DocumentGenerator\DataProvider\Deal::class),
                    'VALUE' => 1031,
                ]
            ]);

        $arrFiles = [];

        while ($row = $doc->fetch()) {

            $document = \Bitrix\DocumentGenerator\Document::loadById($row['ID']);

            $result = $document->getFile();
            $diskFileId = $result->getData()['emailDiskFile'];
            \Bitrix\Main\Loader::includeModule('disk');
            $diskFile = Bitrix\Disk\File::getById($diskFileId);
            $file = $diskFile->getFile();
            $arr = explode('.', $file['ORIGINAL_NAME']);
            $ext = end($arr);

            $base = file_get_contents('https://' . $_SERVER['SERVER_NAME'] . $file['SRC']);


            $uid = md5(uniqid(rand(), true));

            $arrFiles[] = [
                'id' => $file['ID'] . '_' .$uid,
                'name' => $file['ORIGINAL_NAME'],
                'file' => base64_encode($base),
                'unsignExpiredDate' => $dateTime->format("Y-m-d\TH:i:s\Z"),
            ];
        }

        $uidClient = md5(uniqid(rand(), true));

        $docPack = [
            'documents' => $arrFiles,
            'client' => [
                'id' => $uidClient,

                "phone" => "+79137080925", //забирать
                "name" => "Тестов Тест Тестович",//забирать


                "clientRole" => "Клиент"
            ],
            'package' => [
                'id' => $uidClient,
                "name" => "Документы по заявки" . $dealId,
                "operatorAutoSign" => true,
            ],
        ];

        $auth = [
            "apiKey" => $credentials['keyApi'],
            "grant" => $credentials['base64'],
            "grantType" => "password",
            "app" => $corpId,
            "corpId" => $corpId
        ];


        //получение токена
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $credentials['urlApi'] . 'api/v1/operator/accessToken',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($auth),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $tokenRAW = curl_exec($curl);

        curl_close($curl);

        $token = json_decode($tokenRAW, true)['accessToken'];


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $credentials['urlApi'] . 'api/v1/document',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($docPack, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: ' . $token,
            ),
        ));

        $response = curl_exec($curl);


        curl_close($curl);


        print_r($response);


    }

    public static function sendWebHookFdoc($query, $nav, \CRestServer $server)
    {

        $credentials = [
            'urlApi' => Option::get('mws.sed.fdoc', 'credentials_fdoc_urlApi', ''),
            'keyApi' => Option::get('mws.sed.fdoc', 'credentials_fdoc_keyApi', ''),
            'loginApi' => Option::get('mws.sed.fdoc', 'credentials_fdoc_loginApi', ''),
            'passwordApi' => Option::get('mws.sed.fdoc', 'credentials_fdoc_passwordApi', ''),
            'login' => Option::get('mws.sed.fdoc', 'credentials_fdoc_login', ''),
            'password' => Option::get('mws.sed.fdoc', 'credentials_fdoc_password', ''),
            'base64' => base64_encode(Option::get('mws.sed.fdoc', 'credentials_fdoc_login', '') . ":" . Option::get('mws.sed.fdoc', 'credentials_fdoc_password', ''))
        ];

        $corpId = Option::get('mws.sed.fdoc', 'credentials_fdoc_corpId', "");

        $curl = curl_init();


        $data = [
            "apiKey"=> $credentials['keyApi'],
            "events"=> [[
                         "url"=> $query['webHookUrl'],
                         "code"=> "SetStatus",
                         ]
                        ],
            "channelTypeCode"=> "API"
                 ];






        curl_setopt_array($curl, array(
            CURLOPT_URL => $credentials['urlApi'].'api/v1/corp/webhooks',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS =>json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Basic '. base64_encode($credentials['loginApi'].":".$credentials['passwordApi']),
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
       return $response;


    }

    public static function getAllFiles($query, $nav, \CRestServer $server)
    {

        $dealId = $query['dealId'];
        $dateTime = new \Bitrix\Main\Type\DateTime();
        $dateTime = $dateTime->add('+1 day');

        $doc = \Bitrix\DocumentGenerator\Model\DocumentTable::getlist(
            [
                'filter' => [
                    '=PROVIDER' => mb_strtolower(Bitrix\Crm\Integration\DocumentGenerator\DataProvider\Deal::class),
                    'VALUE' => $dealId,
                ]
            ]);

        $arrFiles = [];

        while ($row = $doc->fetch()) {
            $document = \Bitrix\DocumentGenerator\Document::loadById($row['ID']);

            $result = $document->getFile();
            $diskFileId = $result->getData()['emailDiskFile'];
            \Bitrix\Main\Loader::includeModule('disk');
            $diskFile = Bitrix\Disk\File::getById($diskFileId);
            $file = $diskFile->getFile();
            $row['FILE'] =$file;

            $arrFiles[] =$row;
        }

        return $arrFiles;
    }
    public static function sendSelectedFiles($query, $nav, \CRestServer $server)
    {
        Bitrix\Main\Loader::includeModule('DocumentGenerator');
        $rows  = $query['ROWS'];
        $dealId = $query['DEAL_ID'];






        $credentials = [
            'urlApi' => Option::get('mws.sed.fdoc', 'credentials_fdoc_urlApi', ''),
            'keyApi' => Option::get('mws.sed.fdoc', 'credentials_fdoc_keyApi', ''),
            'loginApi' => Option::get('mws.sed.fdoc', 'credentials_fdoc_loginApi', ''),
            'passwordApi' => Option::get('mws.sed.fdoc', 'credentials_fdoc_passwordApi', ''),
            'login' => Option::get('mws.sed.fdoc', 'credentials_fdoc_login', ''),
            'password' => Option::get('mws.sed.fdoc', 'credentials_fdoc_password', ''),
            'base64' => base64_encode(Option::get('mws.sed.fdoc', 'credentials_fdoc_login', '') . ":" . Option::get('mws.sed.fdoc', 'credentials_fdoc_password', ''))
        ];

        $corpId = Option::get('mws.sed.fdoc', 'credentials_fdoc_corpId', "");


        $dateTime = new \Bitrix\Main\Type\DateTime();
        $dateTime = $dateTime->add('+1 day');



        foreach ($rows as $row) {

            $document = \Bitrix\DocumentGenerator\Document::loadById($row['ID']);

            $result = $document->getFile();
            $diskFileId = $result->getData()['emailDiskFile'];
            \Bitrix\Main\Loader::includeModule('disk');
            $diskFile = Bitrix\Disk\File::getById($diskFileId);
            $file = $diskFile->getFile();
            $arr = explode('.', $file['ORIGINAL_NAME']);
            $ext = end($arr);

            $base = file_get_contents('https://' . $_SERVER['SERVER_NAME'] . $file['SRC']);


            $uid = md5(uniqid(rand(), true));

            $arrFiles[] = [
                'id' => $file['ID'] . '_' .$uid,
                'name' => $file['ORIGINAL_NAME'],
                'file' => base64_encode($base),
                'unsignExpiredDate' => $dateTime->format("Y-m-d\TH:i:s\Z"),
            ];
        }

        $uidClient = md5(uniqid(rand(), true));







        $docPack = [
            'documents' => $arrFiles,
            'client' => [
                'id' => $uidClient,

                "phone" => "+79137080925", //забирать
                "name" => "Тестов Тест Тестович",//забирать


                "clientRole" => "Клиент"
            ],
            'package' => [
                'id' => $uidClient,
                "name" => "Документы по заявки" . $dealId,
                "operatorAutoSign" => true,
            ],
        ];

        $auth = [
            "apiKey" => $credentials['keyApi'],
            "grant" => $credentials['base64'],
            "grantType" => "password",
            "app" => $corpId,
            "corpId" => $corpId
        ];


        //получение токена
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $credentials['urlApi'] . 'api/v1/operator/accessToken',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($auth),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $tokenRAW = curl_exec($curl);

        curl_close($curl);

        $token = json_decode($tokenRAW, true)['accessToken'];


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $credentials['urlApi'] . 'api/v1/document',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($docPack, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: ' . $token,
            ),
        ));

        $response = curl_exec($curl);


        curl_close($curl);


       return $response;



    }

}