<?php

namespace Salexandru\Command\Handler\AccessToken;

use Salexandru\Command\AccessToken\IssueCommand;
use Salexandru\Command\Handler\Result;
use Salexandru\Jwt\AdapterInterface as JwtAdapter;

class IssueHandler
{

    private $jwtAdapter;

    public function __construct(JwtAdapter $jwtAdapter)
    {
        $this->jwtAdapter = $jwtAdapter;
    }

    public function handle(IssueCommand $cmd)
    {
        // @todo check user credentials against database

        try {
            $token = $this->jwtAdapter->generateToken();
        } catch (\Exception $e) {
            return Result::accessTokenGenerationError();
        }

        $claims = $this->jwtAdapter->getTokenClaims($token, $parseOnly = true);

        return Result::success(['token' => $token, 'expiresAt' => $claims['exp']]);
    }
}
