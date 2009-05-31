<?php
/*
 * +-----------------------------------------+
 * | Copyright (c) 2008 Tobias Friebel       |
 * +-----------------------------------------+
 * | Authors: Tobias Friebel <TobyF@Web.de>	 |
 * +-----------------------------------------+
 * 
 * CC Namensnennung-Keine kommerzielle Nutzung-Keine Bearbeitung
 * http://creativecommons.org/licenses/by-nc-nd/2.0/de/
 * 
 * $Id$
 */

require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

class GuthabenBoardListener implements EventListener 
{
	private $countGuthaben = 1;
	private $postAddGuthaben = 0;
	private $threadAddGuthaben = 0;
	private $isSave = false;
	
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) 
	{
		switch ($eventName)
		{
			case 'readFormParameters':
				if (isset($_POST['countGuthaben'])) $this->countGuthaben = 1;
				else $this->countGuthaben = 0;
				if (isset($_POST['postAddGuthaben'])) $this->postAddGuthaben = intval($_POST['postAddGuthaben']);
				else $this->postAddGuthaben = 0;
				if (isset($_POST['threadAddGuthaben'])) $this->threadAddGuthaben = intval($_POST['threadAddGuthaben']);
				else $this->threadAddGuthaben = 0;
			break;
			
			case 'save':
				$eventObj->additionalFields['countGuthaben'] = $this->countGuthaben;
				$eventObj->additionalFields['postAddGuthaben'] = $this->postAddGuthaben;
				$eventObj->additionalFields['threadAddGuthaben'] = $this->threadAddGuthaben;
				$this->isSave = true;
			break;
			
			case 'assignVariables':
				if (is_object($eventObj->board) && !$this->isSave)
				{
					WCF::getTPL()->assign(array(
						'countGuthaben' => $eventObj->board->countGuthaben,
						'postAddGuthaben' => $eventObj->board->postAddGuthaben,
						'threadAddGuthaben' => $eventObj->board->threadAddGuthaben,
					));
				}
				else
				{
					WCF::getTPL()->assign(array(
						'countGuthaben' => $this->countGuthaben,
						'postAddGuthaben' => $this->postAddGuthaben,
						'threadAddGuthaben' => $this->threadAddGuthaben,
					));
				}
				
				WCF::getTPL()->append('additionalFields', WCF::getTPL()->fetch('guthabenACP'));
			break;
		} 
	}
}
?>