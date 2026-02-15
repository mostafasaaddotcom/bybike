<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-2">
            <flux:button href="/admin/events" wire:navigate size="sm" variant="ghost">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back
            </flux:button>
        </div>
        <h1 class="text-2xl font-bold dark:text-white">Create Event</h1>
        <p class="text-sm text-zinc-600 dark:text-zinc-400">Add a new event to the system</p>
    </div>

    <form wire:submit="create" class="space-y-6">
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 space-y-6">
            <h2 class="text-lg font-semibold dark:text-white">Event Information</h2>

            <flux:select wire:model="customer_id" label="Customer" placeholder="Select a customer" required>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                @endforeach
            </flux:select>

            <flux:select wire:model="brand" label="Brand" placeholder="Select brand" required>
                @foreach($brands as $brand)
                    <option value="{{ $brand->value }}">{{ $brand->label() }}</option>
                @endforeach
            </flux:select>

            <div class="grid grid-cols-2 gap-4">
                <flux:select wire:model="type" label="Event Type" placeholder="Select type" required>
                    @foreach($eventTypes as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="status" label="Status" placeholder="Select status" required>
                    @foreach($eventStatuses as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input
                    wire:model="number_of_attendees"
                    label="Number of Attendees"
                    type="number"
                    min="1"
                    required
                />

                <flux:input
                    wire:model="date"
                    label="Event Date"
                    type="date"
                    required
                />
            </div>

            <flux:input
                wire:model="location"
                label="Location"
                type="text"
                placeholder="e.g., Grand Ballroom"
                required
            />

            <div class="flex items-center gap-2">
                <flux:switch wire:model="is_indoor" />
                <flux:label>Indoor Event</flux:label>
            </div>

            <div>
                <flux:label>Notes (Optional)</flux:label>
                <textarea
                    wire:model="notes"
                    rows="4"
                    class="w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Additional notes about the event..."
                ></textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <flux:button type="button" variant="ghost" href="/admin/events" wire:navigate>
                Cancel
            </flux:button>
            <flux:button type="submit" variant="primary">
                <span wire:loading.remove wire:target="create">Create Event</span>
                <span wire:loading wire:target="create">Creating...</span>
            </flux:button>
        </div>
    </form>
</div>
