<?php

namespace Sng\AdditionalReports\Eid;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

class CallAjax
{
    public function main(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response)
    {
        include(PATH_site . 'typo3conf/ext/additional_reports/Classes/Eid/class.tx_compareFiles_eID.php');
        return $response;
    }
}
