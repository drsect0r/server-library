<?php

use Assert\Assertion;

use OAuth2\Model\Client\Client;
use OAuth2\Model\Client\ClientId;
use OAuth2\Model\UserAccount\UserAccount;
use OAuth2\Model\UserAccount\UserAccountId;
use OAuth2\Event\Client\ClientCreatedEvent;
use OAuth2\Event\Client\ClientDeletedEvent;
use OAuth2\Event\Client\ClientUpdatedEvent;

/**
 * Defines application features from the specific context.
 */
class ClientContext extends BaseContext
{
    /**
     * @var null|\Psr\Http\Message\ResponseInterface
     */
    private $response = null;

    /**
     * @var null|array
     */
    private $client = null;

    /**
     * @var null|array
     */
    private $error = null;

    /**
     * @Given a valid client registration request is received
     */
    public function aValidClientRegistrationRequestIsReceived()
    {
        /**
         * @var $request \Psr\Http\Message\ServerRequestInterface
         */
        $request = $this->getServerRequestFactory()->createServerRequest([]);
        $request = $request->withMethod('POST');
        $request = $request->withParsedBody([
            'redirect_uris' => ['https://www.foo.com'],
        ]);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withHeader('Authorization', 'Bearer INITIAL_ACCESS_TOKEN_VALID');

        $this->response = $this->getApplication()->getClientRegistrationPipe()->dispatch($request);
        if ($this->response->getBody()->isSeekable()) {
            $this->response->getBody()->rewind();
        }
    }

    /**
     * @Given a client registration request is received with an expired initial access token
     */
    public function aClientRegistrationRequestIsReceivedWithAnExpiredInitialAccessToken()
    {
        /**
         * @var $request \Psr\Http\Message\ServerRequestInterface
         */
        $request = $this->getServerRequestFactory()->createServerRequest([]);
        $request = $request->withMethod('POST');
        $request = $request->withParsedBody([
            'redirect_uris' => ['https://www.foo.com'],
        ]);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withHeader('Authorization', 'Bearer INITIAL_ACCESS_TOKEN_EXPIRED');

        $this->response = $this->getApplication()->getClientRegistrationPipe()->dispatch($request);
        if ($this->response->getBody()->isSeekable()) {
            $this->response->getBody()->rewind();
        }
    }

    /**
     * @Given a client registration request is received but not initial access token is set
     */
    public function aClientRegistrationRequestIsReceivedButNotInitialAccessTokenIsSet()
    {
        /**
         * @var $request \Psr\Http\Message\ServerRequestInterface
         */
        $request = $this->getServerRequestFactory()->createServerRequest([]);
        $request = $request->withMethod('POST');
        $request = $request->withParsedBody([
            'redirect_uris' => ['https://www.foo.com'],
        ]);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');

        $this->response = $this->getApplication()->getClientRegistrationPipe()->dispatch($request);
        if ($this->response->getBody()->isSeekable()) {
            $this->response->getBody()->rewind();
        }
    }

    /**
     * @Given a client registration request is received but an invalid initial access token is set
     */
    public function aClientRegistrationRequestIsReceivedButAnInvalidInitialAccessTokenIsSet()
    {
        /**
         * @var $request \Psr\Http\Message\ServerRequestInterface
         */
        $request = $this->getServerRequestFactory()->createServerRequest([]);
        $request = $request->withMethod('POST');
        $request = $request->withParsedBody([
            'redirect_uris' => ['https://www.foo.com'],
        ]);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withHeader('Authorization', 'Bearer ***INVALID_INITIAL_ACCESS_TOKEN***');

        $this->response = $this->getApplication()->getClientRegistrationPipe()->dispatch($request);
        if ($this->response->getBody()->isSeekable()) {
            $this->response->getBody()->rewind();
        }
    }

    /**
     * @Given a valid client registration request with software statement is received
     */
    public function aValidClientRegistrationRequestWithSoftwareStatementIsReceived()
    {
        /**
         * @var $request \Psr\Http\Message\ServerRequestInterface
         */
        $request = $this->getServerRequestFactory()->createServerRequest([]);
        $request = $request->withMethod('POST');
        $request = $request->withParsedBody([
            'redirect_uris' => ['https://www.foo.com'],
            'software_statement' => $this->createSoftwareStatement(),
        ]);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withHeader('Authorization', 'Bearer INITIAL_ACCESS_TOKEN_VALID');

        $this->response = $this->getApplication()->getClientRegistrationPipe()->dispatch($request);
        if ($this->response->getBody()->isSeekable()) {
            $this->response->getBody()->rewind();
        }
    }

    /**
     * @Given a valid client configuration GET request is received
     */
    public function aValidClientConfigurationGetRequestIsReceived()
    {
        /**
         * @var $request \Psr\Http\Message\ServerRequestInterface
         */
        $request = $this->getServerRequestFactory()->createServerRequest([]);
        $request = $request->withMethod('GET');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withHeader('Authorization', 'Bearer JNWuIxHkTKtUmmtEpipDtPlTc3ordUNpSVVPLbQXKrFKyYVDR7N3k1ZzrHmPWXoibr2J2HrTSSozN6zIhHuypA');
        $client = Client::create(
            ClientId::create('79b407fb-acc0-4880-ab98-254062c214ce'),
            [
                "registration_access_token" => "JNWuIxHkTKtUmmtEpipDtPlTc3ordUNpSVVPLbQXKrFKyYVDR7N3k1ZzrHmPWXoibr2J2HrTSSozN6zIhHuypA",
                "grant_types" => [],
                "response_types" => [],
                "redirect_uris" => [
                    "https://www.foo.com"
                ],
                "software_statement" => "eyJhbGciOiJFUzI1NiJ9.eyJzb2Z0d2FyZV92ZXJzaW9uIjoiMS4wIiwic29mdHdhcmVfbmFtZSI6Ik15IGFwcGxpY2F0aW9uIiwic29mdHdhcmVfbmFtZSNlbiI6Ik15IGFwcGxpY2F0aW9uIiwic29mdHdhcmVfbmFtZSNmciI6Ik1vbiBhcHBsaWNhdGlvbiJ9.88m8-YyguCCx1QNChwfNnMZ9APKpNC--nnfB1rVBpAYyHLixtsyMuuI09svqxuiRfTxwgXuRUvsg_5RozmtusQ",
                "software_version" => "1.0",
                "software_name" => "My application",
                "software_name#en" => "My application",
                "software_name#fr" => "Mon application",
                "registration_client_uri" => "https://www.config.example.com/client/79b407fb-acc0-4880-ab98-254062c214ce",
                "client_id" => "79b407fb-acc0-4880-ab98-254062c214ce",
                "client_id_issued_at" => 1482177703,
            ],
            UserAccount::create(UserAccountId::create('USER #1'), [])
        );
        $request = $request->withAttribute('client', $client);

        $this->response = $this->getApplication()->getClientConfigurationPipe()->dispatch($request);
        if ($this->response->getBody()->isSeekable()) {
            $this->response->getBody()->rewind();
        }
    }

    /**
     * @Given a client configuration GET request is received but no Registration Token is set
     */
    public function aClientConfigurationGetRequestIsReceivedButNoRegistrationTokenIsSet()
    {
        /**
         * @var $request \Psr\Http\Message\ServerRequestInterface
         */
        $request = $this->getServerRequestFactory()->createServerRequest([]);
        $request = $request->withMethod('GET');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $client = Client::create(
            ClientId::create('79b407fb-acc0-4880-ab98-254062c214ce'),
            [
                "registration_access_token" => "JNWuIxHkTKtUmmtEpipDtPlTc3ordUNpSVVPLbQXKrFKyYVDR7N3k1ZzrHmPWXoibr2J2HrTSSozN6zIhHuypA",
                "grant_types" => [],
                "response_types" => [],
                "redirect_uris" => [
                    "https://www.foo.com"
                ],
                "software_statement" => "eyJhbGciOiJFUzI1NiJ9.eyJzb2Z0d2FyZV92ZXJzaW9uIjoiMS4wIiwic29mdHdhcmVfbmFtZSI6Ik15IGFwcGxpY2F0aW9uIiwic29mdHdhcmVfbmFtZSNlbiI6Ik15IGFwcGxpY2F0aW9uIiwic29mdHdhcmVfbmFtZSNmciI6Ik1vbiBhcHBsaWNhdGlvbiJ9.88m8-YyguCCx1QNChwfNnMZ9APKpNC--nnfB1rVBpAYyHLixtsyMuuI09svqxuiRfTxwgXuRUvsg_5RozmtusQ",
                "software_version" => "1.0",
                "software_name" => "My application",
                "software_name#en" => "My application",
                "software_name#fr" => "Mon application",
                "registration_client_uri" => "https://www.config.example.com/client/79b407fb-acc0-4880-ab98-254062c214ce",
                "client_id" => "79b407fb-acc0-4880-ab98-254062c214ce",
                "client_id_issued_at" => 1482177703,
            ],
            UserAccount::create(UserAccountId::create('USER #1'), [])
        );
        $request = $request->withAttribute('client', $client);

        $this->response = $this->getApplication()->getClientConfigurationPipe()->dispatch($request);
        if ($this->response->getBody()->isSeekable()) {
            $this->response->getBody()->rewind();
        }
    }

    /**
     * @Given a client configuration GET request is received but the Registration Token is invalid
     */
    public function aClientConfigurationGetRequestIsReceivedButTheRegistrationTokenIsInvalid()
    {
        /**
         * @var $request \Psr\Http\Message\ServerRequestInterface
         */
        $request = $this->getServerRequestFactory()->createServerRequest([]);
        $request = $request->withMethod('GET');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withHeader('Authorization', 'Bearer InvALID_ToKEn');
        $client = Client::create(
            ClientId::create('79b407fb-acc0-4880-ab98-254062c214ce'),
            [
                "registration_access_token" => "JNWuIxHkTKtUmmtEpipDtPlTc3ordUNpSVVPLbQXKrFKyYVDR7N3k1ZzrHmPWXoibr2J2HrTSSozN6zIhHuypA",
                "grant_types" => [],
                "response_types" => [],
                "redirect_uris" => [
                    "https://www.foo.com"
                ],
                "software_statement" => "eyJhbGciOiJFUzI1NiJ9.eyJzb2Z0d2FyZV92ZXJzaW9uIjoiMS4wIiwic29mdHdhcmVfbmFtZSI6Ik15IGFwcGxpY2F0aW9uIiwic29mdHdhcmVfbmFtZSNlbiI6Ik15IGFwcGxpY2F0aW9uIiwic29mdHdhcmVfbmFtZSNmciI6Ik1vbiBhcHBsaWNhdGlvbiJ9.88m8-YyguCCx1QNChwfNnMZ9APKpNC--nnfB1rVBpAYyHLixtsyMuuI09svqxuiRfTxwgXuRUvsg_5RozmtusQ",
                "software_version" => "1.0",
                "software_name" => "My application",
                "software_name#en" => "My application",
                "software_name#fr" => "Mon application",
                "registration_client_uri" => "https://www.config.example.com/client/79b407fb-acc0-4880-ab98-254062c214ce",
                "client_id" => "79b407fb-acc0-4880-ab98-254062c214ce",
                "client_id_issued_at" => 1482177703,
            ],
            UserAccount::create(UserAccountId::create('USER #1'), [])
        );
        $request = $request->withAttribute('client', $client);

        $this->response = $this->getApplication()->getClientConfigurationPipe()->dispatch($request);
        if ($this->response->getBody()->isSeekable()) {
            $this->response->getBody()->rewind();
        }
    }

    /**
     * @Given a valid client configuration DELETE request is received
     */
    public function aValidClientConfigurationDeleteRequestIsReceived()
    {
        /**
         * @var $request \Psr\Http\Message\ServerRequestInterface
         */
        $request = $this->getServerRequestFactory()->createServerRequest([]);
        $request = $request->withMethod('DELETE');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withHeader('Authorization', 'Bearer JNWuIxHkTKtUmmtEpipDtPlTc3ordUNpSVVPLbQXKrFKyYVDR7N3k1ZzrHmPWXoibr2J2HrTSSozN6zIhHuypA');
        $client = Client::create(
            ClientId::create('79b407fb-acc0-4880-ab98-254062c214ce'),
            [
                "registration_access_token" => "JNWuIxHkTKtUmmtEpipDtPlTc3ordUNpSVVPLbQXKrFKyYVDR7N3k1ZzrHmPWXoibr2J2HrTSSozN6zIhHuypA",
                "grant_types" => [],
                "response_types" => [],
                "redirect_uris" => [
                    "https://www.foo.com"
                ],
                "software_statement" => "eyJhbGciOiJFUzI1NiJ9.eyJzb2Z0d2FyZV92ZXJzaW9uIjoiMS4wIiwic29mdHdhcmVfbmFtZSI6Ik15IGFwcGxpY2F0aW9uIiwic29mdHdhcmVfbmFtZSNlbiI6Ik15IGFwcGxpY2F0aW9uIiwic29mdHdhcmVfbmFtZSNmciI6Ik1vbiBhcHBsaWNhdGlvbiJ9.88m8-YyguCCx1QNChwfNnMZ9APKpNC--nnfB1rVBpAYyHLixtsyMuuI09svqxuiRfTxwgXuRUvsg_5RozmtusQ",
                "software_version" => "1.0",
                "software_name" => "My application",
                "software_name#en" => "My application",
                "software_name#fr" => "Mon application",
                "registration_client_uri" => "https://www.config.example.com/client/79b407fb-acc0-4880-ab98-254062c214ce",
                "client_id" => "79b407fb-acc0-4880-ab98-254062c214ce",
                "client_id_issued_at" => 1482177703,
            ],
            UserAccount::create(UserAccountId::create('USER #1'), [])
        );
        $this->getApplication()->getClientRepository()->save($client);
        $request = $request->withAttribute('client', $client);

        $this->response = $this->getApplication()->getClientConfigurationPipe()->dispatch($request);
        if ($this->response->getBody()->isSeekable()) {
            $this->response->getBody()->rewind();
        }
    }

    /**
     * @Given a client configuration DELETE request is received but no Registration Token is set
     */
    public function aClientConfigurationDeleteRequestIsReceivedButNoRegistrationTokenIsSet()
    {
        /**
         * @var $request \Psr\Http\Message\ServerRequestInterface
         */
        $request = $this->getServerRequestFactory()->createServerRequest([]);
        $request = $request->withMethod('DELETE');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $client = Client::create(
            ClientId::create('79b407fb-acc0-4880-ab98-254062c214ce'),
            [
                "registration_access_token" => "JNWuIxHkTKtUmmtEpipDtPlTc3ordUNpSVVPLbQXKrFKyYVDR7N3k1ZzrHmPWXoibr2J2HrTSSozN6zIhHuypA",
                "grant_types" => [],
                "response_types" => [],
                "redirect_uris" => [
                    "https://www.foo.com"
                ],
                "software_statement" => "eyJhbGciOiJFUzI1NiJ9.eyJzb2Z0d2FyZV92ZXJzaW9uIjoiMS4wIiwic29mdHdhcmVfbmFtZSI6Ik15IGFwcGxpY2F0aW9uIiwic29mdHdhcmVfbmFtZSNlbiI6Ik15IGFwcGxpY2F0aW9uIiwic29mdHdhcmVfbmFtZSNmciI6Ik1vbiBhcHBsaWNhdGlvbiJ9.88m8-YyguCCx1QNChwfNnMZ9APKpNC--nnfB1rVBpAYyHLixtsyMuuI09svqxuiRfTxwgXuRUvsg_5RozmtusQ",
                "software_version" => "1.0",
                "software_name" => "My application",
                "software_name#en" => "My application",
                "software_name#fr" => "Mon application",
                "registration_client_uri" => "https://www.config.example.com/client/79b407fb-acc0-4880-ab98-254062c214ce",
                "client_id" => "79b407fb-acc0-4880-ab98-254062c214ce",
                "client_id_issued_at" => 1482177703,
            ],
            UserAccount::create(UserAccountId::create('USER #1'), [])
        );
        $this->getApplication()->getClientRepository()->save($client);
        $request = $request->withAttribute('client', $client);

        $this->response = $this->getApplication()->getClientConfigurationPipe()->dispatch($request);
        if ($this->response->getBody()->isSeekable()) {
            $this->response->getBody()->rewind();
        }
    }

    /**
     * @Given a client configuration PUT request is received but no Registration Token is set
     */
    public function aClientConfigurationPutRequestIsReceivedButNoRegistrationTokenIsSet()
    {
        /**
         * @var $request \Psr\Http\Message\ServerRequestInterface
         */
        $request = $this->getServerRequestFactory()->createServerRequest([]);
        $request = $request->withMethod('PUT');
        $request = $request->withParsedBody([
            'redirect_uris' => ['https://www.foo.com'],
        ]);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $client = Client::create(
            ClientId::create('79b407fb-acc0-4880-ab98-254062c214ce'),
            [
                "registration_access_token" => "JNWuIxHkTKtUmmtEpipDtPlTc3ordUNpSVVPLbQXKrFKyYVDR7N3k1ZzrHmPWXoibr2J2HrTSSozN6zIhHuypA",
                "grant_types" => [],
                "response_types" => [],
                "redirect_uris" => [
                    "https://www.foo.com"
                ],
                "software_statement" => "eyJhbGciOiJFUzI1NiJ9.eyJzb2Z0d2FyZV92ZXJzaW9uIjoiMS4wIiwic29mdHdhcmVfbmFtZSI6Ik15IGFwcGxpY2F0aW9uIiwic29mdHdhcmVfbmFtZSNlbiI6Ik15IGFwcGxpY2F0aW9uIiwic29mdHdhcmVfbmFtZSNmciI6Ik1vbiBhcHBsaWNhdGlvbiJ9.88m8-YyguCCx1QNChwfNnMZ9APKpNC--nnfB1rVBpAYyHLixtsyMuuI09svqxuiRfTxwgXuRUvsg_5RozmtusQ",
                "software_version" => "1.0",
                "software_name" => "My application",
                "software_name#en" => "My application",
                "software_name#fr" => "Mon application",
                "registration_client_uri" => "https://www.config.example.com/client/79b407fb-acc0-4880-ab98-254062c214ce",
                "client_id" => "79b407fb-acc0-4880-ab98-254062c214ce",
                "client_id_issued_at" => 1482177703,
            ],
            UserAccount::create(UserAccountId::create('USER #1'), [])
        );
        $this->getApplication()->getClientRepository()->save($client);
        $request = $request->withAttribute('client', $client);

        $this->response = $this->getApplication()->getClientConfigurationPipe()->dispatch($request);
        if ($this->response->getBody()->isSeekable()) {
            $this->response->getBody()->rewind();
        }
    }

    /**
     * @Given a valid client configuration PUT request is received
     */
    public function aValidClientConfigurationPutRequestIsReceived()
    {
        /**
         * @var $request \Psr\Http\Message\ServerRequestInterface
         */
        $request = $this->getServerRequestFactory()->createServerRequest([]);
        $request = $request->withMethod('PUT');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withParsedBody([
            'redirect_uris' => ['https://www.bar.com'],
        ]);
        $request = $request->withHeader('Authorization', 'Bearer JNWuIxHkTKtUmmtEpipDtPlTc3ordUNpSVVPLbQXKrFKyYVDR7N3k1ZzrHmPWXoibr2J2HrTSSozN6zIhHuypA');
        $client = Client::create(
            ClientId::create('79b407fb-acc0-4880-ab98-254062c214ce'),
            [
                "registration_access_token" => "JNWuIxHkTKtUmmtEpipDtPlTc3ordUNpSVVPLbQXKrFKyYVDR7N3k1ZzrHmPWXoibr2J2HrTSSozN6zIhHuypA",
                "grant_types" => [],
                "response_types" => [],
                "redirect_uris" => [
                    "https://www.foo.com"
                ],
                "software_statement" => "eyJhbGciOiJFUzI1NiJ9.eyJzb2Z0d2FyZV92ZXJzaW9uIjoiMS4wIiwic29mdHdhcmVfbmFtZSI6Ik15IGFwcGxpY2F0aW9uIiwic29mdHdhcmVfbmFtZSNlbiI6Ik15IGFwcGxpY2F0aW9uIiwic29mdHdhcmVfbmFtZSNmciI6Ik1vbiBhcHBsaWNhdGlvbiJ9.88m8-YyguCCx1QNChwfNnMZ9APKpNC--nnfB1rVBpAYyHLixtsyMuuI09svqxuiRfTxwgXuRUvsg_5RozmtusQ",
                "software_version" => "1.0",
                "software_name" => "My application",
                "software_name#en" => "My application",
                "software_name#fr" => "Mon application",
                "registration_client_uri" => "https://www.config.example.com/client/79b407fb-acc0-4880-ab98-254062c214ce",
                "client_id" => "79b407fb-acc0-4880-ab98-254062c214ce",
                "client_id_issued_at" => 1482177703,
            ],
            UserAccount::create(UserAccountId::create('USER #1'), [])
        );
        $this->getApplication()->getClientRepository()->save($client);
        $request = $request->withAttribute('client', $client);

        $this->response = $this->getApplication()->getClientConfigurationPipe()->dispatch($request);
        if ($this->response->getBody()->isSeekable()) {
            $this->response->getBody()->rewind();
        }
    }

    /**
     * @Given the response contains the updated client
     */
    public function theResponseContainsTheUpdatedClient()
    {
        $response = (string) $this->response->getBody()->getContents();
        $json = json_decode($response, true);
        Assertion::isArray($json);
        Assertion::keyExists($json, 'client_id');
        $this->client = $json;
    }

    /**
     * @Given a valid client configuration PUT request with software statement is received
     */
    public function aValidClientConfigurationPutRequestWithSoftwareStatementIsReceived()
    {
        /**
         * @var $request \Psr\Http\Message\ServerRequestInterface
         */
        $request = $this->getServerRequestFactory()->createServerRequest([]);
        $request = $request->withMethod('PUT');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withParsedBody([
            'redirect_uris' => ['https://www.bar.com'],
            "software_statement" => $this->createSoftwareStatement(),

        ]);
        $request = $request->withHeader('Authorization', 'Bearer JNWuIxHkTKtUmmtEpipDtPlTc3ordUNpSVVPLbQXKrFKyYVDR7N3k1ZzrHmPWXoibr2J2HrTSSozN6zIhHuypA');
        $client = Client::create(
            ClientId::create('79b407fb-acc0-4880-ab98-254062c214ce'),
            [
                "registration_access_token" => "JNWuIxHkTKtUmmtEpipDtPlTc3ordUNpSVVPLbQXKrFKyYVDR7N3k1ZzrHmPWXoibr2J2HrTSSozN6zIhHuypA",
                "grant_types" => [],
                "response_types" => [],
                "redirect_uris" => [
                    "https://www.foo.com"
                ],
                "registration_client_uri" => "https://www.config.example.com/client/79b407fb-acc0-4880-ab98-254062c214ce",
                "client_id" => "79b407fb-acc0-4880-ab98-254062c214ce",
                "client_id_issued_at" => 1482177703,
            ],
            UserAccount::create(UserAccountId::create('USER #1'), [])
        );
        $this->getApplication()->getClientRepository()->save($client);
        $request = $request->withAttribute('client', $client);

        $this->response = $this->getApplication()->getClientConfigurationPipe()->dispatch($request);
        if ($this->response->getBody()->isSeekable()) {
            $this->response->getBody()->rewind();
        }
    }

    /**
     * @Then the response code is :code
     */
    public function theResponseCodeIs($code)
    {
        Assertion::eq((int) $code, $this->response->getStatusCode());
    }

    /**
     * @Then a client deleted event should be recorded
     */
    public function aClientDeletedEventShouldBeRecorded()
    {
        $events = $this->getApplication()->getEventStore()->all();
        Assertion::eq(1, count($events));
        Assertion::allIsInstanceOf($events, ClientDeletedEvent::class);
    }

    /**
     * @Then no client deleted event should be recorded
     */
    public function noClientDeletedEventShouldBeRecorded()
    {
        $events = $this->getApplication()->getEventStore()->all();
        Assertion::eq(0, count($events));
    }


    /**
     * @Then no client updated event should be recorded
     */
    public function noClientUpdatedEventShouldBeRecorded()
    {
        $events = $this->getApplication()->getEventStore()->all();
        Assertion::eq(0, count($events));
    }

    /**
     * @Then a client created event should be recorded
     */
    public function aClientCreatedEventShouldBeRecorded()
    {
        $events = $this->getApplication()->getEventStore()->all();
        Assertion::eq(1, count($events));
        Assertion::allIsInstanceOf($events, ClientCreatedEvent::class);
    }

    /**
     * @Then a client updated event should be recorded
     */
    public function aClientUpdatedEventShouldBeRecorded()
    {
        $events = $this->getApplication()->getEventStore()->all();
        Assertion::eq(1, count($events));
        Assertion::allIsInstanceOf($events, ClientUpdatedEvent::class);
    }

    /**
     * @Then the response contains a client
     */
    public function theResponseContainsAClient()
    {
        $response = $this->response->getBody()->getContents();
        $json = json_decode($response, true);
        Assertion::isArray($json);
        Assertion::keyExists($json, 'client_id');
        $this->client = $json;
    }

    /**
     * @Then no client should be created
     */
    public function noClientShouldBeCreated()
    {
        $events = $this->getApplication()->getEventStore()->all();
        Assertion::eq(0, count($events));
    }

    /**
     * @Then the response contains an error with code :code
     */
    public function theResponseContainsAnError($code)
    {
        Assertion::eq((int)$code, $this->response->getStatusCode());
        Assertion::greaterOrEqualThan($this->response->getStatusCode(), 400);
        if (401 === $this->response->getStatusCode()) {
        } else {
            $response = $this->response->getBody()->getContents();
            $json = json_decode($response, true);
            Assertion::isArray($json);
            Assertion::keyExists($json, 'error');
            $this->error = $json;
        }
    }

    /**
     * @Then the error is :error
     *
     * @param string $error
     */
    public function theErrorIs($error)
    {
        Assertion::notNull($this->error);
        Assertion::keyExists($this->error, 'error');
        Assertion::eq($error, $this->error['error']);
    }

    /**
     * @Then the error description is :errorDescription
     *
     * @param string $errorDescription
     */
    public function theErrorDescriptionIs($errorDescription)
    {
        Assertion::notNull($this->error);
        Assertion::keyExists($this->error, 'error_description');
        Assertion::eq($errorDescription, $this->error['error_description']);
    }

    /**
     * @Then the software statement parameters are in the client parameters
     */
    public function theSoftwareStatementParametersAreInTheClientParameters()
    {
        Assertion::keyExists($this->client, 'software_statement');
        Assertion::keyExists($this->client, 'software_version');
        Assertion::keyExists($this->client, 'software_name');
        Assertion::keyExists($this->client, 'software_name#en');
        Assertion::keyExists($this->client, 'software_name#fr');
        Assertion::eq($this->client['software_version'], '1.0');
        Assertion::eq($this->client['software_name'], 'My application');
        Assertion::eq($this->client['software_name#en'], 'My application');
        Assertion::eq($this->client['software_name#fr'], 'Mon application');
    }

    /**
     * @return string
     */
    private function createSoftwareStatement(): string
    {
        $claims = [
            'software_version' => '1.0',
            'software_name' => 'My application',
            'software_name#en' => 'My application',
            'software_name#fr' => 'Mon application',
        ];
        $headers = [
            'alg' => 'ES256',
        ];
        $key = $this->getApplication()->getSoftwareStatementPrivateKeys()->getKey(0);

        return $this->getApplication()->getJwTCreator()->sign($claims, $headers, $key);
    }
}
