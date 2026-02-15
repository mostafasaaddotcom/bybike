<?php

namespace App\Models;

use App\Enums\Brand;
use App\Enums\EventStatus;
use App\Enums\EventType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'brand',
        'type',
        'number_of_attendees',
        'location',
        'date',
        'is_indoor',
        'status',
        'notes',
        'public_invoice_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'brand' => Brand::class,
            'type' => EventType::class,
            'status' => EventStatus::class,
            'date' => 'date',
            'is_indoor' => 'boolean',
            'number_of_attendees' => 'integer',
        ];
    }

    /**
     * Get the customer that owns the event.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the invoice associated with the event.
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * Scope a query to only include upcoming events.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString());
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus($query, ?EventStatus $status = null)
    {
        if (! $status) {
            return $query;
        }

        return $query->where('status', $status->value);
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeByType($query, ?EventType $type = null)
    {
        if (! $type) {
            return $query;
        }

        return $query->where('type', $type->value);
    }

    /**
     * Scope a query to filter by brand.
     */
    public function scopeByBrand($query, ?Brand $brand = null)
    {
        if (! $brand) {
            return $query;
        }

        return $query->where('brand', $brand->value);
    }

    /**
     * Scope a query to search events.
     */
    public function scopeSearch($query, ?string $search = null)
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('location', 'like', "%{$search}%")
                ->orWhereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        });
    }

    /**
     * Generate a unique public invoice token.
     */
    public static function generatePublicInvoiceToken(): string
    {
        do {
            $token = Str::random(16);
        } while (self::where('public_invoice_token', $token)->exists());

        return $token;
    }

    /**
     * Get the public invoice URL attribute.
     */
    protected function publicInvoiceUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->public_invoice_token
                ? route('invoice.public', ['token' => $this->public_invoice_token])
                : null,
        );
    }
}
