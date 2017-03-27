<?php

class CComptes
{
	protected $_sFrom, $_aData = NULL;

	protected function _getPathFileData()
	{
		return CConfChatbot::pathLibXMPP . '/' . sha1($this->_sFrom);
	}

	function __construct($sFrom)
	{
		$this->_sFrom = $sFrom;
		$pathFileData = $this->_getPathFileData();
		if(file_exists($pathFileData)) {
			$this->_aData = array('from'=>$sFrom, 'historique'=>array());
		}
		else {
			$this->_aData = json_decode(file_get_contents($pathFileData));
		}
	}

	function __destruct()
	{
		echo __METHOD__;
		$pathFileData = $this->_getPathFileData();
		file_put_contents($pathFileData, json_encode($this->_aData));
	}

	function setMessage($sMsg)
	{
		var_dump($sMsg);
		$this->_aData['historique'][] = $sMsg;
	}
		
	function getReponse()
	{
		return 'bonjour';
	}

	function getData()
	{
		return $this->_aData;
	}
}