<x-layouts.public>
<div
    class="min-h-screen bg-zinc-50 dark:bg-zinc-900 py-8 sm:py-12 pb-20 lg:pb-0"
    x-data="invoiceManager(@js($quantities), @js($invoice), '{{ $event->public_invoice_token }}', @js($variantData))"
    x-init="init()"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Invoice Header --}}
        <div class="mb-6 sm:mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold dark:text-white">Invoice #{{ $invoice->invoice_number }}</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-2">Select products and adjust quantities below</p>
        </div>

        {{-- Event & Customer Details --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 mb-6 sm:mb-8 p-4 sm:p-6 bg-white dark:bg-zinc-800 rounded-lg shadow-sm">
            <div>
                <h3 class="text-sm font-semibold text-zinc-500 dark:text-zinc-400 uppercase mb-2">Event Details</h3>
                <p class="text-sm text-zinc-900 dark:text-zinc-300"><strong>Type:</strong> {{ $event->type->label() }}</p>
                <p class="text-sm text-zinc-900 dark:text-zinc-300"><strong>Date:</strong> {{ $event->date->format('M d, Y') }}</p>
                <p class="text-sm text-zinc-900 dark:text-zinc-300"><strong>Location:</strong> {{ $event->location }}</p>
                <p class="text-sm text-zinc-900 dark:text-zinc-300"><strong>Attendees:</strong> {{ $event->number_of_attendees }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-zinc-500 dark:text-zinc-400 uppercase mb-2">Customer Information</h3>
                <p class="text-sm text-zinc-900 dark:text-zinc-300"><strong>Name:</strong> {{ $event->customer->name }}</p>
                <p class="text-sm text-zinc-900 dark:text-zinc-300"><strong>Phone:</strong> {{ $event->customer->phone }}</p>
                @if($event->customer->email)
                    <p class="text-sm text-zinc-900 dark:text-zinc-300"><strong>Email:</strong> {{ $event->customer->email }}</p>
                @endif
            </div>
        </div>

        {{-- Two-Column Layout: Menus + Products | Cart --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- LEFT: Menus + Product Grid --}}
            <div class="lg:col-span-2">
                @if($menus->isNotEmpty())
                    {{-- Menu Selector --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
                        @foreach($menus as $menu)
                            <button
                                data-menu-id="{{ $menu->id }}"
                                @click="selectMenu({{ $menu->id }})"
                                :class="activeMenuId === {{ $menu->id }}
                                    ? 'border-blue-500 dark:border-blue-400 ring-2 ring-blue-500/30'
                                    : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600'"
                                class="relative overflow-hidden rounded-lg border-2 bg-white dark:bg-zinc-800 text-left transition-all duration-200"
                            >
                                @if($menu->imageUrl())
                                    <img src="{{ $menu->imageUrl() }}" alt="{{ $menu->name }}" class="w-full h-24 object-cover">
                                @else
                                    <div class="w-full h-24 bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-zinc-300 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                                <div class="p-2.5">
                                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-white truncate">{{ $menu->name }}</h3>
                                    @if($menu->description)
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 line-clamp-2">{{ $menu->description }}</p>
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </div>

                    {{-- Product Grids (one per menu, toggled via x-show) --}}
                    @foreach($menus as $menu)
                        <div x-show="activeMenuId === {{ $menu->id }}" x-cloak>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($menu->variants as $variant)
                                    @php
                                        $data = $variantData[$variant->id];
                                        $startingPrice = $variant->getPriceForQuantity($variant->minimum_order_quantity ?: 1);
                                    @endphp

                                    <div
                                        class="relative rounded-lg border-2 p-4 sm:p-5 transition-all duration-200"
                                        :class="getQuantity({{ $variant->id }}) > 0 ? 'bg-white dark:bg-zinc-800 border-blue-500 dark:border-blue-400 shadow-md' : 'bg-white dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600'"
                                        x-data="{
                                            variantId: {{ $variant->id }},
                                            priceTiers: @js($data['priceTiers']),
                                            minQty: {{ $variant->minimum_order_quantity }},
                                            increaseRate: {{ $variant->increase_rate }}
                                        }"
                                    >
                                        {{-- Product & Variant Name --}}
                                        <div class="mb-3">
                                            <h3 class="text-base sm:text-lg font-semibold text-zinc-900 dark:text-white">
                                                {{ $variant->product->name }}
                                            </h3>
                                            <span class="inline-block mt-1 px-2 py-1 text-xs font-medium rounded bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300">
                                                {{ $variant->name }}
                                            </span>
                                        </div>

                                        {{-- Pricing --}}
                                        <div class="mb-4">
                                            <div class="flex items-baseline gap-2">
                                                <span class="text-lg sm:text-xl font-bold text-zinc-900 dark:text-white">
                                                    <span x-text="getPrice({{ $variant->id }}, priceTiers, minQty).toFixed(2)"></span> LE
                                                </span>
                                                <span class="text-sm text-zinc-500 dark:text-zinc-400">per unit</span>
                                            </div>
                                            @if($variant->minimum_order_quantity > 1)
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                                    Min: {{ $variant->minimum_order_quantity }} units
                                                </p>
                                            @endif
                                        </div>

                                        {{-- Quantity Controls --}}
                                        <div class="flex items-center justify-between gap-3">
                                            <template x-if="getQuantity({{ $variant->id }}) > 0">
                                                <button
                                                    @click="decrement({{ $variant->id }})"
                                                    :disabled="loading"
                                                    class="shrink-0 px-3 py-2 text-sm font-medium rounded-md border border-zinc-300 dark:border-zinc-600 hover:bg-zinc-100 dark:hover:bg-zinc-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                    </svg>
                                                </button>
                                            </template>

                                            <div class="flex-1 text-center">
                                                <span class="text-2xl font-bold text-zinc-900 dark:text-white" x-text="getQuantity({{ $variant->id }})"></span>
                                            </div>

                                            <button
                                                @click="increment({{ $variant->id }})"
                                                :disabled="loading"
                                                :class="getQuantity({{ $variant->id }}) > 0 ? 'shrink-0' : 'w-full'"
                                                class="px-3 py-2 text-sm font-medium rounded-md bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                            >
                                                <template x-if="getQuantity({{ $variant->id }}) > 0">
                                                    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                    </svg>
                                                </template>
                                                <template x-if="getQuantity({{ $variant->id }}) === 0">
                                                    <span class="flex items-center justify-center gap-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                        </svg>
                                                        Add to Invoice
                                                    </span>
                                                </template>
                                            </button>
                                        </div>

                                        {{-- Loading Indicator --}}
                                        <div
                                            x-show="loading"
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0"
                                            x-transition:enter-end="opacity-100"
                                            class="absolute inset-0 bg-white/50 dark:bg-zinc-800/50 rounded-lg flex items-center justify-center"
                                        >
                                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-12 text-zinc-500 dark:text-zinc-400">
                        <p class="text-lg">No products available at this time.</p>
                    </div>
                @endif
            </div>

            {{-- RIGHT: Desktop Cart (sticky) --}}
            <div class="hidden lg:block">
                <div class="sticky top-6">
                    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-5">
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Invoice Summary</h2>
                        @include('public.invoice._cart-content')
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile Sticky Bottom Bar --}}
    <div
        class="fixed bottom-0 inset-x-0 lg:hidden bg-white dark:bg-zinc-800 border-t border-zinc-200 dark:border-zinc-700 shadow-lg z-40"
        @click="openCart()"
    >
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <svg class="w-6 h-6 text-zinc-700 dark:text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path>
                    </svg>
                    <template x-if="getCartItemCount() > 0">
                        <span class="absolute -top-2 -right-2 bg-blue-600 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center" x-text="getCartItemCount()"></span>
                    </template>
                </div>
                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    <span x-text="getCartItemCount()"></span> item<span x-show="getCartItemCount() !== 1">s</span>
                </span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-lg font-bold text-blue-600 dark:text-blue-400"><span x-text="invoiceData.total.toFixed(2)"></span> LE</span>
                <svg class="w-5 h-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                </svg>
            </div>
        </div>
    </div>

    {{-- Mobile Cart Overlay --}}
    <template x-teleport="body">
        <div
            x-show="cartOpen"
            x-cloak
            class="fixed inset-0 z-50 lg:hidden"
        >
            {{-- Backdrop --}}
            <div
                x-show="cartOpen"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="closeCart()"
                class="absolute inset-0 bg-black/50"
            ></div>

            {{-- Slide-up Panel --}}
            <div
                x-show="cartOpen"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-y-full"
                x-transition:enter-end="translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-y-0"
                x-transition:leave-end="translate-y-full"
                class="absolute bottom-0 inset-x-0 bg-white dark:bg-zinc-800 rounded-t-2xl max-h-[85vh] flex flex-col"
            >
                {{-- Handle & Close --}}
                <div class="flex items-center justify-between p-4 border-b border-zinc-200 dark:border-zinc-700 shrink-0">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Invoice Summary</h2>
                    <button
                        @click="closeCart()"
                        class="p-1 rounded-full hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                    >
                        <svg class="w-6 h-6 text-zinc-500 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Scrollable Cart Content --}}
                <div class="overflow-y-auto flex-1 p-4">
                    @include('public.invoice._cart-content')
                </div>
            </div>
        </div>
    </template>
</div>
</x-layouts.public>
