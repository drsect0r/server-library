<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\OpenIDConnect;

use Assert\Assertion;
use Base64Url\Base64Url;
use Jose\ClaimChecker\ClaimCheckerManager;
use Jose\Factory\DecrypterFactory;
use Jose\Factory\VerifierFactory;
use Jose\Object\JWKInterface;
use Jose\Object\JWKSet;
use OAuth2\Behaviour\HasJWTCreator;
use OAuth2\Behaviour\HasJWTLoader;
use OAuth2\Client\ClientInterface;
use OAuth2\Client\TokenLifetimeExtensionInterface;
use OAuth2\EndUser\EndUserInterface;
use OAuth2\Exception\ExceptionManagerInterface;
use OAuth2\Util\JWTCreator;
use OAuth2\Util\JWTLoader;

class IdTokenManager implements IdTokenManagerInterface
{
    use HasJWTLoader;
    use HasJWTCreator;

    /**
     * @var int
     */
    private $id_token_lifetime = 3600;

    /**
     * @var string
     */
    private $issuer;

    /**
     * @var string
     */
    private $signature_algorithm;

    /**
     * IdTokenManager constructor.
     *
     * @param \OAuth2\Exception\ExceptionManagerInterface $exception_manager
     * @param string                                      $signature_algorithm
     * @param \Jose\Object\JWKInterface                   $signature_key
     * @param string                                      $issuer
     */
    public function __construct(ExceptionManagerInterface $exception_manager,
                                $signature_algorithm,
                                JWKInterface $signature_key,
                                $issuer
    ) {
        Assertion::string($signature_algorithm);
        Assertion::string($issuer);
        $this->issuer = $issuer;
        $this->signature_algorithm = $signature_algorithm;

        $key_set = new JWKSet();
        $key_set = $key_set->addKey($signature_key);
        $this->setJWTLoader(new JWTLoader(
            new ClaimCheckerManager(),
            VerifierFactory::createVerifier([$signature_algorithm]),
            DecrypterFactory::createDecrypter([]),
            $exception_manager,
            $key_set,
            false
        ));
        $this->setJWTCreator(new JWTCreator(
            $signature_algorithm,
            $signature_key
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function createIdToken(ClientInterface $client, EndUserInterface $end_user, array $id_token_claims = [], $access_token = null, $auth_code = null)
    {
        $id_token = $this->createEmptyIdToken();
        $exp = time() + $this->getLifetime($client);

        $headers = [
            'typ'       => 'JWT',
            'alg'       => $this->getSignatureAlgorithm(),
        ];

        $payload = [
            'iss'       => $this->issuer,
            'sub'       => $end_user->getPublicId(),
            'aud'       => $client->getPublicId(),
            'iat'       => time(),
            'nbf'       => time(),
            'exp'       => $exp,
            'auth_time' => $end_user->getLastLoginAt(),
        ];

        if (null !== $access_token) {
            $payload['at_hash'] = $this->getHash($access_token);
        }
        if (null !== $auth_code) {
            $payload['c_hash'] = $this->getHash($auth_code);
        }
        if (!empty($id_token_claims)) {
            $payload = array_merge($payload, $id_token_claims);
        }
        $jws = $this->getJWTCreator()->createJWT($payload, $headers, false);

        $id_token->setExpiresAt($exp);
        $id_token->setClientPublicId($client->getPublicId());
        $id_token->setResourceOwnerPublicId($end_user->getPublicId());
        $id_token->setToken($jws);

        $this->saveIdToken($id_token);

        return $id_token;
    }

    /**
     * {@inheritdoc}
     */
    public function revokeIdToken(IdTokenInterface $token)
    {
        //Not supported
    }

    /**
     * {@inheritdoc}
     */
    public function getIdToken($id_token)
    {
        $jws = $this->getJWTLoader()->load($id_token);

        $token = $this->createEmptyIdToken();
        $token->setToken($id_token);
        $token->setJWS($jws);
        $token->setExpiresAt($jws->getClaim('exp'));
        $token->setClientPublicId($jws->getClaim('aud'));
        $token->setResourceOwnerPublicId($jws->getClaim('sub'));
        $token->setScope([]);
        $token->setAccessTokenHash($jws->hasClaim('at_hash') ? $jws->getClaim('at_hash') : null);
        $token->setAuthorizationCodeHash($jws->hasClaim('c_hash') ? $jws->getClaim('c_hash') : null);
        $token->setNonce($jws->hasClaim('nonce') ? $jws->getClaim('nonce') : null);

        return $token;
    }

    /**
     * @return \OAuth2\OpenIDConnect\IdTokenInterface
     */
    protected function createEmptyIdToken()
    {
        return new IdToken();
    }

    /**
     * @param \OAuth2\OpenIDConnect\IdTokenInterface $id_token
     */
    protected function saveIdToken(IdTokenInterface $id_token)
    {
        //Nothing to do
    }

    /**
     * @param string $token
     *
     * @return string
     */
    private function getHash($token)
    {
        return Base64Url::encode(substr(hash($this->getHashMethod(), $token, true), 0, $this->getHashSize()));
    }

    /**
     * @throws \OAuth2\Exception\BaseExceptionInterface
     *
     * @return string
     */
    private function getHashMethod()
    {
        switch ($this->signature_algorithm) {
            case 'HS256':
            case 'ES256':
            case 'RS256':
            case 'PS256':
                return 'sha256';
            case 'HS384':
            case 'ES384':
            case 'RS384':
            case 'PS384':
                return 'sha384';
            case 'HS512':
            case 'ES512':
            case 'RS512':
            case 'PS512':
                return 'sha512';
            default:
                throw new \InvalidArgumentException(sprintf('Algorithm "%s" is not supported', $this->signature_algorithm));
        }
    }

    /**
     * @throws \OAuth2\Exception\BaseExceptionInterface
     *
     * @return int
     */
    private function getHashSize()
    {
        switch ($this->signature_algorithm) {
            case 'HS256':
            case 'ES256':
            case 'RS256':
            case 'PS256':
                return 128 / 8;
            case 'HS384':
            case 'ES384':
            case 'RS384':
            case 'PS384':
                return 192 / 8;
            case 'HS512':
            case 'ES512':
            case 'RS512':
            case 'PS512':
                return 256 / 8;
            default:
                throw new \InvalidArgumentException(sprintf('Algorithm "%s" is not supported', $this->signature_algorithm));
        }
    }

    /**
     * @param \OAuth2\Client\ClientInterface $client Client
     *
     * @return int
     */
    private function getLifetime(ClientInterface $client)
    {
        $lifetime = $this->getIdTokenLifetime();
        if ($client instanceof TokenLifetimeExtensionInterface && is_int($_lifetime = $client->getTokenLifetime('id_token'))) {
            return $_lifetime;
        }

        return $lifetime;
    }

    /**
     * @throws \OAuth2\Exception\BaseExceptionInterface
     *
     * @return string
     */
    private function getSignatureAlgorithm()
    {
        return $this->signature_algorithm;
    }

    /**
     * @return int
     */
    public function getIdTokenLifetime()
    {
        return $this->id_token_lifetime;
    }

    /**
     * @param int $id_token_lifetime
     */
    public function setIdTokenLifetime($id_token_lifetime)
    {
        Assertion::integer($id_token_lifetime);
        Assertion::greaterThan($id_token_lifetime, 0);
        $this->id_token_lifetime = $id_token_lifetime;
    }
}