<?php declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\Test\Stub\Event;

use OAuth2\Model\Event\Event;

abstract class EventHandler
{
    /**
     * @var Event[]
     */
    private $events = [];

    /**
     * @param Event $event
     */
    protected function save(Event $event)
    {
        $this->events[] = $event;
    }

    /**
     * @return Event[]
     */
    public function getEvents(): array
    {
        return $this->events;
    }
}
