<?php

namespace GuzzleHttp\Subscriber\Oauth\AccessToken;

use DateTime;
use DateInterval;
use JsonSerializable;
use Serializable;
use GuzzleHttp\Message\RequestInterface;

/**
 * Models OAuth2 access tokens with additional functionality.
 */
final class AccessToken implements JsonSerializable, Serializable
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var DateTime
     */
    private $expires;

    /**
     * @var DateInterval
     */
    private $expiresIn;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $scope;

    /**
     * @param string $token     the access token
     * @param int    $expiresIn the number of seconds before expiration
     * @param string $type      the token type
     * @param string $scope     the token scope
     */
    public function __construct($token, $expiresIn, $type, $scope)
    {
        $this->token = (string) $token;
        $this->type  = (string) $type;
        $this->scope = (string) $scope;
        $this->setExpiresIn($expiresIn);
    }

    /**
     * Builds an instance from an array of data
     *
     * @param array $data the paramater array
     * @return \GuzzleHttp\Subscriber\Oauth\AccessToken\AccessToken
     */
    public static function fromArray(array $data)
    {
        extract($data);
        return new static($access_token, $expires_in, $token_type, $scope);
    }

    /**
     * Has the token expired?
     *
     * @param  DateTime $onDate optional, defaults to 'now'
     * @return bool
     */
    public function isValid(DateTime $onDate = null)
    {
        return ($this->expires >= $onDate ?: new DateTime);
    }

    /**
     * Check the token type for a match
     *
     * @param  string $type the type to check
     * @return bool
     */
    public function hasType($type)
    {
        return ($this->type == (string) $type);
    }

    /**
     * Check the scope for a match
     *
     * @param  string $scope the scope to check
     * @return bool
     */
    public function hasScope($scope)
    {
        return ($this->scope == (string) $scope);
    }

    /**
     * Add an authorize header to the request
     *
     * @param RequestInterface $request the request to modify
     */
    public function addAuthorizationHeader(RequestInterface $request)
    {
        $request->addHeader('Authorization', 'Bearer ' . $this->token);
    }

    /**
     * (non-PHPdoc)
     * @see JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
        return [
            'access_token' => $this->token,
            'expires_in'   => $this->expiresIn->s,
            'token_type'   => $this->type,
            'scope'        => $this->scope,
        ];
    }

    /**
     * (non-PHPdoc)
     * @see Serializable::serialize()
     */
    public function serialize()
    {
        return serialize($this->jsonSerialize());
    }

    /**
     * (non-PHPdoc)
     * @see Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        extract(unserialize($serialized));
        $this->token = $access_token;
        $this->type  = $token_type;
        $this->scope = $scope;
        $this->setExpiresIn($expires_in);
    }

    /**
     * Build the expires date and expiresIn interval
     *
     * @param int $seconds the number of seconds before expiration
     */
    private function setExpiresIn($seconds)
    {
        $this->expires   = new DateTime;
        $this->expiresIn = new DateInterval("PT{$seconds}S");
        $this->expires->add($this->expiresIn);
    }
}
