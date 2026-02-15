<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Paid = 'paid';
    case Canceled = 'canceled';

    /**
     * Get the label for the invoice status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Pending => 'Pending',
            self::Paid => 'Paid',
            self::Canceled => 'Canceled',
        };
    }

    /**
     * Get the color for the status badge.
     */
    public function color(): string
    {
        return match ($this) {
            self::Draft => 'zinc',
            self::Pending => 'yellow',
            self::Paid => 'green',
            self::Canceled => 'red',
        };
    }
}
