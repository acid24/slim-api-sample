<?php

namespace Salexandru\Util;

use Psr\Http\Message\MessageInterface;

final class PsrHttp
{

    private function __construct()
    {
    }

    public static function retrieveMediaTypeFrom(MessageInterface $psrMessage)
    {
        $contentType = null;
        $result = $psrMessage->getHeader('content-type');
        if ($result) {
            $contentType = $result ? $result[0] : null;
        }

        if ($contentType) {
            return strtok($contentType, ';');
        }

        return null;
    }
}
