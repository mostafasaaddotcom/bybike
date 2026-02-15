<?php

namespace App\Livewire\Admin\Products;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public Product $product;

    public ?int $category_id = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public $image = null;

    public ?string $existing_image = null;

    public int $sort_order = 0;

    public bool $is_available = true;

    public bool $is_for_birthday = false;

    public bool $is_for_wedding = false;

    public bool $is_indoor = false;

    public ?string $brand = null;

    public array $variants = [];

    /**
     * Mount the component with existing product data.
     */
    public function mount(Product $product): void
    {
        $this->product = $product->load(['variants.priceTiers']);
        $this->category_id = $product->category_id;
        $this->name = $product->name;
        $this->slug = $product->slug;
        $this->description = $product->description ?? '';
        $this->existing_image = $product->image;
        $this->sort_order = $product->sort_order;
        $this->is_available = $product->is_available;
        $this->is_for_birthday = $product->is_for_birthday;
        $this->is_for_wedding = $product->is_for_wedding;
        $this->is_indoor = $product->is_indoor;
        $this->brand = $product->brand;

        foreach ($product->variants as $variant) {
            $this->variants[] = [
                'id' => $variant->id,
                'name' => $variant->name,
                'image' => null,
                'existing_image' => $variant->image,
                'minimum_order_quantity' => $variant->minimum_order_quantity,
                'increase_rate' => $variant->increase_rate,
                'maximum_available_quantity_per_day' => $variant->maximum_available_quantity_per_day,
                'is_available' => $variant->is_available,
                'price_tiers' => $variant->priceTiers->map(fn ($tier) => [
                    'id' => $tier->id,
                    'quantity_from' => $tier->quantity_from,
                    'quantity_to' => $tier->quantity_to,
                    'price' => $tier->price,
                ])->toArray(),
            ];
        }

        if (empty($this->variants)) {
            $this->addVariant();
        }
    }

    /**
     * Update slug when name changes.
     */
    public function updatedName(): void
    {
        $this->slug = Str::slug($this->name);
    }

    /**
     * Add a new variant.
     */
    public function addVariant(): void
    {
        $this->variants[] = [
            'id' => null,
            'name' => '',
            'image' => null,
            'existing_image' => null,
            'minimum_order_quantity' => 1,
            'increase_rate' => 1,
            'maximum_available_quantity_per_day' => null,
            'is_available' => true,
            'price_tiers' => [
                [
                    'id' => null,
                    'quantity_from' => 1,
                    'quantity_to' => null,
                    'price' => 0,
                ],
            ],
        ];
    }

    /**
     * Remove a variant.
     */
    public function removeVariant(int $index): void
    {
        if (count($this->variants) > 1) {
            unset($this->variants[$index]);
            $this->variants = array_values($this->variants);
        }
    }

    /**
     * Add a price tier to a variant.
     */
    public function addPriceTier(int $variantIndex): void
    {
        $this->variants[$variantIndex]['price_tiers'][] = [
            'id' => null,
            'quantity_from' => 1,
            'quantity_to' => null,
            'price' => 0,
        ];
    }

    /**
     * Remove a price tier from a variant.
     */
    public function removePriceTier(int $variantIndex, int $tierIndex): void
    {
        if (count($this->variants[$variantIndex]['price_tiers']) > 1) {
            unset($this->variants[$variantIndex]['price_tiers'][$tierIndex]);
            $this->variants[$variantIndex]['price_tiers'] = array_values($this->variants[$variantIndex]['price_tiers']);
        }
    }

    /**
     * Update the product with variants and price tiers.
     */
    public function update(): void
    {
        $validated = $this->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:products,slug,'.$this->product->id],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_available' => ['required', 'boolean'],
            'is_for_birthday' => ['required', 'boolean'],
            'is_for_wedding' => ['required', 'boolean'],
            'is_indoor' => ['required', 'boolean'],
            'brand' => ['required', 'string', 'max:255'],
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.name' => ['required', 'string', 'max:255'],
            'variants.*.image' => ['nullable', 'image', 'max:2048'],
            'variants.*.minimum_order_quantity' => ['required', 'integer', 'min:1'],
            'variants.*.increase_rate' => ['required', 'integer', 'min:1'],
            'variants.*.maximum_available_quantity_per_day' => ['nullable', 'integer', 'min:1'],
            'variants.*.is_available' => ['required', 'boolean'],
            'variants.*.price_tiers' => ['required', 'array', 'min:1'],
            'variants.*.price_tiers.*.quantity_from' => ['required', 'integer', 'min:1'],
            'variants.*.price_tiers.*.quantity_to' => ['nullable', 'integer', 'min:1'],
            'variants.*.price_tiers.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        $productImage = $this->existing_image;

        if ($this->image) {
            $productImage = $this->image->store('products', 'public');

            if ($this->existing_image) {
                \Storage::disk('public')->delete($this->existing_image);
            }
        }

        $this->product->update([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'],
            'image' => $productImage,
            'sort_order' => $validated['sort_order'],
            'is_available' => $validated['is_available'],
            'is_for_birthday' => $validated['is_for_birthday'],
            'is_for_wedding' => $validated['is_for_wedding'],
            'is_indoor' => $validated['is_indoor'],
            'brand' => $validated['brand'],
        ]);

        $existingVariantIds = collect($validated['variants'])->pluck('id')->filter()->toArray();
        $this->product->variants()->whereNotIn('id', $existingVariantIds)->delete();

        foreach ($validated['variants'] as $variantData) {
            $variantImage = $variantData['existing_image'] ?? null;

            if (isset($variantData['image']) && $variantData['image']) {
                $variantImage = $variantData['image']->store('variants', 'public');

                if ($variantData['existing_image'] ?? null) {
                    \Storage::disk('public')->delete($variantData['existing_image']);
                }
            }

            $variant = $this->product->variants()->updateOrCreate(
                ['id' => $variantData['id'] ?? null],
                [
                    'name' => $variantData['name'],
                    'image' => $variantImage,
                    'minimum_order_quantity' => $variantData['minimum_order_quantity'],
                    'increase_rate' => $variantData['increase_rate'],
                    'maximum_available_quantity_per_day' => $variantData['maximum_available_quantity_per_day'],
                    'is_available' => $variantData['is_available'],
                ]
            );

            $existingTierIds = collect($variantData['price_tiers'])->pluck('id')->filter()->toArray();
            $variant->priceTiers()->whereNotIn('id', $existingTierIds)->delete();

            foreach ($variantData['price_tiers'] as $tierData) {
                $variant->priceTiers()->updateOrCreate(
                    ['id' => $tierData['id'] ?? null],
                    [
                        'quantity_from' => $tierData['quantity_from'],
                        'quantity_to' => $tierData['quantity_to'],
                        'price' => $tierData['price'],
                    ]
                );
            }
        }

        session()->flash('success', 'Product updated successfully.');

        $this->redirect('/admin/products', navigate: true);
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $categories = Category::ordered()->get();

        return view('livewire.admin.products.edit', [
            'categories' => $categories,
        ]);
    }
}
