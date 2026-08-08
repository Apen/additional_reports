<?php

declare(strict_types=1);

use Sng\AdditionalReports\Eid\CallAjax;

return [
    'additional_reports_compareFiles' => [
        'path' => '/additional_reports/compareFiles',
        'target' => CallAjax::class . '::main',
    ],
];
