<?php

namespace Salexandru\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use GuzzleHttp\Client as HttpClient;
use Behat\Gherkin\Node\PyStringNode;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Response;
use Interop\Container\ContainerInterface as Container;
use Salexandru\Api\Server\Bootstrap\ContainerServicesProvider;
use Salexandru\Behat\Context\Exception\OutOfBoundsException;
use Salexandru\Behat\Context\Exception\UnexpectedValueException;
use Salexandru\Bootstrap\ConfigInitializer;
use Salexandru\Jwt\AdapterInterface;
use Salexandru\Jwt\Adapter\LcobucciAdapter as JwtAdapter;
use Salexandru\Jwt\Adapter\Configuration as AdapterConfiguration;
use Slim\Container as SlimContainer;

class BaseContext implements Context, SnippetAcceptingContext
{

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $requestMethod;

    /**
     * @var PyStringNode
     */
    protected $requestBody;

    /**
     * @var string
     */
    protected $requestMediaType = 'application/json';

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var array
     */
    protected $excludedHeaders = [];

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var Container
     */
    protected static $container;

    public function __construct($baseUrl)
    {
        $this->httpClient = new HttpClient(['base_uri' => $baseUrl]);
    }

    /**
     * @BeforeSuite
     */
    public static function bootstrap()
    {
        require realpath(__DIR__ . '/../../../bootstrap/constants.php');

        (new ConfigInitializer($container = new SlimContainer([])))
            ->run();
        (new ContainerServicesProvider())
            ->register($container);

        self::$container = $container;
    }

    /**
     * @Given /^the request body is:$/
     */
    public function initRequestBody(PyStringNode $requestBody)
    {
        $this->requestBody = $requestBody;
    }

    /**
     * @Given /^I make a "(GET|PUT|POST|DELETE)" request to the "(\/[^"]+)" endpoint$/
     */
    public function initRequestMethodAndTargetEndpoint($requestMethod, $endpoint)
    {
        $this->requestMethod = $requestMethod;
        $this->endpoint = $endpoint;
    }

    /**
     * @Given /^I am an authorized API client$/
     */
    public function generateValidAccessToken()
    {
        $expirationTime = (new \DateTime("+5 minutes"))->getTimestamp();
        /** @var AdapterInterface $jwtAdapter */
        $jwtAdapter = $this->newJwtAdapter(array('expiration' => $expirationTime));
        $this->accessToken = $jwtAdapter->generateToken();
    }

    /**
     * @Given /^my access token is invalid$/
     */
    public function generateInvalidAccessToken()
    {
        $this->accessToken = 'a.b.c';
    }

    /**
     * @Given /^the request media type is "([^"]+)"$/
     */
    public function setRequestContentType($mediaType)
    {
        $this->requestMediaType = $mediaType;
    }

    /**
     * @Given /^the "([^"]+)" header is missing$/
     */
    public function excludeHeaderFromNextRequest($header)
    {
        $this->excludedHeaders[$header] = true;
    }

    /**
     * @Then /^the status code should be ([12345][0-9]{2})$/
     */
    public function checkResponseStatusCode($responseCode)
    {
        $expected = (int)$responseCode;
        $actual = (int)$this->response->getStatusCode();

        \PHPUnit_Framework_Assert::assertEquals($expected, $actual, "The response code should be {$responseCode}");
    }

    /**
     * @Then /^the error code inside the response body should be "(ERR-[0-9]{6})"$/
     */
    public function checkErrorCode($errorCode)
    {
        $expected = $errorCode;
        $actual = $this->getResponseBodyProperty('error.code');

        \PHPUnit_Framework_Assert::assertEquals($expected, $actual, "The error code should be {$errorCode}");
    }

    /**
     * @When /^I receive the response$/
     */
    public function performTheHttpRequest()
    {
        try {
            switch ($this->requestMethod) {
                case 'POST':
                case 'PUT':
                    $headers = [];
                    if ($this->headerIsNotExcluded('Content-Type')) {
                        $headers['Content-Type'] = $this->requestMediaType;
                    }
                    if (null !== $this->accessToken && $this->headerIsNotExcluded('Authorization')) {
                        $headers['Authorization'] = "Bearer $this->accessToken";
                    }

                    $response = $this->httpClient->request($this->requestMethod, $this->endpoint, [
                        'headers' => $headers,
                        'body' => $this->requestBody
                    ]);
                    break;

                default:
                    $headers = [];
                    if (null !== $this->accessToken && $this->headerIsNotExcluded('Authorization')) {
                        $headers['Authorization'] = "Bearer $this->accessToken";
                    }

                    $response = $this->httpClient->request(
                        $this->requestMethod,
                        $this->endpoint,
                        ['headers' => $headers]
                    );
                    break;
            }

            $this->response = $response;
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
            if (null === $response) {
                throw $e;
            }

            $this->response = $response;
        }
    }

    protected function getResponseBodyProperty($name)
    {
        $body = $this->response->getBody();
        $content = json_decode($body, true);

        $parts = explode('.', $name);

        do {
            $part = array_shift($parts);

            if (!array_key_exists($part, $content)) {
                throw new OutOfBoundsException("$part key not found");
            }

            if (!is_array($content)) {
                throw new UnexpectedValueException("Cannot go deeper; reached non-array value");
            }

            $content = $content[$part];
        } while (!empty($parts));

        return $content;
    }

    protected function newJwtAdapter(array $settings = null)
    {
        $iniSettings = self::$container->get('settings')['jwt'];
        if (null === $settings) {
            $settings = $iniSettings;
        } else {
            $settings = array_merge($iniSettings, $settings);
        }

        $adapterConfiguration = AdapterConfiguration::loadFromArray($settings);
        $adapter = new JwtAdapter($adapterConfiguration);

        return $adapter;
    }

    protected function headerIsNotExcluded($header)
    {
        return !isset($this->excludedHeaders[$header]);
    }
}
