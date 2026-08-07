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

    public function eid(ServerRequestInterface $request): ResponseInterface
    {
        return $this->render($request, Eid::class, 'eid');
    }

    public function commandControllers(ServerRequestInterface $request): ResponseInterface
    {
        return $this->render($request, CommandControllers::class, 'commandcontrollers');
    }

    public function plugins(ServerRequestInterface $request): ResponseInterface
    {
        return $this->render($request, Plugins::class, 'plugins');
    }

    public function xclass(ServerRequestInterface $request): ResponseInterface
    {
        return $this->render($request, Xclass::class, 'xclass');
    }

    public function hooks(ServerRequestInterface $request): ResponseInterface
    {
        return $this->render($request, Hooks::class, 'hooks');
    }

    public function status(ServerRequestInterface $request): ResponseInterface
    {
        return $this->render($request, Status::class, 'status');
    }

    public function logErrors(ServerRequestInterface $request): ResponseInterface
    {
        return $this->render($request, LogErrors::class, 'logerrors');
    }

    public function websiteConf(ServerRequestInterface $request): ResponseInterface
    {
        return $this->render($request, WebsiteConf::class, 'websitesconf');
    }

    public function extensions(ServerRequestInterface $request): ResponseInterface
    {
        return $this->render($request, Extensions::class, 'extensions');
    }

    public function eventDispatcher(ServerRequestInterface $request): ResponseInterface
    {
        return $this->render($request, EventDispatcher::class, 'eventdispatcher');
    }

    public function middlewares(ServerRequestInterface $request): ResponseInterface
    {
        return $this->render($request, Middlewares::class, 'middlewares');
    }

    /**
     * @param class-string<AbstractReport> $reportClass
     */
    private function render(ServerRequestInterface $request, string $reportClass, string $identifier): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $queryParams['report'] = $identifier;
        $request = $request->withQueryParams($queryParams);
        $report = GeneralUtility::makeInstance($reportClass);
        $report->setRequest($request);
        $moduleTemplate = $this->moduleTemplateFactory->create($request);
        $moduleTemplate->setTitle(Utility::getLanguageService()->sL($report->getTitle()));
        $moduleTemplate->makeDocHeaderModuleMenu();
        $moduleTemplate->assign('reportContent', $report->getReport());

        return $moduleTemplate->renderResponse('Backend/Report');
    }
}
