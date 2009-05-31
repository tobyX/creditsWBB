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

require_once (WCF_DIR.'lib/system/event/EventListener.class.php');
require_once (WBB_DIR.'lib/data/thread/GuthabenThreadAction.class.php');

class GuthabenThreadListener implements EventListener
{
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName)
	{
		if (!GUTHABEN_ENABLE_GLOBAL)
			return;

		switch ($className)
		{
			case 'ThreadAddForm':
				if ($eventObj->board->threadAddGuthaben != 0)
					$add = $eventObj->board->threadAddGuthaben;
				else
					$add = GUTHABEN_EARN_PER_THREAD;

				if ($eventObj->board->countGuthaben == 1 && GUTHABEN_EARN_PER_THREAD > 0 && $eventObj->newThread->isDisabled == false)
					Guthaben :: add($add,'wbb.guthaben.log.newthread', $eventObj->subject, 'index.php?page=Thread&threadID='.$eventObj->newThread->threadID);
			break;

			case 'ThreadActionPage' :
				if (in_array($eventObj->action, ThreadActionPage::$validFunctions))
				{
					$action = new GuthabenThreadAction($eventObj->board, $eventObj->thread, null, $eventObj->threadID, $eventObj->topic, $eventObj->prefix, $eventObj->url);

					if (method_exists($action, $eventObj->action))
						$action->{$eventObj->action}();
				}
			break;
		}
	}
}
?>