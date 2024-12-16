<?php
global $APPLICATION;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
$APPLICATION->SetTitle("Настройки Документооборота");
?>

    <body>
    <script type="module" crossorigin src="/local/modules/mws.sed.fdoc/admin/assets/index-B0CJFIl8.js"></script>
    <link rel="stylesheet" crossorigin href="/local/modules/mws.sed.fdoc/admin/assets/index-C0k3zdXZ.css">

    <div id="app"></div>

    </body>

<?php
require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin.php");
?>