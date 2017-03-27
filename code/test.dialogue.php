<?php

require __DIR__ . '/base.inc.php';

$aMsgs = array(
	'bonjour',
	'tu veux être mon ami',
	'd\'accord', 
	'au revoir',
);

$oCompte = new CComptes('testsmanuels');
foreach($aMsgs as $sMsg) {
	echo "=> $sMsg\n";
	$oCompte->setMessage($sMsg);
	echo "<= " . $oCompte->getReponse() . "\n";
}

var_dump($oCompte->getData());