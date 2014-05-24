<?php

namespace GuzzleHttp\Tests\Subscriber\Oauth;

use PHPUnit_Framework_TestCase as TestCase;
use GuzzleHttp\Subscriber\Oauth\Oauth2;
use GuzzleHttp\Event\RequestEvents;
use GuzzleHttp\Subscriber\Oauth\AccessToken\AccessToken;
use GuzzleHttp\Collection;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\ClientInterface;

class Oauth2Test extends TestCase
{
    private $tokenClient;

    private $tokenGrantor;

    private $tokenResponse;

    private $oauth;

    protected function setUp()
    {
        $this->tokenGrantor = $this->getMockForAbstractClass(
            'GuzzleHttp\Subscriber\Oauth\AccessToken\GrantorInterface'
        );
        $this->tokenClient = $this->getMockForAbstractClass(
            'GuzzleHttp\ClientInterface'
        );

        $this->tokenResponse = $this->buildResponse();
        $this->tokenClient
            ->expects($this->any())
            ->method('send')
            ->will($this->returnValue($this->tokenResponse));

        $this->oauth = new Oauth2($this->tokenGrantor, $this->tokenClient);
    }

    public function testEvents()
    {
        $events = $this->oauth->getEvents();

        $this->assertArrayHasKey('before', $events);
        $this->assertInternalType('array', $events['before']);
        $this->assertEquals('onBefore', $events['before'][0]);
        $this->assertEquals(RequestEvents::SIGN_REQUEST, $events['before'][1]);

        $this->assertArrayHasKey('error', $events);
        $this->assertInternalType('array', $events['error']);
        $this->assertEquals('onError', $events['error'][0]);
        $this->assertEquals(RequestEvents::EARLY, $events['error'][1]);
    }

    public function testOnBeforeSkipsNonOAuth2Events()
    {
        $config = $this->getMock('GuzzleHttp\Collection');
        $config
            ->expects($this->once())
            ->method('get')
            ->with('auth')
            ->will($this->returnValue('test'));

        $request = $this->buildRequest($config);

        $event = $this->buildEvent('GuzzleHttp\Event\BeforeEvent', $request);

        $this->tokenGrantor
            ->expects($this->never())
            ->method('getToken');

        $this->oauth->onBefore($event);
    }

    public function testOnBeforeSetsAuthorizationHeader()
    {
        $config = $this->getMock('GuzzleHttp\Collection');
        $config
            ->expects($this->once())
            ->method('get')
            ->with('auth')
            ->will($this->returnValue('oauth2'));

        $request = $this->buildRequest($config, 'foo');

        $event = $this->buildEvent('GuzzleHttp\Event\BeforeEvent', $request);

        $this->tokenGrantor
            ->expects($this->once())
            ->method('getToken')
            ->with($this->tokenClient)
            ->will($this->returnValue($this->buildAccessToken()));

        $this->oauth->onBefore($event);
    }

    public function testOnErrorRetriesRequest()
    {
        $config = $this->getMock('GuzzleHttp\Collection');
        $config
            ->expects($this->once())
            ->method('get')
            ->with('retried')
            ->will($this->returnValue(false));
        $config
            ->expects($this->once())
            ->method('set')
            ->with('retried', true);

        $request = $this->buildRequest($config, 'foo');

        $response = $this->buildResponse(401);

        $client = $this->getMockForAbstractClass('GuzzleHttp\ClientInterface');
        $client
            ->expects($this->once())
            ->method('send')
            ->with($request)
            ->will($this->returnValue($response));

        $event = $this->buildEvent('GuzzleHttp\Event\ErrorEvent', $request, $client);
        $event
            ->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue($response));
        $event
            ->expects($this->once())
            ->method('intercept')
            ->with($response);

        $this->tokenGrantor
            ->expects($this->once())
            ->method('getToken')
            ->with($this->tokenClient)
            ->will($this->returnValue($this->buildAccessToken()));

        $this->oauth->onError($event);
    }

    private function buildEvent($className, RequestInterface $request, ClientInterface $client = null)
    {
        $event = $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
        $event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));
        if ($client) {
            $event
                ->expects($this->any())
                ->method('getClient')
                ->will($this->returnValue($client));
        }
        return $event;
    }

    private function buildRequest(Collection $config, $header = null)
    {
        $request = $this->getMockForAbstractClass('GuzzleHttp\Message\RequestInterface');
        $request
            ->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($config));

        if ($header) {
            $request
                ->expects($this->once())
                ->method('addHeader')
                ->with('Authorization', "Bearer $header");
        }

        return $request;
    }

    private function buildResponse($statusCode = 200)
    {
        $response = $this->getMockForAbstractClass('GuzzleHttp\Message\ResponseInterface');
        $response
            ->expects($this->any())
            ->method('getStatusCode')
            ->will($this->returnValue($statusCode));
        return $response;
    }

    private function buildAccessToken()
    {
        return new AccessToken('foo', 0, 'test', __CLASS__);
    }
}
