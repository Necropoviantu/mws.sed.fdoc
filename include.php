<?php
\Bitrix\Main\Loader::registerAutoloadClasses(
    'mws.sed.fdoc',
    array(
        "MwsSedFdocRest" => "classes/restservice.php",
        "Mywebstor\Fdoc\MwsSedFdocUsersTable" => "lib/mwsSedFdocUsersTable.php",
    )
);