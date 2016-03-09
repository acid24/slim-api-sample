<?php

namespace Salexandru\EventBus;

use Salexandru\EventBus\Handler\Registry as HandlerRegistry;

class EventBus implements EventBusInterface
{

    private $registry;
    private $storage = [];

    public function __construct(HandlerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function collect(Event $e)
    {
        $this->storage[] = $e;
    }

    public function releaseEvents()
    {
        /** @var Event $e */
        foreach ($this->storage as $e) {
            $this->callHandlersFor($e);
        }
    }

    private function callHandlersFor(Event $e)
    {
        $handlers = $this->registry->getHandlersFor($e);
        foreach ($handlers as $handler) {
            if ($e->isPropagationStopped()) {
                break;
            }

            if (is_callable($handler)) {
                call_user_func($handler, $e);
            }
        }
    }
}
