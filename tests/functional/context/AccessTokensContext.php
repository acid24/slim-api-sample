<?php

namespace Salexandru\Behat\Context;

use Behat\Gherkin\Node\PyStringNode;
use Doctrine\DBAL\Connection;
use Salexandru\Behat\Context\Exception\RuntimeException;
use Salexandru\Jwt\Adapter\LcobucciAdapter as JwtAdapter;

class AccessTokensContext extends BaseContext
{

    /** @var JwtAdapter */
    private $jwtAdapter;

    public function __construct($baseUrl)
    {
        parent::__construct($baseUrl);
        $this->jwtAdapter = $this->newJwtAdapter();
    }

    /**
     * @Then /^the "([^"]+)" property inside the response body should contain a valid API access token$/
     */
    public function verifyValidJwtToken($prop)
    {
        $token = $this->getResponseBodyProperty($prop);
        $this->ensureTokenIsValid($token);
    }

    /**
     * @Then /^the "([^"]+)" property inside the response body should contain a valid API access token with an extended expiration time$/
     */
    public function verifyExtendedExpirationTimeOnJwtToken($prop)
    {
        $oldToken = $this->accessToken;
        $newToken = $this->getResponseBodyProperty($prop);

        $this->ensureTokenIsValid($newToken);

        $oldTokenExpirationTime = $this->jwtAdapter->getTokenClaims($oldToken)['exp'];
        $newTokenExpirationTime = $this->jwtAdapter->getTokenClaims($newToken)['exp'];

        \PHPUnit_Framework_Assert::assertGreaterThanOrEqual(
            $newTokenExpirationTime,
            $oldTokenExpirationTime,
            'The expiration time of the refreshed token should be greater than that of the current token'
        );
    }

    /**
     * @Then /^the "([^"]+)" property inside the response body should contain a valid future unix timestamp$/
     */
    public function verifyIfValidFutureUnixTimestamp($prop)
    {
        $timestamp = $this->getResponseBodyProperty($prop);

        $now = (new \DateTime())->getTimestamp();
        $expiration = (new \DateTime("@$timestamp"))->getTimestamp();

        \PHPUnit_Framework_Assert::assertGreaterThan(
            $now,
            $expiration,
            'The expiration time should be greater than now'
        );
    }

    /**
     * @Given /^I provide my current API access token in the request body$/
     */
    public function initRequestBodyWithValidAccessToken()
    {
        parent::generateValidAccessToken();
        $json = '{ "currentToken": "' . $this->accessToken . '" }';
        $this->initRequestBody(new PyStringNode([$json], 0));
    }

    /**
     * @Given /^I provide an invalid API access token in the request body$/
     */
    public function initRequestBodyWithInvalidAccessToken()
    {
        parent::generateInvalidAccessToken();
        $json = '{ "currentToken": "' . $this->accessToken . '" }';
        $this->initRequestBody(new PyStringNode([$json], 0));
    }

    /**
     * @Given /^there is a user in database with username "([^"]+)" and password "([^"]+)"$/
     */
    public function createTestUser($username, $password)
    {
        $sql = '
          INSERT INTO users
            (username, password, time_created, last_updated)
              VALUES
            (:username, :password, :now, :now)
        ';

        $now = (new \DateTime())->getTimestamp();
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 8]);

        /** @var Connection $conn */
        $conn = self::$container->get('dbConnection');
        $conn->executeUpdate($sql, ['username' => $username, 'password' => $hashedPassword, 'now' => $now]);
    }

    /**
     * @AfterScenario @createsTestUser
     */
    public function deleteTestUser()
    {
        $sql = 'DELETE FROM users WHERE username = :username';

        /** @var Connection $conn */
        $conn = self::$container->get('dbConnection');
        $conn->executeUpdate($sql, ['username' => 'behat']);
    }

    private function ensureTokenIsValid($token)
    {
        if (!$this->jwtAdapter->isValidToken($token)) {
            throw new RuntimeException('token is invalid');
        }
    }
}
