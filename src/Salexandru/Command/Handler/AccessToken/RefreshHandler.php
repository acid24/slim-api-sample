<?php

namespace Salexandru\Command\Handler\AccessToken;

use Salexandru\Command\AccessToken\RefreshCommand;
use Salexandru\Command\Handler\Result;
use Salexandru\Jwt\AdapterInterface as JwtAdapter;
use Salexandru\Jwt\Exception\InvalidTokenException;

class RefreshHandler
{

    private $jwtAdapter;

    public function __construct(JwtAdapter $jwtAdapter)
    {
        $this->jwtAdapter = $jwtAdapter;
    }

    public function __invoke(RefreshCommand $cmd)
    {
        $currentToken = $cmd->getCurrentAccessToken();

        try {
            $token = $this->jwtAdapter->refreshToken($currentToken);
        } catch (InvalidTokenException $e) {
            return Result::invalidAccessTokenError();
        } catch (\Exception $e) {
            return Result::accessTokenGenerationError();
        }

        $claims = $this->jwtAdapter->getTokenClaims($token, $parseOnly = true);

        return Result::success(['token' => $token, 'expiresAt' => $claims['exp']]);
    }
}
