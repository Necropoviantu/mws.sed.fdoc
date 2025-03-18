<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Mywebstor\Fdoc\MwsSedFdocUsersTable;
use Mywebstor\Fdoc\MwsSedFdocSendTable;
use \Bitrix\Main\Type\DateTime;
use Mywebstor\DealEntity\JobTable;
use Mywebstor\DealEntity\ConsumablesTable;

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
                "mwssedfdoc.getClientData" => array(__CLASS__, "getClientData"),

                //TODO Запросы в Fdoc;
                "mwssedfdoc.getCorpId" => array(__CLASS__, "getCorpId"),
                "mwssedfdoc.sendDealFilesFdoc" => array(__CLASS__, "sendDealFilesFdoc"),
                "mwssedfdoc.sendWebHookFdocSigned" => array(__CLASS__, "sendWebHookFdocSigned"),
                "mwssedfdoc.sendWebHookFdocQuery" => array(__CLASS__, "sendWebHookFdocQuery"),
                //TODO Запросы для фронта
                "mwssedfdoc.getAllFiles" => array(__CLASS__, "getAllFiles"),
                "mwssedfdoc.getAllDocByTemplate" => array(__CLASS__, "getAllDocByTemplate"),
                "mwssedfdoc.sendSelectedFiles" => array(__CLASS__, "sendSelectedFiles"),
                "mwssedfdoc.declineDoc" => array(__CLASS__, "declineDoc"),
                "mwssedfdoc.webhookSigned" => array(__CLASS__, "webhookSigned"),
                "mwssedfdoc.webhookQuery" => array(__CLASS__, "webhookQuery"),
                "mwssedfdoc.checkSendDocs" => array(__CLASS__, "checkSendDocs"),
                "mwssedfdoc.checkQueryDocs" => array(__CLASS__, "checkQueryDocs"),
                "mwssedfdoc.getDocumentClient" => array(__CLASS__, "getDocumentClient"),
                "mwssedfdoc.reloadDocuments" => array(__CLASS__, "reloadDocuments"),
                "mwssedfdoc.getDocList" => array(__CLASS__, "getDocList"),
                //TODO темплейты
                "mwssedfdoc.getTemplatesDoc" => array(__CLASS__, "getTemplatesDoc"),
                "mwssedfdoc.hlblock.create" => array(__CLASS__, "hlblockCreate"),
                "mwssedfdoc.hlblock.update" => array(__CLASS__, "hlblockUpdate"),
                "mwssedfdoc.hlblock.getList" => array(__CLASS__, "hlblockgetList"),
                "mwssedfdoc.hlblock.delete" => array(__CLASS__, "hlblockdelete"),

                "mwssedfdoc.getAllSend" => array(__CLASS__, "getAllSend"),
                "mwssedfdoc.getAllQuery" => array(__CLASS__, "getAllQuery"),
                "mwssedfdoc.recognize" => array(__CLASS__, "recognize"),
                "mwssedfdoc.downloadDocs" => array(__CLASS__, "downloadDocs"),
            ),
        );
    }

    public static function getAllQuery($query, $nav, \CRestServer $server){

        \Bitrix\Main\Loader::includeModule('mws.sed.fdoc');
         $dealId=$query['dealID'];
 
         $res = MwsSedFdocSendTable::getList(["filter"=>[
             "DEAL_ID" => $dealId,
             "TYPE_SEND"=>"QUERY",
         ]]);
         $result = [];
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
 
 
 
 
        
 
 
         while($row=$res->fetch()){
 
             $curl = curl_init();
             $build =  http_build_query([
                 'id'=>$row["SEND_ID"],
                 "idType"=>'package'
             ]);
     
     
             curl_setopt_array($curl, array(
                 CURLOPT_URL => $credentials['urlApi'].'/api/v1/document?'.$build,
                 CURLOPT_RETURNTRANSFER => true,
                 CURLOPT_ENCODING => '',
                 CURLOPT_MAXREDIRS => 10,
                 CURLOPT_TIMEOUT => 0,
                 CURLOPT_FOLLOWLOCATION => true,
                 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                 CURLOPT_CUSTOMREQUEST => 'GET',
                 CURLOPT_HTTPHEADER => array(
                     'Content-Type: application/json',
                     'Authorization: ' . $token,
                 ),
             ));
     
             $responseDocument = curl_exec($curl);
     
             curl_close($curl);
             $docks = json_decode($responseDocument,true);
             $result[] = [
             "ID"=> $row["ID"],
         "ENTITY_ID"=> $row["ENTITY_ID"],
         "DEAL_ID"=> $row["DEAL_ID"],
         "SEND_ID"=> $row["SEND_ID"],
         "DOC_NAME"=> $row["DOC_NAME"],
         "TYPE_SEND"=> $row["TYPE_SEND"],
         "STATUS"=> $row["STATUS"],
         "PACKAGE_URL"=> $docks['url'],
         "DATE_CREATE"=> $row["DATE_CREATE"]->format('d.m.Y'),
             ];
         }
         return $result;
     }
     public static function getAllSend($query, $nav, \CRestServer $server){
         \Bitrix\Main\Loader::includeModule('mws.sed.fdoc');
         $dealId=$query['dealID'];
 
         $res = MwsSedFdocSendTable::getList(["filter"=>[
             "DEAL_ID" => $dealId,
             "TYPE_SEND"=>"SIGNED",
         ]]);
 
             $result = [];
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
     
     
     
     
            
     
     
             while($row=$res->fetch()){
     
                 $curl = curl_init();
                 $build =  http_build_query([
                     'id'=>$row["SEND_ID"],
                     "idType"=>'package'
                 ]);
         
         
                 curl_setopt_array($curl, array(
                     CURLOPT_URL => $credentials['urlApi'].'/api/v1/document?'.$build,
                     CURLOPT_RETURNTRANSFER => true,
                     CURLOPT_ENCODING => '',
                     CURLOPT_MAXREDIRS => 10,
                     CURLOPT_TIMEOUT => 0,
                     CURLOPT_FOLLOWLOCATION => true,
                     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                     CURLOPT_CUSTOMREQUEST => 'GET',
                     CURLOPT_HTTPHEADER => array(
                         'Content-Type: application/json',
                         'Authorization: ' . $token,
                     ),
                 ));
         
                 $responseDocument = curl_exec($curl);
         
                 curl_close($curl);
                 $docks = json_decode($responseDocument,true);
                 $result[] = [
                 "ID"=> $row["ID"],
             "ENTITY_ID"=> $row["ENTITY_ID"],
             "DEAL_ID"=> $row["DEAL_ID"],
             "SEND_ID"=> $row["SEND_ID"],
             "DOC_NAME"=> $row["DOC_NAME"],
             "TYPE_SEND"=> $row["TYPE_SEND"],
             "STATUS"=> $row["STATUS"],
             "PACKAGE_URL"=> $docks['url'],
             "DATE_CREATE"=> $row["DATE_CREATE"]->format('d.m.Y'),
                 ];
             }
 
         return $result;
     }
    public static function getClientData($query, $nav, \CRestServer $server)
    {
        \Bitrix\Main\Loader::includeModule('mws.sed.fdoc');
        \Bitrix\Main\Loader::includeModule('crm');
        $dealId= $query['dealId'];


        $res = Bitrix\Crm\DealTable::getList([
            'filter'=>['ID'=>$dealId],
            'runtime'=>[
                new \Bitrix\Main\Entity\ReferenceField(
                    'COM_NAME',
                    Bitrix\Crm\CompanyTable::getEntity(),
                    ['=this.COMPANY_ID'=>'ref.ID']
                )
            ],
            "select"=>[
                'UF_CRM_1693484556784',
                'FULL_NAME'=>'COM_NAME.TITLE',
                'UF_CRM_1696253603031',//дата рождения
                'UF_CRM_1714725844125',//Место рождения
                'UF_CRM_1731403948',//Тип документа
                'UF_CRM_1696253537606',//Серия паспорта
                'UF_CRM_1696253547486',//номер паспорта
                'UF_CRM_1696253588254',//Дата выдачи паспорта
                'UF_CRM_1696253557302',//Кем выдан
                'UF_CRM_1696253488471',//Адрес регистрации
                 "UF_CRM_1738550624",   
            ],

        ])->fetch();

        $res['UF_CRM_1693484556784'] = \Bitrix\Crm\Communication\Normalizer::normalizePhone($res['UF_CRM_1693484556784']);

        if(!$res['UF_CRM_1693484556784']){
            return 'no phone';
        }
        return [
              
                'UF_CRM_1693484556784'=>$res['UF_CRM_1693484556784'],
                'FULL_NAME'=>$res['FULL_NAME'],

                'UF_CRM_1696253603031'=>$res['UF_CRM_1696253603031'] ? $res['UF_CRM_1696253603031']->toString():"",//дата рождения
                'UF_CRM_1714725844125'=>$res['UF_CRM_1714725844125'],//Место рождения
                'UF_CRM_1731403948'=>$res['UF_CRM_1731403948'],//Тип документа
                'UF_CRM_1696253537606'=>$res['UF_CRM_1696253537606'],//Серия паспорта
                'UF_CRM_1696253547486'=>$res['UF_CRM_1696253547486'],//номер паспорта
                'UF_CRM_1696253588254'=>$res['UF_CRM_1696253588254']?$res['UF_CRM_1696253588254']->toString():"",//Дата выдачи паспорта
                'UF_CRM_1696253557302'=>$res['UF_CRM_1696253557302'],//Кем выдан
                'UF_CRM_1696253488471'=>$res['UF_CRM_1696253488471'],//Адрес регистрации
                'UF_CRM_1738550624'=>$res['UF_CRM_1738550624']?$res['UF_CRM_1738550624']->toString():"",
            ];
    }
    public static function getDocList($query, $nav, \CRestServer $server)
    {
        \Bitrix\Main\Loader::includeModule("iblock");
        //dev
//        $res = \Bitrix\Iblock\Iblock::wakeUp(88)->getEntityDataClass()::getList(array(
//            'filter' => [
//                'IBLOCK_ID' => 88,
//
//            ],
//            'select' => [
//                "ID",
//                "NAME",
//                "FDOC_COMMENT"=>"KOMMENTARIY_DLYA_FDOC.VALUE"
//
//            ]
//        ));
     //prod
        $res = \Bitrix\Iblock\Iblock::wakeUp(133)->getEntityDataClass()::getList(array(
            'filter' => [
                'IBLOCK_ID' => 133,

            ],
            'select' => [
                "ID",
                "NAME",
                "FDOC_COMMENT"=>"KOMMENTARIY.VALUE"

            ]
        ));
         return $res->fetchAll();


    }
//    public static  function webhook($query, $nav, \CRestServer $server)
//    {
//        \Bitrix\Main\Loader::includeModule('crm');
//        \Bitrix\Main\Loader::includeModule('mws.sed.fdoc');
//
//        $arr =  $query;
//        $container = \Bitrix\Crm\Service\Container::getInstance();
//        $factory = $container->getFactory(\CCrmOwnerType::Company);
//
//            if(in_array( 'employeeId',array_keys($arr))){
//                //логика на отправленные документы
//                $items = $factory->getItems(['filter'=>['UF_CRM_1737619304'=>$arr['']]]);
//                foreach($items as $item){
//
//                    print_r($item->getId());
//
//                }
//            }else{
//                //логика на запрос документов
//                $items = $factory->getItems(['filter'=>['UF_CRM_1737619304'=>'f78d8991cd3712c197edcbdf5680525c']]);
//                foreach($items as $item){
//
//                    print_r($item->getId());
//
//                }
//
//
//
//            }
//
//
//
//
//
//        \Bitrix\Main\Diag\Debug::writeToFile(print_r($query,true),"","_webhook_log.log");
//    }
    public static  function webhookSigned($query, $nav, \CRestServer $server)
    {
        \Bitrix\Main\Diag\Debug::writeToFile(print_r($query,true),"","_webhookSigned_log.log");
             $data = $query;
            // \Bitrix\Main\Loader::includeModule("crm");
            // \Bitrix\Main\Loader::includeModule("mws.sed.fdoc");
            // // Согласие на ЭДО UF_CRM_1738291314 (сделки)
            // // Документы получены UF_CRM_1738291397 (сделки)
            // // Согласие на ЭДО UF_CRM_1737619490 (клиент)
            // //Запрашиваем из таблицы данные
            // $send = \Mywebstor\Fdoc\MwsSedFdocSendTable::getList(["filter"=>[
            //                "SEND_ID" => $data['packageId'],
            //            ]])->fetch();

            //забираем Компанию и сделку обрабатывем данные 
            $res = MwsSedFdocSendTable::getList([
                'filter'=>['SEND_ID'=>$data['packageId']]
                ]);
               // // Согласие на ЭДО UF_CRM_1738291314 (сделки)
                    // // Документы получены UF_CRM_1738291397 (сделки)
                    // // Согласие на ЭДО UF_CRM_1737619490 (клиент)
                $answer = $res->fetch();
        
                // $entityDealType = \CCrmOwnerType::Deal;
                // $containerDeal = \Bitrix\Crm\Service\Container::getInstance();
                // $factoryDeal = $containerDeal->getFactory($entityDealType);
                // $deal = $factoryDeal->getItem($answer['DEAL_ID']); 
        
                //     print_r($deal->get('UF_CRM_1738291314'));
                //     print_r($deal->get('UF_CRM_1738291397'));
        
                // $entityCompanyType = \CCrmOwnerType::Company;
                // $containerCompany = \Bitrix\Crm\Service\Container::getInstance();
                // $factoryCompany = $containerCompany->getFactory($entityCompanyType);
                // $company = $factoryCompany->getItem($answer['DEAL_ID']);
        
        
                //     print_r($company->get('UF_CRM_1737619490'));
                if(!empty($answer)){
                    $arErrorsTmp =[];
            \CBPDocument::StartWorkflow(
                170,
                array("crm", "CCrmDocumentDeal", "DEAL_" . $answer['DEAL_ID']),
                array(),
                $arErrorsTmp
            );
            }



    }
    public static  function webhookQuery($query, $nav, \CRestServer $server)
    {
        \Bitrix\Main\Diag\Debug::writeToFile(print_r($query,true),"","_webhookSigned_log.log");
        $data = $query;
       // \Bitrix\Main\Loader::includeModule("crm");
       // \Bitrix\Main\Loader::includeModule("mws.sed.fdoc");
       // // Согласие на ЭДО UF_CRM_1738291314 (сделки)
       // // Документы получены UF_CRM_1738291397 (сделки)
       // // Согласие на ЭДО UF_CRM_1737619490 (клиент)
       // //Запрашиваем из таблицы данные
       // $send = \Mywebstor\Fdoc\MwsSedFdocSendTable::getList(["filter"=>[
       //                "SEND_ID" => $data['packageId'],
       //            ]])->fetch();

       //забираем Компанию и сделку обрабатывем данные 
       $res = MwsSedFdocSendTable::getList([
           'filter'=>['SEND_ID'=>$data['packageId']]
           ]);
          // // Согласие на ЭДО UF_CRM_1738291314 (сделки)
               // // Документы получены UF_CRM_1738291397 (сделки)
               // // Согласие на ЭДО UF_CRM_1737619490 (клиент)
           $answer = $res->fetch();
   
           // $entityDealType = \CCrmOwnerType::Deal;
           // $containerDeal = \Bitrix\Crm\Service\Container::getInstance();
           // $factoryDeal = $containerDeal->getFactory($entityDealType);
           // $deal = $factoryDeal->getItem($answer['DEAL_ID']); 
   
           //     print_r($deal->get('UF_CRM_1738291314'));
           //     print_r($deal->get('UF_CRM_1738291397'));
   
           // $entityCompanyType = \CCrmOwnerType::Company;
           // $containerCompany = \Bitrix\Crm\Service\Container::getInstance();
           // $factoryCompany = $containerCompany->getFactory($entityCompanyType);
           // $company = $factoryCompany->getItem($answer['DEAL_ID']);
   
   
           //     print_r($company->get('UF_CRM_1737619490'));
           if(!empty($answer)){
               $arErrorsTmp =[];

       \CBPDocument::StartWorkflow(
           169,
           array("crm", "CCrmDocumentDeal", "DEAL_" . $answer['DEAL_ID']),
           array(),
           $arErrorsTmp
       );
       }

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
    public static function sendWebHookFdocSigned($query, $nav, \CRestServer $server)
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
    public static function sendWebHookFdocQuery($query, $nav, \CRestServer $server)
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
                "code"=> "SetStatusPackageByClient",
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
            $row['DOWNLOAD'] = 'https://'.$_SERVER["SERVER_NAME"].'/bitrix/services/main/ajax.php?action=documentgenerator.api.document.getfile&SITE_ID=s1&id='. $row['ID'];
            $arrFiles[] =$row;
        }

        return $arrFiles;
    }
    public static function sendSelectedFiles($query, $nav, \CRestServer $server)
    {
        global $USER;
        Bitrix\Main\Loader::includeModule('DocumentGenerator');
        Bitrix\Main\Loader::includeModule('mws.sed.fdoc');
        Bitrix\Main\Loader::includeModule('crm');
        $rows  = $query['ROWS'];
        $dealId = $query['dealId'];

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

//        $send = \Mywebstor\Fdoc\MwsSedFdocSendTable::getList(["filter"=>[
//            "DEAL_ID" => $dealId,
//            "TYPE_SEND"=>"SIGNED",
//        ]])->fetch();
//        if($send){
//            \Mywebstor\Fdoc\MwsSedFdocSendTable::delete($send['ID']);
//        }




        $dateTime = new \Bitrix\Main\Type\DateTime();
        $dateTime = $dateTime->add('+1 day');


        $arrFiles=[];
        $arrDoc =[];
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

            $fname = mb_ereg_replace('[^A-Z,^a-z,^А-Я,^а-я,^ ,^.]', '', $file['ORIGINAL_NAME']);

            $fArr = explode(" ", $fname);

            $fras = trim($fArr[(count($fArr)-1)]);
            array_pop($fArr);

            $arrFiles[] = [
                'id' => $file['ID'] . '_' .$uid,
                'name' => implode(" ",$fArr).$fras,//$file['ORIGINAL_NAME'],
                'file' => base64_encode($base),
                'unsignExpiredDate' => $dateTime->format("Y-m-d\TH:i:s\Z"),
            ];

            $arrDoc[]=$file['ORIGINAL_NAME'];
        }



        $res = Bitrix\Crm\DealTable::getList([
            'filter'=>['ID'=>$dealId],
            'runtime'=>[
                new \Bitrix\Main\Entity\ReferenceField(
                    'COM_NAME',
                    Bitrix\Crm\CompanyTable::getEntity(),
                    ['=this.COMPANY_ID'=>'ref.ID']
                )
            ],
            "select"=>[
                'UF_CRM_1693484556784',
                'FULL_NAME'=>'COM_NAME.TITLE',
                'COMPANY_ID'
            ],

        ])->fetch();



        $container = \Bitrix\Crm\Service\Container::getInstance();
        $factory = $container->getFactory(\CCrmOwnerType::Company);
        $item = $factory->getItem( $res['COMPANY_ID']);
//        UF_CRM_1737619304 F.doc (client_id)
//        if(!$item->get('UF_CRM_1737619304')){
            $uidClient = md5(uniqid(rand(), true));
            $item->set('UF_CRM_1737619304',$uidClient);
            $operation = $factory->getUpdateOperation($item);
            $result = $operation
                ->disableCheckFields() //Не проверять обязательные поля
                ->disableCheckAccess() //Не проверять права доступа
                ->disableAfterSaveActions() //Не запускать события OnAfterCrmLeadAdd
                ->enableAutomation() //Запускать роботов (по идее должны по умолчанию запускаться)
                ->enableBizProc() //Запускать бизнес-процессы (по идее должны по умолчанию запускаться)
                ->launch(); //Запуск

//        }else{
//            $uidClient =$item->get('UF_CRM_1737619304');
//        }




        $res['UF_CRM_1693484556784'] = \Bitrix\Crm\Communication\Normalizer::normalizePhone($res['UF_CRM_1693484556784']);

        if(!$res['UF_CRM_1693484556784']){
            return 'no phone';
        }

        $uidpack = md5(uniqid(rand(), true));
        $docPack = [
            'documents' => $arrFiles,
            'client' => [
                'id' => $uidClient,

                "phone" => $res['UF_CRM_1693484556784'], //забирать
                "name" => $res['FULL_NAME'],//забирать


                "clientRole" => "Клиент"
            ],
            'package' => [
                'id' =>  'pack_'.$uidpack,
                "name" => "Документы по заявке  $dealId",
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
        \Bitrix\Main\Diag\Debug::writeToFile(  print_r($docPack,true),"","_SED_log.log");

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

              $fromFdoc = json_decode($response, true);

        $res = \Mywebstor\Fdoc\MwsSedFdocSendTable::add([
            "ENTITY_ID"=>'2',
            "DEAL_ID" =>  $dealId,
            "COMPANY_ID"=>$res['COMPANY_ID'],
            "SEND_ID" => 'pack_'.$uidpack,
            "TYPE_SEND"=>"SIGNED",
            "USER_ID"=>$USER->getId(),
            "DOC_NAME" => implode(", ", $arrDoc),
            "PACKAGE_URL" =>$fromFdoc['url'],
            "DATE_CREATE" => new \Bitrix\Main\Type\DateTime(),
        ]);

        if (!$res->isSuccess()){

            \Bitrix\Main\Diag\Debug::writeToFile(  print_r($res->getErrorMessages(),true),"","_SED_log.log");
        }else{
            \Bitrix\Main\Diag\Debug::writeToFile(  print_r($res->getID(),true),"","_SED_log.log");
        }


        return $response;

    }
    public static function checkSendDocs($query, $nav, \CRestServer $server)
    {
        Bitrix\Main\Loader::includeModule('mws.sed.fdoc');
        $dealId=$query['dealId'];

        $result = \Mywebstor\Fdoc\MwsSedFdocSendTable::getList([
            "order"=>['ID' => 'DESC'],
            "filter"=>[
                        "DEAL_ID" => $dealId,
            "TYPE_SEND"=>"SIGNED",
        ]])->fetch();

        if($result) {
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


            $build =  http_build_query([
                "id"=>$result['SEND_ID'],
                "idType"=>"package",
                "app"=>$corpId,
                "corpId"=>$corpId,
            ]);



            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL =>  $credentials['urlApi'].'api/v1/document/status?'. $build ,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));
            $response = curl_exec($curl);

            curl_close($curl);


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
            $build =  http_build_query([
                'id'=>$result['SEND_ID'],
                "idType"=>'package'
            ]);


            curl_setopt_array($curl, array(
                CURLOPT_URL => $credentials['urlApi'].'/api/v1/document?'.$build,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: ' . $token,
                ),
            ));

            $responseDocument = curl_exec($curl);

            curl_close($curl);


            $result['F_DOC'] = $response;
            $parse = json_decode($response, true);
            $docks = json_decode($responseDocument,true);
          // \Bitrix\Main\Diag\Debug::writeToFile(print_r($parse,true),"","_docLog_log.log");
            \Bitrix\Main\Diag\Debug::writeToFile(print_r($docks,true),"","_docLog_log.log");
           $res = \Mywebstor\Fdoc\MwsSedFdocSendTable::update($result['ID'],[
                'STATUS'=> $parse['status']?:'ERROR_SEND',
                'PACKAGE_URL'=> $docks['url'],
            ]);
        }
        $result = \Mywebstor\Fdoc\MwsSedFdocSendTable::getList(["order"=>['ID' => 'DESC'],"filter"=>["DEAL_ID" => $dealId,"TYPE_SEND"=>"SIGNED",]])->fetch();
            if($result["DATE_CREATE"]) {
                $result["DATE_CREATE"] = $result['DATE_CREATE']->format('d.m.Y');
            }
        return $result;

    }
    public static function checkQueryDocs($query, $nav, \CRestServer $server)
    {
        Bitrix\Main\Loader::includeModule('mws.sed.fdoc');
        $dealId=$query['dealId'];

        $result = \Mywebstor\Fdoc\MwsSedFdocSendTable::getList([
            "order"=>['ID' => 'DESC'],
            "filter"=>[
            "DEAL_ID" => $dealId,
            "TYPE_SEND"=>"QUERY",
        ]])->fetch();

        if($result) {
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


            $build =  http_build_query([
                "packageId"=>$result['SEND_ID'],
                "app"=>$corpId,
                "corpId"=>$corpId,
            ]);



            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL =>  $credentials['urlApi'].'api/v1/package/by/client/status?'. $build ,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));
            $response = curl_exec($curl);

            curl_close($curl);


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
            $build =  http_build_query([
                'id'=>$result['SEND_ID'],
                "idType"=>'package'
            ]);


            curl_setopt_array($curl, array(
                CURLOPT_URL => $credentials['urlApi'].'/api/v1/document?'.$build,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: ' . $token,
                ),
            ));

            $responseDocument = curl_exec($curl);

            curl_close($curl);


            $result['F_DOC'] = $response;
            $parse = json_decode($response, true);
            \Bitrix\Main\Diag\Debug::writeToFile(  print_r($response,true),"","_SED_log.log");
            $docks = json_decode($responseDocument,true);

            \Bitrix\Main\Diag\Debug::writeToFile(  print_r($responseDocument,true),"","_SED_log.log");
            \Mywebstor\Fdoc\MwsSedFdocSendTable::update($result['ID'],[
                'STATUS'=> $parse['status'],
                'PACKAGE_URL'=> $docks['url'],
            ]);
        }
        $result = \Mywebstor\Fdoc\MwsSedFdocSendTable::getList(["order"=>['ID' => 'DESC'],"filter"=>["DEAL_ID" => $dealId,  "TYPE_SEND"=>"QUERY",]])->fetch();
            if($result["DATE_CREATE"]) {
                $result["DATE_CREATE"] = $result['DATE_CREATE']->format('d.m.Y');
            }
        return $result;

    }
    public static function getTemplatesDoc($query, $nav, \CRestServer $server)
    {
        $cat = $query['category'];

        $res = \Bitrix\DocumentGenerator\Model\TemplateTable::getList(array(
            "filter"=>[
                "=PROVIDER.PROVIDER" => mb_strtolower(Bitrix\Crm\Integration\DocumentGenerator\DataProvider\Deal::class)."_category_" . $cat,
            ],

            "select"=>['ID','NAME']
        ));

        return $res->fetchAll();
    }
    public static function declineDoc($query, $nav, \CRestServer $server)
    {
        Bitrix\Main\Loader::includeModule('DocumentGenerator');
        Bitrix\Main\Loader::includeModule('mws.sed.fdoc');
        Bitrix\Main\Loader::includeModule('crm');

        $doc = $query['docPac'];

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

        //авторизация
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



        $docDeclined = [
            "packageGuid"=> $doc['SEND_ID'],
            "declineType"=> "decline"
        ];



        //Зарос на анулирование
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $credentials['urlApi'] . '/api/v1/employee/decline/package',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>json_encode($docDeclined),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: ' . $token,
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return 1;


    }
    public static function getDocumentClient($query, $nav, \CRestServer $server)
    {
        global $USER;
        Bitrix\Main\Loader::includeModule('DocumentGenerator');
        Bitrix\Main\Loader::includeModule('mws.sed.fdoc');
        Bitrix\Main\Loader::includeModule('crm');

        $dealId = $query['dealID'];
        $doc = $query['queryDoc'];




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

        //авторизация
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
       



        $res = Bitrix\Crm\DealTable::getList([
            'filter'=>['ID'=>$dealId],
            'runtime'=>[
                new \Bitrix\Main\Entity\ReferenceField(
                    'COM_NAME',
                    Bitrix\Crm\CompanyTable::getEntity(),
                    ['=this.COMPANY_ID'=>'ref.ID']
                )
            ],
            "select"=>[
                'UF_CRM_1693484556784',
                'FULL_NAME'=>'COM_NAME.TITLE',
                'COMPANY_ID'
            ],

        ])->fetch();
        $container = \Bitrix\Crm\Service\Container::getInstance();
        $factory = $container->getFactory(\CCrmOwnerType::Company);
        $item = $factory->getItem($res['COMPANY_ID']);
//        UF_CRM_1737619304 F.doc (client_id)
        if(!$item->get('UF_CRM_1737619304')){
            $uidClient = md5(uniqid(rand(), true));
            $item->set('UF_CRM_1737619304',$uidClient);
            $operation = $factory->getUpdateOperation($item);
            $result = $operation
                ->disableCheckFields() //Не проверять обязательные поля
                ->disableCheckAccess() //Не проверять права доступа
                ->disableAfterSaveActions() //Не запускать события OnAfterCrmLeadAdd
                ->enableAutomation() //Запускать роботов (по идее должны по умолчанию запускаться)
                ->enableBizProc() //Запускать бизнес-процессы (по идее должны по умолчанию запускаться)
                ->launch(); //Запуск

        }else{
            $uidClient =$item->get('UF_CRM_1737619304');
        }

        $arrDoc =[];
        foreach($doc['documentTypes'] as &$docType ) {
            $arrDoc[] =$docType['name'];
            $docType['isRequired'] = $docType['isRequired']== 1 ? true : false;
        }
        $uidPack = md5(uniqid(rand(), true));
        $doc['client']['id'] =  $uidClient;
        $doc['package']['id'] = 'pac_'.$uidPack;

        \Bitrix\Main\Diag\Debug::writeToFile(  print_r( $doc,true),"","_SED_log.log");
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $credentials['urlApi'] .'api/v1/package/by/client',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>json_encode($doc),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: ' . $token,
            ),
        ));

        $response = curl_exec($curl);
        \Bitrix\Main\Diag\Debug::writeToFile(  print_r($response,true),"","_SED_log.log");

        curl_close($curl);
        $fromFdoc = json_decode($response, true);

        $resl = \Mywebstor\Fdoc\MwsSedFdocSendTable::add([
            "ENTITY_ID"=>'2',
            "DEAL_ID" =>  $dealId,
            "SEND_ID" => 'pac_'.$uidPack,
            "COMPANY_ID"=>$res['COMPANY_ID'],
            "USER_ID"=>$USER->getId(),
            "TYPE_SEND"=>"QUERY",
            "PACKAGE_URL" =>$fromFdoc['url'],
            "DOC_NAME" => implode(", ", $arrDoc),
            "DATE_CREATE" => new \Bitrix\Main\Type\DateTime(),
        ]);

        if (!$resl->isSuccess()){

            \Bitrix\Main\Diag\Debug::writeToFile(  print_r($res->getErrorMessages(),true),"","_SED_log.log");
        }
        // else{
        //     \Bitrix\Main\Diag\Debug::writeToFile(  print_r($res->getID(),true),"","_SED_log.log");
        // }
        $arErrorsTmp=[];
        \CBPDocument::StartWorkflow(
            172,
            array("crm", "CCrmDocumentDeal", "DEAL_" . $dealId),
            array(),
            $arErrorsTmp
        );

        return $response;




    }
    public static function hlblockCreate($query, $nav, \CRestServer $server)
    {
        Bitrix\Main\Loader::includeModule('highloadblock');
        $LKtoUpdate = COption::GetOptionString("mws.sed.fdoc", "mws_sed_fdoc_template_document_sed", 0);
        $hlblockTable = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($LKtoUpdate)->getDataClass();
        $erorrs =[];
        $row = $query['row'];
        $hlEntity = $hlblockTable::add(array(
            "UF_TEMPLATE_CATEGORY" =>  $row["UF_TEMPLATE_CATEGORY"],
            "UF_TEMPLATE_TEMPLATES" =>  implode(', ',$row['UF_TEMPLATE_TEMPLATES']),
        ));
        if(!$hlEntity->isSuccess()){
            $erorrs[] = $hlEntity->getErrorMessages();

        }
        if(!empty($erorrs)){
            return $erorrs;
        }else{
            return 'Ok';
        }

    }
    public static function hlblockUpdate($query, $nav, \CRestServer $server)
    {
        Bitrix\Main\Loader::includeModule('highloadblock');
        $LKtoUpdate = COption::GetOptionString("mws.sed.fdoc", "mws_sed_fdoc_template_document_sed", 0);
        $hlblockTable = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($LKtoUpdate)->getDataClass();
        $erorrs =[];
        $rawData = $query['data'];

//            foreach ($query['rows'] as $row ) {
        $hlEntity = $hlblockTable::update( $rawData['ID'],array(
            "UF_TEMPLATE_CATEGORY" =>  $rawData["UF_TEMPLATE_CATEGORY"],
            "UF_TEMPLATE_TEMPLATES" =>  implode(', ',$rawData['UF_TEMPLATE_TEMPLATES']),
        ));
        if(!$hlEntity->isSuccess()){
            $erorrs[] = $hlEntity->getErrorMessages();
        }
//            }
        if(!empty($erorrs)){
            return $erorrs;
        }else{
            return 'Ok';
        }
    }
    public static function hlblockgetList($query, $nav, \CRestServer $server)
    {
        $LKtoUpdate = COption::GetOptionString("mws.sed.fdoc", "mws_sed_fdoc_template_document_sed", 0);
        $hlblockTable = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($LKtoUpdate)->getDataClass();
        $hlEntity = $hlblockTable::getList(array(
            "filter" => $query['filter'] ?:[],
            "select" => ['*'],
        ));
        $result = [];

        while ($row = $hlEntity->fetch()) {
            $row['UF_TEMPLATE_TEMPLATES'] = explode(', ',$row['UF_TEMPLATE_TEMPLATES']);
            $result[] = $row;

        }




        return $result;
    }
    public static function hlblockdelete($query, $nav, \CRestServer $server)
    {
        Bitrix\Main\Loader::includeModule('highloadblock');
        $LKtoUpdate = COption::GetOptionString("mws.sed.fdoc", "mws_sed_fdoc_template_document_sed", 0);
        $hlblockTable = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($LKtoUpdate)->getDataClass();
        $erorrs =[];
        foreach ($query['rows'] as $row ) {
        $hlEntity = $hlblockTable::delete($row['ID']);
        if(!$hlEntity->isSuccess()){
            $erorrs[] = $hlEntity->getErrorMessages();
        }
         }
        if(!empty($erorrs)){
            return $erorrs;
        }else{
            return 'ok';
        }
    }
    //Чисто автоматизация
    public static function getAllDocByTemplate($query, $nav, \CRestServer $server)
    {
        Bitrix\Main\Loader::includeModule('DocumentGenerator');
        Bitrix\Main\Loader::includeModule('mws.sed.fdoc');
        Bitrix\Main\Loader::includeModule('crm');
        $dealId = $query['dealId'];
        //получаем сделыч
        $container = \Bitrix\Crm\Service\Container::getInstance();
        $factory = $container->getFactory(\CCrmOwnerType::Deal);
        $item = $factory->getItem($dealId);

        if(!$item){
            return 'not found deal';
        }

        $LKtoUpdate = COption::GetOptionString("mws.sed.fdoc", "mws_sed_fdoc_template_document_sed", 0);
        $hlblockTable = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($LKtoUpdate)->getDataClass();
        $hlEntity = $hlblockTable::getList(array(
            'filter'=>['UF_TEMPLATE_CATEGORY'=>$item->getCategoryId()],
            'select'=>["*"]
        ))->fetch();

        if(!$hlEntity){
            return [];
        }
        $expTemplate = explode(', ',$hlEntity['UF_TEMPLATE_TEMPLATES']);


        $doc = \Bitrix\DocumentGenerator\Model\DocumentTable::getlist(
            [
                'filter' => [
                    '=PROVIDER' => mb_strtolower(Bitrix\Crm\Integration\DocumentGenerator\DataProvider\Deal::class),
                    'VALUE' => $dealId,
                    '!=TEMPLATE_ID'=>$expTemplate
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
            $arr =	explode('.',$file['ORIGINAL_NAME']);
            $ext = end($arr);



            $row['FILE'] =$file;
            $dirPath = \Bitrix\Main\Application::getDocumentRoot() . "/mobile/custom_frames/docs/$dealId";

            $ioDir = new \Bitrix\Main\IO\Directory($dirPath);
            if (!$ioDir->isExists()) {
                //Если не существует - создать
                $ioDir->create();
            }


            file_put_contents($_SERVER['DOCUMENT_ROOT'] ."/mobile/custom_frames/docs/$dealId/".$dealId.'_'.$file['ORIGINAL_NAME'], file_get_contents('https://' . $_SERVER['SERVER_NAME'] . $file['SRC']) );

            //$row['DOWNLOAD'] = 'https://'.$_SERVER["SERVER_NAME"].'/bitrix/services/main/ajax.php?action=documentgenerator.api.document.getfile&SITE_ID=s1&id='. $row['ID'];
            $row['DOWNLOAD'] = 'https://'.$_SERVER["SERVER_NAME"]."/mobile/custom_frames/docs/$dealId/".$dealId.'_'.$file['ORIGINAL_NAME'];
            $arrFiles[] =$row;


//            file_put_contents($_SERVER['DOCUMENT_ROOT'] ."/mobile/local_Frames/docs/$dealId/".$file['ORIGINAL_NAME'], file_get_contents('https://' . $_SERVER['SERVER_NAME'] . $file['SRC']) );
//
//            //$row['DOWNLOAD'] = 'https://'.$_SERVER["SERVER_NAME"].'/bitrix/services/main/ajax.php?action=documentgenerator.api.document.getfile&SITE_ID=s1&id='. $row['ID'];
//            $row['DOWNLOAD'] = 'https://'.$_SERVER["SERVER_NAME"]."/mobile/local_Frames/docs//$dealId".$file['ORIGINAL_NAME'];
//            $arrFiles[] =$row;
        }


        return $arrFiles;




    }
    public static function reloadDocuments($query, $nav, \CRestServer $server)
    {
        Bitrix\Main\Loader::includeModule('DocumentGenerator');
        Bitrix\Main\Loader::includeModule('mws.deal.entity');

        Bitrix\Main\Loader::includeModule('mws.sed.fdoc');
        Bitrix\Main\Loader::includeModule('crm');
        $dealId = $query['dealId'];
        //получаем сделыч
        $container = \Bitrix\Crm\Service\Container::getInstance();
        $factory = $container->getFactory(\CCrmOwnerType::Deal);
        $item = $factory->getItem($dealId);

        if(!$item){
            return 'not found deal';
        }

        // получаем пакет докумнтов на переформатирование
        $docs= \Bitrix\DocumentGenerator\Model\DocumentTable::getList(array(
            'filter' => array(
                '=PROVIDER'=> mb_strtolower(Bitrix\Crm\Integration\DocumentGenerator\DataProvider\Deal::class),
                 "VALUE"=>$dealId,
            )
        ))->fetchAll();


        $templNeed = [];
        if($docs){
            foreach ($docs as $doc){
                $templNeed[] = $doc['TEMPLATE_ID'];
                $del =  \Bitrix\DocumentGenerator\Model\DocumentTable::delete($doc['ID']);
            }
        }

        foreach($templNeed as $templateID){
            //TODO для использования на других порталах вырезать этот код
             if($templateID == 75){
                 $data =[];
                 $template = \Bitrix\DocumentGenerator\Template::loadById($templateID);
                 $template->setSourceType(\Bitrix\Crm\Integration\DocumentGenerator\DataProvider\Deal::class);
                 $document = \Bitrix\DocumentGenerator\Document::createByTemplate($template, $dealId);

                 $jobs  = JobTable::getList([
                     'filter'=>[
                         'DEAL_ID'=> $dealId
                     ],
                     'select'=>["PRODUCT_NAME","MEASURE","QUANTITY","PRICE",],

                 ]);
                 $data['jobsSum'] = 0;
                 while ($row = $jobs->fetch()) {
                     $row['SUM'] = $row['QUANTITY'] * $row['PRICE'];
                     $data['jobsSum'] =  $data['jobsSum'] +$row['SUM'];
                     $data['jobs'][] = $row;

                 }

 //        $data['jobs'] = $jobs->fetchAll();

                 $mats = ConsumablesTable::getList([
                     'filter'=>[
                         'DEAL_ID'=>$dealId
                     ],
                     'select'=>["PRODUCT_NAME","MEASURE",'QUANTITY_EXP',"QUANTITY_SALE","PRICE",],
                 ]);

                 while ($row =  $mats->fetch()) {
                     //$row['jobSum'] = $row['QUANTITY'] * $row['PRICE'];
                     $data['mats'][] = $row;
                 }


                 //$data['mats'] = $mats->fetchAll();

                 $fields = [

                     'mats' => [
                         'PROVIDER' => \Bitrix\DocumentGenerator\DataProvider\ArrayDataProvider::class,
                         'OPTIONS' => [
                             'ITEM_NAME' => 'Item',
                             'ITEM_PROVIDER' => \Bitrix\DocumentGenerator\DataProvider\HashDataProvider::class,
                         ],
                     ],
                     'matIdx' => ['VALUE' => 'mats.INDEX'],
                     'matName' => ['VALUE' => 'mats.Item.PRODUCT_NAME'],
                     'matQuantity' => ['VALUE' => 'mats.Item.QUANTITY_EXP'],
                     //'measure' => ['VALUE' => 'goods.Item.ROW_MEASURE_NAME'],

                     'jobs' => [
                         'PROVIDER' => \Bitrix\DocumentGenerator\DataProvider\ArrayDataProvider::class,
                         'OPTIONS' => [
                             'ITEM_NAME' => 'Item',
                             'ITEM_PROVIDER' => \Bitrix\DocumentGenerator\DataProvider\HashDataProvider::class,
                         ],
                     ],
                     'jobIdx' => ['VALUE' => 'jobs.INDEX'],
                     'jobName' => ['VALUE' => 'jobs.Item.PRODUCT_NAME'],
                     'jobPrice' => ['VALUE' => 'jobs.Item.PRICE'],
                     'jobQuantity' => ['VALUE' => 'jobs.Item.QUANTITY'],
                     'jobSum' => ['VALUE' => 'jobs.Item.SUM'],
                 ];

                 $result = $document->setFields($fields)->setValues($data)->getFile();

                 $docId = $result->getData()['id'];





             }else{
                 $template = \Bitrix\DocumentGenerator\Template::loadById($templateID);
                 $template->setSourceType(\Bitrix\Crm\Integration\DocumentGenerator\DataProvider\Deal::class);
                 $document = \Bitrix\DocumentGenerator\Document::createByTemplate($template, $dealId);

                 $result = $document->getFile();

                 $docId = $result->getData()['id'];
             }
         }

        $item->set('UF_CRM_1738550624', new DateTime());
        $operation = $factory->getUpdateOperation($item);
        $result = $operation
        ->disableCheckFields() //Не проверять обязательные поля
        ->disableCheckAccess() //Не проверять права доступа
        ->disableAfterSaveActions() //Не запускать события OnAfterCrmLeadAdd
        ->disableAutomation() //Запускать роботов (по идее должны по умолчанию запускаться)
        ->disableBizProc() //Запускать бизнес-процессы (по идее должны по умолчанию запускаться)
        ->launch(); //Запуск

         return 'ok';

    }

    public static function recognize($query, $nav, \CRestServer $server)
    {
//    use Bitrix\Main\Config\Option;
//    use Bitrix\Main\Loader;
//    use Mywebstor\Fdoc\MwsSedFdocUsersTable;
//    use Mywebstor\Fdoc\MwsSedFdocSendTable;
//    use \Bitrix\Main\Type\DateTime;
//    use Mywebstor\DealEntity\JobTable;
//    use Mywebstor\DealEntity\ConsumablesTable;

          $dealId = $query['dealId'];
        Bitrix\Main\Loader::includeModule('mws.sed.fdoc');
        Bitrix\Main\Loader::includeModule('crm');
        $arErrorsTmp=[];
        $entityTypeID = \CCrmOwnerType::Deal;

        $factory = Bitrix\Crm\Service\Container::getInstance()->getFactory($entityTypeID);

        $item = $factory->getItem($dealId);

        print_r($item->get('UF_CRM_1729243587731'));


        $res = CFile::GetByID(68546);
        $file= $res->fetch();




        $base = file_get_contents('http://' . $_SERVER['SERVER_NAME'] . $file['SRC']);



        $arrFiles = [


            'documentsPhoto' => base64_encode($base),
            'fileExtension'=>"pdf"

        ];

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


        $curl = curl_init();


        $arrFiles = [


            'documentsPhoto' => base64_encode($base),
            'fileExtension'=>"pdf"

        ];

        curl_setopt_array($curl, array(
            CURLOPT_URL => $credentials['urlApi'] . '/api/v1/docs/by/client/recognize',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode( $arrFiles),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: ' . $token,

            ),
        ));

        $responseDoc= curl_exec($curl);

        curl_close($curl);


        print_r(json_decode($responseDoc,true));
    }
    public static function downloadDocs($query, $nav, \CRestServer $server)
    {

//    use Bitrix\Main\Config\Option;
//    use Bitrix\Main\Loader;
//    use Mywebstor\Fdoc\MwsSedFdocUsersTable;
//    use Mywebstor\Fdoc\MwsSedFdocSendTable;
//    use \Bitrix\Main\Type\DateTime;
//    use Mywebstor\DealEntity\JobTable;
//    use Mywebstor\DealEntity\ConsumablesTable;

        Bitrix\Main\Loader::includeModule('mws.sed.fdoc');
        $dealId = $query['dealId'];
        $sendType = $query['type'];

        $result = \Mywebstor\Fdoc\MwsSedFdocSendTable::getList([
            "order"=>['ID' => 'DESC'],
            "filter"=>[
                "DEAL_ID" => $dealId,
                "TYPE_SEND"=>$sendType,
            ]])->fetch();

        if($result) {
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
            $build = [
                'id'=> $result['SEND_ID'],
                "idType"=>'package'
            ];
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $credentials['urlApi'] .'api/v1/corp/archives/document',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($build),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: ' . $token,
                ),
            ));

            $responseDocument = curl_exec($curl);
            print_r($responseDocument);
            curl_close($curl);

            $docks = json_decode($responseDocument,true);
            $arrDoc=[
                'token'=>$docks['token']
            ];

            $curl = curl_init();
            $url =parse_url($credentials['urlApi']);

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://'.$url['host'].'/archive-srv/api/v1/archive/package',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>json_encode($arrDoc),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $guid = json_decode($response,true);

            if($guid['result']['status'] == 'OK'){
                foreach($guid['data']['packageArchives'] as $elem){
                    print_r("https://".$url['host']."/archive-srv/api/v1/archive/".$docks['token']."/download/".$elem['guid']);
                }
            }


        }

    }


//функция на основе шаблонов для переформирования данных

//     public static function reloadDocuments($query, $nav, \CRestServer $server)
//     {
//         Bitrix\Main\Loader::includeModule('DocumentGenerator');
//         Bitrix\Main\Loader::includeModule('mws.deal.entity');

//         Bitrix\Main\Loader::includeModule('mws.sed.fdoc');
//         Bitrix\Main\Loader::includeModule('crm');
//         $dealId = $query['dealId'];
//         //получаем сделыч
//         $container = \Bitrix\Crm\Service\Container::getInstance();
//         $factory = $container->getFactory(\CCrmOwnerType::Deal);
//         $item = $factory->getItem($dealId);

//         if(!$item){
//             return 'not found deal';
//         }

//         $LKtoUpdate = COption::GetOptionString("mws.sed.fdoc", "mws_sed_fdoc_template_document_sed", 0);

//         $hlblockTable = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($LKtoUpdate)->getDataClass();
//         $hlEntity = $hlblockTable::getList(array(
//             'filter'=>['UF_TEMPLATE_CATEGORY'=>$item->getCategoryId()],
//             'select'=>["*"]
//         ))->fetch();

//         if(!$hlEntity){
//             return [];
//         }
//         //шаблоны исключения
//         $expTemplate = explode(', ',$hlEntity['UF_TEMPLATE_TEMPLATES']);

//         //Забираем все шаблоны
//         //чтобы выташить нужные шаблоны

//         $res = \Bitrix\DocumentGenerator\Model\TemplateTable::getList(array(
//             "filter"=>[
//                 "=PROVIDER.PROVIDER" => mb_strtolower(Bitrix\Crm\Integration\DocumentGenerator\DataProvider\Deal::class)."_category_" . $item->getCategoryId(),
//             ],

//             "select"=>['ID','NAME']
//         ));
//         $tempNeed = [];
//       while($templ = $res->fetch()){
//          if(!in_array($templ['ID'],$expTemplate)){
//              $tempNeed[] = $templ['ID'];
//          }
//       }
//         // получаем пакет докумнтов на переформатирование
//         $docs= \Bitrix\DocumentGenerator\Model\DocumentTable::getList(array(
//             'filter' => array(
//                 '=PROVIDER'=> mb_strtolower(Bitrix\Crm\Integration\DocumentGenerator\DataProvider\Deal::class),
//                 "TEMPLATE_ID"=>  $tempNeed,
//                 "VALUE"=>$dealId,
//             )
//         ))->fetchAll();
        
//         if($docs){
//             foreach ($docs as $doc){
//                 $del =  \Bitrix\DocumentGenerator\Model\DocumentTable::delete($doc['ID']);
//             }
//         }
//         foreach($tempNeed as $templateID){
//             //TODO для использования на других порталах вырезать этот код
//             if($templateID == 75){
//                 $data =[];
//                 $template = \Bitrix\DocumentGenerator\Template::loadById($templateID);
//                 $template->setSourceType(\Bitrix\Crm\Integration\DocumentGenerator\DataProvider\Deal::class);
//                 $document = \Bitrix\DocumentGenerator\Document::createByTemplate($template, $dealId);

//                 $jobs  = JobTable::getList([
//                     'filter'=>[
//                         'DEAL_ID'=> $dealId
//                     ],
//                     'select'=>["PRODUCT_NAME","MEASURE","QUANTITY","PRICE",],

//                 ]);
//                 $data['jobsSum'] = 0;
//                 while ($row = $jobs->fetch()) {
//                     $row['SUM'] = $row['QUANTITY'] * $row['PRICE'];
//                     $data['jobsSum'] =  $data['jobsSum'] +$row['SUM'];
//                     $data['jobs'][] = $row;

//                 }

// //        $data['jobs'] = $jobs->fetchAll();

//                 $mats = ConsumablesTable::getList([
//                     'filter'=>[
//                         'DEAL_ID'=>$dealId
//                     ],
//                     'select'=>["PRODUCT_NAME","MEASURE",'QUANTITY_EXP',"QUANTITY_SALE","PRICE",],
//                 ]);

//                 while ($row =  $mats->fetch()) {
//                     //$row['jobSum'] = $row['QUANTITY'] * $row['PRICE'];
//                     $data['mats'][] = $row;
//                 }


//                 //$data['mats'] = $mats->fetchAll();

//                 $fields = [

//                     'mats' => [
//                         'PROVIDER' => \Bitrix\DocumentGenerator\DataProvider\ArrayDataProvider::class,
//                         'OPTIONS' => [
//                             'ITEM_NAME' => 'Item',
//                             'ITEM_PROVIDER' => \Bitrix\DocumentGenerator\DataProvider\HashDataProvider::class,
//                         ],
//                     ],
//                     'matIdx' => ['VALUE' => 'mats.INDEX'],
//                     'matName' => ['VALUE' => 'mats.Item.PRODUCT_NAME'],
//                     'matQuantity' => ['VALUE' => 'mats.Item.QUANTITY_EXP'],
//                     //'measure' => ['VALUE' => 'goods.Item.ROW_MEASURE_NAME'],

//                     'jobs' => [
//                         'PROVIDER' => \Bitrix\DocumentGenerator\DataProvider\ArrayDataProvider::class,
//                         'OPTIONS' => [
//                             'ITEM_NAME' => 'Item',
//                             'ITEM_PROVIDER' => \Bitrix\DocumentGenerator\DataProvider\HashDataProvider::class,
//                         ],
//                     ],
//                     'jobIdx' => ['VALUE' => 'jobs.INDEX'],
//                     'jobName' => ['VALUE' => 'jobs.Item.PRODUCT_NAME'],
//                     'jobPrice' => ['VALUE' => 'jobs.Item.PRICE'],
//                     'jobQuantity' => ['VALUE' => 'jobs.Item.QUANTITY'],
//                     'jobSum' => ['VALUE' => 'jobs.Item.SUM'],
//                 ];

//                 $result = $document->setFields($fields)->setValues($data)->getFile();

//                 $docId = $result->getData()['id'];





//             }else{
//                 $template = \Bitrix\DocumentGenerator\Template::loadById($templateID);
//                 $template->setSourceType(\Bitrix\Crm\Integration\DocumentGenerator\DataProvider\Deal::class);
//                 $document = \Bitrix\DocumentGenerator\Document::createByTemplate($template, $dealId);

//                 $result = $document->getFile();

//                 $docId = $result->getData()['id'];
//             }
//         }
//         return 'ok';
//     }

}
