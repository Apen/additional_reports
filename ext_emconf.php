<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "additional_reports".
 *
 * Auto generated 07-12-2015 11:22
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'Useful information in reports module',
	'description' => 'Useful information in the reports module: xclass, ajax, cliKeys, eID, general status of the system (encoding, DB, php vars...), hooks, compare local and TER extension (diff), used content type, used plugins, ExtDirect... It can really help you during migration or new existing project (to have global reports of the system).',
	'category' => 'misc',
	'shy' => true,
	'version' => '3.0.3',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => true,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => true,
	'lockType' => '',
	'author' => 'CERDAN Yohann',
	'author_email' => 'cerdanyohann@yahoo.fr',
	'author_company' => '',
	'CGLcompliance' => NULL,
	'CGLcompliance_note' => NULL,
	'constraints' => 
	array (
		'depends' => 
		array (
			'TYPO3' => '6.2.0-7.6.99',
		),
		'conflicts' => 
		array (
		),
		'suggests' => 
		array (
		),
	),
);

?>