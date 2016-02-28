<?php

namespace Salexandru\Api\Middleware;

use Psr\Log\LoggerInterface as Logger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

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
        $mediaType = $this->extractMediaTypeFrom($req);
        $httpMethod = $req->getMethod();

        $message = 'Received {http_method} request to {url} from IP {ip}';
        if ($httpMethod === 'POST' || $httpMethod === 'PUT') {
            $message .= ' with body {body}';
            switch ($mediaType) {
                case 'application/json':
                case 'text/plain':
                case 'text/html':
                    $body = "{$req->getBody()}";
                    break;
                default:
                    $body = "(not shown)";
                    break;
            }
            $context['body'] = $body;
        }

        $context['ip'] = isset($serverParams['REMOTE_ADDR']) ? $serverParams['REMOTE_ADDR'] : 'unknown';
        $context['http_method'] = $httpMethod;
        $context['url'] = "{$req->getUri()}";

        $this->logger->info($message, $context);

        return $next($req, $res);
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
}
