<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Service;

final class StructuredDataNormalizer
{
    /**
     * @return list<array{key: string, value: string, children: array<mixed>}>
     */
    public function normalize(mixed $data): array
    {
        if (! is_array($data)) {
            return [['key' => '', 'value' => $this->normalizeValue($data), 'children' => []]];
        }

        $rows = [];
        foreach ($data as $key => $value) {
            $rows[] = [
                'key' => (string) $key,
                'value' => is_array($value) ? '' : $this->normalizeValue($value),
                'children' => is_array($value) ? $this->normalize($value) : [],
            ];
        }

        return $rows;
    }

    private function normalizeValue(mixed $value): string
    {
        if ($value instanceof \Stringable) {
            return $value::class . ': ' . $value;
        }

        if (is_object($value)) {
            return $value::class;
        }

        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }
}
