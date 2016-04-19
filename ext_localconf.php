<?php

// Import ajax

if (version_compare(TYPO3_version, '7.6.0', '>=')) {
    $GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX'][$_EXTKEY . '::compareFiles'] = array(
        'callbackMethod' => 'EXT:' . $_EXTKEY . '/Classes/Eid/class.tx_additionalreports_callajax.php:tx_additionalreports_callajax->main',
        'csrfTokenCheck' => false
    );
} else {
    $GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX'][$_EXTKEY . '::compareFiles'] = 'EXT:' . $_EXTKEY . '/Classes/Eid/class.tx_additionalreports_callajax.php:tx_additionalreports_callajax->main';
}

?>