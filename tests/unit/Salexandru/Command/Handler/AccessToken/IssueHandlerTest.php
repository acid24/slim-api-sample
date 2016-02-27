<?php

namespace Salexandru\Command\Handler\AccessToken;

use Mockery as m;
use Salexandru\Command\AccessToken\IssueCommand;
use Salexandru\Jwt\AdapterInterface as JwtAdapter;

class IssueHandlerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var m\MockInterface;
     */
    private $jwtAdapter;

    protected function setUp()
    {
        $this->jwtAdapter = m::mock(JwtAdapter::class);
    }

    public function testTokenGenerationErrorReturnsErrorResult()
    {
        $this->jwtAdapter
            ->shouldReceive('generateToken')
            ->once()
            ->andThrow('Exception');

        $cmd = new IssueCommand('test', 'test');

        $handler = new IssueHandler($this->jwtAdapter);
        $result = $handler->handle($cmd);

        $this->assertTrue($result->isAccessTokenGenerationError());
    }

    public function testIssueAccessToken()
    {
        $this->jwtAdapter
            ->shouldReceive('generateToken')
            ->once()
            ->andReturn($token = 'a.b.c');
        $this->jwtAdapter
            ->shouldReceive('getTokenClaims')
            ->with($token, true)
            ->andReturn(['exp' => $ts = (new \DateTime())->getTimestamp()]);

        $cmd = new IssueCommand('test', 'test');

        $handler = new IssueHandler($this->jwtAdapter);
        $result = $handler->handle($cmd);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(['token' => $token, 'expiresAt' => $ts], $result->getPayload());
    }
}
