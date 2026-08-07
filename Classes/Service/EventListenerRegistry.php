<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Service;

use TYPO3\CMS\Core\EventDispatcher\ListenerProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class EventListenerRegistry
{
    public function __construct(private ?ListenerProvider $listenerProvider = null) {}

    /** @return list<array{event: string, identifier: string, service: string, method: string}> */
    public function findAll(): array
    {
        $listenerProvider = $this->listenerProvider ?? GeneralUtility::makeInstance(ListenerProvider::class);
        $listeners = [];
        foreach ($listenerProvider->getAllListenerDefinitions() as $event => $definitions) {
            foreach ($definitions as $identifier => $definition) {
                $listeners[] = [
                    'event' => (string) $event,
                    'identifier' => (string) $identifier,
                    'service' => (string) ($definition['service'] ?? ''),
                    'method' => (string) ($definition['method'] ?? '__invoke'),
                ];
            }
        }
        usort($listeners, static fn(array $left, array $right): int => [$left['event'], $left['identifier']] <=> [$right['event'], $right['identifier']]);
        return $listeners;
    }
}
