<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-2">
            <flux:button href="/admin/menus" wire:navigate size="sm" variant="ghost">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back
            </flux:button>
        </div>
        <h1 class="text-2xl font-bold dark:text-white">Edit Menu</h1>
        <p class="text-sm text-zinc-600 dark:text-zinc-400">Update menu information and product variants</p>
    </div>

    <form wire:submit="update" class="space-y-8">
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 space-y-6">
            <h2 class="text-lg font-semibold dark:text-white">Menu Information</h2>

            <flux:input
                wire:model.blur="name"
                label="Menu Name"
                type="text"
                placeholder="e.g., Birthday Party Menu"
                required
            />

            <flux:input
                wire:model="slug"
                label="Slug"
                type="text"
                placeholder="Auto-generated from name"
                required
            />

            <div>
                <flux:label>Description</flux:label>
                <textarea
                    wire:model="description"
                    rows="4"
                    class="w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Describe this menu..."
                ></textarea>
            </div>

            <div>
                <flux:label>Menu Image</flux:label>
                @if($existing_image && !$image)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $existing_image) }}" alt="Current image" class="h-32 w-32 object-cover rounded">
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Current image</p>
                    </div>
                @endif
                <input
                    type="file"
                    wire:model="image"
                    accept="image/*"
                    class="block w-full text-sm text-zinc-500 dark:text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-900 dark:file:text-blue-300 hover:file:bg-blue-100 dark:hover:file:bg-blue-800 cursor-pointer"
                />
                @if ($image)
                    <div class="mt-2">
                        <img src="{{ $image->temporaryUrl() }}" alt="New preview" class="h-32 w-32 object-cover rounded">
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">New image preview</p>
                    </div>
                @endif
                @error('image')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <flux:input
                wire:model="sort_order"
                label="Sort Order"
                type="number"
                min="0"
                placeholder="0"
                required
            />

            <div class="flex items-center gap-2">
                <flux:switch wire:model="is_available" />
                <flux:label>Available for customers</flux:label>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 space-y-6">
            <div>
                <h2 class="text-lg font-semibold dark:text-white">Attach Product Variants</h2>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Select product variants to include in this menu</p>
            </div>

            <div class="max-h-80 overflow-y-auto space-y-4 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                @forelse($products as $product)
                    @if($product->variants->isNotEmpty())
                        <div wire:key="product-{{ $product->id }}">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ $product->name }}</span>
                                @if($product->brand)
                                    <flux:badge>{{ $product->brand }}</flux:badge>
                                @endif
                            </div>
                            <div class="ml-4 space-y-1">
                                @foreach($product->variants as $variant)
                                    <label wire:key="variant-{{ $variant->id }}" class="flex items-center gap-3 p-2 rounded hover:bg-zinc-50 dark:hover:bg-zinc-700/50 cursor-pointer">
                                        <flux:checkbox wire:model="selectedVariants" value="{{ $variant->id }}" />
                                        <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $variant->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @empty
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 text-center py-4">No products available</p>
                @endforelse
            </div>

            @error('selectedVariants')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end gap-3">
            <flux:button type="button" variant="ghost" href="/admin/menus" wire:navigate>
                Cancel
            </flux:button>
            <flux:button type="submit" variant="primary">
                <span wire:loading.remove wire:target="update">Update Menu</span>
                <span wire:loading wire:target="update">Updating...</span>
            </flux:button>
        </div>
    </form>
</div>
