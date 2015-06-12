<?php

class tx_additionalreports_ajax {
    public function main() {
        include(PATH_site . 'typo3conf/ext/additional_reports/Classes/Eid/class.tx_compareFiles_eID.php');
    }
}