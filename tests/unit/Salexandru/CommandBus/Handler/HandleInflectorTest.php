<?php

namespace Salexandru\CommandBus\Handler;

use \Mockery as m;
use Salexandru\Command\CommandInterface as Command;

class HandleInflectorTest extends \PHPUnit_Framework_TestCase
{

    public function testInflect()
    {
        $cmd = m::mock(Command::class);

        $inflector = new HandleInflector();
        $this->assertEquals('handle', $inflector->inflect($cmd, null));
    }
}
