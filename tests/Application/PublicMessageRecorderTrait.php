<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\Test\Application;

use SimpleBus\Message\Recorder\PublicMessageRecorder;

trait PublicMessageRecorderTrait
{
    /**
     * @var null|PublicMessageRecorder
     */
    private $publicMessageRecorder = null;

    /**
     * @return PublicMessageRecorder
     */
    public function getPublicMessageRecorder(): PublicMessageRecorder
    {
        if (null === $this->publicMessageRecorder) {
            $this->publicMessageRecorder = new PublicMessageRecorder();
        }

        return $this->publicMessageRecorder;
    }
}
