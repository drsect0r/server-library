<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\Test\Functional;

use Jose\Factory\JWEFactory;
use Jose\Factory\JWSFactory;
use Jose\Object\JWK;
use OAuth2\Exception\BaseExceptionInterface;
use OAuth2\Exception\ExceptionManagerInterface;
use OAuth2\Test\Base;
use Zend\Diactoros\Response;

/**
 * @group ClientCredentialsGrantType
 */
class JWTBearerGrantTypeTest extends Base
{
    public function testGrantTypeAuthorizedForJWTClientButBadAudience()
    {
        $response = new Response();
        $jwk1 = new JWK([
            'kid' => 'JWK1',
            'use' => 'enc',
            'kty' => 'oct',
            'k'   => 'ABEiM0RVZneImaq7zN3u_wABAgMEBQYHCAkKCwwNDg8',
        ]);
        $jwk2 = new JWK([
            'kid' => 'JWK2',
            'use' => 'sig',
            'kty' => 'oct',
            'k'   => 'AyM1SysPpbyDfgZld3umj1qzKObwVMkoqQ-EstJQLr_T-1qS0gZH75aKtMN3Yj0iPS4hcgUuTwjAzZr1Z9CAow',
        ]);

        $jws = JWSFactory::createJWSToCompactJSON([
                'exp' => time() + 3600,
                'aud' => 'Bad audience',
                'iss' => 'My JWT issuer',
                'sub' => $this->getClientManager()->getClientByName('jwt1')->getPublicId(),
            ],
            $jwk2,
            [
                'kid' => 'JWK2',
                'cty' => 'JWT',
                'alg' => 'HS512',
            ]
        );

        $jwe = JWEFactory::createJWEToCompactJSON(
            $jws,
            $jwk1,
            [
                'kid' => 'JWK1',
                'cty' => 'JWT',
                'alg' => 'A256KW',
                'enc' => 'A256CBC-HS512',
                'exp' => time() + 3600,
                'aud' => $this->getIssuer(),
                'iss' => 'My JWT issuer',
                'sub' => $this->getClientManager()->getClientByName('jwt1')->getPublicId(),
            ]
        );

        $request = $this->createRequest(
            '/',
            'POST',
            [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwe,
            ],
            ['HTTPS' => 'on']
        );

        try {
            $this->getTokenEndpoint()->getAccessToken($request, $response);
        } catch (BaseExceptionInterface $e) {
            $this->assertEquals(ExceptionManagerInterface::ERROR_INVALID_REQUEST, $e->getMessage());
            $this->assertEquals('Bad audience.', $e->getDescription());
        }
    }

    public function testSignedAssertionForJWTClient()
    {
        $response = new Response();
        $jwk2 = new JWK([
            'kid' => 'JWK2',
            'use' => 'sig',
            'kty' => 'oct',
            'k'   => 'AyM1SysPpbyDfgZld3umj1qzKObwVMkoqQ-EstJQLr_T-1qS0gZH75aKtMN3Yj0iPS4hcgUuTwjAzZr1Z9CAow',
        ]);

        $jws = JWSFactory::createJWSToCompactJSON([
                'exp' => time() + 3600,
                'aud' => $this->getIssuer(),
                'iss' => 'My JWT issuer',
                'sub' => $this->getClientManager()->getClientByName('jwt1')->getPublicId(),
            ],
            $jwk2,
            [
                'kid' => 'JWK2',
                'cty' => 'JWT',
                'alg' => 'HS512',
            ]
        );

        $request = $this->createRequest(
            '/',
            'POST',
            [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jws,
            ],
            ['HTTPS' => 'on']
        );

        try {
            $this->getTokenEndpoint()->getAccessToken($request, $response);
        } catch (BaseExceptionInterface $e) {
            $this->assertEquals(ExceptionManagerInterface::ERROR_INVALID_REQUEST, $e->getMessage());
            $this->assertEquals('The assertion must be encrypted.', $e->getDescription());
        }
    }

    public function testEncryptedAndSignedAssertionForJWTClient()
    {
        $response = new Response();
        $jwk1 = new JWK([
            'kid' => 'JWK1',
            'use' => 'enc',
            'kty' => 'oct',
            'k'   => 'ABEiM0RVZneImaq7zN3u_wABAgMEBQYHCAkKCwwNDg8',
        ]);
        $jwk2 = new JWK([
            'kid' => 'JWK2',
            'use' => 'sig',
            'kty' => 'oct',
            'k'   => 'AyM1SysPpbyDfgZld3umj1qzKObwVMkoqQ-EstJQLr_T-1qS0gZH75aKtMN3Yj0iPS4hcgUuTwjAzZr1Z9CAow',
        ]);

        $jws = JWSFactory::createJWSToCompactJSON([
                'exp' => time() + 3600,
                'aud' => $this->getIssuer(),
                'iss' => 'My JWT issuer',
                'sub' => $this->getClientManager()->getClientByName('jwt1')->getPublicId(),
            ],
            $jwk2,
            [
                'kid' => 'JWK2',
                'cty' => 'JWT',
                'alg' => 'HS512',
            ]
        );

        $jwe = JWEFactory::createJWEToCompactJSON(
            $jws,
            $jwk1,
            [
                'kid' => 'JWK1',
                'alg' => 'A256KW',
                'enc' => 'A256CBC-HS512',
                'exp' => time() + 3600,
                'aud' => $this->getIssuer(),
                'iss' => 'My JWT issuer',
                'sub' => $this->getClientManager()->getClientByName('jwt1')->getPublicId(),
            ]
        );

        $request = $this->createRequest(
            '/',
            'POST',
            [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwe,
                'scope'      => 'scope1',
            ],
            ['HTTPS' => 'on']
        );

        $this->getTokenEndpoint()->getAccessToken($request, $response);
        $response->getBody()->rewind();

        $this->assertEquals('application/json', $response->getHeader('Content-Type')[0]);
        $this->assertEquals('no-store, private', $response->getHeader('Cache-Control')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('no-cache', $response->getHeader('Pragma')[0]);
        $this->assertRegExp('{"access_token":"[^"]+","scope":"scope1","token_type":"Bearer","foo":"bar"}', $response->getBody()->getContents());
    }

    public function testEncryptedAndSignedAssertionForPasswordClient()
    {
        $response = new Response();
        $jwk1 = new JWK([
            'kid' => 'JWK1',
            'use' => 'enc',
            'kty' => 'oct',
            'k'   => 'ABEiM0RVZneImaq7zN3u_wABAgMEBQYHCAkKCwwNDg8',
        ]);
        $jwk2 = new JWK([
            'kty' => 'oct',
            'k'   => 'secret',
        ]);

        $jws = JWSFactory::createJWSToCompactJSON([
                'exp' => time() + 3600,
                'aud' => $this->getIssuer(),
                'iss' => 'My JWT issuer',
                'sub' => $this->getClientManager()->getClientByName('bar')->getPublicId(),
            ],
            $jwk2,
            [
                'kid' => 'JWK2',
                'cty' => 'JWT',
                'alg' => 'HS512',
            ]
        );

        $jwe = JWEFactory::createJWEToCompactJSON(
            $jws,
            $jwk1,
            [
                'alg' => 'A256KW',
                'enc' => 'A256CBC-HS512',
            ]
        );

        $request = $this->createRequest(
            '/',
            'POST',
            [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwe,
            ],
            ['HTTPS' => 'on']
        );

        $this->getTokenEndpoint()->getAccessToken($request, $response);
        $response->getBody()->rewind();

        $this->assertEquals('application/json', $response->getHeader('Content-Type')[0]);
        $this->assertEquals('no-store, private', $response->getHeader('Cache-Control')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('no-cache', $response->getHeader('Pragma')[0]);
        $this->assertRegExp('{"access_token":"[^"]+","expires_in":[0-9]+,"token_type":"Bearer","foo":"bar"}', $response->getBody()->getContents());
    }
}
