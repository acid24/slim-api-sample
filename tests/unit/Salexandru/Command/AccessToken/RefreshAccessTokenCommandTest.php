<?php

namespace Salexandru\Command\AccessToken;

use Salexandru\Command\Exception\InvalidArgumentException;

class RefreshAccessTokenCommandTest extends \PHPUnit_Framework_TestCase
{

    public function testNonStringAccessTokenThrowsException()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $cmd = new RefreshAccessTokenCommand(1);
    }

    public function testEmptyAccessTokenThrowsException()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $cmd = new RefreshAccessTokenCommand('');
    }

    public function testGetAccessToken()
    {
        $cmd = new RefreshAccessTokenCommand($t = 'a.dummy.token');
        $this->assertEquals($t, $cmd->getCurrentAccessToken(), "Expected $t");
    }
}
