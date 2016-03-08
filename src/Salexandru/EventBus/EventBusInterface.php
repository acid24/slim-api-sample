<?php

namespace Salexandru\EventBus;

interface EventBusInterface
{

    public function collect(Event $e);
    public function releaseEvents();
}
