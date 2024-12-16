<?php
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;


class MwsSedFdocRest extends IRestService
{
    public static function OnRestServiceBuildDescription()
    {
        return array(
            "mwssedfdoc"=>array(

            ),
        );
    }
}