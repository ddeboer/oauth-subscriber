<?php

namespace GuzzleHttp\Subscriber\Oauth\AccessToken;

use GuzzleHttp\ClientInterface;

/**
 * OAuth2 access token grantor
 */
interface GrantorInterface
{
    /**
     * Get access token
     *
     * @param  ClientInterface $client the token client
     * @param  array           $config the POST body configuration
     * @return \GuzzleHttp\Subscriber\Oauth\AccessToken\AccessToken
     */
    public function getToken(ClientInterface $client, array $config = []);
}
