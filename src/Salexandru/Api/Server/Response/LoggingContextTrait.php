<?php

namespace Salexandru\Api\Server\Response;

use Psr\Http\Message\ResponseInterface as Response;
use Salexandru\Util\PsrHttp as PsrHttpUtilities;

trait LoggingContextTrait
{

    private function getLoggingContextFor(Response $response)
    {
        $context = ['status' => $response->getStatusCode()];
        $context['body'] = '(not shown)';

        $mediaType = PsrHttpUtilities::retrieveMediaTypeFrom($response);
        if ($mediaType === 'application/json') {
            $context['body'] = "{$response->getBody()}";
        }

        return $context;
    }
}
