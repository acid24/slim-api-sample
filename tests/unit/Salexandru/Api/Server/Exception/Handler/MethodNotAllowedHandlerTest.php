<?php

namespace Salexandru\Api\Server\Exception\Handler;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

class MethodNotAllowedHandlerTest extends \PHPUnit_Framework_TestCase
{

    public function testHandlingOfNotAllowedRequestMethod()
    {
        $env = Environment::mock([]);
        $request = Request::createFromEnvironment($env);
        $response = new Response();

        $notAllowedHandler = new MethodNotAllowedHandler();
        /** @var ResponseInterface $response */
        $response = $notAllowedHandler($request, $response, []);
        $body = json_decode($response->getBody(), true);

        $this->assertEquals(405, $response->getStatusCode());
        $this->assertArrayHasKey('code', $body['error']);
        $this->assertArrayHasKey('message', $body['error']);
        $this->assertEquals('ERR-000002', $body['error']['code']);
    }
}
