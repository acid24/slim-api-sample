<?php

namespace Salexandru\Api\Middleware;

use Slim\Http\Environment;
use Slim\Http\Request;
use Mockery as m;
use Slim\Http\Response;
use Psr\Log\LoggerInterface as Logger;

class RequestLoggingMiddlewareTest extends \PHPUnit_Framework_TestCase
{

    private $testIpAddress = '192.168.100.13';
    private $testEndpoint = '/dummy/endpoint';

    /**
     * @var Request
     */
    private $request;
    private $response;

    protected function setUp()
    {
        $env = Environment::mock([
            'REMOTE_ADDR' => $this->testIpAddress,
            'HTTP_HOST' => 'test.url',
            'REQUEST_URI' => '/dummy/endpoint'
        ]);
        $this->request = Request::createFromEnvironment($env);

        $this->response = new Response();
    }

    public function testGetAndDeleteDoNotAddRequestBodyInTheLogMessage()
    {
        $request = $this->request->withMethod('GET');
        $response = $this->response;

        $expectedMessage = 'Received {http_method} request to {endpoint} (query params: {query_params}) from IP {ip}';
        $expectedContext = [
            'ip' => $this->testIpAddress,
            'http_method' => 'GET',
            'endpoint' => $this->testEndpoint,
            'query_params' => 'none'
        ];

        $logger = m::mock(Logger::class)
            ->shouldReceive('info')
            ->once()
            ->with($expectedMessage, $expectedContext)
            ->getMock();

        $middleware = new RequestLoggingMiddleware($logger);
        $next = function (Request $req, Response $res) {
            return $res;
        };

        $middleware($request, $response, $next);
    }

    public function testPostAndPutAddRequestBodyInTheLogMessage()
    {
        $request = $this->request->withMethod('POST');
        $response = $this->response;

        $expectedMessage  = 'Received {http_method} request to {endpoint} (query params: {query_params}) ';
        $expectedMessage .= 'from IP {ip} with body {body}';
        $expectedContext = [
            'body' => '(not shown)',
            'ip' => $this->testIpAddress,
            'http_method' => 'POST',
            'endpoint' => $this->testEndpoint,
            'query_params' => 'none'
        ];

        $logger = m::mock(Logger::class)
            ->shouldReceive('info')
            ->once()
            ->with($expectedMessage, $expectedContext)
            ->getMock();

        $middleware = new RequestLoggingMiddleware($logger);
        $next = function (Request $req, Response $res) {
            return $res;
        };

        $middleware($request, $response, $next);
    }

    public function testJsonContentTypeShowsRequestBodyInTheLogMessage()
    {
        $stream = $this->request->getBody();
        $stream->rewind();
        $stream->write($body = json_encode(['test' => 'test']));

        $request = $this->request
            ->withMethod('POST')
            ->withBody($stream)
            ->withHeader('content-type', 'application/json');
        $response = $this->response;

        $expectedMessage  = 'Received {http_method} request to {endpoint} (query params: {query_params}) ';
        $expectedMessage .= 'from IP {ip} with body {body}';
        $expectedContext = [
            'body' => $body,
            'ip' => $this->testIpAddress,
            'http_method' => 'POST',
            'endpoint' => $this->testEndpoint,
            'query_params' => 'none'
        ];

        $logger = m::mock(Logger::class)
            ->shouldReceive('info')
            ->once()
            ->with($expectedMessage, $expectedContext)
            ->getMock();

        $middleware = new RequestLoggingMiddleware($logger);
        $next = function (Request $req, Response $res) {
            return $res;
        };

        $middleware($request, $response, $next);
    }
}
