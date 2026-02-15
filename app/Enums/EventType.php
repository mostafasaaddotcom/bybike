<?php

namespace App\Enums;

enum EventType: string
{
    case Birthday = 'birthday';
    case Wedding = 'wedding';
    case Corporate = 'corporate';
    case Private = 'private';

    /**
     * Get the label for the event type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Birthday => 'Birthday',
            self::Wedding => 'Wedding',
            self::Corporate => 'Corporate',
            self::Private => 'Private',
        };
    }
}
