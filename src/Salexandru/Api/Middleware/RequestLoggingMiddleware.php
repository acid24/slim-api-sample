<?php

namespace Salexandru\Api\Middleware;

use Psr\Log\LoggerInterface as Logger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Salexandru\Util\PsrHttp as PsrHttpUtilities;

class RequestLoggingMiddleware
{

    private $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(Request $req, Response $res, callable $next)
    {
        $context = [];
        $serverParams = $req->getServerParams();
        $mediaType = PsrHttpUtilities::retrieveMediaTypeFrom($req);
        $httpMethod = $req->getMethod();

        $message = 'Received {http_method} request to {url} from IP {ip}';
        if ($httpMethod === 'POST' || $httpMethod === 'PUT') {
            $message .= ' with body {body}';
            $context['body'] = '(not shown)';
            if ($mediaType === 'application/json') {
                $context['body'] = "{$req->getBody()}";
            }
        }

        $context['ip'] = isset($serverParams['REMOTE_ADDR']) ? $serverParams['REMOTE_ADDR'] : 'unknown';
        $context['http_method'] = $httpMethod;
        $context['url'] = "{$req->getUri()}";

        $this->logger->info($message, $context);

        return $next($req, $res);
    }
}
