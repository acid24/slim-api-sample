<?php

namespace Salexandru\EventBus;

class EventTest extends \PHPUnit_Framework_TestCase
{

    public function testRetrieveEventName()
    {
        $e = new Event($name = 'test');
        $this->assertEquals($name, $e->getName());
    }

    public function testPropagation()
    {
        $e = new Event('test');
        $this->assertFalse($e->isPropagationStopped());

        $e->stopPropagation();
        $this->assertTrue($e->isPropagationStopped());
    }
}
