<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_keforumnotifications_config'] = array (
	'ctrl' => $TCA['tx_keforumnotifications_config']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,title,datapid,usergroups,templatefile,singleviewlink,sender_mail,sender_name,debug_email'
	),
	'feInterface' => $TCA['tx_keforumnotifications_config']['feInterface'],
	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'title' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ke_forum_notifications/locallang_db.xml:tx_keforumnotifications_config.title',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',
			)
		),
		'datapid' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ke_forum_notifications/locallang_db.xml:tx_keforumnotifications_config.datapid',		
			'config' => array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'pages',	
				'size' => 3,	
				'minitems' => 0,
				'maxitems' => 99,
			)
		),
		'usergroups' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ke_forum_notifications/locallang_db.xml:tx_keforumnotifications_config.usergroups',		
			'config' => array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'fe_groups',	
				'size' => 3,	
				'minitems' => 0,
				'maxitems' => 99,
			)
		),
		'templatefile' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ke_forum_notifications/locallang_db.xml:tx_keforumnotifications_config.templatefile',		
			'config' => array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => '',	
				'disallowed' => 'php,php3',	
				'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],	
				'uploadfolder' => 'uploads/tx_keforumnotifications',
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'singleviewlink' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ke_forum_notifications/locallang_db.xml:tx_keforumnotifications_config.singleviewlink',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',
			)
		),
		'sender_mail' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ke_forum_notifications/locallang_db.xml:tx_keforumnotifications_config.sender_mail',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',
			)
		),
		'sender_name' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ke_forum_notifications/locallang_db.xml:tx_keforumnotifications_config.sender_name',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',
			)
		),
		'debug_email' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ke_forum_notifications/locallang_db.xml:tx_keforumnotifications_config.debug_email',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, datapid;;;;3-3-3, usergroups, templatefile, singleviewlink, sender_mail, sender_name, debug_email')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);



$TCA['tx_keforumnotifications_log'] = array (
	'ctrl' => $TCA['tx_keforumnotifications_log']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,forumpost'
	),
	'feInterface' => $TCA['tx_keforumnotifications_log']['feInterface'],
	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'forumpost' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ke_forum_notifications/locallang_db.xml:tx_keforumnotifications_log.forumpost',		
			'config' => array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'tx_keforum_posts',	
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'hidden;;1;;1-1-1, forumpost')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);
?>