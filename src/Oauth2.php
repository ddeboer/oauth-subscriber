<?php

namespace GuzzleHttp\Subscriber\Oauth;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Event\RequestEvents;
use GuzzleHttp\Event\SubscriberInterface;

/**
 * OAuth2 subscriber
 */
final class Oauth2 implements SubscriberInterface
{
    /**
     * @var array
     */
    private $events = [
        'before' => ['onBefore', RequestEvents::SIGN_REQUEST],
        'error'  => ['onError', RequestEvents::EARLY],
    ];

    /**
     * @var ClientInterface
     */
    private $tokenClient;

    /**
     * @var callable
     */
    private $tokenGrantor;

    /**
     * @param callable         $tokenGrantor
     * @param ClientInterface  $tokenClient
     */
    public function __construct(callable $tokenGrantor, ClientInterface $tokenClient)
    {
        $this->tokenGrantor = $tokenGrantor;
        $this->tokenClient  = $tokenClient;
    }

    /**
     * (non-PHPdoc)
     * @see \GuzzleHttp\Event\SubscriberInterface::getEvents()
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * If the event authorization is 'oauth2', add a token to the request header.
     *
     * @param BeforeEvent $event the event to handle
     */
    public function onBefore(BeforeEvent $event)
    {
        $request = $event->getRequest();
        $grantor = $this->tokenGrantor;

        // Only sign requests using "auth"="oauth2"
        if ($request->getConfig()->get('auth') == 'oauth2'
            && $token = $grantor($this->tokenClient)
        ) {
            $token->addAuthorizationHeader($request);
        }
    }

    /**
     * If the response code is 401 and the request has not been retried, retry with
     * an access token.
     *
     * @param ErrorEvent $event the event to handle
     */
    public function onError(ErrorEvent $event)
    {
        $request = $event->getRequest();
        $config  = $request->getConfig();
        $grantor = $this->tokenGrantor;

        if (401 == $event->getResponse()->getStatusCode() && !$config->get('retried')) {
            $config->set('retried', true);
            if ($token = $grantor($this->tokenClient)) {
                $token->addAuthorizationHeader($request);
                $event->intercept($event->getClient()->send($request));
            }
        }
    }
}
