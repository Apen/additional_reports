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
use TYPO3\CMS\Core\Utility\CommandUtility;
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
        $content = '<p class="help">' . $GLOBALS['LANG']->getLL('status_description') . '</p>';

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

        // infos about typo3 versions
        $jsonVersions = Utility::getJsonVersionInfos();
        $currentVersionInfos = Utility::getCurrentVersionInfos($jsonVersions, TYPO3_version);
        $currentBranch = Utility::getCurrentBranchInfos($jsonVersions, TYPO3_version);
        $latestStable = Utility::getLatestStableInfos($jsonVersions);
        $latestLts = Utility::getLatestLtsInfos($jsonVersions);
        $headerVersions = Utility::getLl('status_version') . '<br/>';
        $headerVersions .= Utility::getLl('latestbranch') . '<br/>';
        $headerVersions .= Utility::getLl('lateststable') . '<br/>';
        $headerVersions .= Utility::getLl('latestlts');
        $htmlVersions = TYPO3_version . ' [' . $currentVersionInfos['date'] . ']';
        $htmlVersions .= '<br/>' . $currentBranch['version'] . ' [' . $currentBranch['date'] . ']';
        $htmlVersions .= '<br/>' . $latestStable['version'] . ' [' . $latestStable['date'] . ']';
        $htmlVersions .= '<br/>' . $latestLts['version'] . ' [' . $latestLts['date'] . ']';

        // TYPO3
        $content = Utility::writeInformation(Utility::getLl('status_sitename'), $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']);
        $content .= Utility::writeInformation($headerVersions, $htmlVersions);
        $content .= Utility::writeInformation(Utility::getLl('status_path'), PATH_site);
        $content .= Utility::writeInformation(
            'dbname<br/>user<br/>host',
            $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['dbname'] . '<br/>'
            . $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['user'] . '<br/>'
            . $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['host']
        );
        if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path'] != '') {
            $cmd = CommandUtility::imageMagickCommand('convert', '-version');
            exec($cmd, $ret);
            $content .= Utility::writeInformation(
                Utility::getLl('status_im'),
                $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path'] . ' (' . $ret[0] . ')'
            );
        }
        $content .= Utility::writeInformation('forceCharset', $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset']);
        $content .= Utility::writeInformation('DB/Connections/Default/initCommands', $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['initCommands']);
        $content .= Utility::writeInformation('no_pconnect', $GLOBALS['TYPO3_CONF_VARS']['SYS']['no_pconnect']);
        $content .= Utility::writeInformation('displayErrors', $GLOBALS['TYPO3_CONF_VARS']['SYS']['displayErrors']);
        $content .= Utility::writeInformation('maxFileSize', $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize']);

        $extensions = explode(',', $GLOBALS['TYPO3_CONF_VARS']['EXT']['extList']);

        if (is_file(PATH_site . 'typo3conf/PackageStates.php')) {
            $extensions = [];
            $packages = include(PATH_site . 'typo3conf/PackageStates.php');
            foreach ($packages['packages'] as $extensionKey => $package) {
                $extensions[] = $extensionKey;
            }
        }

        sort($extensions);
        foreach ($extensions as $aKey => $extension) {
            $extensions[$aKey] = $extension . ' (' . Utility::getExtensionVersion($extension) . ')';
        }
        $content .= Utility::writeInformationList(
            Utility::getLl('status_loadedextensions'),
            $extensions
        );

        $view->assign('typo3', $content);

        // Debug
        $content = '';
        $vars = GeneralUtility::getIndpEnv('_ARRAY');
        foreach ($vars as $varKey => $varValue) {
            $content .= Utility::writeInformation($varKey, $varValue);
        }
        $gE_keys = explode(',', 'HTTP_ACCEPT,HTTP_ACCEPT_ENCODING,HTTP_CONNECTION,HTTP_COOKIE,REMOTE_PORT,SERVER_ADDR,SERVER_ADMIN,SERVER_NAME,SERVER_PORT,SERVER_SIGNATURE,SERVER_SOFTWARE,GATEWAY_INTERFACE,SERVER_PROTOCOL,REQUEST_METHOD,PATH_TRANSLATED');
        foreach ($gE_keys as $k) {
            $content .= Utility::writeInformation($k, getenv($k));
        }
        $view->assign('getIndpEnv', $content);

        // PHP
        $content = Utility::writeInformation(Utility::getLl('status_version'), phpversion());
        $content .= Utility::writeInformation('memory_limit', ini_get('memory_limit'));
        $content .= Utility::writeInformation('max_execution_time', ini_get('max_execution_time'));
        $content .= Utility::writeInformation('post_max_size', ini_get('post_max_size'));
        $content .= Utility::writeInformation('upload_max_filesize', ini_get('upload_max_filesize'));
        $content .= Utility::writeInformation('display_errors', ini_get('display_errors'));
        $content .= Utility::writeInformation('error_reporting', ini_get('error_reporting'));
        if (function_exists('posix_getpwuid') && function_exists('posix_getgrgid')) {
            $apacheUser = posix_getpwuid(posix_getuid());
            $apacheGroup = posix_getgrgid(posix_getgid());
            $content .= Utility::writeInformation(
                'Apache user',
                $apacheUser['name'] . ' (' . $apacheUser['uid'] . ')'
            );
            $content .= Utility::writeInformation(
                'Apache group',
                $apacheGroup['name'] . ' (' . $apacheGroup['gid'] . ')'
            );
        }
        $extensions = array_map('strtolower', get_loaded_extensions());
        natcasesort($extensions);
        $content .= Utility::writeInformationList(
            Utility::getLl('status_loadedextensions'),
            $extensions
        );

        $view->assign('php', $content);

        // Apache
        if (function_exists('apache_get_version') && function_exists('apache_get_modules')) {
            $extensions = apache_get_modules();
            natcasesort($extensions);
            $content = Utility::writeInformation(
                Utility::getLl('status_version'),
                apache_get_version()
            );
            $content .= Utility::writeInformationList(
                Utility::getLl('status_loadedextensions'),
                $extensions
            );
            $view->assign('apache', $content);
        } else {
            $view->assign('apache', Utility::getLl('noresults'));
        }

        $connection = Utility::getDatabaseConnection();
        $connectionParams = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections'][ConnectionPool::DEFAULT_CONNECTION_NAME];

        // MySQL
        $content = Utility::writeInformation('Version', $connection->getServerVersion());

        $items = Utility::getQueryBuilder()
            ->select('default_character_set_name', 'default_collation_name')
            ->from('information_schema.schemata')
            ->where("schema_name = '" . $connectionParams['dbname'] . "'")
            ->execute()
            ->fetchAll();

        $content .= Utility::writeInformation(
            'default_character_set_name',
            $items[0]['default_character_set_name']
        );
        $content .= Utility::writeInformation('default_collation_name', $items[0]['default_collation_name']);
        $content .= Utility::writeInformation('query_cache', Utility::getMySqlCacheInformations());
        $content .= Utility::writeInformation('character_set', Utility::getMySqlCharacterSet());

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
                'name'      => $itemValue['table_name'],
                'engine'    => $itemValue['engine'],
                'collation' => $itemValue['table_collation'],
                'rows'      => $itemValue['table_rows'],
                'size'      => round($itemValue['size'], 2),
            ];
            $size += round($itemValue['size'], 2);
        }

        $view->assign('mysql', $content);
        $view->assign('tables', $tables);
        $view->assign('tablessize', round($size, 2));
        $view->assign('typo3db', $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['dbname']);

        // Crontab
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
        $content = Utility::writeInformation('Crontab', $crontabString);
        $view->assign('crontab', $content);

        return $view->render();
    }
}
