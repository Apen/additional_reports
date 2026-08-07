<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Service;

use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

final class UnifiedDiffRenderer
{
    public function render(string $localContent, string $remoteContent): string
    {
        $differ = new Differ(new UnifiedDiffOutputBuilder('', true));
        return $this->renderUnifiedDiff($differ->diff($localContent, $remoteContent));
    }

    private function renderUnifiedDiff(string $diff): string
    {
        $lines = preg_split('/(?<=\n)/', $diff, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $output = '<pre style="overflow:auto; padding:8px;">';
        foreach ($lines as $line) {
            $escapedLine = htmlspecialchars($line, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $style = match ($line[0] ?? '') {
                '-' => 'background-color:#FDD;',
                '+' => 'background-color:#DFD;',
                '@' => 'color:#555; font-weight:bold;',
                default => '',
            };
            $output .= '<span style="display:block;' . $style . '">' . $escapedLine . '</span>';
        }
        return $output . '</pre>';
    }
}
