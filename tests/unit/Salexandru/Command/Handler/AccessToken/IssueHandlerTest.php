<?php

namespace Salexandru\Command\Handler\AccessToken;

use Mockery as m;
use Salexandru\Authentication\Result as AuthResult;
use Salexandru\Command\AccessToken\IssueCommand;
use Salexandru\Jwt\AdapterInterface as JwtAdapter;
use Salexandru\Authentication\AuthenticationManager as AuthManager;

class IssueHandlerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var m\MockInterface;
     */
    private $jwtAdapter;

    /**
     * @var m\MockInterface
     */
    private $authManager;

    protected function setUp()
    {
        $this->jwtAdapter = m::mock(JwtAdapter::class);
        $this->authManager = m::mock(AuthManager::class);
    }

    public function testInvalidUserCredentialsReturnsErrorResult()
    {
        $authResult = AuthResult::invalidCredentialsFailure();
        $this->authManager
            ->shouldReceive('authenticate')
            ->once()
            ->andReturn($authResult);

        $cmd = new IssueCommand('test', 'test');

        $handler = new IssueHandler($this->authManager, $this->jwtAdapter);
        $result = $handler($cmd);

        $this->assertTrue($result->isInvalidUserCredentialsError());
    }

    public function testTokenGenerationErrorReturnsErrorResult()
    {
        $authResult = AuthResult::success();
        $this->authManager
            ->shouldReceive('authenticate')
            ->once()
            ->andReturn($authResult);

        $this->jwtAdapter
            ->shouldReceive('generateToken')
            ->once()
            ->andThrow('Exception');

        $cmd = new IssueCommand('test', 'test');

        $handler = new IssueHandler($this->authManager, $this->jwtAdapter);
        $result = $handler($cmd);

        $this->assertTrue($result->isAccessTokenGenerationError());
    }

    public function testIssueAccessToken()
    {
        $authResult = AuthResult::success();
        $this->authManager
            ->shouldReceive('authenticate')
            ->once()
            ->andReturn($authResult);

        $this->jwtAdapter
            ->shouldReceive('generateToken')
            ->once()
            ->andReturn($token = 'a.b.c');
        $this->jwtAdapter
            ->shouldReceive('getTokenClaims')
            ->with($token, true)
            ->andReturn(['exp' => $ts = (new \DateTime())->getTimestamp()]);

        $cmd = new IssueCommand('test', 'test');

        $handler = new IssueHandler($this->authManager, $this->jwtAdapter);
        $result = $handler($cmd);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(['token' => $token, 'expiresAt' => $ts], $result->getPayload());
    }
}
