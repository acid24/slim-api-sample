<?php

namespace Salexandru\Api\Middleware;

use Psr\Log\LoggerInterface as Logger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Salexandru\Util\PsrHttp as PsrHttpUtilities;

class ResponseLoggingMiddleware
{

    private $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(Request $req, Response $res, callable $next)
    {
        /** @var Response $response */
        $response = $next($req, $res);

        $context = ['status' => $response->getStatusCode()];
        $context['body'] = '(not shown)';

        $mediaType = PsrHttpUtilities::retrieveMediaTypeFrom($response);
        if ($mediaType === 'application/json') {
            $context['body'] = "{$response->getBody()}";
        }

        $this->logger->info('Sent {status} response with body {body}', $context);

        return $response;
    }
}
