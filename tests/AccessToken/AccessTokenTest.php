<?php

namespace GuzzleHttp\Tests\Subscriber\Oauth\AccessToken;

use PHPUnit_Framework_TestCase as TestCase;
use GuzzleHttp\Subscriber\Oauth\AccessToken\AccessToken;

class AccessTokenTest extends TestCase
{
    const TOKEN   = 'token';
    const EXPIRES = 300;
    const TYPE    = __CLASS__;
    const SCOPE   = __NAMESPACE__;

    /**
     * @var AccessToken
     */
    private $token;

    protected function setUp()
    {
        $this->token = new AccessToken(
            self::TOKEN,
            self::EXPIRES,
            self::TYPE,
            self::SCOPE
        );
    }

    public function testJsonSerializable()
    {
        $this->assertInstanceOf('JsonSerializable', $this->token);
        $json = json_encode($this->token);
        $this->assertInternalType('string', $json);
        $this->assertRegExp(sprintf("#%d#", self::EXPIRES), $json);
    }

    public function testFromArray()
    {
        $data = $this->token->jsonSerialize();
        $this->assertEquals($this->token, AccessToken::fromArray($data));
    }

    public function testSerialize()
    {
        $this->assertInstanceOf('Serializable', $this->token);
        $this->assertInternalType('string', serialize($this->token));
    }

    public function testUnserialize()
    {
        $serialized = serialize($this->token);
        $this->assertEquals($this->token, unserialize($serialized));
    }

    public function testIsValid()
    {
        $this->assertTrue($this->token->isValid());
    }

    public function testHasType()
    {
        $this->assertTrue($this->token->hasType(self::TYPE));
    }

    public function testHasScope()
    {
        $this->assertTrue($this->token->hasScope(self::SCOPE));
    }

    public function testAddAuthorizationHeader()
    {
        $request = $this->getMockForAbstractClass('GuzzleHttp\Message\RequestInterface');
        $request
            ->expects($this->once())
            ->method('addHeader')
            ->with('Authorization', 'Bearer ' . self::TOKEN);

        $this->token->addAuthorizationHeader($request);
    }
}
