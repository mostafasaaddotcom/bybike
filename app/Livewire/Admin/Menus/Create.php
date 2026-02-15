<?php

namespace App\Livewire\Admin\Menus;

use App\Models\Menu;
use App\Models\Product;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public $image = null;

    public int $sort_order = 0;

    public bool $is_available = true;

    public array $selectedVariants = [];

    /**
     * Update slug when name changes.
     */
    public function updatedName(): void
    {
        $this->slug = Str::slug($this->name);
    }

    /**
     * Create a new menu.
     */
    public function create(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:menus,slug'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_available' => ['required', 'boolean'],
            'selectedVariants' => ['nullable', 'array'],
            'selectedVariants.*' => ['exists:product_variants,id'],
        ]);

        if ($this->image) {
            $validated['image'] = $this->image->store('menus', 'public');
        }

        $menu = Menu::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'],
            'image' => $validated['image'] ?? null,
            'sort_order' => $validated['sort_order'],
            'is_available' => $validated['is_available'],
        ]);

        if (! empty($validated['selectedVariants'])) {
            $menu->variants()->attach($validated['selectedVariants']);
        }

        session()->flash('success', 'Menu created successfully.');

        $this->redirect('/admin/menus', navigate: true);
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $products = Product::with('variants')->ordered()->get();

        return view('livewire.admin.menus.create', [
            'products' => $products,
        ]);
    }
}
