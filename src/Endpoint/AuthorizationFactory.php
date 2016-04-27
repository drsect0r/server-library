<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\Endpoint;

use Assert\Assertion;
use Jose\Object\JWKSetInterface;
use OAuth2\Behaviour\HasClientManager;
use OAuth2\Behaviour\HasExceptionManager;
use OAuth2\Behaviour\HasJWTLoader;
use OAuth2\Behaviour\HasScopeManager;
use OAuth2\Client\ClientInterface;
use OAuth2\Client\ClientManagerInterface;
use OAuth2\Exception\ExceptionManagerInterface;
use OAuth2\Scope\ScopeManagerInterface;
use OAuth2\User\UserInterface;
use Jose\JWTLoader;
use Psr\Http\Message\ServerRequestInterface;

final class AuthorizationFactory
{
    use HasJWTLoader;
    use HasScopeManager;
    use HasClientManager;
    use HasExceptionManager;

    /**
     * @var bool
     */
    private $request_object_allowed = false;

    /**
     * @var bool
     */
    private $request_object_reference_allowed = false;

    /**
     * @var \Jose\Object\JWKSetInterface|null
     */
    private $key_encryption_key_set = null;

    /**
     * AuthorizationFactory constructor.
     *
     * @param \OAuth2\Scope\ScopeManagerInterface             $scope_manager
     * @param \OAuth2\Client\ClientManagerInterface $client_manager
     * @param \OAuth2\Exception\ExceptionManagerInterface     $exception_manager
     */
    public function __construct(
        ScopeManagerInterface $scope_manager,
        ClientManagerInterface $client_manager,
        ExceptionManagerInterface $exception_manager
    ) {
        $this->setScopeManager($scope_manager);
        $this->setExceptionManager($exception_manager);
        $this->setClientManager($client_manager);
    }

    /**
     * @return bool
     */
    public function isRequestObjectSupportEnabled()
    {
        return $this->request_object_allowed;
    }

    /**
     * @return bool
     */
    public function isRequestObjectReferenceSupportEnabled()
    {
        return $this->request_object_reference_allowed;
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedSignatureAlgorithms()
    {
        return null === $this->getJWTLoader() ? [] : $this->getJWTLoader()->getSupportedSignatureAlgorithms();
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedKeyEncryptionAlgorithms()
    {
        return null === $this->getJWTLoader() ? [] : $this->getJWTLoader()->getSupportedKeyEncryptionAlgorithms();
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedContentEncryptionAlgorithms()
    {
        return null === $this->getJWTLoader() ? [] : $this->getJWTLoader()->getSupportedContentEncryptionAlgorithms();
    }

    /**
     * @param \Jose\JWTLoader $jwt_loader
     */
    public function enableRequestObjectSupport(JWTLoader $jwt_loader)
    {
        $this->setJWTLoader($jwt_loader);
        $this->request_object_allowed = true;
    }

    public function enableRequestObjectReferenceSupport()
    {
        Assertion::true($this->isRequestObjectSupportEnabled(), 'Request object support must be enabled first.');
        $this->request_object_reference_allowed = true;
    }

    /**
     * @param \Jose\Object\JWKSetInterface $key_encryption_key_set
     */
    public function enableEncryptedRequestObjectSupport(JWKSetInterface $key_encryption_key_set)
    {
        Assertion::true($this->isRequestObjectSupportEnabled(), 'Request object support must be enabled first.');

        $this->key_encryption_key_set = $key_encryption_key_set;
    }

    /**
     * @return bool
     */
    public function isEncryptedRequestsSupportEnabled()
    {
        return null !== $this->key_encryption_key_set;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \OAuth2\User\UserInterface               $user
     * @param bool                                     $is_authorized
     *
     * @return \OAuth2\Endpoint\Authorization
     */
    public function createFromRequest(ServerRequestInterface $request, UserInterface $user, $is_authorized)
    {
        $params = $request->getQueryParams();
        if (array_key_exists('request', $params)) {
            return $this->createFromRequestParameter($params, $user, $is_authorized);
        } elseif (array_key_exists('request_uri', $params)) {
            return $this->createFromRequestUriParameter($params, $user, $is_authorized);
        }

        return $this->createFromStandardRequest($params, $user, $is_authorized);
    }

    /**
     * @param array                      $params
     * @param \OAuth2\User\UserInterface $user
     * @param bool                       $is_authorized
     *
     * @return \OAuth2\Endpoint\Authorization
     */
    private function createFromRequestParameter(array $params, UserInterface $user, $is_authorized)
    {
        if (false === $this->isRequestObjectSupportEnabled()) {
            throw $this->getExceptionManager()->getBadRequestException(ExceptionManagerInterface::REQUEST_NOT_SUPPORTED, 'The parameter "request" is not supported.');
        }
        $request = $params['request'];
        Assertion::string($request);

        $scope = [];
        $jws = $this->loadRequest($request, $scope, $client);
        $params = array_merge($params, $jws->getClaims());
        unset($params['request']);

        return $this->createAuthorization($params, $user, $client, $scope, $is_authorized);
    }

    /**
     * @param string                              $request
     * @param \OAuth2\Client\ClientInterface|null $client
     * @param \string[]                           $scope
     *
     * @return \Jose\Object\JWEInterface|\Jose\Object\JWSInterface
     */
    private function loadRequest($request, array &$scope, ClientInterface &$client = null)
    {
        $jwt = $this->getJWTLoader()->load(
            $request,
            $this->key_encryption_key_set,
            false
        );

        try {
            Assertion::true($jwt->hasClaims(), 'The request object does not contain claims.');
            $client = $this->getClient($jwt->getClaims());
            Assertion::isInstanceOf($client, ClientInterface::class, 'Invalid client.');
            
            $public_key_set = $client->getPublicKeySet();

            Assertion::notNull($public_key_set, 'The client does not have signature capabilities.');

            $this->getJWTLoader()->verifySignature(
                $jwt,
                $public_key_set
            );
            $scope = $this->getScope($jwt->getClaims());
        } catch (\Exception $e) {
            throw $this->getExceptionManager()->getBadRequestException(ExceptionManagerInterface::INVALID_REQUEST_OBJECT, $e->getMessage());
        }

        return $jwt;
    }

    /**
     * @param array                      $params
     * @param \OAuth2\User\UserInterface $user
     * @param bool                       $is_authorized
     *
     * @return \OAuth2\Endpoint\Authorization
     */
    private function createFromRequestUriParameter(array $params, UserInterface $user, $is_authorized)
    {
        if (false === $this->isRequestObjectReferenceSupportEnabled()) {
            throw $this->getExceptionManager()->getBadRequestException(ExceptionManagerInterface::REQUEST_URI_NOT_SUPPORTED, 'The parameter "request" is not supported.');
        }
        $request_uri = $params['request_uri'];
        Assertion::url($request_uri, 'Invalid URL.');

        $content = $this->downloadContent($request_uri);
        $scope = [];
        $jws = $this->loadRequest($content, $scope, $client);
        $params = array_merge($params, $jws->getClaims());
        unset($params['request_uri']);

        return $this->createAuthorization($params, $user, $client, $scope, $is_authorized);
    }

    /**
     * @param array                      $params
     * @param \OAuth2\User\UserInterface $user
     * @param bool                       $is_authorized
     *
     * @return \OAuth2\Endpoint\Authorization
     */
    private function createFromStandardRequest(array $params, UserInterface $user, $is_authorized)
    {
        $client = $this->getClient($params);
        $scope = $this->getScope($params);

        return $this->createAuthorization($params, $user, $client, $scope, $is_authorized);
    }

    /**
     * @param array                          $params
     * @param \OAuth2\User\UserInterface     $user
     * @param \OAuth2\Client\ClientInterface $client
     * @param string[]                       $scope
     * @param bool                           $is_authorized
     *
     * @return \OAuth2\Endpoint\Authorization
     */
    private function createAuthorization(array $params, UserInterface $user, ClientInterface $client, array $scope, $is_authorized)
    {
        return new Authorization($params, $user, $is_authorized, $client, $scope);
    }

    /**
     * @param array $params
     *
     * @throws \OAuth2\Exception\BaseExceptionInterface
     *
     * @return \OAuth2\Client\ClientInterface
     */
    private function getClient(array $params)
    {
        $client = array_key_exists('client_id', $params) ? $this->getClientManager()->getClient($params['client_id']) : null;
        if (!$client instanceof ClientInterface) {
            throw $this->getExceptionManager()->getBadRequestException(ExceptionManagerInterface::INVALID_REQUEST, 'Parameter "client_id" missing or invalid.');
        }

        return $client;
    }

    /**
     * @param array $params
     *
     * @return \string[]
     */
    private function getScope(array $params)
    {
        if (array_key_exists('scope', $params)) {
            return $this->getScopeManager()->convertToArray($params['scope']);
        }

        return [];
    }

    /**
     * @param string $url
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    private function downloadContent($url)
    {
        // The URL must be a valid URL and scheme must be https
        Assertion::false(
            false === filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED),
            'Invalid URL.'
        );
        Assertion::false('https://' !==  mb_substr($url, 0, 8, '8bit'), 'Unsecured connection.');

        $params = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL            => $url,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $params);
        $content = curl_exec($ch);
        curl_close($ch);

        if (null === $content) {
            throw $this->getExceptionManager()->getBadRequestException(ExceptionManagerInterface::INVALID_REQUEST_URI, 'Unable to get content.');
        }

        return $content;
    }
}
