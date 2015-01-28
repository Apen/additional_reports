<?php

// Import ajax
$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX'][$_EXTKEY.'::compareFiles'] = 'EXT:' . $_EXTKEY . '/Classes/Eid/class.tx_additionalreports_ajax.php:tx_additionalreports_ajax->main';

?>