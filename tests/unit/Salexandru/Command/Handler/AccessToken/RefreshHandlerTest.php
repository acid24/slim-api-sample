<?php

namespace Salexandru\Command\Handler\AccessToken;

use Mockery as m;
use Salexandru\Command\AccessToken\RefreshCommand;
use Salexandru\Jwt\AdapterInterface as JwtAdapter;
use Salexandru\Jwt\Exception\InvalidTokenException;

class RefreshHandlerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var m\MockInterface;
     */
    private $jwtAdapter;

    private $token = 'a.b.c';

    protected function setUp()
    {
        $this->jwtAdapter = m::mock(JwtAdapter::class);
    }

    public function testInvalidAccessTokenReturnsErrorResult()
    {
        $this->jwtAdapter
            ->shouldReceive('refreshToken')
            ->once()
            ->with($this->token)
            ->andThrow(InvalidTokenException::class);

        $cmd = new RefreshCommand($this->token);

        $handler = new RefreshHandler($this->jwtAdapter);
        $result = $handler->handle($cmd);

        $this->assertTrue($result->isInvalidAccessTokenError());
    }

    public function testAccessTokenGenerationErrorReturnsErrorResult()
    {
        $this->jwtAdapter
            ->shouldReceive('refreshToken')
            ->once()
            ->with($this->token)
            ->andThrow('Exception');

        $cmd = new RefreshCommand($this->token);

        $handler = new RefreshHandler($this->jwtAdapter);
        $result = $handler->handle($cmd);

        $this->assertTrue($result->isAccessTokenGenerationError());
    }

    public function testRefreshAccessToken()
    {
        $this->jwtAdapter
            ->shouldReceive('refreshToken')
            ->once()
            ->with($this->token)
            ->andReturn($token = 'x.y.z');
        $this->jwtAdapter
            ->shouldReceive('getTokenClaims')
            ->with($token, true)
            ->andReturn(['exp' => $ts = (new \DateTime())->getTimestamp()]);

        $cmd = new RefreshCommand($this->token);

        $handler = new RefreshHandler($this->jwtAdapter);
        $result = $handler->handle($cmd);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(['token' => $token, 'expiresAt' => $ts], $result->getPayload());
    }
}
