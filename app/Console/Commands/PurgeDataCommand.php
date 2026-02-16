<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Console\Command;

class PurgeDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:purge-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete all customers, events, and invoices';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {

        $invoiceItemCount = InvoiceItem::count();
        InvoiceItem::query()->delete();

        $invoiceCount = Invoice::withTrashed()->count();
        Invoice::withTrashed()->forceDelete();

        $eventCount = Event::withTrashed()->count();
        Event::withTrashed()->forceDelete();

        $customerCount = Customer::withTrashed()->count();
        Customer::withTrashed()->forceDelete();

        $this->info("Purged: {$invoiceItemCount} invoice items, {$invoiceCount} invoices, {$eventCount} events, {$customerCount} customers.");

        return self::SUCCESS;
    }
}
