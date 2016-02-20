<?php

namespace Salexandru\Jwt\Adapter;

use Salexandru\Jwt\Exception\InvalidArgumentException;
use Salexandru\Jwt\Exception\RuntimeException;

final class Configuration
{

    private $expiration;
    private $algorithm;
    private $secret;

    private function __construct()
    {
    }

    public static function loadFromArray(array $options)
    {
        if (isset($options['expiresIn'])) {
            $expirationExpression = $options['expiresIn'];
            $options['expiration'] = $expirationExpression;
            unset($options['expiresIn']);
        }

        $configuration = new self();
        foreach ($options as $name => $value) {
            $method = sprintf('set%s', ucfirst($name));
            if (method_exists($configuration, $method)) {
                $configuration->$method($value);
            }
        }

        $configuration->check();

        return $configuration;
    }

    /**
     * @return mixed
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * @return mixed
     */
    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    /**
     * @return mixed
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * An parsable strtotime() or \DateTime time expression (like 30 minutes, or 1 week 2 hours, or a unix timestamp)
     *
     * Examples: 30 minutes, 1 day 2 hours 22 seconds, 1 week, etc
     *
     * @param string $expiration
     */
    private function setExpiration($expiration)
    {
        if (is_numeric($expiration)) {
            $expr = "@$expiration";
        } else {
            $expr = "+$expiration";
        }

        try {
            $expirationDate = new \DateTime($expr, new \DateTimeZone('UTC'));
        } catch (\Exception $e) {
            throw new InvalidArgumentException('Invalid expiration time expression', 0, $e);
        }

        $this->expiration = $expirationDate->getTimestamp();
    }

    /**
     * @param mixed $algorithm
     */
    private function setAlgorithm($algorithm)
    {
        $allowedAlgorithms = array('sha256' => true, 'sha384' => true, 'sha512' => true);
        if (!isset($allowedAlgorithms[$algorithm = strtolower($algorithm)])) {
            $message = sprintf(
                'Algorithm %s is not allowed. Use one of: %s',
                $algorithm,
                implode(', ', $allowedAlgorithms)
            );
            throw new InvalidArgumentException($message);
        }

        $this->algorithm = $algorithm;
    }

    /**
     * @param mixed $secret
     */
    private function setSecret($secret)
    {
        if (!is_string($secret) || empty($secret)) {
            throw new InvalidArgumentException('Secret must not be empty');
        }

        $this->secret = $secret;
    }

    private function check()
    {
        $this->ensureSigningAlgorithmHasBeenProvided();
        $this->ensureExpirationTimeHasBeenProvided();
        $this->ensureSecretKeyHasBeenProvided();
    }

    private function ensureExpirationTimeHasBeenProvided()
    {
        if (null === $this->expiration) {
            throw new RuntimeException('Missing expiration time');
        }
    }

    private function ensureSecretKeyHasBeenProvided()
    {
        if (null === $this->secret) {
            throw new RuntimeException('Missing secret key');
        }
    }

    private function ensureSigningAlgorithmHasBeenProvided()
    {
        if (null === $this->algorithm) {
            $this->algorithm = 'sha256';
        }
    }
}
