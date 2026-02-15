<?php

namespace App\Livewire\Admin\Invoices;

use App\Models\Event;
use App\Models\Invoice;
use App\Models\ProductVariant;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Invoice')]
class Edit extends Component
{
    public Invoice $invoice;

    public int $event_id;

    public string $status = '';

    public float $tax_rate = 0;

    public float $discount_amount = 0;

    public string $notes = '';

    public string $issued_at = '';

    public ?string $due_at = null;

    public array $items = [];

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice->load(['items', 'event.customer']);
        $this->event_id = $invoice->event_id;
        $this->status = $invoice->status->value;
        $this->tax_rate = $invoice->tax_rate;
        $this->discount_amount = $invoice->discount_amount;
        $this->notes = $invoice->notes ?? '';
        $this->issued_at = $invoice->issued_at->toDateString();
        $this->due_at = $invoice->due_at?->toDateString();

        foreach ($invoice->items as $item) {
            $this->items[] = [
                'id' => $item->id,
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ];
        }
    }

    public function addItem(): void
    {
        $this->items[] = [
            'id' => null,
            'product_variant_id' => '',
            'quantity' => 1,
            'unit_price' => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        $itemId = $this->items[$index]['id'] ?? null;
        if ($itemId) {
            $this->invoice->items()->where('id', $itemId)->delete();
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updatedItems($value, $key): void
    {
        $parts = explode('.', $key);
        $index = (int) $parts[0];
        $field = $parts[1] ?? null;

        if ($field === 'product_variant_id' && ! empty($value)) {
            $variant = ProductVariant::with('priceTiers')->find($value);
            if ($variant) {
                $quantity = $this->items[$index]['quantity'] ?? 1;
                $this->items[$index]['unit_price'] = $variant->getPriceForQuantity($quantity) ?? 0;
            }
        } elseif ($field === 'quantity' && ! empty($this->items[$index]['product_variant_id'])) {
            $variant = ProductVariant::with('priceTiers')->find($this->items[$index]['product_variant_id']);
            if ($variant) {
                $this->items[$index]['unit_price'] = $variant->getPriceForQuantity((int) $value) ?? 0;
            }
        }
    }

    public function update(): void
    {
        $validated = $this->validate([
            'event_id' => ['required', 'exists:events,id'],
            'status' => ['required', 'string'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'discount_amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'issued_at' => ['required', 'date'],
            'due_at' => ['nullable', 'date', 'after_or_equal:issued_at'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $this->invoice->update([
            'event_id' => $validated['event_id'],
            'status' => $validated['status'],
            'tax_rate' => $validated['tax_rate'],
            'discount_amount' => $validated['discount_amount'],
            'notes' => $validated['notes'],
            'issued_at' => $validated['issued_at'],
            'due_at' => $validated['due_at'],
        ]);

        $existingItemIds = [];
        foreach ($validated['items'] as $item) {
            $variant = ProductVariant::with('product')->find($item['product_variant_id']);
            $subtotal = $item['quantity'] * $item['unit_price'];

            if (! empty($item['id'])) {
                $invoiceItem = $this->invoice->items()->find($item['id']);
                if ($invoiceItem) {
                    $invoiceItem->update([
                        'product_variant_id' => $item['product_variant_id'],
                        'product_name' => $variant->product->name,
                        'variant_name' => $variant->name,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $subtotal,
                    ]);
                    $existingItemIds[] = $invoiceItem->id;
                }
            } else {
                $newItem = $this->invoice->items()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'product_name' => $variant->product->name,
                    'variant_name' => $variant->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                ]);
                $existingItemIds[] = $newItem->id;
            }
        }

        $this->invoice->items()->whereNotIn('id', $existingItemIds)->delete();
        $this->invoice->recalculate();

        session()->flash('success', 'Invoice updated successfully.');

        $this->redirect(route('admin.invoices.index'), navigate: true);
    }

    public function render()
    {
        $events = Event::with('customer')->orderBy('date', 'desc')->get();
        $variants = ProductVariant::with(['product', 'priceTiers'])
            ->where('is_available', true)
            ->orderBy('name')
            ->get();

        return view('livewire.admin.invoices.edit', [
            'events' => $events,
            'variants' => $variants,
        ]);
    }
}
