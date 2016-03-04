<?php

namespace Salexandru\Authentication;

use Salexandru\Authentication\Exception\InvalidArgumentException;

final class Result
{

    const SUCCESS = 1;
    const FAILURE_GENERIC = 0;
    const FAILURE_INVALID_CREDENTIALS = -1;

    private $status;
    private $error;

    private function __construct($status, $error = null)
    {
        $this->setStatus($status);
        if (null !== $error) {
            $this->error = $error;
        }
    }

    public function isSuccess()
    {
        return $this->status >= self::SUCCESS;
    }

    public function isFailure()
    {
        return false === $this->isSuccess();
    }

    public static function success()
    {
        return new self(self::SUCCESS);
    }

    public static function failure($type, $error = null)
    {
        return new self($type, $error);
    }

    public static function genericFailure($error = null)
    {
        return self::failure(self::FAILURE_GENERIC, $error);
    }

    public static function invalidCredentialsFailure($error = null)
    {
        return self::failure(self::FAILURE_INVALID_CREDENTIALS, $error);
    }

    /**
     * @return string|null
     */
    public function getError()
    {
        return $this->error;
    }

    private function setStatus($status)
    {
        $statusMap = [
            self::SUCCESS => true,
            self::FAILURE_GENERIC => true,
            self::FAILURE_INVALID_CREDENTIALS => true
        ];

        if (!isset($statusMap[$status])) {
            throw new InvalidArgumentException("Unknown status $status");
        }

        $this->status = $status;
    }
}
