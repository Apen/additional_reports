<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

final class HookResolver
{
    public function isHook(mixed $hook): bool
    {
        if (is_array($hook)) {
            $hook = $hook[1] ?? '';
        }

        if (! is_string($hook) || $hook === '') {
            return false;
        }

        $hook = ltrim($hook, '&');
        if (class_exists($hook)) {
            return true;
        }

        if (str_contains($hook, '.php')) {
            $file = GeneralUtility::getFileAbsFileName(explode('.php', $hook, 2)[0] . '.php');
            if (is_file($file)) {
                return true;
            }
        }

        if (! str_contains($hook, '->')) {
            return false;
        }

        [$className] = explode('->', $hook, 2);
        return class_exists($className);
    }

    public function resolve(mixed $candidate): mixed
    {
        if (! is_array($candidate)) {
            return $this->isHook($candidate) ? $candidate : null;
        }

        foreach ($candidate as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $nestedKey => $nestedValue) {
                    if (is_array($nestedValue) || ! $this->isHook($nestedValue)) {
                        unset($value[$nestedKey]);
                    }
                }
            } elseif (! $this->isHook($value)) {
                $value = null;
            }

            if (empty($value)) {
                unset($candidate[$key]);
            } else {
                $candidate[$key] = $value;
            }
        }

        return $candidate;
    }
}
