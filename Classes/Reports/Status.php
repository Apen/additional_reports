<?php

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Reports\ReportInterface;

class Status extends AbstractReport implements ReportInterface
{

    /**
     * This method renders the report
     *
     * @return string the status report as HTML
     */
    public function getReport()
    {
        $content = '<p class="help">' . Utility::getLanguageService()->getLL('status_description') . '</p>';

        if (!isset($this->reportObject->doc)) {
            $this->reportObject->doc = GeneralUtility::makeInstance(DocumentTemplate::class);
        }
        return $content . $this->display();
    }

    /**
     * Generate the global status report
     *
     * @return string HTML code
     */
    public function display()
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/status-fluid.html');
        $view->getRequest()->setControllerExtensionName('additional_reports');

        $this->displayTypo3($view);
        $this->displayEnv($view);
        $this->displayPhp($view);
        $this->displayMySql($view);
        $this->displayCronTab($view);

        return $view->render();
    }

    /**
     * @param \TYPO3\CMS\Fluid\View\AbstractTemplateView $view
     */
    public function displayTypo3(\TYPO3\CMS\Fluid\View\AbstractTemplateView $view)
    {
        // infos about typo3 versions
        $datas = [];
        $jsonVersions = Utility::getJsonVersionInfos();
        $currentVersionInfos = Utility::getCurrentVersionInfos($jsonVersions, TYPO3_version);
        $currentBranch = Utility::getCurrentBranchInfos($jsonVersions, TYPO3_version);
        $latestStable = Utility::getLatestStableInfos($jsonVersions);
        $latestLts = Utility::getLatestLtsInfos($jsonVersions);

        $extensions = [];
        if (is_file(Utility::getPathSite() . '/typo3conf/PackageStates.php')) {
            $packages = include(Utility::getPathSite() . '/typo3conf/PackageStates.php');
            foreach ($packages['packages'] as $extensionKey => $package) {
                $extensions[] = $extensionKey;
            }
        }
        sort($extensions);
        foreach ($extensions as $aKey => $extension) {
            $extensions[$aKey] = $extension . ' (' . Utility::getExtensionVersion($extension) . ')';
        }

        $datas['sitename'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'];
        $datas['version'] = TYPO3_version . ' [' . $currentVersionInfos['date'] . ']';
        $datas['current_branch'] = $currentBranch['version'] . ' [' . $currentBranch['date'] . ']';
        $datas['latest_stable'] = $latestStable['version'] . ' [' . $latestStable['date'] . ']';
        $datas['latest_lts'] = $latestLts['version'] . ' [' . $latestLts['date'] . ']';
        $datas['path'] = Utility::getPathSite();
        $datas['db_name'] = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['dbname'];
        $datas['db_user'] = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['user'];
        $datas['db_host'] = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['host'];
        $datas['db_init'] = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['initCommands'];
        $datas['db_pcon'] = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['persistentConnection'];

        // debug
        $datas['displayErrors'] = [
            'BE/debug : ' . $GLOBALS['TYPO3_CONF_VARS']['BE']['debug'],
            'FE/debug : ' . $GLOBALS['TYPO3_CONF_VARS']['FE']['debug'],
            'devIPmask : ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'],
            'displayErrors : ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['displayErrors'],
            'systemLogLevel : ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLogLevel'],
        ];

        // gfx
        $datas['gfx'] = [
            'processor_enabled : ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['processor_enabled'],
            'processor_path : ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['processor_path'],
            'processor_path_lzw : ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['processor_path_lzw'],
            'processor : ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['processor'],
            'processor_effects : ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['processor_effects'],
            'processor_allowTemporaryMasksAsPng : ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['processor_allowTemporaryMasksAsPng'],
            'processor_colorspace : ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['processor_colorspace'],
        ];

        // mail
        $datas['mail'] = [
            'transport : ' . $GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport'],
            'transport_sendmail_command : ' . $GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_sendmail_command'],
            'transport_smtp_server : ' . $GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_smtp_server'],
            'transport_smtp_encrypt : ' . $GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_smtp_encrypt'],
            'transport_smtp_username : ' . $GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_smtp_username'],
            'transport_smtp_password : ' . $GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_smtp_password'],
        ];

        $datas['password'] = [
            'BE/passwordHashing/className : ' . $GLOBALS['TYPO3_CONF_VARS']['BE']['passwordHashing']['className'],
            'FE/passwordHashing/className : ' . $GLOBALS['TYPO3_CONF_VARS']['FE']['passwordHashing']['className'],
        ];

        $datas['extensions'] = $extensions;

        $view->assign('datas_typo3', $datas);
    }

    /**
     * @param \TYPO3\CMS\Fluid\View\AbstractTemplateView $view
     */
    public function displayEnv(\TYPO3\CMS\Fluid\View\AbstractTemplateView $view)
    {
        $datas = [];
        $vars = GeneralUtility::getIndpEnv('_ARRAY');
        foreach ($vars as $varKey => $varValue) {
            $datas[$varKey] = $varValue;
        }
        $gE_keys = explode(',', 'HTTP_ACCEPT,HTTP_ACCEPT_ENCODING,HTTP_CONNECTION,HTTP_COOKIE,REMOTE_PORT,SERVER_ADDR,SERVER_ADMIN,SERVER_NAME,SERVER_PORT,SERVER_SIGNATURE,SERVER_SOFTWARE,GATEWAY_INTERFACE,SERVER_PROTOCOL,REQUEST_METHOD,PATH_TRANSLATED');
        foreach ($gE_keys as $k) {
            $datas[$k] = getenv($k);
        }
        $view->assign('datas_env', $datas);
    }

    /**
     * @param \TYPO3\CMS\Fluid\View\AbstractTemplateView $view
     */
    public function displayPhp(\TYPO3\CMS\Fluid\View\AbstractTemplateView $view)
    {
        $datas = [];

        $datas['status_version'] = phpversion();
        $datas['memory_limit'] = ini_get('memory_limit');
        $datas['max_execution_time'] = ini_get('max_execution_time');
        $datas['post_max_size'] = ini_get('post_max_size');
        $datas['upload_max_filesize'] = ini_get('upload_max_filesize');
        $datas['display_errors'] = ini_get('display_errors');
        $datas['error_reporting'] = ini_get('error_reporting');

        if (function_exists('posix_getpwuid') && function_exists('posix_getgrgid')) {
            $apacheUser = posix_getpwuid(posix_getuid());
            $apacheGroup = posix_getgrgid(posix_getgid());
            $datas['apache_user'] = $apacheUser['name'] . ' (' . $apacheUser['gid'] . ')';
            $datas['apache_group'] = $apacheGroup['name'] . ' (' . $apacheGroup['gid'] . ')';
        }
        $extensions = array_map('strtolower', get_loaded_extensions());
        natcasesort($extensions);
        $datas['extensions'] = $extensions;

        $view->assign('datas_php', $datas);
    }

    /**
     * @param \TYPO3\CMS\Fluid\View\AbstractTemplateView $view
     */
    public function displayMySql(\TYPO3\CMS\Fluid\View\AbstractTemplateView $view)
    {
        $datas = [];

        $connection = Utility::getDatabaseConnection();
        $connectionParams = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections'][ConnectionPool::DEFAULT_CONNECTION_NAME];

        $datas['version'] = $connection->getServerVersion();

        $items = Utility::getQueryBuilder()
            ->select('default_character_set_name', 'default_collation_name')
            ->from('information_schema.schemata')
            ->where("schema_name = '" . $connectionParams['dbname'] . "'")
            ->execute()
            ->fetchAll();

        $datas['default_character_set_name'] = $items[0]['default_character_set_name'];
        $datas['default_collation_name'] = $items[0]['default_collation_name'];
        $datas['query_cache'] = Utility::getMySqlCacheInformations();
        $datas['character_set'] = Utility::getMySqlCharacterSet();

        // TYPO3 database
        $items = Utility::getQueryBuilder()
            ->select(
                'table_name as table_name',
                'engine as engine',
                'table_collation as table_collation',
                'table_rows as table_rows'
            )
            ->add('select', '((data_length+index_length)/1024/1024) as "size"', true)
            ->from('information_schema.tables')
            ->where("table_schema = '" . $connectionParams['dbname'] . "'")
            ->orderBy('table_name')
            ->execute()
            ->fetchAll();

        $tables = [];
        $size = 0;

        foreach ($items as $itemValue) {
            $tables[] = [
                'name' => $itemValue['table_name'],
                'engine' => $itemValue['engine'],
                'collation' => $itemValue['table_collation'],
                'rows' => $itemValue['table_rows'],
                'size' => round($itemValue['size'], 2),
            ];
            $size += round($itemValue['size'], 2);
        }

        $datas['tables'] = $tables;
        $datas['tablessize'] = round($size, 2);
        $datas['typo3db'] = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['dbname'];

        $view->assign('datas_mysql', $datas);
    }

    /**
     * @param \TYPO3\CMS\Fluid\View\AbstractTemplateView $view
     */
    public function displayCronTab(\TYPO3\CMS\Fluid\View\AbstractTemplateView $view)
    {
        $datas = [];
        exec('crontab -l', $crontab);
        $crontabString = Utility::getLl('status_nocrontab');
        if (count($crontab) > 0) {
            $crontabString = '';
            foreach ($crontab as $cron) {
                if (trim($cron) !== '') {
                    $crontabString .= $cron . '<br />';
                }
            }
        }
        $datas['crontab'] = $crontabString;
        $view->assign('datas_crontab', $datas);
    }
}
