<?php

namespace App\Livewire\Admin\Menus;

use App\Models\Menu;
use App\Models\Product;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public Menu $menu;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public $image = null;

    public ?string $existing_image = null;

    public int $sort_order = 0;

    public bool $is_available = true;

    public array $selectedVariants = [];

    /**
     * Mount the component with existing menu data.
     */
    public function mount(Menu $menu): void
    {
        $this->menu = $menu;
        $this->name = $menu->name;
        $this->slug = $menu->slug;
        $this->description = $menu->description ?? '';
        $this->existing_image = $menu->image;
        $this->sort_order = $menu->sort_order;
        $this->is_available = $menu->is_available;
        $this->selectedVariants = $menu->variants()->pluck('product_variants.id')->map(fn ($id) => (string) $id)->toArray();
    }

    /**
     * Update slug when name changes.
     */
    public function updatedName(): void
    {
        $this->slug = Str::slug($this->name);
    }

    /**
     * Update the menu.
     */
    public function update(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:menus,slug,'.$this->menu->id],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_available' => ['required', 'boolean'],
            'selectedVariants' => ['nullable', 'array'],
            'selectedVariants.*' => ['exists:product_variants,id'],
        ]);

        $menuImage = $this->existing_image;

        if ($this->image) {
            $menuImage = $this->image->store('menus', 'public');

            if ($this->existing_image) {
                \Storage::disk('public')->delete($this->existing_image);
            }
        }

        $this->menu->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'],
            'image' => $menuImage,
            'sort_order' => $validated['sort_order'],
            'is_available' => $validated['is_available'],
        ]);

        $this->menu->variants()->sync($validated['selectedVariants'] ?? []);

        session()->flash('success', 'Menu updated successfully.');

        $this->redirect('/admin/menus', navigate: true);
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $products = Product::with('variants')->ordered()->get();

        return view('livewire.admin.menus.edit', [
            'products' => $products,
        ]);
    }
}
