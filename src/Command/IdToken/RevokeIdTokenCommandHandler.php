<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\Command\IdToken;

use OAuth2\Event\IdToken\IdTokenRevokedEvent;
use OAuth2\Model\IdToken\IdTokenRepositoryInterface;
use SimpleBus\Message\Recorder\RecordsMessages;

final class RevokeIdTokenCommandHandler
{
    /**
     * @var IdTokenRepositoryInterface
     */
    private $idTokenRepository;

    /**
     * @var RecordsMessages
     */
    private $messageRecorder;

    /**
     * CreateClientCommandHandler constructor.
     *
     * @param IdTokenRepositoryInterface $idTokenRepository
     * @param RecordsMessages            $messageRecorder
     */
    public function __construct(IdTokenRepositoryInterface $idTokenRepository, RecordsMessages $messageRecorder)
    {
        $this->idTokenRepository = $idTokenRepository;
        $this->messageRecorder = $messageRecorder;
    }

    /**
     * @param RevokeIdTokenCommand $command
     */
    public function handle(RevokeIdTokenCommand $command)
    {
        $idToken = $command->getIdToken();
        $this->idTokenRepository->revoke($idToken);
        $event = IdTokenRevokedEvent::create($idToken);
        $this->messageRecorder->record($event);
    }
}