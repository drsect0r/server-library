<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\Command\Client;

use OAuth2\DataTransporter;
use OAuth2\Model\UserAccount\UserAccount;

final class CreateClientCommand
{
    /**
     * @var array
     */
    private $parameters;

    /**
     * @var UserAccount
     */
    private $userAccount;

    /**
     * @var DataTransporter
     */
    private $callback;

    /**
     * CreateClientCommand constructor.
     *
     * @param UserAccount     $userAccount
     * @param array           $parameters
     * @param DataTransporter $callback
     */
    protected function __construct(UserAccount $userAccount, array $parameters, DataTransporter $callback)
    {
        $this->parameters = $parameters;
        $this->userAccount = $userAccount;
        $this->callback = $callback;
    }

    /**
     * @param UserAccount     $userAccount
     * @param array           $parameters
     * @param DataTransporter $callback
     *
     * @return CreateClientCommand
     */
    public static function create(UserAccount $userAccount, array $parameters, DataTransporter $callback): self
    {
        return new self($userAccount, $parameters, $callback);
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return UserAccount
     */
    public function getUserAccount(): UserAccount
    {
        return $this->userAccount;
    }

    /**
     * @return DataTransporter
     */
    public function getCallback(): DataTransporter
    {
        return $this->callback;
    }
}
