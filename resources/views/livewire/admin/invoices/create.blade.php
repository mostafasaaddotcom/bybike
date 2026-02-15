<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-2">
            <flux:button href="/admin/invoices" wire:navigate size="sm" variant="ghost">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back
            </flux:button>
        </div>
        <h1 class="text-2xl font-bold dark:text-white">Create Invoice</h1>
        <p class="text-sm text-zinc-600 dark:text-zinc-400">Create a new invoice for an event</p>
    </div>

    <form wire:submit="create" class="space-y-6">
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 space-y-6">
            <h2 class="text-lg font-semibold dark:text-white">Invoice Information</h2>

            <flux:select wire:model.live="event_id" label="Event" required>
                @foreach($events as $event)
                    <option>Select an event</option>
                    <option value="{{ $event->id }}">
                        {{ $event->customer->name }} - {{ $event->location }} ({{ $event->date->format('M d, Y') }})
                    </option>
                @endforeach
            </flux:select>

            <div class="grid grid-cols-2 gap-4">
                <flux:select wire:model="status" label="Status" placeholder="Select status" required>
                    @foreach(\App\Enums\InvoiceStatus::cases() as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </flux:select>

                <flux:input
                    wire:model="tax_rate"
                    label="Tax Rate (%)"
                    type="number"
                    step="0.01"
                    min="0"
                    max="100"
                    required
                />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input
                    wire:model="issued_at"
                    label="Issue Date"
                    type="date"
                    required
                />

                <flux:input
                    wire:model="due_at"
                    label="Due Date (Optional)"
                    type="date"
                />
            </div>

            <flux:input
                wire:model="discount_amount"
                label="Discount Amount (LE)"
                type="number"
                step="0.01"
                min="0"
                required
            />

            <div>
                <flux:label>Notes (Optional)</flux:label>
                <textarea
                    wire:model="notes"
                    rows="3"
                    class="w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Additional notes about the invoice..."
                ></textarea>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold dark:text-white">Invoice Items</h2>
                <flux:button type="button" variant="primary" wire:click="addItem">
                    Add Item
                </flux:button>
            </div>

            <div class="space-y-4">
                @foreach($items as $index => $item)
                    <div wire:key="item-{{ $index }}" class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium dark:text-white">Item #{{ $index + 1 }}</span>
                            @if(count($items) > 1)
                                <flux:button type="button" variant="danger" size="sm" wire:click="removeItem({{ $index }})">
                                    Remove
                                </flux:button>
                            @endif
                        </div>

                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-6">
                                <flux:select wire:model.live="items.{{ $index }}.product_variant_id" label="Product Variant" placeholder="Select variant" required>
                                    @foreach($variants as $variant)
                                        <option value="{{ $variant->id }}">
                                            {{ $variant->product->name }} - {{ $variant->name }}
                                        </option>
                                    @endforeach
                                </flux:select>
                            </div>

                            <div class="col-span-2">
                                <flux:input
                                    wire:model.live="items.{{ $index }}.quantity"
                                    label="Quantity"
                                    type="number"
                                    min="1"
                                    required
                                />
                            </div>

                            <div class="col-span-2">
                                <flux:input
                                    wire:model="items.{{ $index }}.unit_price"
                                    label="Unit Price (LE)"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    required
                                />
                            </div>

                            <div class="col-span-2">
                                <flux:label>Subtotal</flux:label>
                                <div class="mt-1 px-3 py-2 bg-zinc-100 dark:bg-zinc-800 rounded-md">
                                    <span class="text-sm font-medium dark:text-white">
                                        {{ number_format(($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0), 2) }} LE
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <flux:button type="button" variant="ghost" href="/admin/invoices" wire:navigate>
                Cancel
            </flux:button>
            <flux:button type="submit" variant="primary">
                <span wire:loading.remove wire:target="create">Create Invoice</span>
                <span wire:loading wire:target="create">Creating...</span>
            </flux:button>
        </div>
    </form>
</div>
