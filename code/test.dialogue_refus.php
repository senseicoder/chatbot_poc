<?php

require __DIR__ . '/base.inc.php';

$aMsgs = array(
	'bonjour',
	'cédric',
	'non',
	'non',
);

$oCompte = new CComptes('testsmanuels');
foreach($aMsgs as $sMsg) {
	echo "=> $sMsg\n";
	$oCompte->setMessage($sMsg);
	foreach($oCompte->getReponse() as $s) {
		echo "<= $s\n";
	}
}

#var_dump($oCompte->getData());