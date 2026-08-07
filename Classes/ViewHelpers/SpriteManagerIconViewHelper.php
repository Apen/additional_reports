<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\ViewHelpers;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Displays sprite icon identified by iconName key
 */
class SpriteManagerIconViewHelper extends AbstractViewHelper
{
    /**
     * Plain HTML should be returned, no output escaping allowed
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('iconName', 'string', 'as', true);
        $this->registerArgument('size', 'string', 'size', false, 'small');
    }

    /**
     * Prints sprite icon html for $iconName key
     *
     * @return string
     */
    public function render(): mixed
    {
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $size = IconSize::tryFrom($this->arguments['size']) ?? IconSize::SMALL;
        return (string) $iconFactory->getIcon($this->arguments['iconName'], $size);
    }
}
