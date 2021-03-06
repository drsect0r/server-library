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

use OAuth2\TokenType\MacToken as Base;

class MacToken extends Base
{
    /**
     * {@inheritdoc}
     */
    protected function generateMacKey()
    {
        return bin2hex(random_bytes(50));
    }
}
