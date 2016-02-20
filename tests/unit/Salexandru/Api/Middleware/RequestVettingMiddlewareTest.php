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

class RequestVettingMiddlewareTest extends \PHPUnit_Framework_TestCase
{

    private $jwtAdapter;
    private $response;
    private $callable;

    protected function setUp()
    {
        $this->jwtAdapter = $this->getMockBuilder(JwtAdapter::class)
            ->getMock();

        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();

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
        $request->expects($this->any())
            ->method('getHeader')
            ->with('content-type')
            ->will($this->returnValue(null));

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
        $request->expects($this->any())
            ->method('getHeader')
            ->with('content-type')
            ->will($this->returnValue(['text/plain']));
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
        $request->expects($this->any())
            ->method('getHeader')
            ->with('content-type')
            ->will($this->returnValue(['application/json']));
        $request->expects($this->any())
            ->method('getParsedBody')
            ->will($this->returnValue($json));
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
        $request->expects($this->any())
            ->method('getHeader')
            ->with('authorization')
            ->will($this->returnValue(null));
        $response = $this->response;

        $requestFilters = new RequestVettingMiddleware($this->jwtAdapter, ['requiresAuthorization' => true]);
        $requestFilters($request, $response, $this->callable);
    }

    public function testRequestWithInvalidAccessTokenDoesNotPass()
    {
        $this->setExpectedException(InvalidAccessTokenException::class);

        $token = 'a.dummy.token';

        $request = $this->newRequest('GET');
        $request->expects($this->any())
            ->method('getHeader')
            ->with('authorization')
            ->will($this->returnValue(["Bearer $token"]));
        $response = $this->response;

        $this->jwtAdapter->expects($this->any())
            ->method('isValidToken')
            ->with($token)
            ->will($this->returnValue(false));

        $requestFilters = new RequestVettingMiddleware($this->jwtAdapter, ['requiresAuthorization' => true]);
        $requestFilters($request, $response, $this->callable);
    }

    public function testValidAccessTokenIsSetIntoRequestObject()
    {
        $token = 'a.dummy.token';

        $request = $this->newRequest('GET');
        $request->expects($this->any())
            ->method('getHeader')
            ->with('authorization')
            ->will($this->returnValue(["Bearer $token"]));
        $request->expects($this->once())
            ->method('withAttribute')
            ->with('accessToken', $token)
            ->will($this->returnSelf());
        $response = $this->response;

        $this->jwtAdapter->expects($this->any())
            ->method('isValidToken')
            ->with($token)
            ->will($this->returnValue(true));

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
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->getMock();

        $request->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue($method));

        return $request;
    }
}
