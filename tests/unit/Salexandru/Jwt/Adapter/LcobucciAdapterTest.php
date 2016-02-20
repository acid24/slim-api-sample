<?php

namespace Salexandru\Jwt\Adapter;

use Salexandru\Jwt\AdapterInterface;

class LcobucciAdapterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var AdapterInterface
     */
    private $adapter;

    protected function setUp()
    {
        $this->adapter = $this->setupAdapter();
    }

    public function testGenerateToken()
    {

        $token = $this->adapter->generateToken();

        $this->assertInternalType('string', $token, 'Invalid token; must be string');
        $this->assertNotEmpty($token);

        $parts = explode('.', $token);
        $this->assertCount(3, $parts, 'Invalid token; must have 2 dots');

        foreach ($parts as $part) {
            $this->assertNotEmpty($part);
        }
    }

    public function testRetrieveTokenClaims()
    {
        $now = (new \DateTime())->getTimestamp();

        $adapter = $this->setupAdapter(['expiration' => $now]);
        $token = $adapter->generateToken(['claim1' => 1, 'claim2' => 2]);

        $claims = $adapter->getTokenClaims($token);

        $this->assertArrayHasKey('exp', $claims);
        $this->assertArrayHasKey('iat', $claims);
        $this->assertArrayHasKey('claim1', $claims);
        $this->assertArrayHasKey('claim2', $claims);

        $this->assertEquals($now, $claims['exp']);
        $this->assertEquals(1, $claims['claim1']);
        $this->assertEquals(2, $claims['claim2']);
    }

    public function testTokenValidation()
    {
        $this->assertFalse($this->adapter->isValidToken('nope'));
        $this->assertFalse($this->adapter->isValidToken(null));
        $this->assertFalse($this->adapter->isValidToken(1));
        $this->assertTrue($this->adapter->isValidToken($this->adapter->generateToken()));
    }

    public function testRefreshTokenExtendsExpirationTime()
    {
        $adapter = $this->setupAdapter(['expiresIn' => '5 minutes']);
        $oldToken = $adapter->generateToken();

        $adapter = $this->setupAdapter(['expiresIn' => '30 minutes']);
        $newToken = $adapter->refreshToken($oldToken);

        $oldTokenExpirationTime = $adapter->getTokenClaims($oldToken)['exp'];
        $newTokenExpirationTime = $adapter->getTokenClaims($newToken)['exp'];

        $this->assertEquals(25 * 60, $newTokenExpirationTime - $oldTokenExpirationTime, '', 1);
    }

    private function setupAdapter(array $settings = null)
    {
        $defaults = [
            'algorithm' => 'sha256',
            'expiration' => (new \DateTime())->getTimestamp(),
            'secret' => 'S3cr3t!',
        ];

        if (null === $settings) {
            $settings = $defaults;
        } else {
            $settings = array_merge($defaults, $settings);
        }

        $conf = Configuration::loadFromArray($settings);
        return new LcobucciAdapter($conf);
    }
}
