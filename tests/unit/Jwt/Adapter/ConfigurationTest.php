<?php

namespace Salexandru\Jwt\Adapter;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{

    private $algorithm = 'sha256';

    public function testNoSigningAlgorithmProvidedThrowsException()
    {
        $this->setExpectedException('RuntimeException');

        Configuration::loadFromArray([]);
    }

    public function testInvalidSigningAlgorithmThrowsException()
    {
        $this->setExpectedException('InvalidArgumentException');

        Configuration::loadFromArray(['algorithm' => 'invalid']);
    }

    public function testNoExpirationTimeProvidedThrowsException()
    {
        $this->setExpectedException('RuntimeException');

        Configuration::loadFromArray(['algorithm' => $this->algorithm]);
    }

    public function testInvalidExpirationExpressionThrowsException()
    {
        $this->setExpectedException('InvalidArgumentException');

        Configuration::loadFromArray(['algorithm' => $this->algorithm, 'expiration' => 'invalid']);
    }

    public function testNoSecretSigningKeyProvidedThrowsException()
    {
        $this->setExpectedException('RuntimeException');

        Configuration::loadFromArray([
            'algorithm' => $this->algorithm,
            'expiration' => 0,
        ]);
    }

    public function testInvalidSecretSigningKeyThrowsException()
    {
        $this->setExpectedException('InvalidArgumentException');

        Configuration::loadFromArray([
            'algorithm' => $this->algorithm,
            'expiration' => 0,
            'secret' => ''
        ]);
    }

    public function testConfigurationGetters()
    {
        $now = new \DateTime();

        $conf = Configuration::loadFromArray([
            'algorithm' => $this->algorithm,
            'expiration' => $exp = $now->getTimestamp(),
            'secret' => $secret = 'S3cr3t!',
        ]);

        $this->assertEquals($exp, $conf->getExpiration());
        $this->assertEquals($this->algorithm, $conf->getAlgorithm());
        $this->assertEquals($secret, $conf->getSecret());
    }

    public function testExpirationProvidedAsTimeExpression()
    {
        $expr = '30 minutes';

        $conf = Configuration::loadFromArray([
            'algorithm' => $this->algorithm,
            'expiration' => $expr,
            'secret' => $secret = 'S3cr3t!',
        ]);

        $expected = (new \DateTime($expr))->getTimestamp();
        $actual = $conf->getExpiration();

        $this->assertEquals($expected, $actual, '', 1);
    }
}
