<?php

namespace OAuth2\Client;

use OAuth2\Behaviour\HasExceptionManager;
use OAuth2\Exception\ExceptionManagerInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class PublicClientManager implements ClientManagerInterface
{
    use HasExceptionManager;

    /**
     * {@inheritdoc}
     */
    public function getSchemesParameters()
    {
        return [];
    }

    /**
     * @return array
     */
    abstract protected function findClientMethods();

    /**
     * {@inheritdoc}
     */
    public function findClient(ServerRequestInterface $request)
    {
        $methods = $this->findClientMethods();
        $result = [];

        foreach ($methods as $method) {
            $data = $this->$method($request);
            if (null !== $data) {
                $result[] = $data;
            }
        }

        $client = $this->checkResult($result);
        if (null === $client) {
            return $client;
        }

        return $client;
    }

    /**
     * @param array $result
     *
     * @throws \OAuth2\Exception\BaseExceptionInterface
     *
     * @return null|\OAuth2\Client\ClientInterface|string
     */
    private function checkResult(array $result)
    {
        if (count($result) > 1) {
            throw $this->getExceptionManager()->getException(ExceptionManagerInterface::BAD_REQUEST, ExceptionManagerInterface::INVALID_REQUEST, 'Only one authentication method may be used to authenticate the client.');
        }

        if (count($result) < 1) {
            return;
        }

        $client = $this->getClient($result[0]);

        if (!$client instanceof PublicClientInterface) {
            throw $this->getExceptionManager()->getException(ExceptionManagerInterface::AUTHENTICATE, ExceptionManagerInterface::INVALID_CLIENT, 'Client authentication failed.', ['schemes' => $this->getSchemesParameters()]);
        }

        return $client;
    }
}