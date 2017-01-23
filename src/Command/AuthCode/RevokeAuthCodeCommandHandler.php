<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2017 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\Command\AuthCode;

use OAuth2\Model\AuthCode\AuthCodeRepositoryInterface;

final class RevokeAuthCodeCommandHandler
{
    /**
     * @var AuthCodeRepositoryInterface
     */
    private $authCodeRepository;

    /**
     * CreateClientCommandHandler constructor.
     *
     * @param AuthCodeRepositoryInterface $authCodeRepository
     */
    public function __construct(AuthCodeRepositoryInterface $authCodeRepository)
    {
        $this->authCodeRepository = $authCodeRepository;
    }

    /**
     * @param RevokeAuthCodeCommand $command
     */
    public function handle(RevokeAuthCodeCommand $command)
    {
        $authCodeId = $command->getAuthCodeId();
        $this->authCodeRepository->revoke($authCodeId);
    }
}
