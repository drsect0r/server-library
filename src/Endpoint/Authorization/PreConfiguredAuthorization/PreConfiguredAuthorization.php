<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\Endpoint\Authorization\PreConfiguredAuthorization;

class PreConfiguredAuthorization implements PreConfiguredAuthorizationInterface
{
    /**
     * @var string
     */
    private $resource_owner_public_id;

    /**
     * @var string
     */
    private $client_public_id;

    /**
     * @var string[]
     */
    private $requested_scopes;

    /**
     * @var string[]
     */
    private $validated_scopes;

    /**
     * {@inheritdoc}
     */
    public function getResourceOwnerPublicId()
    {
        return $this->resource_owner_public_id;
    }

    /**
     * {@inheritdoc}
     */
    public function setResourceOwnerPublicId($resource_owner_public_id)
    {
        $this->resource_owner_public_id = $resource_owner_public_id;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientPublicId()
    {
        return $this->client_public_id;
    }

    /**
     * {@inheritdoc}
     */
    public function setClientPublicId($client_public_id)
    {
        $this->client_public_id = $client_public_id;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestedScopes()
    {
        return $this->requested_scopes;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestedScopes(array $requested_scopes)
    {
        $this->requested_scopes = $requested_scopes;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidatedScopes()
    {
        return $this->validated_scopes;
    }

    /**
     * {@inheritdoc}
     */
    public function setValidatedScopes(array $validated_scopes)
    {
        $this->validated_scopes = $validated_scopes;
    }
}

