<?php

namespace Salexandru\Jwt\Adapter;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Claim;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Hmac\Sha384;
use Lcobucci\JWT\Signer\Hmac\Sha512;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;
use Salexandru\Jwt\AdapterInterface;
use Salexandru\Jwt\Exception\InvalidTokenException;

class LcobucciAdapter implements AdapterInterface
{

    private $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function generateToken(array $privateClaims = null)
    {
        $builder = new Builder();

        if (null !== $privateClaims) {
            foreach ($privateClaims as $name => $value) {
                $builder->set($name, $value);
            }
        }

        /** @var Signer $signer */
        $signer = $this->getSigner($this->configuration->getAlgorithm());

        $token = $builder
            ->setExpiration($this->configuration->getExpiration())
            ->setIssuedAt((new \DateTime())->getTimestamp())
            ->sign($signer, $this->configuration->getSecret())
            ->getToken();

        return (string)$token;
    }

    public function isValidToken($token)
    {
        try {
            $this->parseVerifyAndValidateToken($token);
        } catch (InvalidTokenException $e) {
            return false;
        }

        return true;
    }

    public function refreshToken($token, array $privateClaims = null)
    {
        /** @var Token $parsedToken */
        $parsedToken = $this->parseVerifyAndValidateToken($token);

        $builder = new Builder();

        // copy claims from the old token into the builder
        foreach ($parsedToken->getClaims() as $name => $value) {
            $builder->set($name, $value);
        }

        if (null !== $privateClaims) {
            foreach ($privateClaims as $name => $value) {
                $builder->set($name, $value);
            }
        }

        /** @var Signer $signer */
        $signer = $this->getSigner($this->configuration->getAlgorithm());

        $refreshedToken = $builder
            ->setExpiration($this->configuration->getExpiration())
            ->setIssuedAt((new \DateTime())->getTimestamp())
            ->sign($signer, $this->configuration->getSecret())
            ->getToken();

        return (string)$refreshedToken;
    }

    /**
     * @param string $token
     * @return array
     */
    public function getTokenClaims($token)
    {
        /** @var Token $parsedToken */
        $parsedToken = $this->parseVerifyAndValidateToken($token);

        $claims = [];

        /** @var Claim $claim */
        foreach ($parsedToken->getClaims() as $claim) {
            $claims[$claim->getName()] = $claim->getValue();
        }

        return $claims;
    }

    private function parseVerifyAndValidateToken($token)
    {
        $token = $this->parseToken($token);
        $this->verifyToken($token);
        $this->validateToken($token);

        return $token;
    }

    private function parseToken($token)
    {
        $parser = new Parser();
        try {
            return $parser->parse("$token");
        } catch (\InvalidArgumentException $e) {
            throw new InvalidTokenException('Token could not be parsed');
        }
    }

    private function verifyToken(Token $token)
    {
        $signer = $this->getSigner($this->configuration->getAlgorithm());
        try {
            $isVerified = $token->verify($signer, $this->configuration->getSecret());
        } catch (\BadMethodCallException $e) {
            $isVerified = false;
        }
        if (!$isVerified) {
            throw new InvalidTokenException('Signature mismatch');
        }
    }

    private function validateToken(Token $token)
    {
        if (!$token->validate(new ValidationData())) {
            throw new InvalidTokenException('Registered claim mismatch');
        }
    }

    private function getSigner($type)
    {
        switch (strtolower($type)) {
            case 'sha256':
                return new Sha256();
            case 'sha384':
                return new Sha384();
            case 'sha512':
                return new Sha512();
            default:
                return null;
        }
    }
}
