<?php

class CComptes
{
	const ATTENTE_RIEN = 'ATTENTE_RIEN';
	const ATTENTE_NOM = 'ATTENTE_NOM';

	const NOM_INCONNU = 'inconnu(e)';

	protected $_aData = NULL;

	protected function _getPathFileData($sFrom)
	{
		return CConfChatbot::pathData . '/' . sha1($sFrom);
	}

	protected function _initData($sFrom)
	{
		$this->_aData = array('from'=>$sFrom, 'nom'=>self::NOM_INCONNU, 'state'=>self::ATTENTE_RIEN, 'historique'=>array());
	}

	protected function _getLastMsg()
	{
		if(empty($this->_aData['historique'])) return '';
		else {
			$id = count($this->_aData['historique']) - 1;
			return $this->_aData['historique'][$id];
		}
	}

	protected function _LastMsgContains($mPatterns)
	{
		if( ! is_array($mPatterns)) $mPatterns = array($mPatterns);

		$sMsg = $this->_getLastMsg();
		foreach($mPatterns as $sPattern) {
			if(strstr(strtolower($sMsg), $sPattern)) return TRUE;
		}
		return FALSE;
	}

	function __construct($sFrom)
	{
		$pathFileData = $this->_getPathFileData($sFrom);
		if( ! file_exists($pathFileData)) {
			$this->_initData($sFrom);
		}
		else {
			$this->_aData = json_decode(file_get_contents($pathFileData), TRUE);
			if(empty($this->_aData)) $this->_initData();
		}
	}

	function __destruct()
	{
		$pathFileData = $this->_getPathFileData($this->_aData['from']);
		file_put_contents($pathFileData, json_encode($this->_aData));
	}

	function setMessage($sMsg)
	{
		$this->_aData['historique'][] = $sMsg;
	}
		
	function getReponse()
	{
		$sMsg = $this->_getLastMsg();
		if($this->_LastMsgContains('bonjour')) {
			if($this->_aData['nom'] === self::NOM_INCONNU) {
				$this->_aData['state'] = self::ATTENTE_NOM;
				return 'bonjour, quel est votre nom ?';
			}
			else {
				$this->_aData['state'] = self::ATTENTE_PARTICIPER;
				return 'bonjour, ' . $this->_aData['nom'] . '. Voulez-vous participer à une étude ?'; 	
			}
		}
		elseif($this->_aData['state'] === self::ATTENTE_NOM) {
			$this->_aData['nom'] = $sMsg;
			$this->_aData['state'] = self::ATTENTE_PARTICIPER;
			return 'bonjour, ' . $this->_aData['nom'] . '. Voulez-vous participer à une étude ?'; 	
		}
		elseif($this->_aData['state'] === self::ATTENTE_PARTICIPER) {
			if($this->_LastMsgContains('oui')) {
				#TODO ici poser la première question
				#TODO passer le retour en array, pouvoir avoir plusieurs messages
				#TODO réorganiser le code pour enchainer les switch et découper, mettre les choix dans des données
				$this->_aData['etude1']['prochainequestion'] = 2;
				return 'parfait ! '
			}
		else return 'undefined (yet)';
	}

	function getData()
	{
		return $this->_aData;
	}
}