<?php

require __DIR__ . '/base.inc.php';

include 'XMPPHP/XMPP.php';

#Use XMPPHP_Log::LEVEL_VERBOSE to get more logging for error reports
#If this doesn't work, are you running 64-bit PHP with < 5.2.6?
$conn = new XMPPHP_XMPP('talk.google.com', 5222, $sXMPPUser, $sXMPPPwd, 'xmpphp', 'gmail.com', $printlog=true, $loglevel=XMPPHP_Log::LEVEL_INFO);
$conn->autoSubscribe();

$vcard_request = array();

try {
	$conn->connect();
	while(!$conn->isDisconnected()) {
		$payloads = $conn->processUntil(array('message', 'presence', 'end_stream', 'session_start', 'vcard'));
		foreach($payloads as $event) {
			$pl = $event[1];
			switch($event[0]) {
			case 'message': 
				if( ! empty($pl['body'])) {
	    				print "---------------------------------------------------------------------------------\n";
	    				print "Message from: {$pl['from']}\n";
	    				if( ! empty($pl['subject'])) print "Subject: {$pl['subject']}\n";
	    				print "body: ". $pl['body'] . "\n";
	    				print "type: ". $pl['type'] . "\n";
	    				var_dump($pl);
	    				print "---------------------------------------------------------------------------------\n";
	    				$conn->message($pl['from'], $body="echo: \"{$pl['body']}\"", $type=$pl['type']);
						$cmd = explode(' ', $pl['body']);
						
						switch($cmd[0]) {
							case 'quit': $conn->disconnect(); break;
							case 'break': $conn->send("</end>"); break;
							case 'vcard': 
								if(!($cmd[1])) $cmd[1] = $conn->user . '@' . $conn->server;
								// take a note which user requested which vcard
								$vcard_request[$pl['from']] = $cmd[1];
								// request the vcard
								$conn->getVCard($cmd[1]);
								break;
							default: 
								$oCompte = new CComptes($pl['from']);
								$oCompte->setMessage($pl['body']);
								$s = $oCompte->getReponse();
								if( ! empty($s)) $conn->send($s);
						}
					}
    			break;
    			case 'presence':
    				print "Presence: {$pl['from']} [{$pl['show']}] {$pl['status']}\n";
    			break;
    			case 'session_start':
    			    print "Session Start\n";
			    	$conn->getRoster();
    				$conn->presence($status="Cheese!");
    			break;
				case 'vcard':
					// check to see who requested this vcard
					$deliver = array_keys($vcard_request, $pl['from']);
					// work through the array to generate a message
					print_r($pl);
					$msg = '';
					foreach($pl as $key => $item) {
						$msg .= "$key: ";
						if(is_array($item)) {
							$msg .= "\n";
							foreach($item as $subkey => $subitem) {
								$msg .= "  $subkey: $subitem\n";
							}
						} else {
							$msg .= "$item\n";
						}
					}
					// deliver the vcard msg to everyone that requested that vcard
					foreach($deliver as $sendjid) {
						// remove the note on requests as we send out the message
						unset($vcard_request[$sendjid]);
    					$conn->message($sendjid, $msg, 'chat');
					}
				break;
    		}
    	}
    }
} catch(XMPPHP_Exception $e) {
    die($e->getMessage());
}
