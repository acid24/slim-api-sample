<?php

namespace Salexandru\Api\Action\AccessToken;

use Mockery as m;
use Salexandru\Command\Handler\Result;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use Salexandru\CommandBus\CommandBusInterface as CommandBus;

class IssueActionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var CommandBus
     */
    private $commandBus;

    protected function setUp()
    {
        $env = Environment::mock([]);
        $request = Request::createFromEnvironment($env);
        $request->withHeader('Content-Type', 'application/json');

        $this->request = $request;
        $this->response = new Response();

        $this->commandBus = m::mock(CommandBus::class);
    }

    public function testMissingUsernameResultsInBadRequestResponse()
    {
        $request = $this->request->withParsedBody([]);
        $response = $this->response;

        $action = new IssueAction($this->commandBus);
        $response = $action($request, $response, []);

        $body = json_decode($response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('code', $body['error']);
        $this->assertArrayHasKey('message', $body['error']);
        $this->assertEquals('ERR-000009', $body['error']['code']);
    }

    public function testMissingPasswordResultsInBadRequestResponse()
    {
        $request = $this->request->withParsedBody(['username' => 'test']);
        $response = $this->response;

        $action = new IssueAction($this->commandBus);
        $response = $action($request, $response, []);

        $body = json_decode($response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('code', $body['error']);
        $this->assertArrayHasKey('message', $body['error']);
        $this->assertEquals('ERR-000009', $body['error']['code']);
    }

    public function testInvalidUsernameResultsInBadRequestResponse()
    {
        $request = $this->request->withParsedBody(['username' => '', 'password' => '']);
        $response = $this->response;

        $action = new IssueAction($this->commandBus);
        $response = $action($request, $response, []);

        $body = json_decode($response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('code', $body['error']);
        $this->assertArrayHasKey('message', $body['error']);
        $this->assertEquals('ERR-000009', $body['error']['code']);
    }

    public function testInvalidPasswordResultsInBadRequestResponse()
    {
        $request = $this->request->withParsedBody(['username' => 'test', 'password' => '']);
        $response = $this->response;

        $action = new IssueAction($this->commandBus);
        $response = $action($request, $response, []);

        $body = json_decode($response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('code', $body['error']);
        $this->assertArrayHasKey('message', $body['error']);
        $this->assertEquals('ERR-000009', $body['error']['code']);
    }

    public function testInvalidCredentialsResultsInBadRequestResponse()
    {
        $request = $this->request->withParsedBody(['username' => 'test', 'password' => 'test']);
        $response = $this->response;

        $result = Result::invalidUserCredentialsError();

        $this->commandBus
            ->shouldReceive('handle')
            ->once()
            ->andReturn($result);

        $action = new IssueAction($this->commandBus);
        $response = $action($request, $response, []);

        $body = json_decode($response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('code', $body['error']);
        $this->assertArrayHasKey('message', $body['error']);
        $this->assertEquals('ERR-000101', $body['error']['code']);
    }

    public function testTokenGenerationErrorResultsInServerErrorResponse()
    {
        $request = $this->request->withParsedBody(['username' => 'test', 'password' => 'test']);
        $response = $this->response;

        $result = Result::accessTokenGenerationError();

        $this->commandBus
            ->shouldReceive('handle')
            ->once()
            ->andReturn($result);

        $action = new IssueAction($this->commandBus);
        $response = $action($request, $response, []);

        $body = json_decode($response->getBody(), true);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertArrayHasKey('code', $body['error']);
        $this->assertArrayHasKey('message', $body['error']);
        $this->assertEquals('ERR-000102', $body['error']['code']);
    }

    public function testGenericErrorResultsInServerErrorResponse()
    {
        $request = $this->request->withParsedBody(['username' => 'test', 'password' => 'test']);
        $response = $this->response;

        $result = Result::genericError();

        $this->commandBus
            ->shouldReceive('handle')
            ->once()
            ->andReturn($result);

        $action = new IssueAction($this->commandBus);
        $response = $action($request, $response, []);

        $body = json_decode($response->getBody(), true);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertArrayHasKey('code', $body['error']);
        $this->assertArrayHasKey('message', $body['error']);
        $this->assertEquals('ERR-000100', $body['error']['code']);
    }

    public function testExecutionSuccessResultsOKResponse()
    {
        $request = $this->request->withParsedBody(['username' => 'test', 'password' => 'test']);
        $response = $this->response;

        $result = Result::success([
            'token' => 'a.b.c',
            'expiresAt' => $expirationTime = (new \DateTime("+30 minutes"))->getTimestamp()
        ]);

        $this->commandBus
            ->shouldReceive('handle')
            ->once()
            ->andReturn($result);

        $action = new IssueAction($this->commandBus);
        $response = $action($request, $response, []);

        $body = json_decode($response->getBody(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('a.b.c', $body['data']['token']);
        $this->assertEquals($expirationTime, $body['data']['expiresAt']);
    }
}
