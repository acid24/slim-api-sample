<?php

namespace Salexandru\Api\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Salexandru\Api\Server\Exception\InvalidAccessTokenException;
use Salexandru\Api\Server\Exception\InvalidJsonSyntaxException;
use Salexandru\Api\Server\Exception\MissingAccessTokenException;
use Salexandru\Api\Server\Exception\MissingContentTypeException;
use Salexandru\Api\Server\Exception\UnsupportedMediaTypeException;
use Salexandru\Jwt\AdapterInterface as JwtAdapter;
use \Mockery as m;

class RequestVettingMiddlewareTest extends \PHPUnit_Framework_TestCase
{

    private $jwtAdapter;
    private $response;
    private $callable;

    protected function setUp()
    {
        $this->jwtAdapter = m::mock(JwtAdapter::class);
        $this->response = m::mock(ResponseInterface::class);

        $this->callable = function (ServerRequestInterface $req, ResponseInterface $res) {
            return $res;
        };
    }

    /**
     * @dataProvider dataProviderForContentTypeTests
     */
    public function testPostAndPutRequestWithMissingContentTypeHeaderDoesNotPass($method)
    {
        $this->setExpectedException(MissingContentTypeException::class);

        $request = $this->newRequest($method);
        $request->shouldReceive('getHeader')
            ->with('content-type')
            ->andReturnNull();

        $response = $this->response;

        $requestFilters = new RequestVettingMiddleware($this->jwtAdapter);
        $requestFilters($request, $response, $this->callable);
    }

    /**
     * @dataProvider dataProviderForContentTypeTests
     */
    public function testPostAndPutRequestWithUnexpectedContentTypeDoesNotPass($method)
    {
        $this->setExpectedException(UnsupportedMediaTypeException::class);

        $request = $this->newRequest($method);
        $request->shouldReceive('getHeader')
            ->with('content-type')
            ->andReturn(['text/plain']);
        $response = $this->response;

        $requestFilters = new RequestVettingMiddleware(
            $this->jwtAdapter,
            ['expectedContentType' => 'application/json']
        );
        $requestFilters($request, $response, $this->callable);
    }

    /**
     * @dataProvider dataProviderForJsonContentTypeTests
     */
    public function testJsonPostAndPutRequestWithInvalidBodyDoesNotPass($method, $json)
    {
        $this->setExpectedException(InvalidJsonSyntaxException::class);

        $request = $this->newRequest($method);
        $request->shouldReceive('getHeader')
            ->with('content-type')
            ->andReturn(['application/json']);
        $request->shouldReceive('getParsedBody')
            ->andReturn($json);
        $response = $this->response;

        $requestFilters = new RequestVettingMiddleware(
            $this->jwtAdapter,
            ['expectedContentType' => 'application/json']
        );
        $requestFilters($request, $response, $this->callable);
    }

    public function testRequestWithMissingAccessTokenDoesNotPass()
    {
        $this->setExpectedException(MissingAccessTokenException::class);

        $request = $this->newRequest('GET');
        $request->shouldReceive('getHeader')
            ->with('authorization')
            ->andReturnNull();
        $response = $this->response;

        $requestFilters = new RequestVettingMiddleware($this->jwtAdapter, ['requiresAuthorization' => true]);
        $requestFilters($request, $response, $this->callable);
    }

    public function testRequestWithInvalidAccessTokenDoesNotPass()
    {
        $this->setExpectedException(InvalidAccessTokenException::class);

        $token = 'a.dummy.token';

        $request = $this->newRequest('GET');
        $request->shouldReceive('getHeader')
            ->with('authorization')
            ->andReturn(["Bearer $token"]);
        $response = $this->response;

        $this->jwtAdapter->shouldReceive('isValidToken')
            ->with($token)
            ->andReturn(false);

        $requestFilters = new RequestVettingMiddleware($this->jwtAdapter, ['requiresAuthorization' => true]);
        $requestFilters($request, $response, $this->callable);
    }

    public function testValidAccessTokenIsSetIntoRequestObject()
    {
        $token = 'a.dummy.token';

        $request = $this->newRequest('GET');
        $request->shouldReceive('getHeader')
            ->with('authorization')
            ->andReturn(["Bearer $token"]);
        $request->shouldReceive('withAttribute')
            ->once()
            ->with('accessToken', $token)
            ->andReturnSelf();
        $response = $this->response;

        $this->jwtAdapter->shouldReceive('isValidToken')
            ->with($token)
            ->andReturn(true);

        $requestFilters = new RequestVettingMiddleware($this->jwtAdapter, ['requiresAuthorization' => true]);
        $requestFilters($request, $response, $this->callable);
    }

    public function dataProviderForContentTypeTests()
    {
        return [
            ['POST'], ['PUT']
        ];
    }

    public function dataProviderForJsonContentTypeTests()
    {
        return [
            ['POST', '{invalid:"json"}'], ['PUT', '{invalid:"json"}']
        ];
    }

    public function dataProviderForAccessTokenTests()
    {
        return [
            ['POST'], ['PUT'], ['GET'], ['DELETE']
        ];
    }

    private function newRequest($method)
    {
        $request = m::mock(ServerRequestInterface::class);

        $request->shouldReceive('getMethod')
            ->andReturn($method);

        return $request;
    }
}
