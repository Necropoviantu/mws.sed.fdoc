<?php
\Bitrix\Main\Loader::registerAutoloadClasses(
    'mws.sed.fdoc',
    array(
        "MwsSedFdocRest" => "classes/restservice.php",
        "RegistrateJs" => "classes/registrateJs.php",
        "Mywebstor\Fdoc\MwsSedFdocUsersTable" => "lib/mwsSedFdocUsersTable.php",
        "Mywebstor\Fdoc\MwsSedFdocSendTable" => "lib/mwsSedFdocSendTable.php",

    )
);