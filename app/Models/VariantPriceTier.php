<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantPriceTier extends Model
{
    /** @use HasFactory<\Database\Factories\VariantPriceTierFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'variant_id',
        'quantity_from',
        'quantity_to',
        'price',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity_from' => 'integer',
            'quantity_to' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    /**
     * Get the variant that owns the price tier.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Scope a query to order by quantity_from.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('quantity_from');
    }
}
