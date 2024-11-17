<?php

declare(strict_types=1);

/*
 * This file is part of the bjoern-hempel/php-calendar-api project.
 *
 * (c) Björn Hempel <https://www.hempel.li/>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace App\Tests\Api;

use App\Entity\User;
use App\OpenApi\JwtDecorator;
use App\Property\Infrastructure\DataFixtures\AppFixtures;
use App\Tests\TestCase\ApiClientTestCase;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.2 (2022-11-12)
 * @since 0.1.2 (2022-11-12) Upgrade to symfony 6.1
 * @since 0.1.1 (2022-01-29) Possibility to disable the JWT locally for debugging processes (#45)
 * @since 0.1.0 Add API tests (#28)
 * @see https://api-platform.com/docs/core/security/#hooking-custom-permission-checks-using-voters
 */
class AuthenticationTest extends ApiClientTestCase
{
    /**
     * @var string[]
     */
    protected static array $credentialsUser1;

    /**
     * @var string[]
     */
    protected static array $credentialsUser2;

    /**
     * This method is called before class.
     *
     * @throws Exception
     */
    public static function setUpBeforeClass(): void
    {
        self::initClientEnvironment();
    }

    /**
     * Test wrong login.
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testWrongLoginUser1(): void
    {
        /* Arrange */
        $endpoint = JwtDecorator::API_ENDPOINT;
        $method = JwtDecorator::API_ENDPOINT_METHOD;
        $userId = 1;
        $options = [
            'json' => [
                'email' => AppFixtures::getEmail($userId),
                'password' => 'wrong-password',
            ],
        ];

        /* Act */
        $this->doRequest($endpoint, $method, $options);

        /* Assert */
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Test login.
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testLoginUser1(): void
    {
        /* Arrange */
        $endpoint = JwtDecorator::API_ENDPOINT;
        $method = JwtDecorator::API_ENDPOINT_METHOD;
        $userId = 1;
        $options = [
            'json' => [
                'email' => AppFixtures::getEmail($userId),
                'password' => AppFixtures::getPassword($userId),
            ],
        ];

        /* Act */
        $response = $this->doRequest($endpoint, $method, $options);
        self::$credentialsUser1 = $response->toArray();

        /* Assert */
        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('token', self::$credentialsUser1);
    }

    /**
     * Test getting users without a token
     *
     * @throws TransportExceptionInterface
     */
    public function testWithoutTokenUserCollection1(): void
    {
        /* Arrange */
        $endpoint = $this->getApiEndpoint(User::API_ENDPOINT_COLLECTION);
        $method = Request::METHOD_GET;

        /* Act */
        $this->doRequest($endpoint, $method);

        /* Assert */
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Test getting user with a token
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testWithTokenUserCollection1(): void
    {
        /* Arrange */
        $userId = 1;
        $endpoint = $this->getApiEndpoint(User::API_ENDPOINT_COLLECTION);
        $method = Request::METHOD_GET;
        $expected = [AppFixtures::getUserAsJson($userId)];

        /* Act */
        $response = $this->doRequest($endpoint, $method, bearer: self::$credentialsUser1['token']);
        $current = $response->toArray();

        /* Assert */
        $this->assertResponseIsSuccessful();
        $this->assertSame($expected, $current);
    }

    /**
     * Test getting user without a token
     *
     * @throws TransportExceptionInterface
     */
    public function testWithoutTokenUser1(): void
    {
        /* Arrange */
        $endpoint = $this->getApiEndpointItem(User::API_ENDPOINT_ITEM, 1);
        $method = Request::METHOD_GET;

        /* Act */
        $this->doRequest($endpoint, $method);

        /* Assert */
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Test getting user with a token
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testWithTokenUser1(): void
    {
        /* Arrange */
        $userId = 1;
        $endpoint = $this->getApiEndpointItem(User::API_ENDPOINT_ITEM, $userId);
        $method = Request::METHOD_GET;
        $expected = AppFixtures::getUserAsJson($userId);

        /* Act */
        $response = $this->doRequest($endpoint, $method, bearer: self::$credentialsUser1['token']);
        $current = $response->toArray();

        /* Assert */
        $this->assertResponseIsSuccessful();
        $this->assertSame($expected, $current);
    }

    /**
     * Test getting forbidden user.
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testTryForbiddenUser1(): void
    {
        /* Arrange */
        $userId = 2;
        $endpoint = $this->getApiEndpointItem(User::API_ENDPOINT_ITEM, $userId);
        $method = Request::METHOD_GET;

        /* Act */
        $this->doRequest($endpoint, $method, bearer: self::$credentialsUser1['token']);

        /* Assert */
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * Test login.
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testLoginUser2(): void
    {
        /* Arrange */
        $endpoint = JwtDecorator::API_ENDPOINT;
        $method = JwtDecorator::API_ENDPOINT_METHOD;
        $userId = 2;
        $options = [
            'json' => [
                'email' => AppFixtures::getEmail($userId),
                'password' => AppFixtures::getPassword($userId),
            ],
        ];

        /* Act */
        $response = $this->doRequest($endpoint, $method, $options);
        self::$credentialsUser2 = $response->toArray();

        /* Assert */
        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('token', self::$credentialsUser2);
    }

    /**
     * Test getting user with a token
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testWithTokenUser2(): void
    {
        /* Arrange */
        $userId = 2;
        $endpoint = $this->getApiEndpointItem(User::API_ENDPOINT_ITEM, $userId);
        $method = Request::METHOD_GET;
        $expected = AppFixtures::getUserAsJson($userId);

        /* Act */
        $response = $this->doRequest($endpoint, $method, bearer: self::$credentialsUser2['token']);
        $current = $response->toArray();

        /* Assert */
        $this->assertResponseIsSuccessful();
        $this->assertSame($expected, $current);
    }

    /**
     * Test getting forbidden user.
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testTryForbiddenUser2(): void
    {
        /* Arrange */
        $userId = 1;
        $endpoint = $this->getApiEndpointItem(User::API_ENDPOINT_ITEM, $userId);
        $method = Request::METHOD_GET;

        /* Act */
        $this->doRequest($endpoint, $method, bearer: self::$credentialsUser2['token']);

        /* Assert */
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
