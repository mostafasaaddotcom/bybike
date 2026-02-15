<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-2">
            <flux:button href="/admin/products" wire:navigate size="sm" variant="ghost">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back
            </flux:button>
        </div>
        <h1 class="text-2xl font-bold dark:text-white">Create Product</h1>
        <p class="text-sm text-zinc-600 dark:text-zinc-400">Add a new product with variants and pricing</p>
    </div>

    <form wire:submit="create" class="space-y-8">
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 space-y-6">
            <h2 class="text-lg font-semibold dark:text-white">Product Information</h2>

            <flux:select wire:model="category_id" label="Category" required>
                <option>Select Category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </flux:select>

            <flux:input
                wire:model.blur="name"
                label="Product Name"
                type="text"
                placeholder="e.g., BBQ Burger Combo"
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
                    placeholder="Describe this product..."
                ></textarea>
            </div>

            <div>
                <flux:label>Product Image</flux:label>
                <input
                    type="file"
                    wire:model="image"
                    accept="image/*"
                    class="block w-full text-sm text-zinc-500 dark:text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-900 dark:file:text-blue-300 hover:file:bg-blue-100 dark:hover:file:bg-blue-800 cursor-pointer"
                />
                @if ($image)
                    <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="mt-2 h-32 w-32 object-cover rounded">
                @endif
                @error('image')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input
                    wire:model="sort_order"
                    label="Sort Order"
                    type="number"
                    min="0"
                    placeholder="0"
                    required
                />

                <flux:select wire:model="brand" label="Brand" required>
                    <option>Select Brand</option>
                    <option value="Biki’s">Biki’s</option>
                    <option value="byBike">byBike</option>
                </flux:select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="flex items-center gap-2">
                    <flux:switch wire:model="is_available" />
                    <flux:label>Available for customers</flux:label>
                </div>

                <div class="flex items-center gap-2">
                    <flux:switch wire:model="is_for_birthday" />
                    <flux:label>For Birthday</flux:label>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="flex items-center gap-2">
                    <flux:switch wire:model="is_for_wedding" />
                    <flux:label>For Wedding</flux:label>
                </div>

                <div class="flex items-center gap-2">
                    <flux:switch wire:model="is_indoor" />
                    <flux:label>Indoor Event</flux:label>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold dark:text-white">Product Variants</h2>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">At least one variant is required</p>
                </div>
                <flux:button type="button" wire:click="addVariant" size="sm">
                    Add Variant
                </flux:button>
            </div>

            @foreach($variants as $variantIndex => $variant)
                <div wire:key="variant-{{ $variantIndex }}" class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="font-medium dark:text-white">Variant #{{ $variantIndex + 1 }}</h3>
                        @if(count($variants) > 1)
                            <flux:button
                                type="button"
                                wire:click="removeVariant({{ $variantIndex }})"
                                size="sm"
                                variant="danger"
                            >
                                Remove
                            </flux:button>
                        @endif
                    </div>

                    <flux:input
                        wire:model="variants.{{ $variantIndex }}.name"
                        label="Variant Name"
                        type="text"
                        placeholder="e.g., Small, Medium, Large"
                        required
                    />

                    <div>
                        <flux:label>Variant Image (optional)</flux:label>
                        <input
                            type="file"
                            wire:model="variants.{{ $variantIndex }}.image"
                            accept="image/*"
                            class="block w-full text-sm text-zinc-500 dark:text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-900 dark:file:text-blue-300 hover:file:bg-blue-100 dark:hover:file:bg-blue-800 cursor-pointer"
                        />
                        @error('variants.' . $variantIndex . '.image')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <flux:input
                            wire:model="variants.{{ $variantIndex }}.minimum_order_quantity"
                            label="Min Order Qty"
                            type="number"
                            min="1"
                            required
                        />

                        <flux:input
                            wire:model="variants.{{ $variantIndex }}.increase_rate"
                            label="Increase Rate"
                            type="number"
                            min="1"
                            required
                        />

                        <flux:input
                            wire:model="variants.{{ $variantIndex }}.maximum_available_quantity_per_day"
                            label="Max Per Day"
                            type="number"
                            min="1"
                            placeholder="Unlimited"
                        />
                    </div>

                    <div class="flex items-center gap-2">
                        <flux:switch wire:model="variants.{{ $variantIndex }}.is_available" />
                        <flux:label>Variant available</flux:label>
                    </div>

                    <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-medium dark:text-white">Price Tiers</h4>
                            <flux:button
                                type="button"
                                wire:click="addPriceTier({{ $variantIndex }})"
                                size="sm"
                                variant="ghost"
                            >
                                Add Tier
                            </flux:button>
                        </div>

                        @foreach($variant['price_tiers'] as $tierIndex => $tier)
                            <div wire:key="tier-{{ $variantIndex }}-{{ $tierIndex }}" class="bg-zinc-50 dark:bg-zinc-900 p-3 rounded grid grid-cols-4 gap-3 items-end">
                                <flux:input
                                    wire:model="variants.{{ $variantIndex }}.price_tiers.{{ $tierIndex }}.quantity_from"
                                    label="From Qty"
                                    type="number"
                                    min="1"
                                    required
                                />

                                <flux:input
                                    wire:model="variants.{{ $variantIndex }}.price_tiers.{{ $tierIndex }}.quantity_to"
                                    label="To Qty"
                                    type="number"
                                    min="1"
                                    placeholder="No limit"
                                />

                                <flux:input
                                    wire:model="variants.{{ $variantIndex }}.price_tiers.{{ $tierIndex }}.price"
                                    label="Price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    required
                                />

                                @if(count($variant['price_tiers']) > 1)
                                    <flux:button
                                        type="button"
                                        wire:click="removePriceTier({{ $variantIndex }}, {{ $tierIndex }})"
                                        size="sm"
                                        variant="danger"
                                    >
                                        Remove
                                    </flux:button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            @error('variants')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end gap-3">
            <flux:button type="button" variant="ghost" href="/admin/products" wire:navigate>
                Cancel
            </flux:button>
            <flux:button type="submit" variant="primary">
                <span wire:loading.remove wire:target="create">Create Product</span>
                <span wire:loading wire:target="create">Creating...</span>
            </flux:button>
        </div>
    </form>
</div>
