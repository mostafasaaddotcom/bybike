<?php

namespace App\Livewire\Admin\Products;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public ?int $category_id = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public $image = null;

    public int $sort_order = 0;

    public bool $is_available = true;

    public bool $is_for_birthday = false;

    public bool $is_for_wedding = false;

    public bool $is_indoor = false;

    public ?string $brand = null;

    public array $variants = [];

    /**
     * Mount the component with one default variant.
     */
    public function mount(): void
    {
        $this->addVariant();
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
            'name' => '',
            'image' => null,
            'minimum_order_quantity' => 1,
            'increase_rate' => 1,
            'maximum_available_quantity_per_day' => null,
            'is_available' => true,
            'price_tiers' => [
                [
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
     * Create a new product with variants and price tiers.
     */
    public function create(): void
    {
        $validated = $this->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:products,slug'],
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

        if ($this->image) {
            $validated['image'] = $this->image->store('products', 'public');
        }

        $product = Product::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'],
            'image' => $validated['image'] ?? null,
            'sort_order' => $validated['sort_order'],
            'is_available' => $validated['is_available'],
            'is_for_birthday' => $validated['is_for_birthday'],
            'is_for_wedding' => $validated['is_for_wedding'],
            'is_indoor' => $validated['is_indoor'],
            'brand' => $validated['brand'],
        ]);

        foreach ($validated['variants'] as $variantData) {
            $variantImage = null;

            if (isset($variantData['image']) && $variantData['image']) {
                $variantImage = $variantData['image']->store('variants', 'public');
            }

            $variant = $product->variants()->create([
                'name' => $variantData['name'],
                'image' => $variantImage,
                'minimum_order_quantity' => $variantData['minimum_order_quantity'],
                'increase_rate' => $variantData['increase_rate'],
                'maximum_available_quantity_per_day' => $variantData['maximum_available_quantity_per_day'],
                'is_available' => $variantData['is_available'],
            ]);

            foreach ($variantData['price_tiers'] as $tierData) {
                $variant->priceTiers()->create([
                    'quantity_from' => $tierData['quantity_from'],
                    'quantity_to' => $tierData['quantity_to'],
                    'price' => $tierData['price'],
                ]);
            }
        }

        session()->flash('success', 'Product created successfully with '.count($validated['variants']).' variant(s).');

        $this->redirect('/admin/products', navigate: true);
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $categories = Category::ordered()->get();

        return view('livewire.admin.products.create', [
            'categories' => $categories,
        ]);
    }
}
