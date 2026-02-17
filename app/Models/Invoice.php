<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory;

    protected $fillable = [
        'event_id',
        'invoice_number',
        'status',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total',
        'notes',
        'issued_at',
        'due_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'issued_at' => 'date',
            'due_at' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function calculateSubtotal(): float
    {
        return $this->items->sum('subtotal');
    }

    public function calculateTax(): float
    {
        return ($this->subtotal * $this->tax_rate) / 100;
    }

    public function calculateTotal(): float
    {
        return $this->subtotal + $this->tax_amount - $this->discount_amount;
    }

    public function recalculate(): void
    {
        $this->subtotal = $this->calculateSubtotal();
        $this->tax_amount = $this->calculateTax();
        $this->total = $this->calculateTotal();
        $this->save();
    }

    public function markAsPaid(): void
    {
        $this->status = InvoiceStatus::Paid;
        $this->paid_at = now();
        $this->save();
    }

    public static function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $lastInvoice = static::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -4) + 1 : 1;

        return sprintf('INV-%d-%04d', $year, $sequence);
    }

    public function scopeByStatus($query, InvoiceStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', InvoiceStatus::Paid)
            ->where('status', '!=', InvoiceStatus::Canceled)
            ->whereNotNull('due_at')
            ->whereDate('due_at', '<', now());
    }

    public function scopeSearch($query, ?string $search)
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('invoice_number', 'like', "%{$search}%")
                ->orWhereHas('event', function ($eventQuery) use ($search) {
                    $eventQuery->where('location', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
        });
    }
}
