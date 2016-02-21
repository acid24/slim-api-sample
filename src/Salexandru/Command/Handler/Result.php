<?php

namespace Salexandru\Command\Handler;

final class Result
{

    const RESULT_OK = 'OK';
    const RESULT_ERROR = 'ERROR';

    const ERROR_GENERIC = 100;
    const ERROR_INVALID_USER_CREDENTIALS = 101;
    const ERROR_ACCESS_TOKEN_GENERATION = 102;
    const ERROR_INVALID_ACCESS_TOKEN =  103;

    private $status;
    private $payload;
    private $errorCode;
    private $errorMessages = [];

    private function __construct()
    {
        // no instantiation
    }

    public static function success($payload = null)
    {
        $result = new self();
        $result->status = self::RESULT_OK;
        if (null !== $payload) {
            $result->payload = $payload;
        }

        return $result;
    }

    public static function error($code, $messages = null)
    {
        $result = new self();
        $result->status = self::RESULT_ERROR;
        $result->errorCode = $code;

        if (null !== $messages) {
            if (!is_array($messages)) {
                $messages = [$messages];
            }

            $result->errorMessages = $messages;
        }

        return $result;
    }

    public static function genericError()
    {
        return self::error(self::ERROR_GENERIC, 'Internal server error');
    }

    public static function invalidUserCredentialsError()
    {
        return self::error(self::ERROR_INVALID_USER_CREDENTIALS, 'Invalid user credentials');
    }

    public static function accessTokenGenerationError()
    {
        return self::error(self::ERROR_ACCESS_TOKEN_GENERATION, 'Access token could not be generated');
    }

    public static function invalidAccessTokenError()
    {
        return self::error(self::ERROR_INVALID_ACCESS_TOKEN, 'Invalid access token');
    }

    public function isSuccess()
    {
        return $this->status === self::RESULT_OK;
    }

    public function isError()
    {
        return $this->status === self::RESULT_ERROR;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    public function getFirstErrorMessage()
    {
        if (isset($this->errorMessages[0])) {
            return $this->errorMessages[0];
        }

        return null;
    }

    public function getLastErrorMessage()
    {
        $count = count($this->errorMessages);
        if ($count > 0) {
            return $this->errorMessages[$count - 1];
        }

        return null;
    }

    public function isGenericError()
    {
        return $this->isError() && $this->getErrorCode() === self::ERROR_GENERIC;
    }

    public function isInvalidUserCredentialsError()
    {
        return $this->isError() && $this->getErrorCode() === self::ERROR_INVALID_USER_CREDENTIALS;
    }

    public function isAccessTokenGenerationError()
    {
        return $this->isError() && $this->getErrorCode() === self::ERROR_ACCESS_TOKEN_GENERATION;
    }

    public function isInvalidAccessTokenError()
    {
        return $this->isError() && $this->getErrorCode() === self::ERROR_INVALID_ACCESS_TOKEN;
    }
}
