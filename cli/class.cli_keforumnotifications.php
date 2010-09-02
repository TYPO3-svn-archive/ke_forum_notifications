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

if (!defined('TYPO3_cliMode'))  die('You cannot run this script directly!');


// Include basis cli class
require_once(PATH_t3lib.'class.t3lib_cli.php');
require_once(t3lib_extMgm::extPath('ke_forum_notifications').'class.tx_keforumnotifications.php');

class tx_keforumnotifications_cli extends t3lib_cli {
	
	var $prefixId      = 'tx_keforumnotifications_cli';		// Same as class name
	var $scriptRelPath = 'cli/class.cli_keforumnotifications.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'ke_forum_notifications';	// The extension key.

	
	/**
	 * Constructor
	 */
    #function __construct() {
    function tx_keforumnotifications_cli() {
		
		// Running parent class constructor
        parent::t3lib_cli();
		
        // Setting help texts:
        $this->cli_help['name'] = 'class.cli_keforumnotifications.php';
        $this->cli_help['synopsis'] = '###OPTIONS###';
        $this->cli_help['description'] = 'CLI-Script for sending forum notifications about new posts';
        $this->cli_help['examples'] = '.../typo3/cli_dispatch.phpsh ke_forum_notifications processdaily';
        $this->cli_help['author'] = 'Andreas Kiefer (kennziffer.com), (c)2010';
    }

    /**
     * CLI engine
     *
     * @param    array        Command line arguments
     * @return    string
     */
    function cli_main($argv) {
		
		// validate input
		$this->cli_validateArgs();
		
        // select called function
		switch ((string)$this->cli_args['_DEFAULT'][1]) {
			
			case 'processdaily':
				$this->processdaily();
				break;
			
			default:
				$this->cli_help();
	            exit;
			
		}
		
    }
    
	/**
	 * processing of daily 
	 */
    function processdaily(){
		
		$this->cli_echo("====================\r\n");
		$this->cli_echo("Process Daily Report\r\n");
		$this->cli_echo("====================\r\n\r\n");
		
		$notifier = t3lib_div::makeInstance('tx_keforumnotifications');
		$notifier-> process();
		
		
    }
}

// call function
$ke_forum_notifications  = t3lib_div::makeInstance('tx_keforumnotifications_cli');
$ke_forum_notifications->cli_main($_SERVER['argv']);

?>