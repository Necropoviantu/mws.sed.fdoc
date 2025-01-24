<?php
global $APPLICATION;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
$APPLICATION->SetTitle("Настройки Документооборота");
?>

    <body>
    <script type="module" crossorigin src="/local/modules/mws.sed.fdoc/admin/assets/index-BPNOBu0C.js"></script>
    <link rel="stylesheet" crossorigin href="/local/modules/mws.sed.fdoc/admin/assets/index-4qKhG8FF.css">
    <div id="app"></div>

    </body>

<?php
require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin.php");
?>