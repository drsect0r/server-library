<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\Grant;

use OAuth2\Endpoint\Token\GrantTypeResponse;
use OAuth2\Response\OAuth2Exception;
use OAuth2\Response\OAuth2ResponseFactoryManagerInterface;
use Psr\Http\Message\ServerRequestInterface;

class ClientCredentialsGrantType implements GrantTypeInterface
{
    /**
     * @var bool
     */
    private $issueRefreshTokenWithAccessToken = false;

    /**
     * {@inheritdoc}
     */
    public function getAssociatedResponseTypes(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getGrantType(): string
    {
        return 'client_credentials';
    }

    /**
     * {@inheritdoc}
     */
    public function checkTokenRequest(ServerRequestInterface $request)
    {
        $client = $request->getAttribute('client');
        if ($client->isPublic()) {
            throw new OAuth2Exception(400, ['error' => OAuth2ResponseFactoryManagerInterface::ERROR_INVALID_CLIENT, 'error_description' => 'The client is not a confidential client.']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepareTokenResponse(ServerRequestInterface $request, GrantTypeResponse $grantTypeResponse): GrantTypeResponse
    {
        // Nothing to do
        return $grantTypeResponse;
    }

    /**
     * {@inheritdoc}
     */
    public function grant(ServerRequestInterface $request, GrantTypeResponse $grantTypeResponse): GrantTypeResponse
    {
        if (true === $this->isRefreshTokenIssuedWithAccessToken() ) {
            $grantTypeResponse = $grantTypeResponse->withMetadata('refresh_token', true);
        }

        $grantTypeResponse = $grantTypeResponse->withResourceOwner($grantTypeResponse->getClient());

        return $grantTypeResponse;
    }

    /**
     * @return bool
     */
    public function isRefreshTokenIssuedWithAccessToken()
    {
        return $this->issueRefreshTokenWithAccessToken;
    }

    public function enableRefreshTokenIssuanceWithAccessToken()
    {
        $this->issueRefreshTokenWithAccessToken = true;
    }

    public function disableRefreshTokenIssuanceWithAccessToken()
    {
        $this->issueRefreshTokenWithAccessToken = false;
    }
}
