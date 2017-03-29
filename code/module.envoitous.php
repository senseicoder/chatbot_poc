<?php

require __DIR__ . '/base.inc.php';

include 'XMPPHP/XMPP.php';

#Use XMPPHP_Log::LEVEL_VERBOSE to get more logging for error reports
#If this doesn't work, are you running 64-bit PHP with < 5.2.6?
$conn = new XMPPHP_XMPP('talk.google.com', 5222, CConfChatbot::sXMPPUser, CConfChatbot::sXMPPPwd, 'xmpphp', 'gmail.com', $printlog=true, $loglevel=XMPPHP_Log::LEVEL_INFO);

try {
    $conn->connect();
    $conn->processUntil('session_start');
    $conn->presence();
    foreach(CComptes::Liste() as $oCompte) {
    	$aData = $oCompte->getData();
    	$conn->message($aData['from'], 'Bonjour '.$aData['nom'].'. Une nouvelle enquête est disponible, merci de répondre pour commencer à répondre.');
    }
    $conn->disconnect();
} catch(XMPPHP_Exception $e) {
    die($e->getMessage());
}
