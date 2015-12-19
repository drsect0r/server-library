<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2015 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\Endpoint\TokenType;

use Jose\Object\JWTInterface;
use OAuth2\Behaviour\HasAccessTokenManager;
use OAuth2\Behaviour\HasConfiguration;
use OAuth2\Behaviour\HasRefreshTokenManager;
use OAuth2\Configuration\ConfigurationInterface;
use OAuth2\Token\AccessTokenInterface;
use OAuth2\Token\AccessTokenManagerInterface;
use OAuth2\Token\RefreshTokenInterface;
use OAuth2\Token\RefreshTokenManagerInterface;
use OAuth2\Token\TokenInterface;

final class AccessToken implements IntrospectionTokenTypeInterface, RevocationTokenTypeInterface
{
    use HasAccessTokenManager;
    use HasRefreshTokenManager;
    use HasConfiguration;

    /**
     * AccessToken constructor.
     *
     * @param \OAuth2\Configuration\ConfigurationInterface    $configuration
     * @param \OAuth2\Token\AccessTokenManagerInterface       $access_token_manager
     * @param \OAuth2\Token\RefreshTokenManagerInterface|null $refresh_token_manager
     */
    public function __construct(ConfigurationInterface $configuration, AccessTokenManagerInterface $access_token_manager, RefreshTokenManagerInterface $refresh_token_manager = null)
    {
        $this->setConfiguration($configuration);
        $this->setAccessTokenManager($access_token_manager);
        if ($refresh_token_manager instanceof RefreshTokenManagerInterface) {
            $this->setRefreshTokenManager($refresh_token_manager);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenTypeHint()
    {
        return 'access_token';
    }

    /**
     * {@inheritdoc}
     */
    public function getToken($token)
    {
        return $this->getAccessTokenManager()->getAccessToken($token);
    }

    /**
     * {@inheritdoc}
     */
    public function revokeToken(TokenInterface $token)
    {
        if ($token instanceof AccessTokenInterface) {
            if (true === $this->getConfiguration()->get('revoke_refresh_token_and_access_token', true)
                && null !== $token->getRefreshToken()
                && $this->getRefreshTokenManager() instanceof RefreshTokenManagerInterface) {
                $refresh_token = $this->getRefreshTokenManager()->getRefreshToken($token->getRefreshToken());
                if ($refresh_token instanceof RefreshTokenInterface) {
                    $this->getRefreshTokenManager()->revokeRefreshToken($refresh_token);
                }
            }
            $this->getAccessTokenManager()->revokeAccessToken($token);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function introspectToken(TokenInterface $token)
    {
        if (!$token instanceof AccessTokenInterface) {
            return [];
        }

        $result = [
            'active'     => !$token->hasExpired(),
            'client_id'  => $token->getClientPublicId(),
            'token_type' => 'access_token',
        ];
        if (!empty($token->getScope())) {
            $result['scope'] = $token->getScope();
        }
        if ($token instanceof JWTInterface) {
            $result = array_merge($result, $this->getJWTInformation($token));
        }

        return $result;
    }

    /**
     * @param \Jose\Object\JWTInterface $token
     *
     * @return array
     */
    private function getJWTInformation(JWTInterface $token)
    {
        $result = [];
        foreach (['exp', 'iat', 'nbf', 'sub', 'aud', 'iss', 'jti'] as $key) {
            if ($token->hasClaim($key)) {
                $result[$key] = $token->getClaim($key);
            }
        }

        return $result;
    }
}