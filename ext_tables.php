<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}
$TCA['tx_keforumnotifications_config'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:ke_forum_notifications/locallang_db.xml:tx_keforumnotifications_config',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY tstamp',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_keforumnotifications_config.gif',
	),
);

$TCA['tx_keforumnotifications_log'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:ke_forum_notifications/locallang_db.xml:tx_keforumnotifications_log',		
		'label'     => 'forumpost',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_keforumnotifications_log.gif',
	),
);
?>