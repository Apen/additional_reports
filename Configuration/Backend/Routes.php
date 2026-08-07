<?php

declare(strict_types=1);

return [
    'additional_reports_compareFiles' => [
        'path' => '/additional_reports/compareFiles',
        'target' => \Sng\AdditionalReports\Eid\CallAjax::class . '::main',
    ],
];
