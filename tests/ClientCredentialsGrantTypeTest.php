<?php

namespace OAuth2\Test;

use OAuth2\Exception\BaseExceptionInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group ClientCredentialsGrantType
 */
class ClientCredentialsGrantTypeTest extends Base
{
    public function testUnsecuredRequest()
    {
        $request = $this->createRequest();

        try {
            $this->getTokenEndpoint()->getAccessToken($request);
            $this->fail('Should throw an Exception');
        } catch (BaseExceptionInterface $e) {
            $this->assertEquals('invalid_request', $e->getMessage());
            $this->assertEquals('The request must be secured.', $e->getDescription());
            $this->assertEquals(400, $e->getHttpCode());
        }
    }

    public function testNotPostMethod()
    {
        $request = $this->createRequest('/', 'GET', array(), array('HTTPS' => 'on'));

        try {
            $this->getTokenEndpoint()->getAccessToken($request);
            $this->fail('Should throw an Exception');
        } catch (BaseExceptionInterface $e) {
            $this->assertEquals('invalid_request', $e->getMessage());
            $this->assertEquals('Method must be POST.', $e->getDescription());
            $this->assertEquals(400, $e->getHttpCode());
        }
    }

    public function testGrantTypeIsMissing()
    {
        $request = $this->createRequest('/', 'POST', array(), array('HTTPS' => 'on'));

        try {
            $this->getTokenEndpoint()->getAccessToken($request);
            $this->fail('Should throw an Exception');
        } catch (BaseExceptionInterface $e) {
            $this->assertEquals('invalid_request', $e->getMessage());
            $this->assertEquals('The parameter "grant_type" parameter is missing.', $e->getDescription());
            $this->assertEquals(400, $e->getHttpCode());
        }
    }

    public function testUnknownClient()
    {
        $request = $this->createRequest('/', 'POST', array(), array('HTTPS' => 'on', 'PHP_AUTH_USER' => 'plic', 'PHP_AUTH_PW' => 'secret'), array(), http_build_query(array('grant_type' => 'client_credentials')));

        try {
            $this->getTokenEndpoint()->getAccessToken($request);
            $this->fail('Should throw an Exception');
        } catch (BaseExceptionInterface $e) {
            $this->assertEquals('invalid_client', $e->getMessage());
            $this->assertEquals('Unknown client', $e->getDescription());
            $this->assertEquals(401, $e->getHttpCode());
        }
    }

    public function testUnsupportedGrantType()
    {
        $request = $this->createRequest('/', 'POST', array(), array('HTTPS' => 'on', 'PHP_AUTH_USER' => 'bar', 'PHP_AUTH_PW' => 'secret'), array(), http_build_query(array('grant_type' => 'bar')));

        try {
            $this->getTokenEndpoint()->getAccessToken($request);
            $this->fail('Should throw an Exception');
        } catch (BaseExceptionInterface $e) {
            $this->assertEquals('unsupported_grant_type', $e->getMessage());
            $this->assertEquals('The grant type "bar" is not supported by this server', $e->getDescription());
            $this->assertEquals(501, $e->getHttpCode());
        }
    }

    public function testGrantTypeUnauthorizedForClient()
    {
        $request = $this->createRequest('/', 'POST', array(), array('HTTPS' => 'on', 'PHP_AUTH_USER' => 'baz', 'PHP_AUTH_PW' => 'secret'), array(), http_build_query(array('grant_type' => 'client_credentials')));

        try {
            $this->getTokenEndpoint()->getAccessToken($request);
            $this->fail('Should throw an Exception');
        } catch (BaseExceptionInterface $e) {
            $this->assertEquals('unauthorized_client', $e->getMessage());
            $this->assertEquals('The grant type "client_credentials" is unauthorized for this client_id', $e->getDescription());
            $this->assertEquals(400, $e->getHttpCode());
        }
    }

    public function testGrantTypeAuthorizedForClient()
    {
        $request = $this->createRequest('/', 'POST', array(), array('HTTPS' => 'on', 'PHP_AUTH_USER' => 'bar', 'PHP_AUTH_PW' => 'secret'), array(), http_build_query(array('grant_type' => 'client_credentials')));

        $response = $this->getTokenEndpoint()->getAccessToken($request);

        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals('no-store, private', $response->headers->get('Cache-Control'));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('no-cache', $response->headers->get('Pragma'));
        $this->assertRegExp('{"access_token":"[^"]+","expires_in":[^"]+,"scope":"scope1 scope2","token_type":"Bearer"}', $response->getContent());
    }

    public function testClientNotConfidential()
    {
        $request = $this->createRequest('/', 'POST', array(), array('HTTPS' => 'on'), array('X-OAuth2-Public-Client-ID' => 'foo'), http_build_query(array('grant_type' => 'client_credentials')));

        try {
            $this->getTokenEndpoint()->getAccessToken($request);
            $this->fail('Should throw an Exception');
        } catch (BaseExceptionInterface $e) {
            $this->assertEquals('invalid_client', $e->getMessage());
            $this->assertEquals('The client is not a confidential client', $e->getDescription());
            $this->assertEquals(400, $e->getHttpCode());
        }
    }
}
