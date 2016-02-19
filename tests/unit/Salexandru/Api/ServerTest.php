<?php

namespace Salexandru\Api;

use Salexandru\Api\Server\Bootstrap;

class ServerTest extends \PHPUnit_Framework_TestCase
{

    public function testBootstrapReturnsCorrectInstance()
    {
        $server = new Server();
        $bootstrap = $server->bootstrap();

        $this->assertInstanceOf(Bootstrap::class, $bootstrap, sprintf('Should be an instance of %s', Bootstrap::class));
    }
}
