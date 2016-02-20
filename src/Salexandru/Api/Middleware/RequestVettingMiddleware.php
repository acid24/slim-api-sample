<?php

namespace Salexandru\Api\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Salexandru\Api\Server\Exception\InvalidAccessTokenException;
use Salexandru\Api\Server\Exception\InvalidJsonSyntaxException;
use Salexandru\Api\Server\Exception\MissingAccessTokenException;
use Salexandru\Api\Server\Exception\MissingContentTypeException;
use Salexandru\Api\Server\Exception\UnsupportedMediaTypeException;
use Salexandru\Jwt\AdapterInterface as JwtAdapter;

class RequestVettingMiddleware
{

    private $jwtAdapter;
    private $requiresAccessToken;
    private $expectedMediaType;

    public function __construct(JwtAdapter $jwtAdapter, array $options = null)
    {
        $this->jwtAdapter = $jwtAdapter;

        $defaults = [
            'requiresAccessToken' => true,
            'expectedMediaType' => 'application/json'
        ];

        if (null === $options) {
            $options = $defaults;
        } else {
            $options = array_merge($defaults, $options);
        }

        $this->setOptions($options);
    }

    public function __invoke(Request $req, Response $res, callable $next)
    {
        $req = $this->ensureRequirementsAreMetFor($req);
        $res = $next($req, $res);

        return $res;
    }

    private function ensureRequirementsAreMetFor(Request $request)
    {
        if ($request->getMethod() === 'POST' || $request->getMethod() === 'PUT') {
            $this->ensureExpectedMediaType($mediaType = $this->extractMediaTypeFrom($request));
            if ($mediaType === 'application/json') {
                try {
                    $parsedBody = $request->getParsedBody();
                } catch (\RuntimeException $e) {
                    $parsedBody = null;
                }
                $this->ensureValidJsonBody($parsedBody);
            }
        }

        if ($this->requiresAccessToken) {
            $token = $this->extractAccessTokenFrom($request);
            if (null === $token) {
                throw new MissingAccessTokenException('An access token is required to access this resource');
            }

            if (!$this->jwtAdapter->isValidToken($token)) {
                throw new InvalidAccessTokenException('Provided access token is invalid');
            }

            $request = $request->withAttribute('accessToken', $token);
        }

        return $request;
    }

    private function ensureExpectedMediaType($mediaType)
    {
        if (null === $mediaType) {
            throw new MissingContentTypeException('POST and PUT requests are required to have a content-type header');
        }

        if (null !== $this->expectedMediaType && $this->expectedMediaType !== $mediaType) {
            throw new UnsupportedMediaTypeException(
                "Unexpected content type; expected '$this->expectedMediaType' got '$mediaType'"
            );
        }
    }

    private function ensureValidJsonBody($parsedBody)
    {
        if (!is_array($parsedBody) && !is_object($parsedBody)) {
            throw new InvalidJsonSyntaxException('The provided request body is not valid JSON');
        }
    }

    private function extractAccessTokenFrom(Request $req)
    {
        $authorization = null;
        $result = $req->getHeader('authorization');
        if ($result) {
            $authorization = $result ? $result[0] : null;
        }

        if ($authorization) {
            preg_match('!bearer\s+(.+?)\s*$!i', $authorization, $matches);
            if (is_array($matches) && isset($matches[1])) {
                return $matches[1];
            }
        }

        return null;
    }

    private function extractMediaTypeFrom(Request $req)
    {
        $contentType = null;
        $result = $req->getHeader('content-type');
        if ($result) {
            $contentType = $result ? $result[0] : null;
        }

        if ($contentType) {
            return strtok($contentType, ';');
        }

        return null;
    }

    private function setOptions(array $options)
    {
        $requiresAccessToken = true;
        $expectedMediaType = null;

        extract($options);

        $this->requiresAccessToken = ($requiresAccessToken == true);
        $this->expectedMediaType = $expectedMediaType;
    }
}
