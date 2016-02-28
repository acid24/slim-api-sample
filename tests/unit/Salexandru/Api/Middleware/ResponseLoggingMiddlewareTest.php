<?php

namespace Salexandru\Api\Middleware;

use Slim\Http\Environment;
use Slim\Http\Request;
use Mockery as m;
use Slim\Http\Response;
use Psr\Log\LoggerInterface as Logger;

class ResponseLoggingMiddlewareTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    protected function setUp()
    {
        $env = Environment::mock([]);
        $this->request = Request::createFromEnvironment($env);

        $this->response = new Response();
    }

    public function testJsonContentTypeShowsResponseBodyInTheLogMessage()
    {
        $stream = $this->response->getBody();
        $stream->rewind();
        $stream->write($body = json_encode(['test' => 'test']));

        $request = $this->request;
        $response = $this->response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200)
            ->withBody($stream);

        $expectedMessage = 'Sent {status} response with body {body}';
        $expectedContext = [
            'status' => 200,
            'body' => $body
        ];

        $logger = m::mock(Logger::class)
            ->shouldReceive('info')
            ->once()
            ->with($expectedMessage, $expectedContext)
            ->getMock();

        $middleware = new ResponseLoggingMiddleware($logger);
        $next = function (Request $req, Response $res) {
            return $res;
        };

        $middleware($request, $response, $next);
    }

    public function testResponseBodyIsNotShownInTheLogMessageForNonJsonContentTypes()
    {
        $request = $this->request;
        $response = $this->response->withStatus(200);

        $expectedMessage = 'Sent {status} response with body {body}';
        $expectedContext = [
            'status' => 200,
            'body' => '(not shown)'
        ];

        $logger = m::mock(Logger::class)
            ->shouldReceive('info')
            ->once()
            ->with($expectedMessage, $expectedContext)
            ->getMock();

        $middleware = new ResponseLoggingMiddleware($logger);
        $next = function (Request $req, Response $res) {
            return $res;
        };

        $middleware($request, $response, $next);
    }
}
