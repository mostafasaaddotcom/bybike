<?php

namespace App\Enums;

enum Brand: string
{
    case ByBike = 'byBike';
    case Bikis = 'bikis';

    /**
     * Get the label for the brand.
     */
    public function label(): string
    {
        return match ($this) {
            self::ByBike => 'byBike',
            self::Bikis => 'Biki\'s',
        };
    }
}
