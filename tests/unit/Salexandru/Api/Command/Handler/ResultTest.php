<?php

namespace Salexandru\Api\Server\Exception\Handler;

use Salexandru\Command\Handler\Result;

class ResultTest extends \PHPUnit_Framework_TestCase
{

    public function testCreateSuccessResult()
    {
        $result = Result::success();
        $this->assertInstanceOf(Result::class, $result, sprintf('Should be an instanceof %s', Result::class));
        $this->assertNull($result->getPayload(), 'Payload should be null');
        $this->assertTrue($result->isSuccess(), sprintf('Result should be of type %s', Result::RESULT_OK));

        $result = Result::success($expected = 'test');
        $this->assertTrue($result->isSuccess(), sprintf('Result should be of type %s', Result::RESULT_OK));
        $this->assertEquals($expected, $actual = $result->getPayload(), "Payload should be '$expected'; got '$actual'");
    }

    public function testCreateErrorResult()
    {
        $result = Result::error($code = 0, $message = 'test');
        $this->assertInstanceOf(Result::class, $result, sprintf('Should be an instance of %s', Result::class));
        $this->assertTrue($result->isError(), sprintf('Result should be of type %s', Result::RESULT_ERROR));
        $this->assertNull($result->getPayload(), 'Payload should be null');
        $this->assertEquals($code, $actual = $result->getErrorCode(), "Error code should be $code; got $actual");
        $this->assertEquals(
            [$message],
            $result->getErrorMessages(),
            "Error messages should be an array with one element with the value $message"
        );
        $this->assertEquals($message, $result->getFirstErrorMessage(), "First error message should be $message");
        $this->assertEquals($message, $result->getLastErrorMessage(), "Last error message should be $message");
    }

    public function testErrorResultWithArrayOfMessages()
    {
        $result = Result::error($code = 0, $messages = ['first', 'last']);
        $this->assertEquals($messages, $result->getErrorMessages());
        $this->assertEquals($messages[0], $result->getFirstErrorMessage(), "First message should be {$messages[0]}");
        $this->assertEquals($messages[1], $result->getLastErrorMessage(), "Last message should be {$messages[1]}");
    }

    public function testErrorResultWithNoMessages()
    {
        $result = Result::error($code = 0);
        $this->assertEquals([], $result->getErrorMessages());
        $this->assertNull($result->getFirstErrorMessage(), "First message should be NULL");
        $this->assertNull($result->getLastErrorMessage(), "Last message should be NULL");
    }

    public function testCreateGenericErrorResult()
    {
        $result = Result::genericError();
        $this->assertInstanceOf(Result::class, $result, sprintf('Should be an instanceof %s', Result::class));
        $this->assertTrue($result->isError(), sprintf('Result should be of type %s', Result::RESULT_ERROR));
        $this->assertTrue($result->isGenericError());
        $this->assertEquals(
            $expected = Result::ERROR_GENERIC,
            $actual = $result->getErrorCode(),
            "Error code should be $expected; got $actual"
        );
    }

    public function testCreateInvalidUserCredentialsErrorResult()
    {
        $result = Result::invalidUserCredentialsError();
        $this->assertInstanceOf(Result::class, $result, sprintf('Should be an instanceof %s', Result::class));
        $this->assertTrue($result->isError(), sprintf('Result should be of type %s', Result::RESULT_ERROR));
        $this->assertTrue($result->isInvalidUserCredentialsError());
        $this->assertEquals(
            $expected = Result::ERROR_INVALID_USER_CREDENTIALS,
            $actual = $result->getErrorCode(),
            "Error code should be $expected; got $actual"
        );
    }

    public function testCreateAccessTokenGenerationErrorResult()
    {
        $result = Result::accessTokenGenerationError();
        $this->assertInstanceOf(Result::class, $result, sprintf('Should be an instanceof %s', Result::class));
        $this->assertTrue($result->isError(), sprintf('Result should be of type %s', Result::RESULT_ERROR));
        $this->assertTrue($result->isAccessTokenGenerationError());
        $this->assertEquals(
            $expected = Result::ERROR_ACCESS_TOKEN_GENERATION,
            $actual = $result->getErrorCode(),
            "Error code should be $expected; got $actual"
        );
    }

    public function testCreateInvalidAccessTokenErrorResult()
    {
        $result = Result::invalidAccessTokenError();
        $this->assertInstanceOf(Result::class, $result, sprintf('Should be an instanceof %s', Result::class));
        $this->assertTrue($result->isError(), sprintf('Result should be of type %s', Result::RESULT_ERROR));
        $this->assertTrue($result->isInvalidAccessTokenError());
        $this->assertEquals(
            $expected = Result::ERROR_INVALID_ACCESS_TOKEN,
            $actual = $result->getErrorCode(),
            "Error code should be $expected; got $actual"
        );
    }
}
