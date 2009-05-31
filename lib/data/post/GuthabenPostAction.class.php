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

// wbb imports
require_once (WBB_DIR . 'lib/data/post/PostEditor.class.php');
require_once (WBB_DIR . 'lib/data/thread/ThreadEditor.class.php');
require_once (WBB_DIR . 'lib/data/thread/ThreadAction.class.php');

class GuthabenPostAction
{
	protected $postIDs = null;
	protected $post = null;
	protected $thread = null;
	protected $board = null;
	protected $add = 0;

	/**
	 * Creates a new PostAction object.
	 *
	 * @param	BoardEditor	$board
	 * @param	ThreadEditor	$thread
	 * @param	PostEditor	$post
	 */
	public function __construct($board = null, $thread = null, $post = null, $topic = '', $forwardURL = '')
	{
		$this->board = $board;
		$this->thread = $thread;
		$this->post = $post;

		// get marked posts from session
		$this->getMarkedPosts();

		if (is_object($this->board) && $this->board->postAddGuthaben != 0)
			$this->add = $this->board->postAddGuthaben;
		else
			$this->add = GUTHABEN_EARN_PER_POST;
	}

	/**
	 * Gets marked posts from session.
	 */
	public function getMarkedPosts()
	{
		$sessionVars = WCF :: getSession()->getVars();
		if (isset($sessionVars['markedPosts']))
		{
			$this->postIDs = implode(',', $sessionVars['markedPosts']);
		}
	}

	/**
	 * Enables the selected post.
	 */
	public function enable()
	{
		if (!$this->board->getModeratorPermission('canEnablePost'))
		{
			return;
		}

		if ($this->post != null && $this->post->isDisabled)
		{
			Guthaben :: add($this->add, 'wbb.guthaben.log.newpost', $this->thread->topic.': '.(!empty($this->post->subject) ? $this->post->subject : '---'), 'index.php?page=Thread&postID='.$this->post->postID.'#post'.$this->post->postID, new User($this->post->userID));
		}
	}

	/**
	 * Disables the selected post.
	 */
	public function disable()
	{
		if (!$this->board->getModeratorPermission('canEnablePost'))
		{
			return;
		}

		if ($this->post != null && !$this->post->isDisabled)
		{
			Guthaben :: sub($this->add, 'wbb.guthaben.log.deletepost', $this->thread->topic . ' ' . $this->post->subject, 'index.php?page=Thread&postID='.$this->post->postID.'#post'.$this->post->postID, new User($this->post->userID));
		}
	}

	/**
	 * Trashes the selected post.
	 */
	public function trash($ignorePermission = false)
	{
		if (!THREAD_ENABLE_RECYCLE_BIN || (!$ignorePermission && !$this->board->getModeratorPermission('canDeletePost')))
		{
			return;
		}

		if ($this->post != null && !$this->post->isDeleted)
		{
			Guthaben :: sub($this->add, 'wbb.guthaben.log.deletepost', $this->thread->topic . ' ' . $this->post->subject, '', new User($this->post->userID));
		}
	}

	/**
	 * Restores the selected post.
	 */
	public function recover()
	{
		if (!$this->board->getModeratorPermission('canDeletePostCompletely'))
		{
			return;
		}

		if ($this->post != null && $this->post->isDeleted)
		{
			Guthaben :: add($this->add, 'wbb.guthaben.log.recoverpost', $this->thread->topic . ' ' . $this->post->subject, '', new User($this->post->userID));
		}
	}

	/**
	 * Deletes the selected post.
	 */
	public function delete()
	{
		if ($this->post == null)
		{
			require_once(WCF_DIR.'lib/system/exception/IllegalLinkException.class.php');
			throw new IllegalLinkException();
		}

		if (THREAD_ENABLE_RECYCLE_BIN)
		{
			return;
		}

		if ($this->post != null)
		{
			Guthaben :: sub($this->add, 'wbb.guthaben.log.deletepost', $this->thread->topic . ' ' . $this->post->subject, '', new User($this->post->userID));
		}
	}

	/**
	 * Deletes all marked posts.
	 */
	public function deleteAll()
	{
		// get threadids
		$threadIDs = PostEditor :: getThreadIDs($this->postIDs);

		// get boards
		list ($boards) = ThreadEditor :: getBoards($threadIDs);

		// check permissions
		foreach ($boards as $board)
		{
			$board->checkModeratorPermission('canDeletePost');
		}

		$sql = 'SELECT 	post.postID, post.userID, post.subject, thread.topic,
						thread.firstPostID, board.threadAddGuthaben, board.postAddGuthaben
				FROM 	wbb' . WBB_N . '_post post
				JOIN	wbb' . WBB_N . '_thread thread ON (post.threadID = thread.threadID)
				JOIN	wbb' . WBB_N . '_board board ON (thread.boardID = board.boardID)
				WHERE	post.postID IN ('.$this->postIDs.')';

		$result = WCF :: getDB()->sendQuery($sql);

		while (false !== ($data = WCF :: getDB()->fetchArray($result)))
		{
			if ($data['postAddGuthaben'] != 0)
				$add = $data['postAddGuthaben'];
			else
				$add = GUTHABEN_EARN_PER_POST;

			if ($data['postID'] == $data['firstPostID'])
			{
				if ($data['threadAddGuthaben'] != 0)
					$add = $data['threadAddGuthaben'];
				else
					$add = GUTHABEN_EARN_PER_THREAD;

				Guthaben :: sub($add, 'wbb.guthaben.log.deletethread', $data['topic'] . ' ' . $data['subject'], '', new User($data['userID']));
			}
			else
			{
				Guthaben :: sub($add, 'wbb.guthaben.log.deletepost', $data['topic'] . ' ' . $data['subject'], '', new User($data['userID']));
			}
		}
	}
}
?>