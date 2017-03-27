<?php

class CComptes
{
	protected $_sFrom, $_aData = NULL;

	protected _getPathFileData()
	{
		return CConfChatbot::pathLibXMPP . '/' . sha1($this->_sFrom);
	}

	function __construct($sFrom)
	{
		$this->_sFrom = $sFrom;
		$pathFileData = $this->_getPathFileData();
		$this->_aData = json_decode(file_get_contents($pathFileData))
	}

	function __destruct()
	{
		$pathFileData = $this->_getPathFileData();
		file_put_contents($pathFileData, json_encode($this->_aData));
	}

	function setMessage($sMsg)
	{
		$this->_aData['historique'][] = $sMsg;
	}
		
	function getReponse()
	{
		return 'bonjour';
	}
}