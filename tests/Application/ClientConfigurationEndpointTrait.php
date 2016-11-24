<?php

namespace OAuth2\Test\Application;

use OAuth2\Middleware\Pipe;
use OAuth2\Response\OAuth2ExceptionMiddleware;
use OAuth2\Test\Stub\ClientRepository;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use OAuth2\Endpoint\ClientConfiguration\ClientConfigurationEndpoint;
use OAuth2\TokenType\BearerToken;

trait ClientConfigurationEndpointTrait
{
    abstract public function getOAuth2ResponseMiddleware(): OAuth2ExceptionMiddleware;
    abstract public function getCommandBus(): MessageBusSupportingMiddleware;
    abstract public function getClientRepository(): ClientRepository;

    /**
     * @var null|ClientConfigurationEndpoint
     */
    private $clientConfigurationEndpoint = null;

    /**
     * @return ClientConfigurationEndpoint
     */
    public function getClientConfigurationEndpoint(): ClientConfigurationEndpoint
    {
        if (null === $this->clientConfigurationEndpoint) {
            $this->clientConfigurationEndpoint = new ClientConfigurationEndpoint(
                new BearerToken(),
                $this->getCommandBus(),
                $this->getClientRepository()
            );
        }

        return $this->clientConfigurationEndpoint;
    }

    /**
     * @var null|Pipe
     */
    private $clientConfigurationPipe = null;

    /**
     * @return Pipe
     */
    public function getClientConfigurationPipe(): Pipe
    {
        if (null === $this->clientConfigurationPipe) {
            $this->clientConfigurationPipe = new Pipe();

            $this->clientConfigurationPipe->appendMiddleware($this->getOAuth2ResponseMiddleware());
            $this->clientConfigurationPipe->appendMiddleware($this->getClientConfigurationEndpoint());
        }

        return $this->clientConfigurationPipe;
    }
}
