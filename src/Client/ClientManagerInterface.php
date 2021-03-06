<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\Client;

interface ClientManagerInterface
{
    /**
     * @return \OAuth2\Client\ClientInterface Return a new client object.
     */
    public function createClient();

    /**
     * Get a client using its Id.
     *
     * @param string $client_id The Id of the client
     *
     * @return null|\OAuth2\Client\ClientInterface Return the client object or null if no client is found.
     */
    public function getClient($client_id);

    /**
     * Save the client.
     *
     * @param \OAuth2\Client\ClientInterface $client
     */
    public function saveClient(ClientInterface $client);

    /**
     * Delete the client.
     *
     * @param \OAuth2\Client\ClientInterface $client
     */
    public function deleteClient(ClientInterface $client);
}
