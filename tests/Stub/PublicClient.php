<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\Test\Stub;

use OAuth2\Client\PublicClient as BasePublicClient;

class PublicClient extends BasePublicClient
{

    /**
     * @param string $grant_type
     */
    public function addAllowedGrantType($grant_type)
    {
        if (!$this->isAllowedGrantType($grant_type)) {
            $this->grant_types[] = $grant_type;
        }
    }

    /**
     * @param string[] $grant_types
     */
    public function setAllowedGrantTypes(array $grant_types)
    {
        $this->grant_types = $grant_types;
    }

    /**
     * @param string $grant_type
     */
    public function removeAllowedGrantType($grant_type)
    {
        $key = array_search($grant_type, $this->grant_types);
        if (false !== $key) {
            unset($this->grant_types[$key]);
        }
    }

    /**
     * @param string[] $redirect_uris
     */
    public function setRedirectUris(array $redirect_uris)
    {
        $this->redirect_uris = $redirect_uris;
    }

    /**
     * @param string $redirect_uri
     */
    public function addRedirectUri($redirect_uri)
    {
        if (!$this->hasRedirectUri($redirect_uri)) {
            $this->redirect_uris[] = $redirect_uri;
        }
    }

    /**
     * @param string $redirect_uri
     */
    public function removeRedirectUri($redirect_uri)
    {
        $key = array_search($redirect_uri, $this->redirect_uris);
        if (false !== $key) {
            unset($this->redirect_uris[$key]);
        }
    }
}
