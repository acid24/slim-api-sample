<?php

namespace Salexandru\Jwt;

interface AdapterInterface
{

    /**
     * @param array|null $privateClaims
     * @return string
     */
    public function generateToken(array $privateClaims = null);

    /**
     * @param string $token
     * @return boolean
     */
    public function isValidToken($token);

    /**
     * @param string $token
     * @param array|null $privateClaims
     * @return string
     */
    public function refreshToken($token, array $privateClaims = null);

    /**
     * @param string $token
     * @return array
     */
    public function getTokenClaims($token);
}
