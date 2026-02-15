<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    /** @use HasFactory<\Database\Factories\ProductVariantFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'name',
        'image',
        'minimum_order_quantity',
        'increase_rate',
        'maximum_available_quantity_per_day',
        'is_available',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'minimum_order_quantity' => 'integer',
            'increase_rate' => 'integer',
            'maximum_available_quantity_per_day' => 'integer',
            'is_available' => 'boolean',
        ];
    }

    /**
     * Get the product that owns the variant.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the price tiers for the variant.
     */
    public function priceTiers(): HasMany
    {
        return $this->hasMany(VariantPriceTier::class, 'variant_id');
    }

    /**
     * Get the menus that the variant belongs to.
     */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'menu_product_variant')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    /**
     * Get the invoice items for this variant.
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Scope a query to only include available variants.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Get the image URL.
     */
    public function imageUrl(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }

    /**
     * Get the price for a given quantity.
     */
    public function getPriceForQuantity(int $quantity): ?float
    {
        $tier = $this->priceTiers()
            ->where('quantity_from', '<=', $quantity)
            ->where(function ($query) use ($quantity) {
                $query->where('quantity_to', '>=', $quantity)
                    ->orWhereNull('quantity_to');
            })
            ->first();

        return $tier?->price;
    }

    /**
     * Check if a quantity is valid for this variant.
     */
    public function isValidQuantity(int $quantity): bool
    {
        if ($quantity < $this->minimum_order_quantity) {
            return false;
        }

        if ($this->increase_rate > 1 && ($quantity - $this->minimum_order_quantity) % $this->increase_rate !== 0) {
            return false;
        }

        return true;
    }
}
