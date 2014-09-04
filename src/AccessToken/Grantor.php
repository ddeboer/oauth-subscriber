<?php

namespace GuzzleHttp\Subscriber\Oauth\AccessToken;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Collection;

/**
 * An OAuth access token grantor
 */
final class Grantor implements GrantorInterface
{
    /**
     * @var string[]
     */
    private $defaults = [
        'client_secret' => '',
        'scope'         => '',
    ];

    /**
     * @var string[]
     */
    private $required;

    /**
     * @param string $grantType the grant type
     * @param array  $required  required POST parameters
     * @param array  $defaults  default POST parameter values
     */
    private function __construct($grantType, array $required = [], array $defaults = [])
    {
        $this->required  = $required;
        $this->defaults += $defaults + ['grant_type' => $grantType];
    }

    /**
     * Build a client credentials token grantor
     *
     * @param  array $defaults default POST body values
     * @return \GuzzleHttp\Subscriber\Oauth\AccessToken\Grantor
     */
    public static function clientCredentials(array $defaults = [])
    {
        return new static('client_credentials', ['client_id'], $defaults);
    }

    /**
     * Build a password token grantor
     *
     * @param  array $defaults default POST body values
     * @return \GuzzleHttp\Subscriber\Oauth\AccessToken\Grantor
     */
    public static function password(array $defaults = [])
    {
        return new static('password', ['client_id', 'username', 'password'], $defaults);
    }

    /**
     * (non-PHPdoc)
     * @see \GuzzleHttp\Subscriber\Oauth\AccessToken\GrantorInterface::__invoke()
     */
    public function __invoke(ClientInterface $client, array $config = [])
    {
        $config   = Collection::fromConfig($config, $this->defaults, $this->required);
        $response = $client->post(null, ['body' => $config->toArray()]);

        return AccessToken::fromArray($response->json());
    }

    /**
     * Convert to a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->defaults['grant_type'];
    }
}
