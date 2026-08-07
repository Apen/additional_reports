<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Service;

use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\SystemResource\Publishing\SystemResourcePublisherInterface;
use TYPO3\CMS\Core\SystemResource\Publishing\UriGenerationOptions;
use TYPO3\CMS\Core\SystemResource\SystemResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

final class ContentTypeResolver
{
    public function __construct(private readonly ?Typo3Version $typo3Version = null) {}

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
        $field = $type === 'plugin' && $this->hasLegacyListType() ? 'list_type' : 'CType';
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

    public function hasLegacyListType(): bool
    {
        $typo3Version = $this->typo3Version ?? new Typo3Version();
        return $typo3Version->getMajorVersion() < 14
            && isset($GLOBALS['TCA']['tt_content']['columns']['list_type']);
    }

    /** @return list<string> */
    public function getPluginContentTypes(): array
    {
        $contentTypeGroups = ['default', 'lists', 'menu', 'forms', 'special'];
        $pluginContentTypes = [];
        foreach (($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] ?? []) as $item) {
            $value = $item['value'] ?? $item[1] ?? null;
            $group = $item['group'] ?? $item[3] ?? 'default';
            if (is_string($value) && $value !== '' && ! in_array($group, $contentTypeGroups, true)) {
                $pluginContentTypes[] = $value;
            }
        }
        return array_values(array_unique($pluginContentTypes));
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
            return $this->publishExtensionResource($icon);
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
            ? $this->publishExtensionResource($source)
            : PathUtility::getAbsoluteWebPath($source);
    }

    private function publishExtensionResource(string $identifier): string
    {
        $resource = GeneralUtility::makeInstance(SystemResourceFactory::class)->createPublicResource($identifier);
        return (string) GeneralUtility::makeInstance(SystemResourcePublisherInterface::class)->generateUri(
            $resource,
            null,
            new UriGenerationOptions(absoluteUri: false),
        );
    }
}
