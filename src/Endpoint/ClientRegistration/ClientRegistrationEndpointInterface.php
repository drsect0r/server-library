<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\Endpoint\ClientRegistration;

use OAuth2\Token\AccessTokenInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ClientRegistrationEndpointInterface
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     * @param \OAuth2\Token\AccessTokenInterface|null  $access_token
     */
    public function register(ServerRequestInterface $request, ResponseInterface &$response, AccessTokenInterface $access_token = null);
}
