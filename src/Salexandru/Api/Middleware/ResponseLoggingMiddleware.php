<?php

namespace Salexandru\Api\Middleware;

use Psr\Log\LoggerInterface as Logger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Salexandru\Api\Server\Response\LoggingContextTrait;

class ResponseLoggingMiddleware
{

    use LoggingContextTrait;

    private $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(Request $req, Response $res, callable $next)
    {
        /** @var Response $response */
        $response = $next($req, $res);

        $context = $this->getLoggingContextFor($response);
        $this->logger->info('Sent {status} response with body {body}', $context);

        return $response;
    }
}
