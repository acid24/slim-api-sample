<?php

namespace Salexandru\Command\Handler\AccessToken;

use Salexandru\Api\Authentication\ApiClient;
use Salexandru\Authentication\AuthenticationManager;
use Salexandru\Authentication\Result as AuthResult;
use Salexandru\Command\AccessToken\IssueCommand;
use Salexandru\Command\Handler\Result;
use Salexandru\Jwt\AdapterInterface as JwtAdapter;

class IssueHandler
{

    private $authManager;
    private $jwtAdapter;

    public function __construct(AuthenticationManager $authManager, JwtAdapter $jwtAdapter)
    {
        $this->jwtAdapter = $jwtAdapter;
        $this->authManager = $authManager;
    }

    public function handle(IssueCommand $cmd)
    {
        $apiClient = new ApiClient($cmd->getUsername(), $cmd->getPassword());
        /** @var AuthResult $authResult */
        $authResult = $this->authManager->authenticate($apiClient);
        if ($authResult->isFailure()) {
            return Result::invalidUserCredentialsError();
        }

        try {
            $token = $this->jwtAdapter->generateToken();
        } catch (\Exception $e) {
            return Result::accessTokenGenerationError();
        }

        $claims = $this->jwtAdapter->getTokenClaims($token, $parseOnly = true);

        return Result::success(['token' => $token, 'expiresAt' => $claims['exp']]);
    }
}
