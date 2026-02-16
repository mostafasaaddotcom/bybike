<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold dark:text-white">Events</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Manage customer events</p>
        </div>
        <flux:button variant="primary" href="/admin/events/create" wire:navigate>
            Create Event
        </flux:button>
    </div>

    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Search by customer or location..."
                type="text"
            />

            <flux:select wire:model.live="brandFilter" placeholder="All Brands">
                <option value="">All Brands</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->value }}">{{ $brand->label() }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="typeFilter" placeholder="All Types">
                <option value="">All Types</option>
                @foreach($eventTypes as $type)
                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="statusFilter" placeholder="All Statuses">
                <option value="">All Statuses</option>
                @foreach($eventStatuses as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </flux:select>
        </div>
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Brand</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Attendees</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($events as $event)
                    <tr wire:key="event-{{ $event->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $event->customer?->name ?? 'Deleted' }}</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $event->customer?->phone }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-zinc-900 dark:text-zinc-300">{{ $event->brand->label() }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-zinc-900 dark:text-zinc-300">{{ $event->type->label() }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-zinc-900 dark:text-zinc-300">{{ $event->date->format('M d, Y') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-zinc-900 dark:text-zinc-300">{{ $event->location }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-zinc-900 dark:text-zinc-300">{{ $event->number_of_attendees }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <flux:badge variant="{{ $event->status->color() }}">{{ $event->status->label() }}</flux:badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                @if($event->invoice)
                                    <flux:button size="sm" variant="primary" href="/admin/invoices/{{ $event->invoice->id }}/view" wire:navigate>
                                        View Invoice
                                    </flux:button>
                                    <flux:button size="sm" variant="primary" href="/invoice/{{ $event->public_invoice_token }}" wire:navigate>
                                        Invoice Link
                                    </flux:button>
                                @else
                                    <flux:button size="sm" variant="primary" href="/admin/invoices/create?event={{ $event->id }}" wire:navigate>
                                        Create Invoice
                                    </flux:button>
                                @endif
                                <flux:button size="sm" variant="ghost" href="/admin/events/{{ $event->id }}/edit" wire:navigate>
                                    Edit
                                </flux:button>
                                <flux:button
                                    size="sm"
                                    variant="danger"
                                    wire:click="delete({{ $event->id }})"
                                    wire:confirm="Are you sure you want to delete this event?"
                                >
                                    Delete
                                </flux:button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            No events found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700">
            {{ $events->links() }}
        </div>
    </div>
</div>
