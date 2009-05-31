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
require_once (WBB_DIR . 'lib/data/board/BoardEditor.class.php');
require_once (WBB_DIR . 'lib/data/thread/ThreadEditor.class.php');
require_once (WBB_DIR . 'lib/data/post/PostEditor.class.php');

// wcf imports
require_once (WCF_DIR . 'lib/system/exception/IllegalLinkException.class.php');
require_once (WCF_DIR . 'lib/system/exception/PermissionDeniedException.class.php');

class GuthabenThreadAction
{
	protected $boardID = 0;
	protected $threadID = 0;
	protected $threadIDs = null;
	protected $postIDs = null;
	protected $board = null;
	protected $thread = null;

	/**
	 * Creates a new ThreadAction object.
	 *
	 * @param	BoardEditor	$board
	 * @param	ThreadEditor	$thread
	 * @param	PostEditor	$post
	 */
	public function __construct($board = null, $thread = null, $post = null, $threadID = 0, $topic = '', $prefix = '', $forwardURL = '')
	{
		$this->board = $board;
		$this->thread = $thread;
		$this->post = $post;

		if ($threadID != 0)
			$this->threadID = $threadID;
		else if ($thread)
			$this->threadID = $thread->threadID;

		if ($board)
			$this->boardID = $board->boardID;

		// get marked threads from session
		$this->getMarkedThreads();

		if (is_object($this->board) && $this->board->postAddGuthaben != 0)
			$this->add = $this->board->postAddGuthaben;
		else
			$this->add = GUTHABEN_EARN_PER_POST;
	}

	/**
	 * Gets marked threads and posts from session.
	 */
	public function getMarkedThreads()
	{
		$sessionVars = WCF :: getSession()->getVars();
		if (isset($sessionVars['markedThreads']))
		{
			$this->threadIDs = implode(',', $sessionVars['markedThreads']);
		}

		if (isset($sessionVars['markedPosts']))
		{
			$this->postIDs = implode(',', $sessionVars['markedPosts']);
		}
	}

	/**
	 * Disables the selected thread.
	 */
	public function disable()
	{
		if (!$this->board->getModeratorPermission('canEnableThread'))
		{
			return;
		}

		if ($this->thread != null && !$this->thread->isDisabled)
		{
			if ($this->board->threadAddGuthaben != 0)
				$add = $this->board->threadAddGuthaben;
			else
				$add = GUTHABEN_EARN_PER_THREAD;

			Guthaben :: sub($add, 'wbb.guthaben.log.deletethread', $this->thread->topic, '', new User($this->thread->userID));
		}
	}

	/**
	 * Enables the selected thread.
	 */
	public function enable()
	{
		if (!$this->board->getModeratorPermission('canEnableThread'))
		{
			return;
		}

		if ($this->thread != null && $this->thread->isDisabled)
		{
			if ($this->board->threadAddGuthaben != 0)
				$add = $this->board->threadAddGuthaben;
			else
				$add = GUTHABEN_EARN_PER_THREAD;

			Guthaben :: add($add, 'wbb.guthaben.log.newthread', $this->thread->topic, 'index.php?page=Thread&threadID='.$this->thread->threadID, new User($this->thread->userID));
		}
	}

	/**
	 * Trashes the selected thread.
	 */
	public function trash()
	{
		if (!THREAD_ENABLE_RECYCLE_BIN || !$this->board->getModeratorPermission('canDeleteThread'))
		{
			return;
		}

		if ($this->thread != null && !$this->thread->isDeleted)
		{
			$postIDs = explode(',', ThreadEditor :: getAllPostIDs($this->thread->threadID));

			foreach ($postIDs as $postID)
			{
				if ($postID == $this->thread->firstPostID)
				{
					if ($this->board->threadAddGuthaben != 0)
						$add = $this->board->threadAddGuthaben;
					else
						$add = GUTHABEN_EARN_PER_THREAD;

					$what = 'thread';
				}
				else
				{
					$add = $this->add;
					$what = 'post';
				}

				$post = new PostEditor($postID);
				Guthaben :: sub($add, 'wbb.guthaben.log.delete'.$what, $this->thread->topic . ' ' . $post->subject, '', new User($post->userID));
			}
		}
	}

	/**
	 * Deletes the selected thread.
	 */
	public function delete()
	{
		if ($this->thread == null)
		{
			throw new IllegalLinkException();
		}

		$this->board->checkModeratorPermission('canDeleteThreadCompletely');

		if ((!$this->thread->isDeleted || !THREAD_ENABLE_RECYCLE_BIN) && $this->thread->movedThreadID == 0)
		{
			$postIDs = ThreadEditor :: getAllPostIDs($this->thread->threadID);

			$postIDs = explode(',', $postIDs);

			foreach ($postIDs as $postID)
			{
				if ($postID == $this->thread->firstPostID)
				{
					if ($this->board->threadAddGuthaben != 0)
						$add = $this->board->threadAddGuthaben;
					else
						$add = GUTHABEN_EARN_PER_THREAD;

					$what = 'thread';
				}
				else
				{
					$add = $this->add;
					$what = 'post';
				}

				$post = new PostEditor($postID);
				Guthaben :: sub($add, 'wbb.guthaben.log.delete'.$what, $this->thread->topic . ' ' . $post->subject, '', new User($post->userID));
			}
		}
	}

	/**
	 * Recovers the selected thread.
	 */
	public function recover()
	{
		if (!$this->board->getModeratorPermission('canDeleteThreadCompletely'))
		{
			return;
		}

		if ($this->thread != null && $this->thread->isDeleted)
		{
			$postIDs = explode(',', ThreadEditor :: getAllPostIDs($this->thread->threadID));

			foreach ($postIDs as $postID)
			{
				if ($postID == $this->thread->firstPostID)
				{
					if ($this->board->threadAddGuthaben != 0)
						$add = $this->board->threadAddGuthaben;
					else
						$add = GUTHABEN_EARN_PER_THREAD;

					$what = 'thread';
				}
				else
				{
					$add = $this->add;
					$what = 'post';
				}

				$post = new PostEditor($postID);
				Guthaben :: add($add, 'wbb.guthaben.log.recover'.$what, $this->thread->topic . ' ' . $post->subject, '', new User($post->userID));
			}
		}
	}

	/**
	 * Deletes all marked threads.
	 */
	public function deleteAll()
	{
		list ($boards, $boardIDs) = ThreadEditor :: getBoards($this->threadIDs);

		// check permissions
		foreach ($boards as $board)
		{
			$board->checkModeratorPermission('canDeleteThread');
		}

		$postIDs = ThreadEditor :: getAllPostIDs($this->threadIDs);

		$sql = 'SELECT 	post.postID, post.userID, post.subject, thread.topic,
						thread.firstPostID, board.threadAddGuthaben, board.postAddGuthaben
				FROM 	wbb' . WBB_N . '_post post
				JOIN	wbb' . WBB_N . '_thread thread ON (post.threadID = thread.threadID)
				JOIN	wbb' . WBB_N . '_board board ON (thread.boardID = board.boardID)
				WHERE	post.postID IN ('.$postIDs.')';

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