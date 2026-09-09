/**
 * ==========================================================================
 * Store Billing & POS System JavaScript Module
 * Connects directly to Laravel REST API v1 via fetch()
 * Integrates with <x-alert /> Blade component and inline form validation
 * ==========================================================================
 */

let availableProducts = [];
let activeGrandTotal = 0;
let isSyncingLowStock = false;

// -------------------------------------------------------------
// Initialization on DOM Ready
// -------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    // 1. Fetch available products via GET /api/v1/products
    fetchProductsApi();

    // 2. Fetch low stock alert via GET /api/v1/products/low-stock
    fetchLowStockProductsApi(false);

    // Pre-fill initial customer email for instant testing
    const emailInput = document.getElementById('customerEmail');
    if (emailInput && !emailInput.value) {
        emailInput.value = 'thomas@example.com';
        lookupCustomerByEmail('thomas@example.com');
    }

    const paidInput = document.getElementById('paidAmountInput');
    if (paidInput && !paidInput.value) {
        paidInput.value = 250;
    }
});

// Quick Cash Helper
function setQuickCash(amount) {
    const paidInput = document.getElementById('paidAmountInput');
    if (paidInput) {
        paidInput.value = Math.ceil(amount);
        clearFieldError('paidAmountInput');
        calculateChangeReturn();
    }
}

// -------------------------------------------------------------
// Field-Level Inline Validation Helpers
// -------------------------------------------------------------
function setFieldError(fieldId, errorElId, message) {
    const field = document.getElementById(fieldId);
    const errorEl = document.getElementById(errorElId);

    if (field) {
        field.classList.add('border-rose-500', 'ring-2', 'ring-rose-200', 'bg-rose-50/20');
        field.classList.remove('border-slate-300', 'border-slate-200');
    }

    if (errorEl) {
        errorEl.innerHTML = `
            <svg class="w-3.5 h-3.5 text-rose-500 shrink-0 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>${message}</span>
        `;
        errorEl.classList.remove('hidden');
    }
}

function clearFieldError(fieldId) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.classList.remove('border-rose-500', 'ring-2', 'ring-rose-200', 'bg-rose-50/20');
        field.classList.add('border-slate-300');
    }

    const mapping = {
        'customerEmail': 'customerEmailError',
        'customerName': 'customerNameError',
        'paidAmountInput': 'paidAmountError',
        'orderItemsTableContainer': 'orderItemsError'
    };

    const errorElId = mapping[fieldId];
    if (errorElId) {
        const errorEl = document.getElementById(errorElId);
        if (errorEl) {
            errorEl.innerHTML = '';
            errorEl.classList.add('hidden');
        }
    }
}

function clearAllFieldErrors() {
    ['customerEmail', 'customerName', 'paidAmountInput', 'orderItemsTableContainer'].forEach(id => {
        clearFieldError(id);
    });
}

// -------------------------------------------------------------
// API 1: Fetch Available Products (GET /api/v1/products)
// -------------------------------------------------------------
function fetchProductsApi() {
    fetch('/api/v1/products')
        .then(res => res.json())
        .then(response => {
            if (response.status && Array.isArray(response.data)) {
                availableProducts = response.data;

                // Setup initial rows matching Wireframe (Colgate + Parle-G)
                const colgate = availableProducts.find(p => p.code === 'PRD-COLG-01') || availableProducts[0];
                const parleG = availableProducts.find(p => p.code === 'PRD-PARL-01') || availableProducts[1];

                const container = document.getElementById('productRowsContainer');
                if (container) {
                    container.innerHTML = '';
                    if (colgate) addProductRow(colgate.id, 2);
                    if (parleG) addProductRow(parleG.id, 5);
                    calculateTotals();
                }
            } else {
                showAlert('API returned unexpected response structure for products.', 'warning', 'Catalog Notice');
            }
        })
        .catch(err => {
            showAlert('Unable to fetch product catalog from /api/v1/products. Please ensure server is running.', 'error', 'Product Catalog API Error');
        });
}

// -------------------------------------------------------------
// API 2: Fetch Low Stock Alert Products (GET /api/v1/products/low-stock)
// Actionable Sync with Live Animation & Warnings
// -------------------------------------------------------------
function fetchLowStockProductsApi(isManual = false) {
    if (isSyncingLowStock) return;
    isSyncingLowStock = true;

    const listEl = document.getElementById('lowStockList');
    const syncBtn = document.getElementById('btnRefreshLowStock');
    const syncIcon = document.getElementById('syncIcon');

    // Trigger visual sync animation on refresh icon
    if (syncIcon) {
        syncIcon.classList.add('is-syncing');
    }
    if (syncBtn) {
        syncBtn.setAttribute('aria-busy', 'true');
        syncBtn.classList.add('opacity-70', 'cursor-not-allowed');
    }

    fetch('/api/v1/products/low-stock')
        .then(res => res.json())
        .then(response => {
            if (response.status && Array.isArray(response.data)) {
                if (response.data.length === 0) {
                    listEl.innerHTML = `
                        <div class="text-xs text-emerald-800 bg-emerald-50/90 p-3 rounded-xl border border-emerald-200 flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>All product inventories are currently above reorder limits.</span>
                        </div>
                    `;
                    return;
                }

                // Render matching the uploaded reference image (media_1788983408347.png)
                let html = '';
                response.data.forEach(item => {
                    html += `
                        <div class="bg-white border border-amber-200/90 rounded-xl px-4 py-2.5 flex items-center justify-between shadow-2xs hover:border-amber-300 transition">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shrink-0"></span>
                                <span class="font-bold text-slate-900 text-xs sm:text-sm">${item.name}</span>
                            </div>
                            <span class="text-xs font-bold text-amber-900 bg-amber-100/90 px-3 py-1 rounded-full whitespace-nowrap">
                                ${item.stock_on_hand} left
                            </span>
                        </div>
                    `;
                });
                listEl.innerHTML = html;

                if (isManual) {
                    showAlert('Inventory levels synchronized successfully with latest database stock counts.', 'success', 'Inventory Sync Complete');
                }
            } else {
                showAlert('Failed to parse inventory data from server.', 'warning', 'Inventory Notice');
            }
        })
        .catch(err => {
            listEl.innerHTML = `
                <div class="text-xs text-rose-700 bg-rose-50 p-3 rounded-xl border border-rose-200">
                    Failed to synchronize inventory levels. Please try again.
                </div>
            `;
            showAlert('Could not synchronize inventory alerts with /api/v1/products/low-stock.', 'warning', 'Sync Failure Notice');
        })
        .finally(() => {
            setTimeout(() => {
                if (syncIcon) {
                    syncIcon.classList.remove('is-syncing');
                }
                if (syncBtn) {
                    syncBtn.removeAttribute('aria-busy');
                    syncBtn.classList.remove('opacity-70', 'cursor-not-allowed');
                }
                isSyncingLowStock = false;
            }, 400);
        });
}

// -------------------------------------------------------------
// Dynamic Products Table Management
// -------------------------------------------------------------
function addProductRow(selectedId = null, initialQty = 1) {
    const container = document.getElementById('productRowsContainer');
    if (!container) return;

    clearFieldError('orderItemsTableContainer');
    const rowId = 'row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);

    let optionsHtml = '';
    availableProducts.forEach(prod => {
        const isSelected = selectedId && prod.id == selectedId ? 'selected' : '';
        optionsHtml += `<option value="${prod.id}" data-price="${prod.price_per_unit}" data-tax="${prod.tax_percentage}" data-stock="${prod.stock_on_hand}" ${isSelected}>
            ${prod.name} (₹${Math.round(prod.price_per_unit)} • ${prod.stock_on_hand} in stock)
        </option>`;
    });

    const tr = document.createElement('tr');
    tr.id = rowId;
    tr.className = 'hover:bg-slate-50/80 transition group';
    tr.innerHTML = `
        <td class="py-3 px-4">
            <select class="product-select w-full bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 rounded-lg text-slate-800 font-medium py-1.5 px-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 cursor-pointer text-sm" onchange="onProductSelectChange('${rowId}')">
                ${optionsHtml}
            </select>
        </td>
        <td class="py-3 px-3 text-center">
            <input type="number" min="1" value="${initialQty}" 
                class="qty-input w-16 text-center mx-auto block py-1.5 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm font-bold text-slate-900 bg-white"
                oninput="calculateTotals(); clearFieldError('orderItemsTableContainer');">
        </td>
        <td class="py-3 px-3 text-right text-slate-700 font-semibold unit-price tabular-nums">
            ₹0.00
        </td>
        <td class="py-3 px-4 text-right text-slate-900 font-bold line-total tabular-nums">
            ₹0.00
        </td>
        <td class="py-3 px-2 text-center">
            <button type="button" onclick="removeProductRow('${rowId}')" title="Remove item" 
                class="w-7 h-7 rounded-lg text-slate-300 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition text-sm cursor-pointer">
                ✕
            </button>
        </td>
    `;

    container.appendChild(tr);
    onProductSelectChange(rowId);
}

function removeProductRow(rowId) {
    const row = document.getElementById(rowId);
    if (row) {
        row.remove();
        calculateTotals();
    }
}

function onProductSelectChange(rowId) {
    const row = document.getElementById(rowId);
    if (!row) return;

    const select = row.querySelector('.product-select');
    const selectedOption = select ? select.options[select.selectedIndex] : null;

    if (selectedOption) {
        const price = parseFloat(selectedOption.dataset.price) || 0;
        const unitPriceEl = row.querySelector('.unit-price');
        if (unitPriceEl) {
            unitPriceEl.textContent = `₹${price.toFixed(2)}`;
        }
    }

    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    let totalTax = 0;

    const rows = document.querySelectorAll('#productRowsContainer tr');
    rows.forEach(row => {
        const select = row.querySelector('.product-select');
        const qtyInput = row.querySelector('.qty-input');
        if (!select || !qtyInput) return;

        const selectedOption = select.options[select.selectedIndex];
        if (!selectedOption) return;

        const price = parseFloat(selectedOption.dataset.price) || 0;
        const taxRate = parseFloat(selectedOption.dataset.tax) || 0;
        const qty = Math.max(1, parseInt(qtyInput.value) || 1);

        const lineSubtotal = price * qty;
        const lineTax = (lineSubtotal * taxRate) / 100;
        const lineTotal = lineSubtotal + lineTax;

        subtotal += lineSubtotal;
        totalTax += lineTax;

        const unitEl = row.querySelector('.unit-price');
        const lineEl = row.querySelector('.line-total');
        if (unitEl) unitEl.textContent = `₹${price.toFixed(2)}`;
        if (lineEl) lineEl.textContent = `₹${lineTotal.toFixed(2)}`;
    });

    const grandTotal = subtotal + totalTax;
    activeGrandTotal = grandTotal;

    const subtotalEl = document.getElementById('displaySubtotal');
    const taxEl = document.getElementById('displayTax');
    const grandEl = document.getElementById('displayGrandTotal');

    if (subtotalEl) subtotalEl.textContent = `₹${subtotal.toFixed(2)}`;
    if (taxEl) taxEl.textContent = `₹${totalTax.toFixed(2)}`;
    if (grandEl) grandEl.textContent = `₹${grandTotal.toFixed(2)}`;

    calculateChangeReturn();
}

// Live calculation of change due and currency denomination breakdown
function calculateChangeReturn() {
    const paidInput = document.getElementById('paidAmountInput');
    const balanceEl = document.getElementById('displayBalanceAndDenom');
    if (!paidInput || !balanceEl) return;

    const paid = parseFloat(paidInput.value) || 0;

    if (paid < activeGrandTotal) {
        const shortage = (activeGrandTotal - paid).toFixed(2);
        balanceEl.innerHTML = `<span class="inline-flex items-center gap-1 text-rose-600 font-semibold"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Short by ₹${shortage}</span>`;
        return;
    }

    const change = paid - activeGrandTotal;
    if (change === 0) {
        balanceEl.innerHTML = `<span class="inline-flex items-center gap-1 text-emerald-600 font-bold"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> ₹0.00 (Exact Amount Paid)</span>`;
        return;
    }

    // Greedy optimal denomination calculation
    const denominations = [500, 200, 100, 50, 20, 10, 5, 2, 1];
    let remaining = Math.floor(change);
    const breakdown = [];

    for (const note of denominations) {
        if (remaining >= note) {
            const count = Math.floor(remaining / note);
            breakdown.push(`<span class="inline-block bg-slate-100 text-slate-800 px-1.5 py-0.5 rounded border border-slate-200 font-bold">${count}×₹${note}</span>`);
            remaining = remaining % note;
        }
    }

    const denomStr = breakdown.length > 0 ? ` <span class="text-slate-400 mx-1">&rarr;</span> ${breakdown.join(' + ')}` : '';
    balanceEl.innerHTML = `<span class="text-emerald-700 font-bold">₹${change.toFixed(2)}</span>${denomStr}`;
}

// -------------------------------------------------------------
// API 3: Create Order & Generate Bill (POST /api/v1/orders)
// Dual-Layer Validation: Highlights Inline Fields & Renders Top Alert
// -------------------------------------------------------------
function submitOrderViaApi() {
    clearAllFieldErrors();

    const emailInput = document.getElementById('customerEmail');
    const nameInput = document.getElementById('customerName');
    const paidInput = document.getElementById('paidAmountInput');

    const email = (emailInput ? emailInput.value : '').trim();
    const name = (nameInput ? nameInput.value : '').trim();
    const paidAmount = parseFloat(paidInput ? paidInput.value : 0) || 0;

    let hasValidationError = false;
    const validationMessages = [];

    // Client-side instant pre-validation
    if (!email) {
        setFieldError('customerEmail', 'customerEmailError', 'Customer email address is required.');
        validationMessages.push('Customer email is required to generate the bill.');
        hasValidationError = true;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        setFieldError('customerEmail', 'customerEmailError', 'Please enter a valid email address (e.g. name@domain.com).');
        validationMessages.push('Customer email must be a valid email format.');
        hasValidationError = true;
    }

    if (!name) {
        setFieldError('customerName', 'customerNameError', 'Customer name is required.');
        validationMessages.push('Customer full name is required.');
        hasValidationError = true;
    }

    const rows = document.querySelectorAll('#productRowsContainer tr');
    if (rows.length === 0) {
        setFieldError('orderItemsTableContainer', 'orderItemsError', 'At least one product line item is required to place an order.');
        validationMessages.push('At least one product line item is required.');
        hasValidationError = true;
    }

    if (paidAmount < activeGrandTotal) {
        const diff = (activeGrandTotal - paidAmount).toFixed(2);
        setFieldError('paidAmountInput', 'paidAmountError', `Amount given (₹${paidAmount.toFixed(2)}) is less than Grand Total (₹${activeGrandTotal.toFixed(2)}). Short by ₹${diff}.`);
        validationMessages.push(`Cash given is less than Grand Total by ₹${diff}.`);
        hasValidationError = true;
    }

    // If client pre-validation failed, render top <x-alert /> immediately
    if (hasValidationError) {
        showAlert(
            'Please correct the highlighted form errors before submitting the order:',
            'error',
            'Order Validation Failed',
            validationMessages
        );
        return;
    }

    // Build payload items
    const items = [];
    rows.forEach(row => {
        const select = row.querySelector('.product-select');
        const qtyInput = row.querySelector('.qty-input');
        if (select && qtyInput) {
            items.push({
                product_id: parseInt(select.value),
                quantity: Math.max(1, parseInt(qtyInput.value) || 1)
            });
        }
    });

    const btn = document.getElementById('btnGenerateBill');
    btn.disabled = true;
    btn.innerHTML = `<svg class="animate-spin h-5 w-5 text-white inline mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg> Processing Order...`;

    fetch('/api/v1/orders', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            customer_name: name,
            customer_email: email,
            paid_amount: paidAmount,
            items: items
        })
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok) throw data;
        return data;
    })
    .then(response => {
        clearAllFieldErrors();
        showAlert(`Order #${response.data.order_number} created successfully. Stock deducted and bill generated on page.`, 'success', 'Order Placed Successfully');
        renderBillOnPage(response.data);
        fetchLowStockProductsApi(false); // Real-time refresh of low-stock alert
        fetchProductsApi();              // Refresh stock quantities in dropdowns
    })
    .catch(err => {
        // Intercept HTTP 422: Insufficient stock shortage
        if (err.errors && err.errors.stock && Array.isArray(err.errors.stock)) {
            const stockShortages = err.errors.stock.map(s => 
                `${s.product_name}: Requested ${s.requested_quantity}, only ${s.stock_on_hand} available in stock (Short by ${s.shortage} units)`
            );
            showAlert(
                err.message || 'Cannot fulfill order due to insufficient inventory stock.',
                'error',
                'Insufficient Stock Shortage',
                stockShortages
            );
            setFieldError('orderItemsTableContainer', 'orderItemsError', 'One or more items exceed inventory stock on hand.');
        }
        // Intercept HTTP 422: FormRequest validation errors
        else if (err.errors && typeof err.errors === 'object') {
            const validationErrors = Object.values(err.errors).flat();
            showAlert(
                err.message || 'Validation failed on the server for one or more fields:',
                'error',
                'Order Validation Failed',
                validationErrors
            );

            // Highlight specific invalid fields inline
            if (err.errors.customer_email) {
                setFieldError('customerEmail', 'customerEmailError', err.errors.customer_email[0]);
            }
            if (err.errors.customer_name) {
                setFieldError('customerName', 'customerNameError', err.errors.customer_name[0]);
            }
            if (err.errors.items || err.errors['items.0.product_id'] || err.errors['items.0.quantity']) {
                const itemErr = err.errors.items ? err.errors.items[0] : 'Please ensure valid product line items.';
                setFieldError('orderItemsTableContainer', 'orderItemsError', itemErr);
            }
            if (err.errors.paid_amount) {
                setFieldError('paidAmountInput', 'paidAmountError', err.errors.paid_amount[0]);
            }
        }
        // Intercept generic error messages from backend
        else if (err.message) {
            showAlert(err.message, 'error', 'Order Generation Error');
        }
        // Intercept unexpected network failures
        else {
            showAlert('A network failure or unexpected server error prevented the order from being processed.', 'error', 'API Connection Error');
        }
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = `<svg class="w-5 h-5 transition group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> <span>Generate Bill</span>`;
    });
}

// Render generated bill directly on page
function renderBillOnPage(order) {
    const section = document.getElementById('generatedBillSection');
    if (!section) return;

    document.getElementById('invoiceOrderNumber').textContent = order.order_number;
    document.getElementById('invoiceDate').textContent = 'Date: ' + new Date(order.created_at).toLocaleString();
    document.getElementById('invoiceCustomerName').textContent = order.customer ? order.customer.name : 'Guest';
    document.getElementById('invoiceCustomerEmail').textContent = order.customer ? order.customer.email : '';
    document.getElementById('invoicePaymentStatus').textContent = order.status;

    const tbody = document.getElementById('invoiceItemsTableBody');
    tbody.innerHTML = '';
    if (order.items) {
        order.items.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="py-2.5 px-4 font-semibold text-slate-900">${item.product_name || item.product?.name || 'Item'}</td>
                <td class="py-2.5 px-3 text-center font-bold">${item.quantity}</td>
                <td class="py-2.5 px-3 text-right tabular-nums">₹${parseFloat(item.unit_price).toFixed(2)}</td>
                <td class="py-2.5 px-3 text-right text-slate-500">${item.tax_percentage}%</td>
                <td class="py-2.5 px-4 text-right font-bold text-slate-900 tabular-nums">₹${parseFloat(item.total).toFixed(2)}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    document.getElementById('invoiceSubtotal').textContent = `₹${parseFloat(order.subtotal).toFixed(2)}`;
    document.getElementById('invoiceTax').textContent = `₹${parseFloat(order.tax_amount).toFixed(2)}`;
    document.getElementById('invoiceGrandTotal').textContent = `₹${parseFloat(order.grand_total).toFixed(2)}`;
    document.getElementById('invoicePaidAmount').textContent = `₹${parseFloat(order.paid_amount).toFixed(2)}`;
    document.getElementById('invoiceChangeAmount').textContent = `₹${parseFloat(order.change_amount).toFixed(2)}`;

    const denomEl = document.getElementById('invoiceDenominations');
    if (order.denominations) {
        const parts = Object.entries(order.denominations).map(([note, count]) => `<span class="inline-block bg-slate-100 border border-slate-200 rounded px-1.5 py-0.5 font-bold">${count}×₹${note}</span>`);
        denomEl.innerHTML = `<span class="text-slate-400 font-sans block mb-1">Optimal Note Breakdown:</span> ${parts.join(' + ') || 'Exact cash received'}`;
    } else {
        denomEl.innerHTML = '';
    }

    section.classList.remove('hidden');
    section.scrollIntoView({ behavior: 'smooth' });
}

function dismissBillSection() {
    const section = document.getElementById('generatedBillSection');
    if (section) section.classList.add('hidden');
}

// -------------------------------------------------------------
// API 4: Customer Lookup & Order History (GET /api/v1/customers/orders)
// -------------------------------------------------------------
function lookupCustomerByEmail(email) {
    email = (email || '').trim().toLowerCase();
    const nameInput = document.getElementById('customerName');
    if (!email) return;

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        setFieldError('customerEmail', 'customerEmailError', 'Please enter a valid email address (e.g. name@example.com).');
        return;
    } else {
        clearFieldError('customerEmail');
    }

    fetch(`/api/v1/customers/orders?email=${encodeURIComponent(email)}`)
        .then(async res => {
            const data = await res.json();
            if (!res.ok) throw data;
            return data;
        })
        .then(data => {
            if (data.status && data.customer && data.customer.name) {
                if (nameInput) {
                    nameInput.value = data.customer.name;
                    clearFieldError('customerName');
                }
            }
        })
        .catch(err => {
            // Unregistered customer: allow manual name entry
        });
}

function openOrderHistoryModal() {
    const email = document.getElementById('customerEmail').value.trim();
    if (!email) {
        setFieldError('customerEmail', 'customerEmailError', 'Please enter a customer email address first.');
        showAlert('Please enter a customer email address to view historical orders.', 'warning', 'Customer Email Required');
        document.getElementById('customerEmail').focus();
        return;
    }
    document.getElementById('historyEmailTarget').textContent = 'Order history for: ' + email;
    document.getElementById('orderHistoryModal').classList.remove('hidden');
    fetchCustomerOrderHistoryApi();
}

function closeOrderHistoryModal() {
    const modal = document.getElementById('orderHistoryModal');
    if (modal) modal.classList.add('hidden');
}

function fetchCustomerOrderHistoryApi() {
    const email = document.getElementById('customerEmail').value.trim();
    const status = document.getElementById('historyStatusFilter').value;
    const minTotal = document.getElementById('historyMinTotalFilter').value;
    const sortDir = document.getElementById('historySortDir').value;
    const container = document.getElementById('historyOrdersContainer');

    container.innerHTML = `<div class="text-center text-slate-400 py-8 text-xs flex items-center justify-center gap-2">
        <svg class="animate-spin h-4 w-4 text-blue-500" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
        <span>Loading order history via API...</span>
    </div>`;

    let url = `/api/v1/customers/orders?email=${encodeURIComponent(email)}&sort_direction=${sortDir}`;
    if (status) url += `&status=${encodeURIComponent(status)}`;
    if (minTotal) url += `&min_total=${encodeURIComponent(minTotal)}`;

    fetch(url)
        .then(async res => {
            const data = await res.json();
            if (!res.ok) throw data;
            return data;
        })
        .then(response => {
            if (response.status && response.data && response.data.length > 0) {
                let html = '<div class="divide-y divide-slate-100 border border-slate-200 rounded-xl bg-white overflow-hidden">';
                response.data.forEach(order => {
                    html += `
                        <div class="p-3.5 hover:bg-slate-50 transition flex justify-between items-center text-xs">
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="font-bold text-slate-900">${order.order_number}</p>
                                    <span class="uppercase text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">${order.status}</span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-0.5">${new Date(order.created_at).toLocaleDateString()} • ${order.items ? order.items.length : 0} items</p>
                            </div>
                            <div class="text-right">
                                <p class="font-black text-sm text-slate-900 tabular-nums">₹${order.grand_total}</p>
                                <p class="text-[10px] text-slate-400">Paid: ₹${order.paid_amount}</p>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = `<div class="text-center text-slate-400 py-8 text-xs bg-slate-50 rounded-xl border border-dashed border-slate-200">
                    No orders found matching the filter criteria.
                </div>`;
            }
        })
        .catch(err => {
            container.innerHTML = `<div class="text-center text-rose-500 py-4 text-xs">Failed to load order history.</div>`;
            if (err.message) {
                showAlert(err.message, 'warning', 'Customer History Notice');
            }
        });
}

// -------------------------------------------------------------
// Dismiss Alert Helper
// -------------------------------------------------------------
function dismissAlert(id = 'alertBanner') {
    const banner = document.getElementById(id);
    if (banner) {
        banner.classList.add('hidden');
    }
}

// -------------------------------------------------------------
// Global Alert Notification Helper for <x-alert /> Blade Component
// -------------------------------------------------------------
function showAlert(message, type = 'info', title = null, details = []) {
    const banner = document.getElementById('alertBanner');
    if (!banner) return;

    const iconEl = document.getElementById('alertBannerIcon');
    const titleEl = document.getElementById('alertBannerTitle');
    const badgeEl = document.getElementById('alertBannerBadge');
    const messageEl = document.getElementById('alertBannerMessage');
    const detailsEl = document.getElementById('alertBannerDetails');

    // Color & icon configurations matching <x-alert /> Blade component
    const typeConfig = {
        error: {
            bg: 'bg-rose-50/95',
            border: 'border-rose-300',
            text: 'text-rose-950',
            iconBg: 'bg-rose-100 text-rose-700',
            badge: 'bg-rose-200 text-rose-800',
            icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
            defaultTitle: 'API Error Encountered'
        },
        warning: {
            bg: 'bg-amber-50/95',
            border: 'border-amber-300',
            text: 'text-amber-950',
            iconBg: 'bg-amber-100 text-amber-800',
            badge: 'bg-amber-200 text-amber-900',
            icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>`,
            defaultTitle: 'API Warning Notice'
        },
        success: {
            bg: 'bg-emerald-50/95',
            border: 'border-emerald-300',
            text: 'text-emerald-950',
            iconBg: 'bg-emerald-100 text-emerald-700',
            badge: 'bg-emerald-200 text-emerald-900',
            icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
            defaultTitle: 'Action Completed'
        },
        info: {
            bg: 'bg-blue-50/95',
            border: 'border-blue-300',
            text: 'text-blue-950',
            iconBg: 'bg-blue-100 text-blue-700',
            badge: 'bg-blue-200 text-blue-900',
            icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
            defaultTitle: 'System Information'
        }
    };

    const cfg = typeConfig[type] || typeConfig.info;

    // Apply color styling to component container
    banner.className = `rounded-2xl p-4 sm:p-5 border shadow-sm transition-all duration-300 relative overflow-hidden mb-6 ${cfg.bg} ${cfg.border} ${cfg.text}`;

    if (iconEl) {
        iconEl.className = `${cfg.iconBg} shrink-0 w-9 h-9 rounded-xl flex items-center justify-center font-bold shadow-2xs`;
        iconEl.innerHTML = cfg.icon;
    }

    if (titleEl) {
        titleEl.textContent = title || cfg.defaultTitle;
    }

    if (badgeEl) {
        badgeEl.className = `${cfg.badge} text-[10px] font-mono uppercase px-2 py-0.5 rounded-full font-bold`;
        badgeEl.textContent = type.toUpperCase();
    }

    if (messageEl) {
        messageEl.textContent = message;
    }

    if (detailsEl) {
        if (Array.isArray(details) && details.length > 0) {
            detailsEl.innerHTML = details.map(d => `
                <li class="flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70 shrink-0"></span>
                    <span>${d}</span>
                </li>
            `).join('');
            detailsEl.classList.remove('hidden');
        } else {
            detailsEl.innerHTML = '';
            detailsEl.classList.add('hidden');
        }
    }

    banner.classList.remove('hidden');
    banner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    // Auto-dismiss success/info alerts after 8 seconds; keep error/warnings visible until closed
    if (window._alertTimer) clearTimeout(window._alertTimer);
    if (type === 'success' || type === 'info') {
        window._alertTimer = setTimeout(() => {
            banner.classList.add('hidden');
        }, 8000);
    }
}
