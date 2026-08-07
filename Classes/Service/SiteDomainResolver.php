<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Service;

use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class SiteDomainResolver
{
    public function __construct(private ?SiteFinder $siteFinder = null) {}

    public function resolve(int $pageId): string
    {
        try {
            $siteFinder = $this->siteFinder ?? GeneralUtility::makeInstance(SiteFinder::class);
            return $siteFinder->getSiteByPageId($pageId)->getBase()->getHost();
        } catch (SiteNotFoundException) {
            return '';
        }
    }
}
