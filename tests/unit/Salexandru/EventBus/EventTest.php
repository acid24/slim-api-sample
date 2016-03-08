<?php

namespace Salexandru\EventBus;

class EventTest extends \PHPUnit_Framework_TestCase
{

    public function testPropagation()
    {
        $e = new Event();
        $this->assertFalse($e->isPropagationStopped());

        $e->stopPropagation();
        $this->assertTrue($e->isPropagationStopped());
    }
}
