export default function invoiceManager(initialQuantities, initialInvoice, token, variantData, eventData) {
    return {
        quantities: initialQuantities,
        invoiceData: {
            id: initialInvoice.id,
            subtotal: parseFloat(initialInvoice.subtotal),
            tax_amount: parseFloat(initialInvoice.tax_amount),
            tax_rate: parseFloat(initialInvoice.tax_rate),
            discount_amount: parseFloat(initialInvoice.discount_amount),
            total: parseFloat(initialInvoice.total),
        },
        loading: false,
        submitting: false,
        submitted: false,
        token: token,
        variantData: variantData,
        eventData: eventData,
        activeMenuId: null,
        cartOpen: false,
        unitPrices: {},

        init() {
            // Build initial unitPrices from existing quantities
            for (const [variantId, qty] of Object.entries(this.quantities)) {
                if (qty > 0 && this.variantData[variantId]) {
                    const tiers = this.variantData[variantId].priceTiers;
                    this.unitPrices[variantId] = this.calculatePriceForQuantity(qty, tiers);
                }
            }

            // Auto-select first menu
            const firstMenuEl = document.querySelector('[data-menu-id]');
            if (firstMenuEl) {
                this.activeMenuId = parseInt(firstMenuEl.dataset.menuId);
            }
        },

        /**
         * Set the active menu
         */
        selectMenu(menuId) {
            this.activeMenuId = menuId;
        },

        /**
         * Get the current quantity for a variant
         */
        getQuantity(variantId) {
            return this.quantities[variantId] || 0;
        },

        /**
         * Get cart items with qty > 0
         */
        getCartItems() {
            const items = [];
            for (const [variantId, qty] of Object.entries(this.quantities)) {
                if (qty > 0 && this.variantData[variantId]) {
                    const data = this.variantData[variantId];
                    const price = this.unitPrices[variantId] || this.calculatePriceForQuantity(qty, data.priceTiers);
                    items.push({
                        variantId: parseInt(variantId),
                        productName: data.productName,
                        variantName: data.variantName,
                        quantity: qty,
                        unitPrice: price,
                        subtotal: price * qty,
                    });
                }
            }
            return items;
        },

        /**
         * Get total number of items in cart
         */
        getCartItemCount() {
            let count = 0;
            for (const qty of Object.values(this.quantities)) {
                if (qty > 0) {
                    count++;
                }
            }
            return count;
        },

        /**
         * Open mobile cart overlay
         */
        openCart() {
            this.cartOpen = true;
            document.body.style.overflow = 'hidden';
        },

        /**
         * Close mobile cart overlay
         */
        closeCart() {
            this.cartOpen = false;
            document.body.style.overflow = '';
        },

        /**
         * Calculate price based on quantity and price tiers
         */
        getPrice(variantId, priceTiers, minQty) {
            const quantity = this.getQuantity(variantId);

            if (quantity === 0) {
                // Return starting price when quantity is 0
                const startQty = minQty || 1;
                return this.calculatePriceForQuantity(startQty, priceTiers);
            }

            return this.calculatePriceForQuantity(quantity, priceTiers);
        },

        /**
         * Calculate price for a specific quantity based on tiers
         */
        calculatePriceForQuantity(quantity, priceTiers) {
            if (!priceTiers || priceTiers.length === 0) {
                return 0;
            }

            // Find the appropriate price tier
            for (let i = 0; i < priceTiers.length; i++) {
                const tier = priceTiers[i];

                if (tier.to === null) {
                    // Last tier with no upper limit
                    if (quantity >= tier.from) {
                        return parseFloat(tier.price);
                    }
                } else {
                    // Tier with upper limit
                    if (quantity >= tier.from && quantity <= tier.to) {
                        return parseFloat(tier.price);
                    }
                }
            }

            // Fallback to first tier price
            return parseFloat(priceTiers[0].price);
        },

        /**
         * Calculate subtotal for a variant
         */
        getSubtotal(variantId, priceTiers) {
            const quantity = this.getQuantity(variantId);
            if (quantity === 0) {
                return 0;
            }

            const price = this.getPrice(variantId, priceTiers, null);
            return quantity * price;
        },

        /**
         * Get CSRF token from meta tag
         */
        getCsrfToken() {
            const token = document.querySelector('meta[name="csrf-token"]');
            return token ? token.getAttribute('content') : '';
        },

        /**
         * Increment quantity for a variant
         */
        async increment(variantId) {
            if (this.loading) {
                return;
            }

            this.loading = true;

            try {
                const response = await fetch(`/invoice/${this.token}/increment/${variantId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.getCsrfToken(),
                    },
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();

                if (data.success) {
                    // Update local quantities
                    this.quantities[variantId] = data.quantity;

                    // Store server-confirmed unit price
                    this.unitPrices[variantId] = parseFloat(data.unit_price);

                    // Update invoice totals
                    this.invoiceData.subtotal = parseFloat(data.invoice.subtotal);
                    this.invoiceData.tax_amount = parseFloat(data.invoice.tax_amount);
                    this.invoiceData.tax_rate = parseFloat(data.invoice.tax_rate);
                    this.invoiceData.discount_amount = parseFloat(data.invoice.discount_amount);
                    this.invoiceData.total = parseFloat(data.invoice.total);
                } else {
                    alert('Failed to update quantity. Please try again.');
                }
            } catch (error) {
                console.error('Error incrementing quantity:', error);
                alert('An error occurred. Please try again.');
            } finally {
                this.loading = false;
            }
        },

        /**
         * Submit invoice to webhook
         */
        async submitInvoice() {
            if (this.submitting || this.submitted) {
                return;
            }

            this.submitting = true;

            try {
                const items = this.getCartItems().map(item => ({
                    productName: item.productName,
                    variantName: item.variantName,
                    quantity: item.quantity,
                    unitPrice: item.unitPrice,
                    subtotal: item.subtotal,
                }));

                const payload = {
                    event: this.eventData,
                    invoice: {
                        id: this.invoiceData.id,
                        invoice_number: this.eventData.invoice_number,
                        subtotal: this.invoiceData.subtotal,
                        tax_rate: this.invoiceData.tax_rate,
                        tax_amount: this.invoiceData.tax_amount,
                        discount_amount: this.invoiceData.discount_amount,
                        total: this.invoiceData.total,
                    },
                    items: items,
                    total: this.invoiceData.total,
                };

                const response = await fetch('https://n8n.bybike.cloud/webhook/invoice-submission', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                if (!response.ok) {
                    throw new Error('Failed to submit invoice');
                }

                this.submitted = true;
            } catch (error) {
                console.error('Error submitting invoice:', error);
                alert('Failed to submit invoice. Please try again.');
            } finally {
                this.submitting = false;
            }
        },

        /**
         * Decrement quantity for a variant
         */
        async decrement(variantId) {
            if (this.loading) {
                return;
            }

            // Check if item is on invoice
            if (this.getQuantity(variantId) === 0) {
                return;
            }

            this.loading = true;

            try {
                const response = await fetch(`/invoice/${this.token}/decrement/${variantId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.getCsrfToken(),
                    },
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();

                if (data.success) {
                    // Update local quantities
                    this.quantities[variantId] = data.quantity;

                    // Update unit price or remove if quantity is 0
                    if (data.quantity > 0) {
                        this.unitPrices[variantId] = parseFloat(data.unit_price);
                    } else {
                        delete this.unitPrices[variantId];
                    }

                    // Update invoice totals
                    this.invoiceData.subtotal = parseFloat(data.invoice.subtotal);
                    this.invoiceData.tax_amount = parseFloat(data.invoice.tax_amount);
                    this.invoiceData.tax_rate = parseFloat(data.invoice.tax_rate);
                    this.invoiceData.discount_amount = parseFloat(data.invoice.discount_amount);
                    this.invoiceData.total = parseFloat(data.invoice.total);
                } else {
                    alert(data.message || 'Failed to update quantity. Please try again.');
                }
            } catch (error) {
                console.error('Error decrementing quantity:', error);
                alert('An error occurred. Please try again.');
            } finally {
                this.loading = false;
            }
        },
    };
}
