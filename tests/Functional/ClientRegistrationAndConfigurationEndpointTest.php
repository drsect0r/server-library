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

use OAuth2\Client\ClientInterface;
use OAuth2\Test\Base;
use Zend\Diactoros\Response;

/**
 * @group ClientRegistrationAndConfiguration
 */
class ClientRegistrationAndConfigurationEndpointTest extends Base
{
    public function testRequestNotSecured()
    {
        $request = $this->createRequest('/', 'POST');

        $response = new Response();
        $this->getClientRegistrationEndpoint()->register($request, $response);
        $response->getBody()->rewind();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('{"error":"invalid_request","error_description":"The request must be secured.","error_uri":"https:\/\/foo.test\/Error\/BadRequest\/invalid_request"}', $response->getBody()->getContents());
    }

    public function testNoInitialAccessToken()
    {
        $request = $this->createRequest('/', 'POST', [], ['HTTPS' => 'on']);

        $response = new Response();
        $this->getClientRegistrationEndpoint()->register($request, $response);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('{"error":"invalid_request","error_description":"Initial Access Token is missing or invalid.","error_uri":"https:\/\/foo.test\/Error\/BadRequest\/invalid_request"}', $content);
    }

    public function testExpiredInitialAccessToken()
    {
        $initial_access_token = $this->getInitialAccessTokenManager()->getInitialAccessToken('INITIAL_ACCESS_TOKEN_EXPIRED');
        $request = $this->createRequest('/', 'POST', [], ['HTTPS' => 'on'], [
            'Authorization' => 'Bearer '.$initial_access_token->getToken(),
        ]);

        $response = new Response();
        $this->getClientRegistrationEndpoint()->register($request, $response);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('{"error":"invalid_request","error_description":"Initial Access Token expired.","error_uri":"https:\/\/foo.test\/Error\/BadRequest\/invalid_request"}', $content);
    }

    public function testNoAuthenticationMethod()
    {
        $initial_access_token = $this->getInitialAccessTokenManager()->getInitialAccessToken('INITIAL_ACCESS_TOKEN_VALID');
        $request = $this->createRequest('/', 'POST', [], ['HTTPS' => 'on'], [
            'Authorization' => 'Bearer '.$initial_access_token->getToken(),
        ]);

        $response = new Response();
        $this->getClientRegistrationEndpoint()->register($request, $response);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('{"error":"invalid_request","error_description":"The parameter \"token_endpoint_auth_method\" is missing.","error_uri":"https:\/\/foo.test\/Error\/BadRequest\/invalid_request"}', $content);
    }

    public function testUnsupportedAuthenticationMethod()
    {
        $initial_access_token = $this->getInitialAccessTokenManager()->getInitialAccessToken('INITIAL_ACCESS_TOKEN_VALID');
        $request = $this->createRequest('/', 'POST', ['token_endpoint_auth_method' => 'foo'], ['HTTPS' => 'on'], [
            'Authorization' => 'Bearer '.$initial_access_token->getToken(),
        ]);

        $response = new Response();
        $this->getClientRegistrationEndpoint()->register($request, $response);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('{"error":"invalid_request","error_description":"The token endpoint authentication method \"foo\" is not supported. Please use one of the following values: [\"none\",\"client_secret_basic\",\"client_secret_post\",\"client_secret_jwt\",\"private_key_jwt\"]","error_uri":"https:\/\/foo.test\/Error\/BadRequest\/invalid_request"}', $content);
    }

    public function testKeysNotSetForPrivateKeyJWTAuthenticationMethod()
    {
        $initial_access_token = $this->getInitialAccessTokenManager()->getInitialAccessToken('INITIAL_ACCESS_TOKEN_VALID');
        $request = $this->createRequest('/', 'POST', ['token_endpoint_auth_method' => 'private_key_jwt'], ['HTTPS' => 'on'], [
            'Authorization' => 'Bearer '.$initial_access_token->getToken(),
        ]);

        $response = new Response();
        $this->getClientRegistrationEndpoint()->register($request, $response);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('{"error":"invalid_request","error_description":"The parameter \"jwks\" or \"jwks_uri\" must be set.","error_uri":"https:\/\/foo.test\/Error\/BadRequest\/invalid_request"}', $content);
    }

    public function testClientCreatedWithPrivateKeyJWTAuthenticationMethod()
    {
        $keyset_as_array = json_decode('{"keys":[{"kid":"KEY","kty":"RSA","n":"3nsn7a7nHV_tfNlbH11p_9Bw6ZVDEjT6K4_GD9iTJ8wmYpbOSFFaqEdckeWa5GJThIAUrxjwXLDt41kldYuT295Rmr4EUG5fp-kzgXM4Y7TrWdevHY7kVddz8FWMU7CerJfVjqS3Z1u-V1ODdG_JtAoxdn0xBnab2a-lzCLeoPqKebJnfGKOUaJjwuKz8VkMMRPgT186z8TE-tBTgkGUF_qXF4P51_wZgsR1G-hc7p8WFzBcfX6SOKzyaRmxEhLAH-bpZwSLAG--7Hss0Rkfm7lub4xaG0V8OlePXjN0_E1u66splePcTswFQaXqIxEzWtCJKytF4OQViGNj8-ENew","e":"AQAB"}]}', true);

        $initial_access_token = $this->getInitialAccessTokenManager()->getInitialAccessToken('INITIAL_ACCESS_TOKEN_VALID');
        $request = $this->createRequest('/', 'POST', ['token_endpoint_auth_method' => 'private_key_jwt', 'jwks' => $keyset_as_array], ['HTTPS' => 'on'], [
            'Authorization' => 'Bearer '.$initial_access_token->getToken(),
        ]);

        $response = new Response();
        $this->getClientRegistrationEndpoint()->register($request, $response);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertEquals(200, $response->getStatusCode());
        $client_config = json_decode($content, true);

        $this->assertTrue(array_key_exists('client_id', $client_config));
        $this->assertTrue(array_key_exists('jwks', $client_config));
        $this->assertTrue(array_key_exists('token_endpoint_auth_method', $client_config));
        $this->assertEquals('private_key_jwt', $client_config['token_endpoint_auth_method']);
        $this->assertEquals($keyset_as_array, $client_config['jwks']);
    }

    public function testBadSectorIdentifierUriResponse()
    {
        $initial_access_token = $this->getInitialAccessTokenManager()->getInitialAccessToken('INITIAL_ACCESS_TOKEN_VALID');
        $request = $this->createRequest('/', 'POST', ['sector_identifier_uri' => 'https://www.google.com', 'token_endpoint_auth_method' => 'none'], ['HTTPS' => 'on'], [
            'Authorization' => 'Bearer '.$initial_access_token->getToken(),
        ]);

        $response = new Response();
        $this->getClientRegistrationEndpoint()->register($request, $response);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('{"error":"invalid_request","error_description":"The provided sector identifier URI is not valid: bad response.","error_uri":"https:\/\/foo.test\/Error\/BadRequest\/invalid_request"}', $content);
    }

    public function testEmptySectorIdentifierUriResponse()
    {
        $initial_access_token = $this->getInitialAccessTokenManager()->getInitialAccessToken('INITIAL_ACCESS_TOKEN_VALID');
        $request = $this->createRequest('/', 'POST', ['sector_identifier_uri' => 'https://127.0.0.1:8181/empty_sector_identifier_uri', 'token_endpoint_auth_method' => 'none'], ['HTTPS' => 'on'], [
            'Authorization' => 'Bearer '.$initial_access_token->getToken(),
        ]);

        $response = new Response();
        $this->getClientRegistrationEndpoint()->register($request, $response);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('{"error":"invalid_request","error_description":"The provided sector identifier URI is not valid: it must contain at least one URI.","error_uri":"https:\/\/foo.test\/Error\/BadRequest\/invalid_request"}', $content);
    }

    public function testSectorIdentifierUriContainsBadValues()
    {
        $initial_access_token = $this->getInitialAccessTokenManager()->getInitialAccessToken('INITIAL_ACCESS_TOKEN_VALID');
        $request = $this->createRequest('/', 'POST', ['sector_identifier_uri' => 'https://127.0.0.1:8181/sector_identifier_uri_with_bad_values', 'token_endpoint_auth_method' => 'none'], ['HTTPS' => 'on'], [
            'Authorization' => 'Bearer '.$initial_access_token->getToken(),
        ]);

        $response = new Response();
        $this->getClientRegistrationEndpoint()->register($request, $response);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('{"error":"invalid_request","error_description":"The provided sector identifier URI is not valid: it must contain only URIs.","error_uri":"https:\/\/foo.test\/Error\/BadRequest\/invalid_request"}', $content);
    }

    public function testSectorIdentifierUriContainsUriWithBadScheme()
    {
        $initial_access_token = $this->getInitialAccessTokenManager()->getInitialAccessToken('INITIAL_ACCESS_TOKEN_VALID');
        $request = $this->createRequest('/', 'POST', ['sector_identifier_uri' => 'https://127.0.0.1:8181/sector_identifier_uri_with_bad_scheme', 'token_endpoint_auth_method' => 'none'], ['HTTPS' => 'on'], [
            'Authorization' => 'Bearer '.$initial_access_token->getToken(),
        ]);

        $response = new Response();
        $this->getClientRegistrationEndpoint()->register($request, $response);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('{"error":"invalid_request","error_description":"The provided sector identifier URI is not valid: it must contain only URIs.","error_uri":"https:\/\/foo.test\/Error\/BadRequest\/invalid_request"}', $content);
    }

    public function testSectorIdentifierUriResponse()
    {
        $initial_access_token = $this->getInitialAccessTokenManager()->getInitialAccessToken('INITIAL_ACCESS_TOKEN_VALID');
        $request = $this->createRequest('/', 'POST', ['sector_identifier_uri' => 'https://127.0.0.1:8181/sector_identifier_uri', 'token_endpoint_auth_method' => 'none'], ['HTTPS' => 'on'], [
            'Authorization' => 'Bearer '.$initial_access_token->getToken(),
        ]);

        $response = new Response();
        $this->getClientRegistrationEndpoint()->register($request, $response);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertEquals(200, $response->getStatusCode());
        $client_config = json_decode($content, true);

        $this->assertTrue(array_key_exists('client_id', $client_config));
        $this->assertTrue(array_key_exists('sector_identifier_uri', $client_config));
        $this->assertTrue(array_key_exists('token_endpoint_auth_method', $client_config));
        $this->assertEquals('none', $client_config['token_endpoint_auth_method']);
        $this->assertEquals('https://127.0.0.1:8181/sector_identifier_uri', $client_config['sector_identifier_uri']);
    }

    public function testInvalidCharacterInTheScope()
    {
        $initial_access_token = $this->getInitialAccessTokenManager()->getInitialAccessToken('INITIAL_ACCESS_TOKEN_VALID');
        $request = $this->createRequest('/', 'POST', ['scope' => 'read write &é~', 'token_endpoint_auth_method' => 'none'], ['HTTPS' => 'on'], [
            'Authorization' => 'Bearer '.$initial_access_token->getToken(),
        ]);

        $response = new Response();
        $this->getClientRegistrationEndpoint()->register($request, $response);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('{"error":"invalid_request","error_description":"Invalid characters found in the \"scope\" parameter.","error_uri":"https:\/\/foo.test\/Error\/BadRequest\/invalid_request"}', $content);
    }

    public function testClientCreatedWithScopeAndScopePolicy()
    {
        $initial_access_token = $this->getInitialAccessTokenManager()->getInitialAccessToken('INITIAL_ACCESS_TOKEN_VALID');
        $request = $this->createRequest('/', 'POST', ['scope' => 'read write', 'default_scope' => 'read', 'scope_policy' => 'default', 'token_endpoint_auth_method' => 'none'], ['HTTPS' => 'on'], [
            'Authorization' => 'Bearer '.$initial_access_token->getToken(),
        ]);

        $response = new Response();
        $this->getClientRegistrationEndpoint()->register($request, $response);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertEquals(200, $response->getStatusCode());
        $client_config = json_decode($content, true);

        $this->assertTrue(array_key_exists('client_id', $client_config));
        $this->assertTrue(array_key_exists('scope', $client_config));
        $this->assertTrue(array_key_exists('scope_policy', $client_config));
        $this->assertTrue(array_key_exists('default_scope', $client_config));
        $this->assertTrue(array_key_exists('token_endpoint_auth_method', $client_config));
        $this->assertEquals('none', $client_config['token_endpoint_auth_method']);
        $this->assertEquals('read write', $client_config['scope']);
        $this->assertEquals('default', $client_config['scope_policy']);
        $this->assertEquals('read', $client_config['default_scope']);
    }

    public function testClientCreatedWithCommonParameters()
    {
        $initial_access_token = $this->getInitialAccessTokenManager()->getInitialAccessToken('INITIAL_ACCESS_TOKEN_VALID');
        $request = $this->createRequest(
            '/',
            'POST',
            [
                'scope'                      => 'read write',
                'default_scope'              => 'read',
                'scope_policy'               => 'default',
                'token_endpoint_auth_method' => 'none',
                'client_name'                => 'My Example',
                'client_name#fr'             => 'Mon Exemple',
                'software_id'                => 'ABCD0123',
                'software_version'           => '10.2',
                'redirect_uris'              => ['https://www.example.com/callback1', 'https://www.example.com/callback2'],
                'request_uris'               => ['https://www.example.com/request1', 'https://www.example.com/request2'],
                'policy_uri'                 => 'http://www.example.com/policy',
                'policy_uri#fr'              => 'http://www.example.com/vie_privee',
                'tos_uri'                    => 'http://www.example.com/tos',
                'tos_uri#fr'                 => 'http://www.example.com/termes_de_service',
            ],
            ['HTTPS' => 'on'],
            [
                'Authorization' => 'Bearer '.$initial_access_token->getToken(),
            ]
        );

        $response = new Response();
        $this->getClientRegistrationEndpoint()->register($request, $response);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertEquals(200, $response->getStatusCode());
        $client_config = json_decode($content, true);

        $this->assertTrue(array_key_exists('client_name', $client_config));
        $this->assertTrue(array_key_exists('client_name#fr', $client_config));
        $this->assertTrue(array_key_exists('policy_uri', $client_config));
        $this->assertTrue(array_key_exists('policy_uri#fr', $client_config));
        $this->assertTrue(array_key_exists('tos_uri', $client_config));
        $this->assertTrue(array_key_exists('tos_uri#fr', $client_config));
        $this->assertTrue(array_key_exists('software_id', $client_config));
        $this->assertTrue(array_key_exists('software_version', $client_config));
        $this->assertTrue(array_key_exists('client_id', $client_config));
        $this->assertTrue(array_key_exists('scope', $client_config));
        $this->assertTrue(array_key_exists('scope_policy', $client_config));
        $this->assertTrue(array_key_exists('default_scope', $client_config));
        $this->assertTrue(array_key_exists('token_endpoint_auth_method', $client_config));
        $this->assertEquals('none', $client_config['token_endpoint_auth_method']);
        $this->assertEquals('read write', $client_config['scope']);
        $this->assertEquals('default', $client_config['scope_policy']);
        $this->assertEquals('read', $client_config['default_scope']);

        $this->assertEquals('ABCD0123', $client_config['software_id']);
        $this->assertEquals('10.2', $client_config['software_version']);
        $this->assertEquals('My Example', $client_config['client_name']);
        $this->assertEquals('Mon Exemple', $client_config['client_name#fr']);
        $this->assertEquals('http://www.example.com/tos', $client_config['tos_uri']);
        $this->assertEquals('http://www.example.com/termes_de_service', $client_config['tos_uri#fr']);
        $this->assertEquals('http://www.example.com/policy', $client_config['policy_uri']);
        $this->assertEquals('http://www.example.com/vie_privee', $client_config['policy_uri#fr']);

        // We verify the client is available through the client manager
        $client = $this->getClientManager()->getClient($client_config['client_id']);
        $this->assertInstanceOf(ClientInterface::class, $client);
    }

    public function testFailedBecauseBadSoftwareStatement()
    {
        $this->enableSoftwareStatementSupport();
        $this->getClientRegistrationEndpoint()->disallowRegistrationWithoutInitialAccessToken();
        $this->getClientRegistrationEndpoint()->allowRegistrationWithoutInitialAccessToken();
        $this->getClientRegistrationEndpoint()->disallowRegistrationWithoutSoftwareStatement();
        $this->getClientRegistrationEndpoint()->allowRegistrationWithoutSoftwareStatement();
        $request = $this->createRequest('/', 'POST',
            [
                'scope'                      => 'read write',
                'default_scope'              => 'read',
                'scope_policy'               => 'default',
                'token_endpoint_auth_method' => 'none',
                'client_name'                => 'My Example',
                'client_name#fr'             => 'Mon Exemple',
                'software_statement'         => 'Hello',
            ],
            ['HTTPS' => 'on']
        );

        $response = new Response();
        $this->getClientRegistrationEndpoint()->register($request, $response);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertTrue($this->getClientRegistrationEndpoint()->isSoftwareStatementSupported());
        $this->assertEquals('{"error":"invalid_request","error_description":"Invalid Software Statement","error_uri":"https:\/\/foo.test\/Error\/BadRequest\/invalid_request"}', $content);
    }

    public function testFailedBecauseNoSoftwareStatement()
    {
        $this->getClientRegistrationEndpoint()->disallowRegistrationWithoutSoftwareStatement();
        $initial_access_token = $this->getInitialAccessTokenManager()->getInitialAccessToken('INITIAL_ACCESS_TOKEN_VALID');
        $request = $this->createRequest('/', 'POST',
            [
                'scope'                      => 'read write',
                'default_scope'              => 'read',
                'scope_policy'               => 'default',
                'token_endpoint_auth_method' => 'none',
                'client_name'                => 'My Example',
                'client_name#fr'             => 'Mon Exemple',
            ],
            ['HTTPS' => 'on'],
            [
                'Authorization' => 'Bearer '.$initial_access_token->getToken(),
            ]
        );

        $response = new Response();
        $this->getClientRegistrationEndpoint()->register($request, $response);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertTrue($this->getClientRegistrationEndpoint()->isSoftwareStatementSupported());
        $this->assertEquals('{"error":"invalid_request","error_description":"Software Statement required.","error_uri":"https:\/\/foo.test\/Error\/BadRequest\/invalid_request"}', $content);
    }

    public function testSuccessWithValidSoftwareStatement()
    {
        $this->getClientRegistrationEndpoint()->allowRegistrationWithoutInitialAccessToken();
        $software_statement = $this->getJWTCreator()->sign(
            [
                'software_id'      => 'This is my software ID',
                'software_version' => '10.2',
                'client_name'      => 'My Example',
            ],
            ['alg' => 'HS512'],
            $this->getSignatureKeySet()[0]);
        $initial_access_token = $this->getInitialAccessTokenManager()->getInitialAccessToken('INITIAL_ACCESS_TOKEN_VALID');
        $request = $this->createRequest('/', 'POST',
            [
                'scope'                           => 'read write',
                'grant_types'                     => ['authorization_code'],
                'response_types'                  => ['code', 'token'],
                'subject_type'                    => 'pairwise',
                'id_token_encrypted_response_alg' => 'RSA1_5',
                'id_token_encrypted_response_enc' => 'A256GCM',
                'default_scope'                   => 'read',
                'scope_policy'                    => 'default',
                'token_endpoint_auth_method'      => 'client_secret_jwt',
                'software_statement'              => $software_statement,
                'client_name'                     => 'My Bad Example',
                'client_name#fr'                  => 'Mon Exemple',
            ],
            ['HTTPS' => 'on'],
            [
                'Authorization' => 'Bearer '.$initial_access_token->getToken(),
            ]
        );

        $response = new Response();
        $this->getClientRegistrationEndpoint()->register($request, $response);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertEquals(200, $response->getStatusCode());
        $client_config = json_decode($content, true);

        $this->assertTrue(array_key_exists('software_id', $client_config));
        $this->assertTrue(array_key_exists('software_version', $client_config));
        $this->assertTrue(array_key_exists('software_statement', $client_config));
        $this->assertTrue(array_key_exists('client_name', $client_config));
        $this->assertTrue(array_key_exists('client_secret', $client_config));
        $this->assertTrue(array_key_exists('client_secret_expires_at', $client_config));
        $this->assertTrue(array_key_exists('grant_types', $client_config));
        $this->assertTrue(array_key_exists('response_types', $client_config));
        $this->assertTrue(array_key_exists('subject_type', $client_config));
        $this->assertTrue(array_key_exists('id_token_encrypted_response_alg', $client_config));
        $this->assertTrue(array_key_exists('id_token_encrypted_response_enc', $client_config));
        $this->assertTrue(array_key_exists('client_secret', $client_config));
        $this->assertTrue(array_key_exists('client_secret_expires_at', $client_config));
        $this->assertEquals('pairwise', $client_config['subject_type']);
        $this->assertEquals('RSA1_5', $client_config['id_token_encrypted_response_alg']);
        $this->assertEquals('A256GCM', $client_config['id_token_encrypted_response_enc']);
        $this->assertEquals(['authorization_code'], $client_config['grant_types']);
        $this->assertEquals(['code', 'token'], $client_config['response_types']);
        $this->assertEquals('This is my software ID', $client_config['software_id']);
        $this->assertEquals('10.2', $client_config['software_version']);
        $this->assertEquals($software_statement, $client_config['software_statement']);
        $this->assertEquals('My Example', $client_config['client_name']);
    }

    public function testSuccessWithClientSecretPost()
    {
        $initial_access_token = $this->getInitialAccessTokenManager()->getInitialAccessToken('INITIAL_ACCESS_TOKEN_VALID');
        $request = $this->createRequest('/', 'POST',
            [
                'token_endpoint_auth_method' => 'client_secret_post',
                'client_name'                => 'My Example',
            ],
            ['HTTPS' => 'on'],
            [
                'Authorization' => 'Bearer '.$initial_access_token->getToken(),
            ]
        );

        $response = new Response();
        $this->getClientRegistrationEndpoint()->register($request, $response);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertEquals(200, $response->getStatusCode());
        $client_config = json_decode($content, true);

        $this->assertTrue(array_key_exists('token_endpoint_auth_method', $client_config));
        $this->assertTrue(array_key_exists('client_name', $client_config));
        $this->assertTrue(array_key_exists('client_secret', $client_config));
        $this->assertTrue(array_key_exists('client_secret_expires_at', $client_config));
        $this->assertEquals('client_secret_post', $client_config['token_endpoint_auth_method']);
        $this->assertEquals('My Example', $client_config['client_name']);
    }

    public function testSuccessWithClientSecretBasic()
    {
        $initial_access_token = $this->getInitialAccessTokenManager()->getInitialAccessToken('INITIAL_ACCESS_TOKEN_VALID');
        $request = $this->createRequest('/', 'POST',
            [
                'token_endpoint_auth_method' => 'client_secret_basic',
                'client_name'                => 'My Example',
            ],
            ['HTTPS' => 'on'],
            [
                'Authorization' => 'Bearer '.$initial_access_token->getToken(),
            ]
        );

        $response = new Response();
        $this->getClientRegistrationEndpoint()->register($request, $response);
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();

        $this->assertEquals(200, $response->getStatusCode());
        $client_config = json_decode($content, true);

        $this->assertTrue(array_key_exists('token_endpoint_auth_method', $client_config));
        $this->assertTrue(array_key_exists('client_name', $client_config));
        $this->assertTrue(array_key_exists('client_secret', $client_config));
        $this->assertTrue(array_key_exists('client_secret_expires_at', $client_config));
        $this->assertEquals('client_secret_basic', $client_config['token_endpoint_auth_method']);
        $this->assertEquals('My Example', $client_config['client_name']);
    }

    public function testCreationAndDeletion()
    {
        $initial_access_token = $this->getInitialAccessTokenManager()->getInitialAccessToken('INITIAL_ACCESS_TOKEN_VALID');
        $creation_request = $this->createRequest('/', 'POST',
            [
                'token_endpoint_auth_method' => 'client_secret_basic',
                'client_name'                => 'My Example',
            ],
            ['HTTPS' => 'on'],
            [
                'Authorization' => 'Bearer '.$initial_access_token->getToken(),
            ]
        );

        $creation_response = new Response();
        $this->getClientRegistrationEndpoint()->register($creation_request, $creation_response);
        $creation_response->getBody()->rewind();
        $content = $creation_response->getBody()->getContents();

        $this->assertEquals(200, $creation_response->getStatusCode());
        $client_config = json_decode($content, true);

        $client = $this->getClientManager()->getClient($client_config['client_id']);

        $configuration_request = $this->createRequest('/', 'GET', [],
            ['HTTPS' => 'on'],
            ['Authorization' => sprintf('Bearer %s', $client_config['registration_access_token'])]
        );

        $configuration_response = new Response();
        $this->getClientConfigurationEndpoint()->handle($configuration_request, $configuration_response, $client);
        $configuration_response->getBody()->rewind();
        $content = $configuration_response->getBody()->getContents();

        $this->assertEquals(200, $configuration_response->getStatusCode());
        $client_config2 = json_decode($content, true);
        $this->assertEquals($client_config, $client_config2);

        $deletion_request = $this->createRequest('/', 'DELETE', [],
            ['HTTPS' => 'on'],
            ['Authorization' => sprintf('Bearer %s', $client_config['registration_access_token'])]
        );

        $deletion_response = new Response();
        $this->getClientConfigurationEndpoint()->handle($deletion_request, $deletion_response, $client);

        $this->assertEquals(204, $deletion_response->getStatusCode());
        $this->assertNull($client = $this->getClientManager()->getClient($client_config['client_id']));
    }

    public function testCreationAndModification()
    {
        $initial_access_token = $this->getInitialAccessTokenManager()->getInitialAccessToken('INITIAL_ACCESS_TOKEN_VALID');
        $creation_request = $this->createRequest('/', 'POST',
            [
                'token_endpoint_auth_method' => 'client_secret_basic',
                'client_name'                => 'My Example',
            ],
            ['HTTPS' => 'on'],
            [
                'Authorization' => 'Bearer '.$initial_access_token->getToken(),
            ]
        );

        $creation_response = new Response();
        $this->getClientRegistrationEndpoint()->register($creation_request, $creation_response);
        $creation_response->getBody()->rewind();
        $content = $creation_response->getBody()->getContents();

        $this->assertEquals(200, $creation_response->getStatusCode());
        $client_config = json_decode($content, true);

        $client = $this->getClientManager()->getClient($client_config['client_id']);
        $new_client_config = $client_config;
        $new_client_config['token_endpoint_auth_method'] = 'none';
        $new_client_config['client_secret'] = null;
        $new_client_config['foo'] = 'bar';
        $software_statement = $this->getJWTCreator()->sign(
            [
                'software_id'      => 'This is my software ID',
                'software_version' => '10.2',
                'client_name'      => 'My Example v2',
                'client_name#fr'   => 'Mon Exemple v2',
            ],
            ['alg' => 'HS512'],
            $this->getSignatureKeySet()[0]);
        $new_client_config['software_statement'] = $software_statement;

        foreach (['registration_access_token', 'registration_client_uri', 'client_secret_expires_at', 'client_id_issued_at'] as $k) {
            if (array_key_exists($k, $new_client_config)) {
                unset($new_client_config[$k]);
            }
        }
        $modification_request = $this->createRequest('/', 'PUT',
            $new_client_config,
            ['HTTPS' => 'on'],
            ['Authorization' => sprintf('Bearer %s', $client_config['registration_access_token'])]
        );

        $modification_response = new Response();
        $this->getClientConfigurationEndpoint()->handle($modification_request, $modification_response, $client);
        $modification_response->getBody()->rewind();
        $content = $modification_response->getBody()->getContents();

        $this->assertEquals(200, $modification_response->getStatusCode());
        $new_client_config = json_decode($content, true);

        $this->assertEquals($client_config['client_id'], $new_client_config['client_id']);
        $this->assertEquals($client_config['client_id_issued_at'], $new_client_config['client_id_issued_at']);
        $this->assertEquals('none', $new_client_config['token_endpoint_auth_method']);
        $this->assertEquals('My Example v2', $new_client_config['client_name']);
        $this->assertEquals('Mon Exemple v2', $new_client_config['client_name#fr']);
        $this->assertEquals($software_statement, $new_client_config['software_statement']);
        $this->assertArrayNotHasKey('client_secret', $new_client_config);
        $this->assertArrayNotHasKey('client_secret_expires_at', $new_client_config);
        $this->assertArrayNotHasKey('foo', $new_client_config);
    }
}
