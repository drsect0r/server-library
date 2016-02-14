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

interface PasswordClientInterface extends ConfidentialClientInterface
{
    /**
     * @return string
     */
    public function getSecret();
}
