<?php

error_reporting(E_ALL & ~E_DEPRECATED);

require __DIR__ . '/config.inc.php';
require __DIR__ . '/comptes.inc.php';

set_include_path(CConfChatbot::pathLibXMPP . PATH_SEPARATOR . __DIR__ . '/');