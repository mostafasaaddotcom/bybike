<?php

namespace App\Enums;

enum EventStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Canceled = 'canceled';

    /**
     * Get the label for the event status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Paid => 'Paid',
            self::Confirmed => 'Confirmed',
            self::Completed => 'Completed',
            self::Canceled => 'Canceled',
        };
    }

    /**
     * Get the color for the status badge.
     */
    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Paid => 'blue',
            self::Confirmed => 'green',
            self::Completed => 'gray',
            self::Canceled => 'red',
        };
    }
}
