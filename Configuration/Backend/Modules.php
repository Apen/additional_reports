<?php

declare(strict_types=1);

use Sng\AdditionalReports\Controller\ReportController;
use TYPO3\CMS\Core\Information\Typo3Version;

if ((new Typo3Version())->getMajorVersion() < 14) {
    return [];
}

$reports = [
    'eid' => 'eid',
    'commandcontrollers' => 'commandControllers',
    'plugins' => 'plugins',
    'xclass' => 'xclass',
    'hooks' => 'hooks',
    'status' => 'status',
    'logerrors' => 'logErrors',
    'websitesconf' => 'websiteConf',
    'extensions' => 'extensions',
    'eventdispatcher' => 'eventDispatcher',
    'middlewares' => 'middlewares',
];

$modules = [];
foreach ($reports as $identifier => $controllerMethod) {
    $modules['system_reports_additionalreports_' . $identifier] = [
        'parent' => 'system_reports',
        'access' => 'admin',
        'path' => '/module/system/reports/additional-reports/' . $identifier,
        'iconIdentifier' => 'additionalreports_' . $identifier,
        'labels' => [
            'title' => 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:' . $identifier . '_title',
            'description' => 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:' . $identifier . '_description',
        ],
        'extensionName' => 'AdditionalReports',
        'routes' => [
            '_default' => [
                'target' => ReportController::class . '::' . $controllerMethod,
            ],
        ],
    ];
}

return $modules;
