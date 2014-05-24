<?php

namespace GuzzleHttp\Tests\Subscriber\Oauth\AccessToken;

use PHPUnit_Framework_TestCase as TestCase;
use GuzzleHttp\Subscriber\Oauth\AccessToken\Grantor;

class GrantorTest extends TestCase
{
    private $jsonData = [
        'access_token' => 'token',
        'expires_in'   => 300,
        'token_type'   => __CLASS__,
        'scope'        => __NAMESPACE__
    ];

    private $client;

    protected function setUp()
    {
        $this->client = $this->getMockForAbstractClass('GuzzleHttp\ClientInterface');
    }

    public function testToStringReturnsType()
    {
        $this->assertSame('password', (string) Grantor::password());
    }

    public function testGetTokenReturnsAccessToken()
    {
        $response = $this->getMockForAbstractClass('GuzzleHttp\Message\ResponseInterface');
        $response
            ->expects($this->once())
            ->method('json')
            ->will($this->returnValue($this->jsonData));
        $this->client
            ->expects($this->once())
            ->method('post')
            ->will($this->returnValue($response));

        $grantor = Grantor::clientCredentials(['client_id' => __FUNCTION__]);
        $this->assertInstanceOf(
            'GuzzleHttp\Subscriber\Oauth\AccessToken\AccessToken',
            $grantor->getToken($this->client)
        );
    }
}
