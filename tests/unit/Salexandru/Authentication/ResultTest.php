<?php

namespace Salexandru\Authentication;

use Salexandru\Authentication\Exception\InvalidArgumentException;

class ResultTest extends \PHPUnit_Framework_TestCase
{

    public function testSuccessResult()
    {
        $result = Result::success();

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isSuccess());
    }

    public function testGenericFailureResult()
    {
        $result = Result::genericFailure($error = 'Major failure');

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isFailure());
        $this->assertEquals($error, $result->getError());
    }

    public function testInvalidCredentialsFailureResult()
    {
        $result = Result::invalidCredentialsFailure($error = 'Wrong credentials');

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isFailure());
        $this->assertEquals($error, $result->getError());
    }

    public function testInvalidResultStatusThrowsException()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        Result::failure(-20, 'test');
    }
}
