<?php

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

class Status extends \Sng\AdditionalReports\Reports\AbstractReport implements \TYPO3\CMS\Reports\ReportInterface
{

    /**
     * This method renders the report
     *
     * @return    string    The status report as HTML
     */
    public function getReport()
    {
        $content = '<p class="help">' . $GLOBALS['LANG']->getLL('status_description') . '</p>';

        if (!isset($this->reportObject->doc)) {
            $this->reportObject->doc = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate');
        }

        $content .= $this->display();
        return $content;
    }

    /**
     * Generate the global status report
     *
     * @return string HTML code
     */
    public function display()
    {
        $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/status-fluid.html');

        // infos about typo3 versions
        $jsonVersions = \Sng\AdditionalReports\Utility::getJsonVersionInfos();
        $currentVersionInfos = \Sng\AdditionalReports\Utility::getCurrentVersionInfos($jsonVersions, TYPO3_version);
        $currentBranch = \Sng\AdditionalReports\Utility::getCurrentBranchInfos($jsonVersions, TYPO3_version);
        $latestStable = \Sng\AdditionalReports\Utility::getLatestStableInfos($jsonVersions);
        $latestLts = \Sng\AdditionalReports\Utility::getLatestLtsInfos($jsonVersions);
        $headerVersions = \Sng\AdditionalReports\Utility::getLl('status_version') . '<br/>';
        $headerVersions .= \Sng\AdditionalReports\Utility::getLl('latestbranch') . '<br/>';
        $headerVersions .= \Sng\AdditionalReports\Utility::getLl('lateststable') . '<br/>';
        $headerVersions .= \Sng\AdditionalReports\Utility::getLl('latestlts');
        $htmlVersions = TYPO3_version . ' [' . $currentVersionInfos['date'] . ']';
        $htmlVersions .= '<br/>' . $currentBranch['version'] . ' [' . $currentBranch['date'] . ']';
        $htmlVersions .= '<br/>' . $latestStable['version'] . ' [' . $latestStable['date'] . ']';
        $htmlVersions .= '<br/>' . $latestLts['version'] . ' [' . $latestLts['date'] . ']';

        // TYPO3
        $content = \Sng\AdditionalReports\Utility::writeInformation(\Sng\AdditionalReports\Utility::getLl('status_sitename'), $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']);
        $content .= \Sng\AdditionalReports\Utility::writeInformation($headerVersions, $htmlVersions);
        $content .= \Sng\AdditionalReports\Utility::writeInformation(\Sng\AdditionalReports\Utility::getLl('status_path'), PATH_site);
        $content .= \Sng\AdditionalReports\Utility::writeInformation(
            'dbname<br/>user<br/>host',
            $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['dbname'] . '<br/>'
            . $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['user'] . '<br/>'
            . $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['host']
        );
        if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path'] != '') {
            $cmd = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', '-version');
            exec($cmd, $ret);
            $content .= \Sng\AdditionalReports\Utility::writeInformation(
                \Sng\AdditionalReports\Utility::getLl('status_im'),
                $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path'] . ' (' . $ret[0] . ')'
            );
        }
        $content .= \Sng\AdditionalReports\Utility::writeInformation('forceCharset', $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset']);
        $content .= \Sng\AdditionalReports\Utility::writeInformation('DB/Connections/Default/initCommands', $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['initCommands']);
        $content .= \Sng\AdditionalReports\Utility::writeInformation('no_pconnect', $GLOBALS['TYPO3_CONF_VARS']['SYS']['no_pconnect']);
        $content .= \Sng\AdditionalReports\Utility::writeInformation('displayErrors', $GLOBALS['TYPO3_CONF_VARS']['SYS']['displayErrors']);
        $content .= \Sng\AdditionalReports\Utility::writeInformation('maxFileSize', $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize']);

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
            $extensions[$aKey] = $extension . ' (' . \Sng\AdditionalReports\Utility::getExtensionVersion($extension) . ')';
        }
        $content .= \Sng\AdditionalReports\Utility::writeInformationList(
            \Sng\AdditionalReports\Utility::getLl('status_loadedextensions'),
            $extensions
        );

        $view->assign('typo3', $content);

        // Debug
        $content = '';
        $vars = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('_ARRAY');
        foreach ($vars as $varKey => $varValue) {
            $content .= \Sng\AdditionalReports\Utility::writeInformation($varKey, $varValue);
        }
        $gE_keys = explode(',', 'HTTP_ACCEPT,HTTP_ACCEPT_ENCODING,HTTP_CONNECTION,HTTP_COOKIE,REMOTE_PORT,SERVER_ADDR,SERVER_ADMIN,SERVER_NAME,SERVER_PORT,SERVER_SIGNATURE,SERVER_SOFTWARE,GATEWAY_INTERFACE,SERVER_PROTOCOL,REQUEST_METHOD,PATH_TRANSLATED');
        foreach ($gE_keys as $k) {
            $content .= \Sng\AdditionalReports\Utility::writeInformation($k, getenv($k));
        }
        $view->assign('getIndpEnv', $content);

        // PHP
        $content = \Sng\AdditionalReports\Utility::writeInformation(\Sng\AdditionalReports\Utility::getLl('status_version'), phpversion());
        $content .= \Sng\AdditionalReports\Utility::writeInformation('memory_limit', ini_get('memory_limit'));
        $content .= \Sng\AdditionalReports\Utility::writeInformation('max_execution_time', ini_get('max_execution_time'));
        $content .= \Sng\AdditionalReports\Utility::writeInformation('post_max_size', ini_get('post_max_size'));
        $content .= \Sng\AdditionalReports\Utility::writeInformation('upload_max_filesize', ini_get('upload_max_filesize'));
        $content .= \Sng\AdditionalReports\Utility::writeInformation('display_errors', ini_get('display_errors'));
        $content .= \Sng\AdditionalReports\Utility::writeInformation('error_reporting', ini_get('error_reporting'));
        if (function_exists('posix_getpwuid') && function_exists('posix_getgrgid')) {
            $apacheUser = posix_getpwuid(posix_getuid());
            $apacheGroup = posix_getgrgid(posix_getgid());
            $content .= \Sng\AdditionalReports\Utility::writeInformation(
                'Apache user',
                $apacheUser['name'] . ' (' . $apacheUser['uid'] . ')'
            );
            $content .= \Sng\AdditionalReports\Utility::writeInformation(
                'Apache group',
                $apacheGroup['name'] . ' (' . $apacheGroup['gid'] . ')'
            );
        }
        $extensions = array_map('strtolower', get_loaded_extensions());
        natcasesort($extensions);
        $content .= \Sng\AdditionalReports\Utility::writeInformationList(
            \Sng\AdditionalReports\Utility::getLl('status_loadedextensions'),
            $extensions
        );

        $view->assign('php', $content);

        // Apache
        if (function_exists('apache_get_version') && function_exists('apache_get_modules')) {
            $extensions = apache_get_modules();
            natcasesort($extensions);
            $content = \Sng\AdditionalReports\Utility::writeInformation(
                \Sng\AdditionalReports\Utility::getLl('status_version'),
                apache_get_version()
            );
            $content .= \Sng\AdditionalReports\Utility::writeInformationList(
                \Sng\AdditionalReports\Utility::getLl('status_loadedextensions'),
                $extensions
            );
            $view->assign('apache', $content);
        } else {
            $view->assign('apache', \Sng\AdditionalReports\Utility::getLl('noresults'));
        }

        $connection = \Sng\AdditionalReports\Utility::getDatabaseConnection();
        $connectionParams = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections'][\TYPO3\CMS\Core\Database\ConnectionPool::DEFAULT_CONNECTION_NAME];

        // MySQL
        $content = \Sng\AdditionalReports\Utility::writeInformation('Version', $connection->getServerVersion());

        $items = \Sng\AdditionalReports\Utility::getQueryBuilder()
            ->select('default_character_set_name', 'default_collation_name')
            ->from('information_schema.schemata')
            ->where('schema_name = \'' . $connectionParams['dbname'] . '\'')
            ->execute()
            ->fetchAll();

        $content .= \Sng\AdditionalReports\Utility::writeInformation(
            'default_character_set_name',
            $items[0]['default_character_set_name']
        );
        $content .= \Sng\AdditionalReports\Utility::writeInformation('default_collation_name', $items[0]['default_collation_name']);
        $content .= \Sng\AdditionalReports\Utility::writeInformation('query_cache', \Sng\AdditionalReports\Utility::getMySqlCacheInformations());
        $content .= \Sng\AdditionalReports\Utility::writeInformation('character_set', \Sng\AdditionalReports\Utility::getMySqlCharacterSet());

        // TYPO3 database
        $items = \Sng\AdditionalReports\Utility::getQueryBuilder()
            ->select('table_name', 'engine', 'table_collation', 'table_rows')
            ->add('select', '((data_length+index_length)/1024/1024) as "size"', true)
            ->from('information_schema.tables')
            ->where('table_schema = \'' . $connectionParams['dbname'] . '\'')
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
        $crontabString = \Sng\AdditionalReports\Utility::getLl('status_nocrontab');
        if (count($crontab) > 0) {
            $crontabString = '';
            foreach ($crontab as $cron) {
                if (trim($cron) != '') {
                    $crontabString .= $cron . '<br />';
                }
            }
        }
        $content = \Sng\AdditionalReports\Utility::writeInformation('Crontab', $crontabString);
        $view->assign('crontab', $content);

        return $view->render();
    }
}
