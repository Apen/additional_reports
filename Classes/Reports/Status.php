<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\AdditionalReports\Repository\DatabaseStatusRepository;
use Sng\AdditionalReports\Service\Typo3VersionInformationService;
use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewInterface;

class Status extends AbstractReport
{
    private readonly DatabaseStatusRepository $databaseStatusRepository;

    private readonly Typo3VersionInformationService $typo3VersionInformationService;

    public function __construct(
        ?object $reportObject = null,
        ?DatabaseStatusRepository $databaseStatusRepository = null,
        ?Typo3VersionInformationService $typo3VersionInformationService = null,
    ) {
        parent::__construct($reportObject);
        $this->databaseStatusRepository = $databaseStatusRepository ?? GeneralUtility::makeInstance(DatabaseStatusRepository::class);
        $this->typo3VersionInformationService = $typo3VersionInformationService ?? GeneralUtility::makeInstance(Typo3VersionInformationService::class);
    }

    /**
     * This method renders the report
     *
     * @return string the status report as HTML
     */
    public function getReport(): string
    {
        $content = '<p class="help">' . Utility::getLL('status_description') . '</p>';

        return $content . $this->display();
    }

    /**
     * Generate the global status report
     *
     * @return string HTML code
     */
    public function display(): string
    {
        $view = $this->createView('status-fluid');

        $this->displayTypo3($view);
        $this->displayEnv($view);
        $this->displayPhp($view);
        $this->displayMySql($view);

        return $view->render();
    }

    public function displayTypo3(ViewInterface $view): void
    {
        // infos about typo3 versions
        $datas = [];
        $jsonVersions = $this->typo3VersionInformationService->fetch();
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class)->getVersion();
        $currentVersionInfos = $this->typo3VersionInformationService->getCurrentVersion($jsonVersions, $typo3Version);
        $currentBranch = $this->typo3VersionInformationService->getCurrentBranch($jsonVersions, $typo3Version);
        $latestStable = $this->typo3VersionInformationService->getLatestStable($jsonVersions);
        $latestLts = $this->typo3VersionInformationService->getLatestLts($jsonVersions);

        $extensions = [];
        $packageManager = GeneralUtility::makeInstance(PackageManager::class);
        foreach ($packageManager->getActivePackages() as $package) {
            $extensions[] = $package->getPackageKey();
        }

        sort($extensions);
        foreach ($extensions as $aKey => $extension) {
            $extensions[$aKey] = $extension . ' (' . Utility::getExtensionVersion($extension) . ')';
        }

        $datas['sitename'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'];
        $datas['version'] = GeneralUtility::makeInstance(Typo3Version::class)->getVersion() . ' [' . ($currentVersionInfos['date'] ?? '') . ']';
        $datas['current_branch'] = ($currentBranch['version'] ?? '') . ' [' . ($currentBranch['date'] ?? '') . ']';
        $datas['latest_stable'] = ($latestStable['version'] ?? '') . ' [' . ($latestStable['date'] ?? '') . ']';
        $datas['latest_lts'] = ($latestLts['version'] ?? '') . ' [' . ($latestLts['date'] ?? '') . ']';
        $datas['path'] = Environment::getPublicPath();
        $datas['db_name'] = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['dbname'] ?? '';
        $datas['db_user'] = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['user'] ?? '';
        $datas['db_host'] = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['host'] ?? '';
        $datas['db_init'] = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['initCommands'] ?? '';
        $datas['db_pcon'] = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['persistentConnection'] ?? '';

        // debug
        $datas['displayErrors'] = [
            'BE/debug : ' . $GLOBALS['TYPO3_CONF_VARS']['BE']['debug'],
            'FE/debug : ' . $GLOBALS['TYPO3_CONF_VARS']['FE']['debug'],
            'devIPmask : ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'],
            'displayErrors : ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['displayErrors'],
            'systemLogLevel : ' . ($GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLogLevel'] ?? ''),
        ];

        // gfx
        $datas['gfx'] = [
            'processor_enabled : ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['processor_enabled'],
            'processor_path : ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['processor_path'],
            'processor_path_lzw : ' . ($GLOBALS['TYPO3_CONF_VARS']['GFX']['processor_path_lzw'] ?? ''),
            'processor : ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['processor'],
            'processor_effects : ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['processor_effects'],
            'processor_allowTemporaryMasksAsPng : ' . ($GLOBALS['TYPO3_CONF_VARS']['GFX']['processor_allowTemporaryMasksAsPng'] ?? ''),
            'processor_colorspace : ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['processor_colorspace'],
        ];

        // mail
        $datas['mail'] = [
            'transport : ' . $GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport'],
            'transport_sendmail_command : ' . $GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_sendmail_command'],
            'transport_smtp_server : ' . $GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_smtp_server'],
            'transport_smtp_encrypt : ' . $GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_smtp_encrypt'],
            'transport_smtp_username : ' . $GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_smtp_username'],
        ];

        $datas['password'] = [
            'BE/passwordHashing/className : ' . $GLOBALS['TYPO3_CONF_VARS']['BE']['passwordHashing']['className'],
            'FE/passwordHashing/className : ' . $GLOBALS['TYPO3_CONF_VARS']['FE']['passwordHashing']['className'],
        ];

        $datas['extensions'] = $extensions;

        $view->assign('datas_typo3', $datas);
    }

    public function displayEnv(ViewInterface $view): void
    {
        $normalizedParams = $this->getRequest()->getAttribute('normalizedParams');
        $datas = $normalizedParams instanceof NormalizedParams ? [
            'HTTP_HOST' => $normalizedParams->getHttpHost(),
            'REQUEST_URL' => $normalizedParams->getRequestUrl(),
            'REQUEST_DIR' => $normalizedParams->getRequestDir(),
            'REMOTE_ADDR' => $normalizedParams->getRemoteAddress(),
            'DOCUMENT_ROOT' => $normalizedParams->getDocumentRoot(),
            'SITE_URL' => $normalizedParams->getSiteUrl(),
            'HTTP_REFERER' => $normalizedParams->getHttpReferer(),
            'HTTP_USER_AGENT' => $normalizedParams->getHttpUserAgent(),
            'HTTP_ACCEPT_ENCODING' => $normalizedParams->getHttpAcceptEncoding(),
            'HTTP_ACCEPT_LANGUAGE' => $normalizedParams->getHttpAcceptLanguage(),
        ] : [];
        $view->assign('datas_env', $datas);
    }

    public function displayPhp(ViewInterface $view): void
    {
        $data = [];
        $data['status_version'] = PHP_VERSION;
        $data['memory_limit'] = ini_get('memory_limit');
        $data['max_execution_time'] = ini_get('max_execution_time');
        $data['post_max_size'] = ini_get('post_max_size');
        $data['upload_max_filesize'] = ini_get('upload_max_filesize');
        $data['display_errors'] = ini_get('display_errors');
        $data['error_reporting'] = ini_get('error_reporting');

        if (function_exists('posix_getpwuid') && function_exists('posix_getgrgid')) {
            $apacheUser = posix_getpwuid(posix_getuid());
            $apacheGroup = posix_getgrgid(posix_getgid());
            $data['apache_user'] = $apacheUser['name'] . ' (' . $apacheUser['gid'] . ')';
            $data['apache_group'] = $apacheGroup['name'] . ' (' . $apacheGroup['gid'] . ')';
        }

        if (function_exists('get_loaded_extensions')) {
            $extensions = array_map(strtolower(...), get_loaded_extensions());
            natcasesort($extensions);
            $data['extensions'] = $extensions;
        }

        $view->assign('datas_php', $data);
    }

    public function displayMySql(ViewInterface $view): void
    {
        $status = $this->databaseStatusRepository->getStatus();
        $data = [
            'version' => $status['version'],
            'default_character_set_name' => $status['defaultCharacterSet'],
            'default_collation_name' => $status['defaultCollation'],
            'tables' => $status['tables'],
            'tablessize' => $status['totalSize'],
            'typo3db' => $status['database'],
        ];

        $view->assign('datas_mysql', $data);
    }

    public function getIdentifier(): string
    {
        return 'additionalreports_status';
    }

    public function getTitle(): string
    {
        return 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:status_title';
    }

    public function getDescription(): string
    {
        return 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:status_description';
    }

    public function getIconIdentifier(): string
    {
        return 'module-reports';
    }
}
