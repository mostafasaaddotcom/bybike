<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Menu;
use App\Models\ProductVariant;

class PublicInvoiceController extends Controller
{
    /**
     * Display the public invoice page.
     */
    public function show(string $token)
    {
        $event = Event::where('public_invoice_token', $token)
            ->with(['customer', 'invoice.items'])
            ->firstOrFail();

        $invoice = $event->invoice ?? abort(404, 'Invoice not found for this event.');

        $menus = Menu::query()
            ->available()
            ->ordered()
            ->whereHas('variants', fn ($q) => $q->where('product_variants.is_available', true))
            ->with([
                'variants' => fn ($q) => $q->where('product_variants.is_available', true),
                'variants.product',
                'variants.priceTiers' => fn ($q) => $q->orderBy('quantity_from'),
            ])
            ->get();

        $variantData = [];
        foreach ($menus as $menu) {
            foreach ($menu->variants as $variant) {
                if (isset($variantData[$variant->id])) {
                    continue;
                }

                $variantData[$variant->id] = [
                    'productName' => $variant->product->name,
                    'variantName' => $variant->name,
                    'minQty' => $variant->minimum_order_quantity,
                    'increaseRate' => $variant->increase_rate,
                    'priceTiers' => $variant->priceTiers->map(fn ($tier) => [
                        'from' => $tier->quantity_from,
                        'to' => $tier->quantity_to,
                        'price' => $tier->price,
                    ])->toArray(),
                ];
            }
        }

        $quantities = $invoice->items->mapWithKeys(function ($item) {
            return [$item->product_variant_id => $item->quantity];
        })->toArray();

        return view('public.invoice.show', [
            'event' => $event,
            'invoice' => $invoice,
            'menus' => $menus,
            'variantData' => $variantData,
            'quantities' => $quantities,
        ]);
    }

    /**
     * Increment quantity for a variant.
     */
    public function incrementQuantity(string $token, int $variantId)
    {
        $event = Event::where('public_invoice_token', $token)->firstOrFail();
        $invoice = $event->invoice ?? abort(404);
        $variant = ProductVariant::with('product')->findOrFail($variantId);

        $currentItem = $invoice->items()->where('product_variant_id', $variantId)->first();
        $currentQty = $currentItem?->quantity ?? 0;

        if ($currentQty === 0) {
            $newQty = max(1, $variant->minimum_order_quantity);
        } else {
            $newQty = $currentQty + $variant->increase_rate;
        }

        $price = $variant->getPriceForQuantity($newQty);

        $invoice->items()->updateOrCreate(
            ['product_variant_id' => $variant->id],
            [
                'product_name' => $variant->product->name,
                'variant_name' => $variant->name,
                'quantity' => $newQty,
                'unit_price' => $price,
                'subtotal' => $price * $newQty,
            ]
        );

        $invoice->recalculate();
        $invoice->save();

        return response()->json([
            'success' => true,
            'quantity' => $newQty,
            'unit_price' => $price,
            'subtotal' => $price * $newQty,
            'invoice' => [
                'subtotal' => $invoice->subtotal,
                'tax_amount' => $invoice->tax_amount,
                'tax_rate' => $invoice->tax_rate,
                'discount_amount' => $invoice->discount_amount,
                'total' => $invoice->total,
            ],
        ]);
    }

    /**
     * Decrement quantity for a variant.
     */
    public function decrementQuantity(string $token, int $variantId)
    {
        $event = Event::where('public_invoice_token', $token)->firstOrFail();
        $invoice = $event->invoice ?? abort(404);
        $variant = ProductVariant::findOrFail($variantId);

        $currentItem = $invoice->items()->where('product_variant_id', $variantId)->first();
        $currentQty = $currentItem?->quantity ?? 0;

        if ($currentQty <= 0) {
            return response()->json(['success' => false, 'message' => 'Item not on invoice']);
        }

        $newQty = max(0, $currentQty - $variant->increase_rate);

        if ($newQty < $variant->minimum_order_quantity) {
            $newQty = 0;
        }

        if ($newQty === 0) {
            $invoice->items()->where('product_variant_id', $variantId)->delete();
            $invoice->recalculate();
            $invoice->save();

            return response()->json([
                'success' => true,
                'quantity' => 0,
                'removed' => true,
                'invoice' => [
                    'subtotal' => $invoice->subtotal,
                    'tax_amount' => $invoice->tax_amount,
                    'tax_rate' => $invoice->tax_rate,
                    'discount_amount' => $invoice->discount_amount,
                    'total' => $invoice->total,
                ],
            ]);
        }

        $price = $variant->getPriceForQuantity($newQty);

        $invoice->items()->where('product_variant_id', $variantId)->update([
            'quantity' => $newQty,
            'unit_price' => $price,
            'subtotal' => $price * $newQty,
        ]);

        $invoice->recalculate();
        $invoice->save();

        return response()->json([
            'success' => true,
            'quantity' => $newQty,
            'unit_price' => $price,
            'subtotal' => $price * $newQty,
            'invoice' => [
                'subtotal' => $invoice->subtotal,
                'tax_amount' => $invoice->tax_amount,
                'tax_rate' => $invoice->tax_rate,
                'discount_amount' => $invoice->discount_amount,
                'total' => $invoice->total,
            ],
        ]);
    }
}
