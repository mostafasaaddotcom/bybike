<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <flux:heading size="xl">Dashboard</flux:heading>
        <flux:text class="mt-1">Overview of your business</flux:text>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <a href="{{ route('admin.customers.index') }}" wire:navigate class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-6 hover:border-zinc-400 dark:hover:border-zinc-500 transition-colors">
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Total Customers</flux:text>
            <flux:heading size="xl" class="mt-2">{{ $totalCustomers }}</flux:heading>
        </a>

        <a href="{{ route('admin.invoices.index', ['statusFilter' => 'draft']) }}" wire:navigate class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-6 hover:border-zinc-400 dark:hover:border-zinc-500 transition-colors">
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Draft Invoices</flux:text>
            <flux:heading size="xl" class="mt-2">{{ $draftInvoices }}</flux:heading>
        </a>

        <a href="{{ route('admin.invoices.index', ['statusFilter' => 'pending']) }}" wire:navigate class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-6 hover:border-zinc-400 dark:hover:border-zinc-500 transition-colors">
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Pending Invoices</flux:text>
            <flux:heading size="xl" class="mt-2">{{ $pendingInvoices }}</flux:heading>
        </a>

        <a href="{{ route('admin.invoices.index', ['statusFilter' => 'paid']) }}" wire:navigate class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-6 hover:border-zinc-400 dark:hover:border-zinc-500 transition-colors">
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Paid Invoices</flux:text>
            <flux:heading size="xl" class="mt-2">{{ $paidInvoices }}</flux:heading>
        </a>

        <a href="{{ route('admin.invoices.index') }}" wire:navigate class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-6 hover:border-zinc-400 dark:hover:border-zinc-500 transition-colors">
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Total Invoices</flux:text>
            <flux:heading size="xl" class="mt-2">{{ $totalInvoices }}</flux:heading>
        </a>
    </div>
</div>
