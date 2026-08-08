<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Sng\AdditionalReports\Service\EventListenerRegistry;
use TYPO3\CMS\Core\EventDispatcher\ListenerProvider;

final class EventListenerRegistryTest extends TestCase
{
    public function testListenerDefinitionsAreNormalizedAndSorted(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $listenerProvider = new ListenerProvider($container);
        $listenerProvider->addListener('Vendor\\Event\\SecondEvent', 'listener.second', null, 'second');
        $listenerProvider->addListener('Vendor\\Event\\FirstEvent', 'listener.first', 'handle', 'first');

        self::assertSame([
            [
                'event' => 'Vendor\\Event\\FirstEvent',
                'eventNamespace' => 'Vendor\\Event\\',
                'eventName' => 'FirstEvent',
                'identifier' => 'first',
                'service' => 'listener.first',
                'serviceNamespace' => '',
                'serviceName' => 'listener.first',
                'method' => 'handle',
            ],
            [
                'event' => 'Vendor\\Event\\SecondEvent',
                'eventNamespace' => 'Vendor\\Event\\',
                'eventName' => 'SecondEvent',
                'identifier' => 'second',
                'service' => 'listener.second',
                'serviceNamespace' => '',
                'serviceName' => 'listener.second',
                'method' => '__invoke',
            ],
        ], (new EventListenerRegistry($listenerProvider))->findAll());
    }
}
