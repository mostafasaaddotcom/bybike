{{-- Empty state --}}
<template x-if="getCartItemCount() === 0">
    <div class="py-8 text-center text-zinc-500 dark:text-zinc-400">
        <svg class="mx-auto h-12 w-12 mb-3 text-zinc-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path>
        </svg>
        <p class="text-sm">No items added yet</p>
    </div>
</template>

{{-- Cart items --}}
<template x-if="getCartItemCount() > 0">
    <div>
        <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
            <template x-for="item in getCartItems()" :key="item.variantId">
                <div class="py-3">
                    <div class="flex justify-between items-start mb-1">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-zinc-900 dark:text-white truncate" x-text="item.productName"></p>
                            <span class="inline-block mt-0.5 px-1.5 py-0.5 text-xs rounded bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300" x-text="item.variantName"></span>
                        </div>
                        <span class="text-sm font-semibold text-zinc-900 dark:text-white ml-2 shrink-0" x-text="item.subtotal.toFixed(2) + ' LE'"></span>
                    </div>
                    <div class="flex items-center justify-between mt-2">
                        <div class="flex items-center gap-2">
                            <button
                                @click="decrement(item.variantId)"
                                :disabled="pendingRequests[item.variantId]"
                                class="w-7 h-7 flex items-center justify-center rounded border border-zinc-300 dark:border-zinc-600 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                            </button>
                            <span class="text-sm font-bold text-zinc-900 dark:text-white w-8 text-center" x-text="item.quantity"></span>
                            <button
                                @click="increment(item.variantId)"
                                :disabled="pendingRequests[item.variantId]"
                                class="w-7 h-7 flex items-center justify-center rounded bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </button>
                        </div>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400" x-text="item.unitPrice.toFixed(2) + ' LE/unit'"></span>
                    </div>
                </div>
            </template>
        </div>

        {{-- Invoice Summary --}}
        <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4 mt-2 space-y-2">
            <div class="flex justify-between">
                <span class="text-sm text-zinc-600 dark:text-zinc-400">Subtotal</span>
                <span class="text-sm font-medium text-zinc-900 dark:text-white"><span x-text="invoiceData.subtotal.toFixed(2)"></span> LE</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-zinc-600 dark:text-zinc-400">Tax (<span x-text="invoiceData.tax_rate"></span>%)</span>
                <span class="text-sm font-medium text-zinc-900 dark:text-white"><span x-text="invoiceData.tax_amount.toFixed(2)"></span> LE</span>
            </div>
            <template x-if="invoiceData.discount_amount > 0">
                <div class="flex justify-between">
                    <span class="text-sm text-zinc-600 dark:text-zinc-400">Discount</span>
                    <span class="text-sm font-medium text-green-600 dark:text-green-400">-<span x-text="invoiceData.discount_amount.toFixed(2)"></span> LE</span>
                </div>
            </template>
            <div class="flex justify-between border-t border-zinc-200 dark:border-zinc-700 pt-2">
                <span class="text-base font-semibold text-zinc-900 dark:text-white">Total</span>
                <span class="text-base font-bold text-blue-600 dark:text-blue-400"><span x-text="invoiceData.total.toFixed(2)"></span> LE</span>
            </div>
        </div>

        {{-- Submit Button --}}
        <div class="mt-4">
            <template x-if="submitted">
                <div class="w-full py-3 px-4 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-sm font-semibold text-center">
                    Invoice submitted successfully!
                </div>
            </template>
            <template x-if="!submitted">
                <button
                    @click="submitInvoice()"
                    :disabled="submitting || getCartItemCount() === 0"
                    class="w-full py-3 px-4 rounded-lg bg-emerald-600 text-white font-semibold text-sm hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <span x-show="!submitting">Submit Invoice</span>
                    <span x-show="submitting" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Submitting...
                    </span>
                </button>
            </template>

            {{-- WhatsApp Notice --}}
            <div class="flex items-start gap-2.5 mt-3 p-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/40">
                <svg class="w-5 h-5 shrink-0 mt-0.5 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                <p class="text-xs text-emerald-700 dark:text-emerald-300/90 leading-relaxed">
                    You will receive your quotation on <span class="font-semibold">WhatsApp</span> after submitting.
                </p>
            </div>
        </div>
    </div>
</template>
