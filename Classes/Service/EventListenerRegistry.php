<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Service;

use TYPO3\CMS\Core\EventDispatcher\ListenerProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class EventListenerRegistry
{
    public function __construct(private ?ListenerProvider $listenerProvider = null) {}

    /** @return list<array{event: string, eventNamespace: string, eventName: string, identifier: string, service: string, serviceNamespace: string, serviceName: string, method: string}> */
    public function findAll(): array
    {
        $listenerProvider = $this->listenerProvider ?? GeneralUtility::makeInstance(ListenerProvider::class);
        $listeners = [];
        foreach ($listenerProvider->getAllListenerDefinitions() as $event => $definitions) {
            foreach ($definitions as $identifier => $definition) {
                $eventName = $this->splitClassName((string) $event);
                $serviceName = $this->splitClassName((string) ($definition['service'] ?? ''));
                $listeners[] = [
                    'event' => (string) $event,
                    'eventNamespace' => $eventName['namespace'],
                    'eventName' => $eventName['name'],
                    'identifier' => (string) $identifier,
                    'service' => (string) ($definition['service'] ?? ''),
                    'serviceNamespace' => $serviceName['namespace'],
                    'serviceName' => $serviceName['name'],
                    'method' => (string) ($definition['method'] ?? '__invoke'),
                ];
            }
        }

        usort($listeners, static fn(array $left, array $right): int => [$left['event'], $left['identifier']] <=> [$right['event'], $right['identifier']]);
        return $listeners;
    }

    /** @return array{namespace: string, name: string} */
    private function splitClassName(string $className): array
    {
        $separatorPosition = strrpos($className, '\\');
        if ($separatorPosition === false) {
            return ['namespace' => '', 'name' => $className];
        }

        return [
            'namespace' => substr($className, 0, $separatorPosition + 1),
            'name' => substr($className, $separatorPosition + 1),
        ];
    }
}
