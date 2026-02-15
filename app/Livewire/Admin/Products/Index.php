<?php

namespace App\Livewire\Admin\Products;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $categoryFilter = null;

    public ?string $availabilityFilter = null;

    /**
     * Reset pagination when search or filters change.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedAvailabilityFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Delete a product.
     */
    public function delete(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $product->delete();

        $this->dispatch('product-deleted');
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $query = Product::query()
            ->with('category')
            ->withCount('variants');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        if ($this->availabilityFilter === 'available') {
            $query->where('is_available', true);
        } elseif ($this->availabilityFilter === 'unavailable') {
            $query->where('is_available', false);
        }

        $products = $query->ordered()->paginate(15);
        $categories = Category::ordered()->get();

        return view('livewire.admin.products.index', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
