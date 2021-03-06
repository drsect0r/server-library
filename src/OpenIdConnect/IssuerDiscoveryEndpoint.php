<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\OpenIdConnect;

use Assert\Assertion;
use OAuth2\Behaviour\HasExceptionManager;
use OAuth2\Behaviour\HasIssuer;
use OAuth2\Behaviour\HasUserAccountManager;
use OAuth2\Exception\BaseExceptionInterface;
use OAuth2\Exception\ExceptionManagerInterface;
use OAuth2\UserAccount\UserAccountManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class IssuerDiscoveryEndpoint implements IssuerDiscoveryEndpointInterface
{
    use HasUserAccountManager;
    use HasExceptionManager;
    use HasIssuer;

    /**
     * @var string
     */
    private $computed_server;

    /**
     * IssuerDiscoveryEndpoint constructor.
     *
     * @param \OAuth2\UserAccount\UserAccountManagerInterface $user_account_manager
     * @param \OAuth2\Exception\ExceptionManagerInterface     $exception_manager
     * @param string                                          $issuer               The issuer of the resource
     * @param string                                          $server               The server URI of this discovery service
     */
    public function __construct(UserAccountManagerInterface $user_account_manager,
                                ExceptionManagerInterface $exception_manager,
                                $issuer,
                                $server
    ) {
        Assertion::url($issuer, 'The issuer must be an URL.');
        Assertion::url($server, 'The server must be an URL.');
        $this->setUserAccountManager($user_account_manager);
        $this->setExceptionManager($exception_manager);
        $this->setIssuer($issuer);
        $this->computed_server = $this->getDomain($server);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request, ResponseInterface &$response)
    {
        try {
            $this->checkRequestIsSecured($request);
            $this->checkRel($request);
            $resource = $this->getResource($request);
            $this->checkUserFromResource($resource);

            $this->populateResponse($resource, $response);
        } catch (BaseExceptionInterface $e) {
            $e->getHttpResponse($response);
        }
    }

    /**
     * @param string                              $resource
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    private function populateResponse($resource, ResponseInterface $response)
    {
        $headers = [
            'Content-Type'  => 'application/jrd+json',
            'Cache-Control' => 'no-store',
            'Pragma'        => 'no-cache',
        ];
        $response = $response->withStatus(200);
        foreach ($headers as $key => $value) {
            $response = $response->withHeader($key, $value);
        }
        $response->getBody()->write(json_encode([
            'subject' => $resource,
            'links'   => [
                [
                    'rel'  => 'http://openid.net/specs/connect/1.0/issuer',
                    'href' => $this->getIssuer(),
                ],
            ],
        ]));
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @throws \OAuth2\Exception\BadRequestExceptionInterface
     */
    private function checkRequestIsSecured(ServerRequestInterface $request)
    {
        $server_params = $request->getServerParams();

        if (empty($server_params['HTTPS']) || 'on' !== mb_strtolower($server_params['HTTPS'], '8bit')) {
            throw $this->getExceptionManager()->getBadRequestException(ExceptionManagerInterface::ERROR_INVALID_REQUEST, 'The request must be secured.');
        }
    }

    /**
     * @param string $resource
     *
     * @return string
     */
    protected function getUsernameFromResource($resource)
    {
        return $resource;
    }

    /**
     * @param string $resource
     *
     * @throws \OAuth2\Exception\BadRequestExceptionInterface
     */
    private function checkUserFromResource($resource)
    {
        $user = $this->getUserAccountManager()->getUserAccountFromResource($resource);
        if (null === $user) {
            throw $this->getExceptionManager()->getBadRequestException(ExceptionManagerInterface::ERROR_INVALID_REQUEST, 'The resource is not supported by this server.');
        }
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @throws \OAuth2\Exception\BadRequestExceptionInterface
     */
    private function checkRel(ServerRequestInterface $request)
    {
        if (!array_key_exists('rel', $request->getQueryParams())) {
            throw $this->getExceptionManager()->getBadRequestException(ExceptionManagerInterface::ERROR_INVALID_REQUEST, 'The parameter "rel" is mandatory.');
        }
        if ($request->getQueryParams()['rel'] !== 'http://openid.net/specs/connect/1.0/issuer') {
            throw $this->getExceptionManager()->getBadRequestException(ExceptionManagerInterface::ERROR_INVALID_REQUEST, 'Unsupported "rel" parameter value.');
        }
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @throws \OAuth2\Exception\BadRequestExceptionInterface
     *
     * @return string
     */
    private function getResource(ServerRequestInterface $request)
    {
        if (!array_key_exists('resource', $request->getQueryParams())) {
            throw $this->getExceptionManager()->getBadRequestException(ExceptionManagerInterface::ERROR_INVALID_REQUEST, 'The parameter "resource" is mandatory.');
        }

        $resource = $request->getQueryParams()['resource'];
        $this->checkResource($resource);

        return $resource;
    }

    /**
     * @param string $resource
     *
     * @throws \OAuth2\Exception\BadRequestExceptionInterface
     *
     * @return string
     */
    private function checkResource($resource)
    {
        if ('acct:' === mb_substr($resource, 0, 5, 'utf-8')) {
            $resource = mb_substr($resource, 5, null, 'utf-8');
        }
        $at = mb_strpos($resource, '@', null, 'utf-8');
        if (0 === $at) {
            throw $this->getExceptionManager()->getBadRequestException(ExceptionManagerInterface::ERROR_INVALID_REQUEST, 'Unsupported Extensible Resource Identifier (XRI) resource value.');
        } elseif (false !== $at) {
            $this->checkEmailResource($resource);
        } else {
            $this->checkUriResource($resource);
        }

        return $resource;
    }

    /**
     * @param string $resource
     */
    private function checkEmailResource($resource)
    {
        list(, $domain) = explode('@', $resource);
        $this->checkDomain($domain);
    }

    /**
     * @param string $resource
     *
     * @return string
     */
    private function checkUriResource($resource)
    {
        $domain = $this->getDomain($resource);
        $this->checkDomain($domain);
    }

    /**
     * @param string $uri
     *
     * @throws \OAuth2\Exception\BadRequestExceptionInterface
     *
     * @return string
     */
    private function getDomain($uri)
    {
        $parsed_uri = parse_url($uri);

        if (false === array_key_exists('host', $parsed_uri)) {
            throw $this->getExceptionManager()->getBadRequestException(ExceptionManagerInterface::ERROR_INVALID_REQUEST, 'Unsupported resource value. Must be compliant with RFC3986 (URI) or RFC5322 (e-mail).');
        }
        $host = $parsed_uri['host'];
        if (array_key_exists('port', $parsed_uri)) {
            $host = sprintf('%s:%s', $host, $parsed_uri['port']);
        }

        return $host;
    }

    /**
     * @param string $domain
     *
     * @throws \OAuth2\Exception\BadRequestExceptionInterface
     */
    private function checkDomain($domain)
    {
        if ($domain !== $this->computed_server) {
            throw $this->getExceptionManager()->getBadRequestException(ExceptionManagerInterface::ERROR_INVALID_REQUEST, 'Unsupported domain.');
        }
    }
}
