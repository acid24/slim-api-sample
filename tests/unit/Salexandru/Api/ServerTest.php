<?php

namespace Salexandru\Api;

use Salexandru\Api\Server\Bootstrapper;

class ServerTest extends \PHPUnit_Framework_TestCase
{

    public function testBootstrapReturnsCorrectInstance()
    {
        $server = new Server();
        $bootstrap = $server->bootstrap();

        $this->assertInstanceOf(Bootstrapper::class, $bootstrap, sprintf('Should be an instance of %s', Bootstrapper::class));
    }
}
