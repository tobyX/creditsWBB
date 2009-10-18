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
require_once (WBB_DIR.'lib/data/post/GuthabenPostAction.class.php');

class GuthabenPostListener implements EventListener
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
			case 'PostAddForm':
			case 'PostQuickAddForm':
				if ($eventObj->board->postAddGuthaben != 0)
					$add = $eventObj->board->postAddGuthaben;
				else
					$add = GUTHABEN_EARN_PER_POST;

				if ($eventObj->board->countGuthaben == 1 && $add != 0 && $eventObj->newPost->isDisabled == false)
					Guthaben :: add($add,'wbb.guthaben.log.newpost', $eventObj->thread->topic.': '.(!empty($eventObj->subject) ? $eventObj->subject : '---'), 'index.php?page=Thread&postID='.$eventObj->newPost->postID.'#post'.$eventObj->newPost->postID);
			break;

			case 'PostEditForm' :
				if ($eventObj->board->postAddGuthaben != 0)
					$add = $eventObj->board->postAddGuthaben;
				else
					$add = GUTHABEN_EARN_PER_POST;

				if ($eventObj->board->countGuthaben == 1 && $add != 0 && $eventObj->post->isDisabled == false)
				{
					if (isset ($_POST['deletePost']))
					{
						if ($eventObj->canDeletePost())
						{
							if (isset ($_POST['sure']))
							{
								if (!$eventObj->post->isDeleted)
								{
									Guthaben :: sub($add, 'wbb.guthaben.log.deletepost', $eventObj->thread->topic . ' ' . $eventObj->post->subject, '', new User($eventObj->post->userID));
								}
							}
						}
					}
				}
			break;

			case 'PostActionPage' :
				if (in_array($eventObj->action, PostActionPage::$validFunctions))
				{
					$action = new GuthabenPostAction($eventObj->board, $eventObj->thread, $eventObj->post, $eventObj->topic, $eventObj->url);

					if (method_exists($action, $eventObj->action))
						$action->{$eventObj->action}();
				}
			break;
		}
	}
}
?>