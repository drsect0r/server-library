<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\Behaviour;

use Assert\Assertion;
use Jose\JWTLoaderInterface;

trait HasJWTLoader
{
    /**
     * @var \Jose\JWTLoaderInterface|null
     */
    private $jwt_loader = null;

    /**
     * @return bool
     */
    protected function hasJWTLoader()
    {
        return null !== $this->jwt_loader;
    }

    /**
     * @return \Jose\JWTLoaderInterface
     */
    protected function getJWTLoader()
    {
        Assertion::true($this->hasJWTLoader(), 'The JWT Loader is not available.');

        return $this->jwt_loader;
    }

    /**
     * @param \Jose\JWTLoaderInterface $jwt_loader
     */
    protected function setJWTLoader(JWTLoaderInterface $jwt_loader)
    {
        $this->jwt_loader = $jwt_loader;
    }
}
