<?php
const SYSM='/core';//核心文件夹
define('APP_PATH',dirname(__DIR__));
define('CORE_PATH',dirname(APP_PATH).SYSM);
include CORE_PATH.'/lib/base/My.php';
\core\My::createWebApp()->run();
?>
