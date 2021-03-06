<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\Token;

use Assert\Assertion;

class Token implements TokenInterface
{
    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @var string
     */
    protected $token;

    /**
     * @var int
     */
    protected $expires_at;

    /**
     * @var string|null
     */
    protected $user_account_public_id;

    /**
     * {@inheritdoc}
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * {@inheritdoc}
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserAccountPublicId()
    {
        return $this->user_account_public_id;
    }

    /**
     * {@inheritdoc}
     */
    public function setUserAccountPublicId($user_account_public_id)
    {
        $this->user_account_public_id = $user_account_public_id;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiresAt()
    {
        return $this->expires_at;
    }

    /**
     * {@inheritdoc}
     */
    public function setExpiresAt($expires_at)
    {
        Assertion::integer($expires_at);
        Assertion::greaterOrEqualThan($expires_at, 0);
        $this->expires_at = $expires_at;
    }

    /**
     * {@inheritdoc}
     */
    public function hasExpired()
    {
        $expires_at = $this->expires_at;
        if (0 === $expires_at) {
            return false;
        }

        return $this->expires_at < time();
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiresIn()
    {
        $expires_at = $this->expires_at;
        if (0 === $expires_at) {
            return 0;
        }

        return $this->expires_at - time() < 0 ? 0 : $this->expires_at - time();
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($key, $value)
    {
        Assertion::string($key);
        $this->parameters[$key] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($key)
    {
        Assertion::true($this->hasParameter($key), sprintf('Parameter with key "%s" does not exist.', $key));

        return $this->parameters[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameter($key)
    {
        Assertion::string($key);

        return array_key_exists($key, $this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function unsetParameter($key)
    {
        Assertion::string($key);
        if (array_key_exists($key, $this->parameters)) {
            unset($this->parameters[$key]);
        }
    }
}
