<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Service;

use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

final class ContentTypeResolver
{
    /** @return array<string, string> */
    public function resolve(string $type, string $value): array
    {
        if ($value === '' || ! in_array($type, ['plugin', 'ctype'], true)) {
            return [];
        }

        $information = [
            $type => $value,
            'extension' => $this->resolveExtensionKey($value),
        ];
        $field = $type === 'plugin' && Utility::hasLegacyListType() ? 'list_type' : 'CType';
        foreach (($GLOBALS['TCA']['tt_content']['columns'][$field]['config']['items'] ?? []) as $item) {
            if (($item['value'] ?? '') !== $value) {
                continue;
            }
            if ($type === 'plugin' && is_string($item['label'] ?? null)) {
                $information['plugin'] = Utility::getLanguageService()->sL($item['label']) . ' (' . $value . ')';
            }
            $icon = $this->resolveIconPath($item['icon'] ?? null);
            if ($icon !== '') {
                $information['iconext'] = $icon;
            }
            break;
        }
        return $information;
    }

    private function resolveExtensionKey(string $value): string
    {
        $separatorPosition = strpos($value, '_');
        return $separatorPosition === false ? '' : substr($value, 0, $separatorPosition);
    }

    private function resolveIconPath(mixed $icon): string
    {
        if (! is_string($icon) || $icon === '') {
            return '';
        }
        if (PathUtility::isExtensionPath($icon)) {
            return PathUtility::getPublicResourceWebPath($icon);
        }

        $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
        if (! $iconRegistry->isRegistered($icon)) {
            return '';
        }
        $configuration = $iconRegistry->getIconConfigurationByIdentifier($icon);
        $source = $configuration['options']['source'] ?? null;
        if (! is_string($source) || $source === '') {
            return '';
        }
        return PathUtility::isExtensionPath($source)
            ? PathUtility::getPublicResourceWebPath($source)
            : PathUtility::getAbsoluteWebPath($source);
    }
}
