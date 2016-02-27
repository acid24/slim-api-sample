<?php

namespace Salexandru\Command\AccessToken;

use Salexandru\Command\Exception\InvalidArgumentException;

class IssueAccessTokenCommandTest extends \PHPUnit_Framework_TestCase
{

    public function testNonStringUsernameThrowsException()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $cmd = new IssueCommand(1, 'test');
    }

    public function testEmptyUsernameThrowsException()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $cmd = new IssueCommand('', 'test');
    }

    public function testNonStringPasswordThrowsException()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $cmd = new IssueCommand('test', null);
    }

    public function testEmptyPasswordThrowsException()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $cmd = new IssueCommand('test', '');
    }

    public function testGetUsernameAndPassword()
    {
        $cmd = new IssueCommand($u = 'test', $p = 'test');

        $this->assertEquals($u, $cmd->getUsername(), "Expected $u");
        $this->assertEquals($p, $cmd->getPassword(), "Expected $p");
    }
}
