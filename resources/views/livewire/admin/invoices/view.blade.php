<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
            <flux:button href="/admin/invoices" wire:navigate size="sm" variant="ghost">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back
            </flux:button>
            <div class="flex gap-2">
                @if($invoice->status !== \App\Enums\InvoiceStatus::Paid)
                    <flux:button wire:click="markAsPaid" variant="primary" wire:confirm="Are you sure you want to mark this invoice as paid?">
                        Mark as Paid
                    </flux:button>
                @endif
                <flux:button href="/admin/invoices/{{ $invoice->id }}/edit" wire:navigate variant="ghost">
                    Edit
                </flux:button>
            </div>
        </div>
        <h1 class="text-2xl font-bold dark:text-white">Invoice #{{ $invoice->invoice_number }}</h1>
        <p class="text-sm text-zinc-600 dark:text-zinc-400">View invoice details</p>
    </div>

    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
        </div>
    @endif

    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-8 space-y-8">
        <div class="grid grid-cols-2 gap-8">
            <div>
                <h3 class="text-sm font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-2">Bill To</h3>
                <div class="text-zinc-900 dark:text-white">
                    <p class="font-semibold">{{ $invoice->event->customer->name }}</p>
                    <p class="text-sm">{{ $invoice->event->customer->phone }}</p>
                    @if($invoice->event->customer->email)
                        <p class="text-sm">{{ $invoice->event->customer->email }}</p>
                    @endif
                </div>
            </div>

            <div class="text-right">
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-2">Invoice Details</h3>
                    <p class="text-sm text-zinc-900 dark:text-zinc-300"><span class="font-medium">Status:</span>
                        <flux:badge variant="{{ $invoice->status->color() }}">{{ $invoice->status->label() }}</flux:badge>
                    </p>
                    <p class="text-sm text-zinc-900 dark:text-zinc-300"><span class="font-medium">Issued:</span> {{ $invoice->issued_at->format('M d, Y') }}</p>
                    @if($invoice->due_at)
                        <p class="text-sm text-zinc-900 dark:text-zinc-300"><span class="font-medium">Due:</span> {{ $invoice->due_at->format('M d, Y') }}</p>
                    @endif
                    @if($invoice->paid_at)
                        <p class="text-sm text-zinc-900 dark:text-zinc-300"><span class="font-medium">Paid:</span> {{ $invoice->paid_at->format('M d, Y') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-2">Event Details</h3>
            <div class="text-zinc-900 dark:text-zinc-300">
                <p><span class="font-medium">Type:</span> {{ $invoice->event->type->label() }}</p>
                <p><span class="font-medium">Location:</span> {{ $invoice->event->location }}</p>
                <p><span class="font-medium">Date:</span> {{ $invoice->event->date->format('M d, Y') }}</p>
                <p><span class="font-medium">Attendees:</span> {{ $invoice->event->number_of_attendees }}</p>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-4">Invoice Items</h3>
            <div class="overflow-x-auto border border-zinc-200 dark:border-zinc-700 rounded-lg">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Item</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Unit Price</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach($invoice->items as $item)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $item->product_name }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $item->variant_name }}</div>
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-zinc-900 dark:text-zinc-300">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 text-right text-sm text-zinc-900 dark:text-zinc-300">{{ number_format($item->unit_price, 2) }} LE</td>
                                <td class="px-6 py-4 text-right text-sm font-medium text-zinc-900 dark:text-white">{{ number_format($item->subtotal, 2) }} LE</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end">
            <div class="w-80 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-zinc-600 dark:text-zinc-400">Subtotal</span>
                    <span class="text-zinc-900 dark:text-white font-medium">{{ number_format($invoice->subtotal, 2) }} LE</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-zinc-600 dark:text-zinc-400">Tax ({{ number_format($invoice->tax_rate, 2) }}%)</span>
                    <span class="text-zinc-900 dark:text-white font-medium">{{ number_format($invoice->tax_amount, 2) }} LE</span>
                </div>
                @if($invoice->discount_amount > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-600 dark:text-zinc-400">Discount</span>
                        <span class="text-red-600 dark:text-red-400 font-medium">-{{ number_format($invoice->discount_amount, 2) }} LE</span>
                    </div>
                @endif
                <div class="flex justify-between text-lg font-bold border-t border-zinc-200 dark:border-zinc-700 pt-2">
                    <span class="text-zinc-900 dark:text-white">Total</span>
                    <span class="text-zinc-900 dark:text-white">{{ number_format($invoice->total, 2) }} LE</span>
                </div>
            </div>
        </div>

        @if($invoice->notes)
            <div>
                <h3 class="text-sm font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-2">Notes</h3>
                <p class="text-sm text-zinc-900 dark:text-zinc-300 whitespace-pre-wrap">{{ $invoice->notes }}</p>
            </div>
        @endif
    </div>
</div>
