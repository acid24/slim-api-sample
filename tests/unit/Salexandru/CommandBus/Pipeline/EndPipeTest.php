<?php

namespace Salexandru\CommandBus\Pipeline;

use Mockery as m;
use Salexandru\Command\CommandInterface as Command;

class EndPipeTest extends \PHPUnit_Framework_TestCase
{

    public function testEndPipeReturnsNull()
    {
        $cmd = m::mock(Command::class);

        $p = new EndPipe();
        $result = $p->receive($cmd);

        $this->assertNull($result, 'Result should be null');
    }
}
