<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-2">
            <flux:button href="/admin/customers" wire:navigate size="sm" variant="ghost">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back
            </flux:button>
        </div>
        <h1 class="text-2xl font-bold dark:text-white">Create Customer</h1>
        <p class="text-sm text-zinc-600 dark:text-zinc-400">Add a new customer to the system</p>
    </div>

    <form wire:submit="create" class="space-y-6">
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 space-y-6">
            <h2 class="text-lg font-semibold dark:text-white">Customer Information</h2>

            <flux:input
                wire:model="name"
                label="Name"
                type="text"
                placeholder="e.g., John Doe"
                required
            />

            <flux:input
                wire:model="phone"
                label="Phone"
                type="text"
                placeholder="e.g., +1234567890"
                required
            />

            <flux:input
                wire:model="email"
                label="Email (Optional)"
                type="email"
                placeholder="e.g., john@example.com"
            />
        </div>

        <div class="flex items-center justify-end gap-3">
            <flux:button type="button" variant="ghost" href="/admin/customers" wire:navigate>
                Cancel
            </flux:button>
            <flux:button type="submit" variant="primary">
                <span wire:loading.remove wire:target="create">Create Customer</span>
                <span wire:loading wire:target="create">Creating...</span>
            </flux:button>
        </div>
    </form>
</div>
