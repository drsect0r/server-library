<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\Endpoint\Token;

use OAuth2\Model\AccessToken\AccessToken;
use Psr\Http\Message\ServerRequestInterface;

interface TokenEndpointExtensionInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param GrantTypeResponse $tokenResponse
     * @param callable $next
     * @return AccessToken
     */
    public function process(ServerRequestInterface $request, GrantTypeResponse $tokenResponse, callable $next): AccessToken;
}
