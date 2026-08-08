<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sng\AdditionalReports\Reports\AbstractReport;
use Sng\AdditionalReports\Reports\CommandControllers;
use Sng\AdditionalReports\Reports\Eid;
use Sng\AdditionalReports\Reports\EventDispatcher;
use Sng\AdditionalReports\Reports\Extensions;
use Sng\AdditionalReports\Reports\Hooks;
use Sng\AdditionalReports\Reports\LogErrors;
use Sng\AdditionalReports\Reports\Middlewares;
use Sng\AdditionalReports\Reports\Plugins;
use Sng\AdditionalReports\Reports\Status;
use Sng\AdditionalReports\Reports\WebsiteConf;
use Sng\AdditionalReports\Reports\Xclass;
use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[AsController]
final readonly class ReportController
{
    public function __construct(
        private ModuleTemplateFactory $moduleTemplateFactory,
    ) {}

    public function eid(ServerRequestInterface $serverRequest): ResponseInterface
    {
        return $this->render($serverRequest, Eid::class, 'eid');
    }

    public function commandControllers(ServerRequestInterface $serverRequest): ResponseInterface
    {
        return $this->render($serverRequest, CommandControllers::class, 'commandcontrollers');
    }

    public function plugins(ServerRequestInterface $serverRequest): ResponseInterface
    {
        return $this->render($serverRequest, Plugins::class, 'plugins');
    }

    public function xclass(ServerRequestInterface $serverRequest): ResponseInterface
    {
        return $this->render($serverRequest, Xclass::class, 'xclass');
    }

    public function hooks(ServerRequestInterface $serverRequest): ResponseInterface
    {
        return $this->render($serverRequest, Hooks::class, 'hooks');
    }

    public function status(ServerRequestInterface $serverRequest): ResponseInterface
    {
        return $this->render($serverRequest, Status::class, 'status');
    }

    public function logErrors(ServerRequestInterface $serverRequest): ResponseInterface
    {
        return $this->render($serverRequest, LogErrors::class, 'logerrors');
    }

    public function websiteConf(ServerRequestInterface $serverRequest): ResponseInterface
    {
        return $this->render($serverRequest, WebsiteConf::class, 'websitesconf');
    }

    public function extensions(ServerRequestInterface $serverRequest): ResponseInterface
    {
        return $this->render($serverRequest, Extensions::class, 'extensions');
    }

    public function eventDispatcher(ServerRequestInterface $serverRequest): ResponseInterface
    {
        return $this->render($serverRequest, EventDispatcher::class, 'eventdispatcher');
    }

    public function middlewares(ServerRequestInterface $serverRequest): ResponseInterface
    {
        return $this->render($serverRequest, Middlewares::class, 'middlewares');
    }

    /**
     * @param class-string<AbstractReport> $reportClass
     */
    private function render(ServerRequestInterface $serverRequest, string $reportClass, string $identifier): ResponseInterface
    {
        $queryParams = $serverRequest->getQueryParams();
        $queryParams['report'] = $identifier;
        $serverRequest = $serverRequest->withQueryParams($queryParams);
        $report = GeneralUtility::makeInstance($reportClass);
        $report->setRequest($serverRequest);

        $moduleTemplate = $this->moduleTemplateFactory->create($serverRequest);
        $moduleTemplate->setTitle(Utility::getLanguageService()->sL($report->getTitle()));
        $moduleTemplate->makeDocHeaderModuleMenu();
        $moduleTemplate->assign('reportContent', $report->getReport());

        return $moduleTemplate->renderResponse('Backend/Report');
    }
}
