<?php

class CComptes
{
	protected $_sFrom, $_aData = NULL;

	protected function _getPathFileData()
	{
		return CConfChatbot::pathData . '/' . sha1($this->_sFrom);
	}

	protected function _initData()
	{
		echo __METHOD__ . "\n";
		$this->_aData = array('from'=>$this->_sFrom, 'historique'=>array());
	}

	function __construct($sFrom)
	{
		$this->_sFrom = $sFrom;
		$pathFileData = $this->_getPathFileData();
		if( ! file_exists($pathFileData)) {
			$this->_initData();
		}
		else {
			$this->_aData = json_decode(file_get_contents($pathFileData), TRUE);
			if(empty($this->_aData)) $this->_initData();
		}
	}

	function __destruct()
	{
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