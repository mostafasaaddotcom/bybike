<?php

namespace App\Livewire\Admin\Menus;

use App\Models\Menu;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?string $availabilityFilter = null;

    /**
     * Reset pagination when search or filters change.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedAvailabilityFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Delete a menu.
     */
    public function delete(int $menuId): void
    {
        $menu = Menu::findOrFail($menuId);
        $menu->delete();

        $this->dispatch('menu-deleted');
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $query = Menu::query()
            ->withCount('variants');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        if ($this->availabilityFilter === 'available') {
            $query->where('is_available', true);
        } elseif ($this->availabilityFilter === 'unavailable') {
            $query->where('is_available', false);
        }

        $menus = $query->ordered()->paginate(15);

        return view('livewire.admin.menus.index', [
            'menus' => $menus,
        ]);
    }
}
