<?php
#TODO passer le retour en array, pouvoir avoir plusieurs messages
#TODO réorganiser le code pour enchainer les switch et découper, mettre les choix dans des données

class CComptes
{
	const ATTENTE_RIEN = 'ATTENTE_RIEN';
	const ATTENTE_NOM = 'ATTENTE_NOM';
	const ATTENTE_ACCORD_QUEST = 'ATTENTE_ACCORD_QUEST';
	const ATTENTE_QUESTIONS = 'ATTENTE_QUESTIONS';
	const ATTENTE_POSE_QUESTION = 'ATTENTE_POSE_QUESTION';
	const ATTENTE_REPONSE_QUESTION = 'ATTENTE_REPONSE_QUESTION';

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

	function _log($sMsg)
	{
		echo "### $sMsg\n";
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

	protected function _AnalyseReponse(& $bBreakWaitAnswer)
	{
		$aResult = array();	
		$aQuestionnaires = array(
			'q1' => array(
				'lib'=>'quelques questions indiscrêtes',
				'questions'=>array(
					'êtes-vous homme, femme, indifférencié, un cas complexe ?',
					'êtes-vous humain, robot, alien ?',
				),
			),
			'q2' => array(
				'lib'=>'préférences gustatives savoyardes',
				'questions'=>array(
					'aimez-vous la fondue ?',
					'aimez-vous la raclette ?',
					'aimez-vous le farcement ?',
				),
			),
		);
		$sMsg = $this->_getLastMsg();

		$this->_log('state ' . $this->_aData['state']);
		switch($this->_aData['state']) {

			case self::ATTENTE_RIEN: 
				if($this->_aData['nom'] === self::NOM_INCONNU) {
					$this->_aData['state'] = self::ATTENTE_NOM;
					$aResult[] = 'bonjour, quel est votre nom ?';
					$bBreakWaitAnswer = TRUE;
				}
				else {
					$this->_aData['state'] = self::ATTENTE_QUESTIONS;
					$aResult[] = 'bonjour, ' . $this->_aData['nom'] . '.'; 	
				}
				break;

			case self::ATTENTE_NOM: 
				$this->_aData['nom'] = $sMsg;
				$this->_aData['state'] = self::ATTENTE_QUESTIONS;
				$aResult[] = 'bonjour, ' . $this->_aData['nom'] . '.'; 	
				break;

			case self::ATTENTE_QUESTIONS:
				$this->_aData['quest_current'] = NULL;
				foreach($aQuestionnaires as $idQuest => $aQuest) {
					$sLib = $aQuest['lib'];
					if( ! isset($this->_aData['quest'][$idQuest])) {
						$this->_log("proposition questionnaire $idQuest / $sLib");
						$this->_aData['state'] = self::ATTENTE_ACCORD_QUEST;
						$this->_aData['quest_current'] = $idQuest;
						$this->_aData['quest'][$idQuest] = array('decision' => NULL, 'decision_boucles' => 0);
						$aResult[] = 'Voulez-vous participer à une étude : ' . $sLib . ' ?';
						$bBreakWaitAnswer = TRUE;
						break;
					}
				}
				if(empty($this->_aData['quest_current'])) {
					$this->_aData['state'] = self::ATTENTE_RIEN;
					$aResult[] = 'Plus aucune question à vous poser.';
					$bBreakWaitAnswer = TRUE;
				}
				break;

			case self::ATTENTE_ACCORD_QUEST:
				$idQuest = $this->_aData['quest_current'];
				if($this->_LastMsgContains('oui')) {
					$this->_aData['quest'][$idQuest]['decision'] = TRUE;
					$this->_aData['quest'][$idQuest]['prochainequestion'] = 0;
					$this->_aData['state'] = self::ATTENTE_POSE_QUESTION;
					$aResult[] = 'Parfait ! Allons-y.';
				}
				elseif($this->_LastMsgContains('non')) {
					$this->_aData['quest'][$idQuest]['decision'] = FALSE;
					$this->_aData['state'] = self::ATTENTE_QUESTIONS;
					$aResult[] = 'Dommage ! Une prochaine peut être.';
				}
				elseif($this->_aData['quest'][$idQuest]['decision_boucles'] > 10) {
					$aResult[] = 'J\'abandonne.';
					$this->_aData['quest'][$idQuest]['decision'] = FALSE;
					$this->_aData['state'] = self::ATTENTE_QUESTIONS;
				}
				else {
					$this->_aData['quest'][$idQuest]['decision_boucles']++;
					$aResult[] = 'Je n\'ai pas compris.';
					$aResult[] = 'Veuillez répondre par oui ou non.';
					$bBreakWaitAnswer = TRUE;
				}
				break;

			case self::ATTENTE_POSE_QUESTION:
				$idQuest = $this->_aData['quest_current'];
				$idQuestion = $this->_aData['quest'][$idQuest]['prochainequestion'];
				$numQuestion = $idQuestion + 1;
				$aResult[] = "question $numQuestion : " . $aQuestionnaires[$idQuest]['questions'][$idQuestion];
				$this->_aData['state'] = self::ATTENTE_REPONSE_QUESTION;
				$bBreakWaitAnswer = TRUE;
				break;

			case self::ATTENTE_REPONSE_QUESTION:
				$idQuest = $this->_aData['quest_current'];
				if( ! empty($sMsg)) {
					$idQuestion = $this->_aData['quest'][$idQuest]['prochainequestion'];
					$this->_aData['quest'][$idQuest]['reponses'][$idQuestion] = trim($sMsg);
					$this->_aData['quest'][$idQuest]['prochainequestion']++;
					if($this->_aData['quest'][$idQuest]['prochainequestion'] >= count($aQuestionnaires[$idQuest]['questions'])) {
						$aResult[] = 'Merci pour vos réponses, fin du questionnaire.';
						$aResult[] = 'Voici vos réponses : ';
						foreach($aQuestionnaires[$idQuest]['questions'] as $idQuestion => $libQuestion) {
							$aResult[] = "$libQuestion : " . $this->_aData['quest'][$idQuest]['reponses'][$idQuestion];
						}
						
						$this->_aData['state'] = self::ATTENTE_QUESTIONS;
					}
					else {
						$this->_aData['state'] = self::ATTENTE_POSE_QUESTION;
					}
				}
				else {
					$bBreakWaitAnswer = TRUE;
				}
				break;

			default: die('mode inconnu : ' . $this->_aData['state']);
		}
		return $aResult;
	}
		
	function getReponse()
	{
		$aResultGlobal = array();

		$bPrevEmpty = FALSE;
		$bGoOn = TRUE;
		$bBreakWaitAnswer = FALSE;
		while($bGoOn &&  ! $bBreakWaitAnswer) {
			#$bBreakWaitAnswer = FALSE;
			$aResult = $this->_AnalyseReponse($bBreakWaitAnswer);
			$this->_log('result : ' . implode(' / ', $aResult));
			$aResultGlobal = array_merge($aResultGlobal, $aResult);
			$bGoOn = ! empty($aResult) || ! $bPrevEmpty;
			$bPrevEmpty = empty($aResult);
		}

		if(empty($aResultGlobal)) $aResultGlobal[] = 'Je ne sais pas quoi répondre ou dire.';

		return $aResultGlobal;
	}

	function getData()
	{
		return $this->_aData;
	}
}