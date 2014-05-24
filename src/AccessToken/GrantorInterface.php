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
     * @param  ClientInterface $client the client
     * @param  array           $config the POST body configuration
     * @return AccessToken
     */
    public function getToken(ClientInterface $client, array $config = []);
}
