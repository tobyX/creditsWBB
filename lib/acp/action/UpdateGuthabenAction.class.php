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

require_once (WBB_DIR . 'lib/acp/action/UpdateCounterAction.class.php');

class UpdateGuthabenAction extends UpdateCounterAction
{
	public $action = 'UpdateGuthaben';

	/**
	 * @see Action::execute()
	 */
	public function execute()
	{
		parent :: execute();

		// count users
		$sql = "SELECT	COUNT(*) AS count
				FROM	wbb" . WBB_N . "_user";
		$row = WCF :: getDB()->getFirstRow($sql);
		$count = $row['count'];

		// get user ids
		$userIDs = '';
		$sql = "SELECT		userID
				FROM		wbb" . WBB_N . "_user
				ORDER BY	userID
				LIMIT		" . $this->limit . "
				OFFSET		" . ($this->limit * $this->loop);

		$result = WCF :: getDB()->sendQuery($sql);

		while ($row = WCF :: getDB()->fetchArray($result))
		{
			$userIDs .= ',' . $row['userID'];
		}

		if (empty ($userIDs))
		{
			$this->calcProgress();
			$this->finish();
		}

		// get boards
		$boardIDs = '';
		$sql = "SELECT	boardID
				FROM	wbb" . WBB_N . "_board
				WHERE	boardType = 0
						AND countGuthaben = 1";

		$result2 = WCF :: getDB()->sendQuery($sql);

		while ($row = WCF :: getDB()->fetchArray($result2))
		{
			$boardIDs .= ',' . $row['boardID'];
		}

		// update guthaben
		$sql = "SELECT		wbb_user.userID, wbb_user.posts,
							COUNT(thread.threadID) AS threads,
							(SELECT COUNT( * )
							FROM wcf" . WCF_N . "_pm pm
							WHERE pm.userID = wbb_user.userID
							AND pm.saveInOutbox =1
							) AS pms
				FROM		wbb" . WBB_N . "_user wbb_user
				LEFT JOIN	wcf" . WCF_N . "_user user
				ON			(user.userID = wbb_user.userID)
				LEFT JOIN	wbb" . WBB_N . "_thread thread
				ON			(thread.userID = wbb_user.userID AND thread.boardID IN (0" . $boardIDs . ")
							AND thread.isDeleted = 0 AND thread.isDisabled = 0)
				WHERE		wbb_user.userID IN (0" . $userIDs . ")
				GROUP BY	wbb_user.userID";

		$result2 = WCF :: getDB()->sendQuery($sql);

		while ($row = WCF :: getDB()->fetchArray($result2))
		{
			$guthaben = $row['threads'] * GUTHABEN_EARN_PER_THREAD;
			$guthaben += ($row['posts'] - $row['threads']) * GUTHABEN_EARN_PER_POST;
			$guthaben += $row['pms'] * GUTHABEN_EARN_PER_PN;

			$user = new User($row['userID']);
			Guthaben :: reset($user);
			Guthaben :: add($guthaben,'wbb.guthaben.log.newcalc', '', '', $user);
		}

		$this->executed();

		$this->calcProgress(($this->limit * $this->loop), $count);
		$this->nextLoop();
	}
}
?>