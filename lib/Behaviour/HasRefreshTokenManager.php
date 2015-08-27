<?php

namespace OAuth2\Behaviour;

use OAuth2\Token\RefreshTokenManagerInterface;

trait HasRefreshTokenManager
{
    /**
     * @var \OAuth2\Token\RefreshTokenManagerInterface|null
     */
    private $refresh_token_manager = null;

    /**
     * @return \OAuth2\Token\RefreshTokenManagerInterface|null
     */
    public function getRefreshTokenManager()
    {
        return $this->refresh_token_manager;
    }

    /**
     * @param \OAuth2\Token\RefreshTokenManagerInterface $refresh_token_manager
     *
     * @return self
     */
    public function setRefreshTokenManager(RefreshTokenManagerInterface $refresh_token_manager)
    {
        $this->refresh_token_manager = $refresh_token_manager;

        return $this;
    }
}
