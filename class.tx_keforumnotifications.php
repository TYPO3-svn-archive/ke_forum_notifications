<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Andreas Kiefer, www.kennziffer.com GmbH <kiefer@kennziffer.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


/**
 * @author	Andreas Kiefer (kennziffer.com) <kiefer@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_keforum_notifications
 */


class tx_keforumnotifications {
	
	var $extKey = 'ke_forum_notifications';
	var $configurationsTable = 'tx_keforumnotifications_config';
	var $logTable = 'tx_keforumnotifications_log';
	var $postsTable = 'tx_keforum_posts';
	var $threadsTable = 'tx_keforum_threads';
	var $categoryTable = 'tx_keforum_categories';
	var $userTable = 'fe_users';
	
	
	
	function process() {
		// get notification configurations from db
		$configurations = $this->getConfigurations();
		
		// run through configurations
		if (is_array($configurations)) {
			foreach ($configurations as $config) {
				
				$this->configData = $config;
				
				echo "=========================\r\n";
				echo $config['title']."\r\n";
				echo "=========================\r\n";
				echo "\r\n";
				
				// get users
				$users = $this->getUsers($config['usergroups']);
				$this->categories = array();
				$this->categoriesData = array();
				$this->threads = array();
				$this->threadsData = array();
				$this->postsData = array();
				$this->userData = array();
				
				// get posts and threads
				$posts = $this->getPostsAndThreads($config['datapid']);
				
				// run through all new posts and check access for all users
				if (is_array($posts) && is_array($users)) {
					
					foreach ($users as $key => $user) {
						
						$this->userData[$user['uid']] = $user;
						foreach ($posts as $key => $post) {
							$this->postsData[$post['uid']] = $post;
							$threadUid = $post['thread'];
							$categoryUid = $this->threadsData[$post['thread']]['category'];
							$access = $this->checkAccess($post['uid'], $threadUid, $categoryUid, $user);
							if ($access) {
								$mails[$user['uid']][$categoryUid][$threadUid][] = $post['uid'];
							}
						}
					}
					$this->renderAndSendNotification($mails);
					
					foreach ($posts as $key => $post) {
						$this->markPostAsNotified($post['uid'],$config['pid']);
					}
					
				}
			}
		}
	}
	
	
	function renderAndSendNotification($mails) {
		echo "------------------------------------------\r\n";
		// get html template
		$templateFile = $this->configData['templatefile'] ? PATH_site.'uploads/tx_keforumnotifications/'.$this->configData['templatefile'] : t3lib_extMgm::extPath($this->extKey).'res/mailtemplate.html';
		$templateContent = file_get_contents($templateFile);
		
		foreach ($mails as $user => $userData) {
			echo $user.": ".$this->userData[$user]['username']." | ".$this->userData[$user]['email']."\r\n";
			echo "------------------------------------------\r\n";
			
			// kategorien
			$postsContent = '';
			foreach ($userData as $cat => $catData) {
				$postsContent .= '<h1>Rubrik: '.$this->categoriesData[$cat]['title'].'</h1>';
				// threads
				foreach ($catData as $thread => $threadData) {
					
					// generate link to singleview
					$link = $this->configData['singleviewlink'];
					$link = str_replace('###CAT###', $cat, $link);
					$link = str_replace('###THREAD###', $thread, $link);
					
					$postsContent .= '<h2>Thema: '.$this->threadsData[$thread]['title'].' (<a href="'.$link.'">zum Thema</a>)</h2>';
					// posts
					foreach ($threadData as $post) {
						// generate content
						$contentString = strlen($this->postsData[$post]['content']) > 100 ? substr($this->postsData[$post]['content'],0,100).'...' : $this->postsData[$post]['content'];
						$authorString = $this->getAuthorInformation($this->postsData[$post]['author']);
						$postsContent .= '<div class="postEntry">';
						$postsContent .= '<i>'.strftime('%d.%m.%Y, %H:%M Uhr  ', $this->postsData[$post]['crdate']).', '.$authorString.'</i><br />';
						$postsContent .= $contentString;
						$postsContent .= '</div>';
					}
				}
			}
			
			// generate mail content
			$content = $templateContent;
			$content = str_replace('###USERNAME###', $this->userData[$user]['username'], $content);
			$content = str_replace('###FIRST_NAME###', $this->userData[$user]['first_name'], $content);
			$content = str_replace('###LAST_NAME###', $this->userData[$user]['last_name'], $content);
			$content = str_replace('###CONFIG_TITLE###', $this->configData['title'], $content);
			$content = str_replace('###POSTS###', $postsContent, $content);
			
			$subject = "Neue Nachrichten im Forum ".$this->configData['title'];
			
			// overwrite recipient with debug email address?
			$recipient = $this->configData['debug_email'] ? $this->configData['debug_email'] : $this->userData[$user]['email'];
			
			$this->sendNotificationMail($recipient, $subject, $content, $this->configData['sender_mail'], $this->configData['sender_name']);
		}
	}
	
	/*
	* Benachrichtigung senden
	*/
	function sendNotificationMail($recipient, $subject, $content, $from_email="", $from_name="") {
		
		$Typo3_htmlmail = t3lib_div::makeInstance('t3lib_htmlmail');
		$Typo3_htmlmail->start();
		
		$html_message = $content;
		// create the plain message body
		$plaintext_message = html_entity_decode(strip_tags($html_message), ENT_QUOTES, $GLOBALS['TSFE']->renderCharset);
		
		$Typo3_htmlmail->subject = $subject;
		$Typo3_htmlmail->from_email = $from_email ? $from_email : $this->conf['email_from'];
		$Typo3_htmlmail->from_name = $from_name ? $from_name : $this->conf['email_from_name'];
		$Typo3_htmlmail->replyto_email = $from_email ? $from_email : $this->conf['email_from'];
		$Typo3_htmlmail->replyto_name = $from_name ? $from_name : $this->conf['email_from_name'];
		$Typo3_htmlmail->organisation = '';
		
		// Fetches the content of the page
		$Typo3_htmlmail->theParts['html']['content'] = $html_message; 
		$Typo3_htmlmail->theParts['html']['path'] = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST') . '/';
		
		$Typo3_htmlmail->extractMediaLinks();
		$Typo3_htmlmail->extractHyperLinks();
		$Typo3_htmlmail->fetchHTMLMedia();
		$Typo3_htmlmail->substMediaNamesInHTML(0);	// 0 = relative
		$Typo3_htmlmail->substHREFsInHTML();  
		$Typo3_htmlmail->setHTML($Typo3_htmlmail->encodeMsg($Typo3_htmlmail->theParts['html']['content']));
		$Typo3_htmlmail->addPlain($plaintext_message);
		$Typo3_htmlmail->setHeaders();
		$Typo3_htmlmail->setContent();
		$Typo3_htmlmail->setRecipient($recipient);
		$Typo3_htmlmail->sendTheMail();
	}
	
	
	
	
	
	
	// check if user has access
	function checkAccess($postUid, $threadUid, $categoryUid, $userRecord) {
		// public --> access always OK
		if ($this->categoriesData[$categoryUid]['public']) {
			return true;
		}
		// not public --> check usergroup settings
		else {
			$access = array();
			$fields = '*';
			$table = 'tx_keforum_categories_read_access_mm';
			$where = 'tx_keforum_categories_read_access_mm.uid_local="'.$categoryUid.'" ';
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$groupBy='',$orderBy='',$limit='');
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$access[$row['uid_foreign']] = 1;
			}
			
			$fields = '*';
			$table = 'tx_keforum_categories_write_access_mm';
			$where = 'tx_keforum_categories_write_access_mm.uid_local="'.$categoryUid.'" ';
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$groupBy='',$orderBy='',$limit='');
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$access[$row['uid_foreign']] = 1;
			}
			
			foreach (explode(',', $userRecord['usergroup']) as $usergroup) {
				if ($access[$usergroup]) return true;
			}
		}
	}
	
	
	// get configuration records
	function getConfigurations() {
		$where = '1=1 ';
		$where .= t3lib_BEfunc::BEenableFields($this->configurationsTable,$inv=0);
		$where .= t3lib_BEfunc::deleteClause($this->configurationsTable,$inv=0);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$this->configurationsTable,$where,$groupBy='',$orderBy='',$limit='');
		while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$configurations[] = $row;
		}
		return $configurations;
	}
	
	// get all posts that 
	// - has been created in last 7 days
	// - are stored in pid that is set in configuration record
	// - has not been notified yet (= not saved in log table)
	function getPostsAndThreads($pid) {
		$createdAfter = time() - (7 * 60 * 60 * 24);
		
		$where = 'pid="'.intval($pid).'" ';
		$where .= ' AND crdate > '.$createdAfter;
		$where .= t3lib_BEfunc::BEenableFields($this->postsTable,$inv=0);
		$where .= t3lib_BEfunc::deleteClause($this->postsTable,$inv=0);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$this->postsTable,$where,$groupBy='',$orderBy='',$limit='');
		while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if (!$this->checkIfAlreadyNotified($row['uid'])) {
				$posts[] = $row;
				if (!in_array($row['thread'],$this->threads)) $this->threads[] = $row['thread'];
			}
		}
		
		foreach ($this->threads as $thread) {
			
			$where = 'uid="'.$thread.'" ';
			$where .= t3lib_BEfunc::BEenableFields($this->threadsTable,$inv=0);
			$where .= t3lib_BEfunc::deleteClause($this->threadsTable,$inv=0);
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$this->threadsTable,$where,$groupBy='',$orderBy='',$limit='');
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$this->threadsData[$row['uid']] = $row;
				$categoryData = $this->getCategoryByThreadUid($row['uid']);
				$this->categoriesData[$categoryData['uid']] = $categoryData;
			}
		}
		
		return $posts;
	}
	
	
	// get category data by thread UID
	function getCategoryByThreadUid($threadUid) {
		
		$fields = '*';
		$table = 'tx_keforum_threads, tx_keforum_categories';
		$where = 'tx_keforum_threads.uid = "'.intval($threadUid).'" ';
		$where .= ' AND tx_keforum_threads.category = tx_keforum_categories.uid';
		$where .= t3lib_BEfunc::BEenableFields($this->threadsTable,$inv=0);
		$where .= t3lib_BEfunc::deleteClause($this->threadsTable,$inv=0);
		$where .= t3lib_BEfunc::BEenableFields($this->categoryTable,$inv=0);
		$where .= t3lib_BEfunc::deleteClause($this->categoryTable,$inv=0);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$groupBy='',$orderBy='',$limit='');
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		return $row;
	}
	
	
	// get users that are member of specified usergroup(s)
	// and that have activated the notify function
	function getUsers($usergroups) {
		$i=0;
		foreach (explode(',', $usergroups) as $usergroup) {
			if ($i>0) $usergroupWhere .= ' OR ';
			$usergroupWhere .= $GLOBALS['TYPO3_DB']->listQuery('usergroup', $usergroup, $this->userTable);
			$i++;
		}
		$where = 'tx_keforum_daily_report=1';
		$where .= ' AND ('.$usergroupWhere.')';
		$where .= t3lib_BEfunc::BEenableFields($this->userTable,$inv=0);
		$where .= t3lib_BEfunc::deleteClause($this->userTable,$inv=0);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$this->userTable,$where,$groupBy='',$orderBy='',$limit='');
		while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$users[$row['uid']] = $row;
		}
		return $users;
	}
	
	
	// check if a record has already been notified
	function checkIfAlreadyNotified($postUid) {
		$where = 'forumpost="'.intval($postUid).'" ';
		$where .= t3lib_BEfunc::BEenableFields($this->logTable,$inv=0);
		$where .= t3lib_BEfunc::deleteClause($this->logTable,$inv=0);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$this->logTable,$where,$groupBy='',$orderBy='',$limit='');
		return $GLOBALS['TYPO3_DB']->sql_num_rows($res);
	}
	
	
	// mark post as notified (= save in log table)
	function markPostAsNotified($postUid, $pid) {
		unset($fields_values);
		$fields_values = array(
			'forumpost' => intval($postUid),
			'tstamp' => time(),
			'crdate' => time(),
			'pid' => intval($pid),
		);
		$GLOBALS['TYPO3_DB']->exec_INSERTquery($this->logTable,$fields_values,$no_quote_fields=FALSE);
	}
	
	
	function getAuthorInformation($userUid) {
		
		$fields = 'first_name,last_name,username';
		$table = 'fe_users';
		$where = 'uid="'.intval($userUid).'" ';
		$where .= t3lib_BEfunc::BEenableFields($table,$inv=0);
		$where .= t3lib_BEfunc::deleteClause($table,$inv=0);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$groupBy='',$orderBy='',$limit='1');
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		if ($row['last_name']) {
			$username_full = $row['first_name'];
			if ($username_full) {
				$username_full .= ' ';
			}
			$username_full .= $row['last_name'];
		} else {
			$username_full = $row['username'];
		}
		return $username_full;
	}
	
}