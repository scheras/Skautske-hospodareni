<?php

declare(strict_types=1);

namespace Model\Event\Handlers\Event;

use eGen\MessageBus\Bus\EventBus;
use Model\Event\Commands\Event\OpenEvent;
use Model\Event\Repositories\IEventRepository;
use Model\Events\Events\EventWasOpened;

final class OpenEventHandler
{
    private IEventRepository $events;

    private EventBus $eventBus;

    public function __construct(IEventRepository $events, EventBus $eventBus)
    {
        $this->events   = $events;
        $this->eventBus = $eventBus;
    }

    public function __invoke(OpenEvent $command) : void
    {
        $event = $this->events->find($command->getEventId());

        $this->events->open($event);

        $this->eventBus->handle(
            new EventWasOpened(
                $event->getId(),
                $event->getUnitId()->toInt(),
                $event->getDisplayName()
            )
        );
    }
}
