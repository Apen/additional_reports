<?php

declare(strict_types=1);

use a9f\Fractor\Configuration\FractorConfiguration;
use a9f\Typo3Fractor\Set\Typo3LevelSetList;

return FractorConfiguration::configure()
    ->withPaths([
        dirname(__DIR__) . '/Configuration',
        dirname(__DIR__) . '/Resources/Private',
    ])
    ->withSkip(['*Build/*'])
    ->withSets([Typo3LevelSetList::UP_TO_TYPO3_13]);
