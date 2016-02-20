<?php

namespace Salexandru\Api\Server\Exception\Handler;

use Psr\Http\Message\ResponseInterface;
use Salexandru\Api\Server\Exception\InvalidAccessTokenException;
use Salexandru\Api\Server\Exception\InvalidJsonSyntaxException;
use Salexandru\Api\Server\Exception\MissingAccessTokenException;
use Salexandru\Api\Server\Exception\MissingContentTypeException;
use Salexandru\Api\Server\Exception\UnsupportedMediaTypeException;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

class FallbackHandlerTest extends \PHPUnit_Framework_TestCase
{

    private $request;
    private $response;

    protected function setUp()
    {
        $env = Environment::mock([]);
        $this->request = Request::createFromEnvironment($env);
        $this->response = new Response();
    }

    public function testHandleMissingContentTypeException()
    {
        $exception = new MissingContentTypeException();

        $errorHandler = new FallbackHandler();
        /** @var ResponseInterface $response */
        $response = $errorHandler($this->request, $this->response, $exception);
        $body = json_decode($response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('code', $body['error']);
        $this->assertArrayHasKey('message', $body['error']);
        $this->assertEquals('ERR-000004', $body['error']['code']);
    }

    public function testHandleUnsupportedMediaTypeException()
    {
        $exception = new UnsupportedMediaTypeException();

        $errorHandler = new FallbackHandler();
        /** @var ResponseInterface $response */
        $response = $errorHandler($this->request, $this->response, $exception);
        $body = json_decode($response->getBody(), true);

        $this->assertEquals(415, $response->getStatusCode());
        $this->assertArrayHasKey('code', $body['error']);
        $this->assertArrayHasKey('message', $body['error']);
        $this->assertEquals('ERR-000005', $body['error']['code']);
    }

    public function testHandleInvalidJsonSyntaxException()
    {
        $exception = new InvalidJsonSyntaxException();

        $errorHandler = new FallbackHandler();
        /** @var ResponseInterface $response */
        $response = $errorHandler($this->request, $this->response, $exception);
        $body = json_decode($response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('code', $body['error']);
        $this->assertArrayHasKey('message', $body['error']);
        $this->assertEquals('ERR-000006', $body['error']['code']);
    }

    public function testHandleMissingAccessTokenException()
    {
        $exception = new MissingAccessTokenException();

        $errorHandler = new FallbackHandler();
        /** @var ResponseInterface $response */
        $response = $errorHandler($this->request, $this->response, $exception);
        $body = json_decode($response->getBody(), true);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertArrayHasKey('code', $body['error']);
        $this->assertArrayHasKey('message', $body['error']);
        $this->assertEquals('ERR-000007', $body['error']['code']);
    }

    public function testHandleInvalidAccessTokenException()
    {
        $exception = new InvalidAccessTokenException();

        $errorHandler = new FallbackHandler();
        /** @var ResponseInterface $response */
        $response = $errorHandler($this->request, $this->response, $exception);
        $body = json_decode($response->getBody(), true);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertArrayHasKey('code', $body['error']);
        $this->assertArrayHasKey('message', $body['error']);
        $this->assertEquals('ERR-000008', $body['error']['code']);
    }

    public function testHandleGenericException()
    {
        $exception = new \Exception();

        $errorHandler = new FallbackHandler();
        /** @var ResponseInterface $response */
        $response = $errorHandler($this->request, $this->response, $exception);
        $body = json_decode($response->getBody(), true);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertArrayHasKey('code', $body['error']);
        $this->assertArrayHasKey('message', $body['error']);
        $this->assertEquals('ERR-000001', $body['error']['code']);
    }
}
