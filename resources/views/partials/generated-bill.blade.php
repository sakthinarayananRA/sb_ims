<!-- On-Screen Generated Bill Section (Wireframe "shows bill on page") -->
<div id="generatedBillSection" class="hidden rounded-2xl p-6 sm:p-8 bg-white border border-slate-200 shadow-xl transition-all">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-200 pb-5 mb-6 gap-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-block bg-emerald-100 text-emerald-800 text-[11px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">
                        Paid & Completed
                    </span>
                    <span class="text-xs text-slate-400 font-mono" id="invoiceDate">Date: -</span>
                </div>
                <h3 class="text-2xl font-black text-slate-900 tracking-tight mt-0.5" id="invoiceOrderNumber">ORD-000000</h3>
            </div>
        </div>

        <div class="flex items-center gap-2 self-end sm:self-auto">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 text-xs bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-xl font-semibold transition shadow-sm cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>Print Bill</span>
            </button>
            <button onclick="dismissBillSection()" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 py-2 rounded-xl font-semibold transition cursor-pointer">
                ✕ Close
            </button>
        </div>
    </div>

    <!-- Invoice Details Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs mb-6 bg-slate-50 p-4 rounded-xl border border-slate-100">
        <div>
            <p class="text-slate-400 uppercase font-semibold tracking-wider text-[10px]">Customer Information</p>
            <p class="font-bold text-sm text-slate-900 mt-1" id="invoiceCustomerName">-</p>
            <p class="text-slate-600 font-mono" id="invoiceCustomerEmail">-</p>
        </div>
        <div class="sm:text-right">
            <p class="text-slate-400 uppercase font-semibold tracking-wider text-[10px]">Transaction Status</p>
            <p class="font-bold text-emerald-600 uppercase text-sm mt-1" id="invoicePaymentStatus">PAID</p>
            <p class="text-slate-500">Atomic Stock Deduction Confirmed</p>
        </div>
    </div>

    <!-- Invoice Line Items Table -->
    <div class="border border-slate-200 rounded-xl overflow-hidden mb-6 shadow-2xs">
        <table class="w-full text-xs text-left">
            <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                <tr>
                    <th class="py-2.5 px-4">Item Name</th>
                    <th class="py-2.5 px-3 text-center w-16">Qty</th>
                    <th class="py-2.5 px-3 text-right w-24">Unit Price</th>
                    <th class="py-2.5 px-3 text-right w-20">Tax %</th>
                    <th class="py-2.5 px-4 text-right w-28">Total</th>
                </tr>
            </thead>
            <tbody id="invoiceItemsTableBody" class="divide-y divide-slate-100 text-slate-800 bg-white">
            </tbody>
        </table>
    </div>

    <!-- Bill Totals Summary -->
    <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 text-xs space-y-2 max-w-sm ml-auto shadow-2xs">
        <div class="flex justify-between text-slate-600">
            <span>Subtotal:</span>
            <span class="font-semibold text-slate-800 tabular-nums" id="invoiceSubtotal">₹0.00</span>
        </div>
        <div class="flex justify-between text-slate-600">
            <span>Tax Amount:</span>
            <span class="font-semibold text-slate-800 tabular-nums" id="invoiceTax">₹0.00</span>
        </div>
        <div class="flex justify-between text-base font-black text-slate-900 border-t border-slate-200 pt-2">
            <span>Grand Total:</span>
            <span class="tabular-nums" id="invoiceGrandTotal">₹0.00</span>
        </div>
        <div class="flex justify-between text-slate-600 border-t border-slate-200 pt-2">
            <span>Amount Paid:</span>
            <span class="font-semibold text-slate-800 tabular-nums" id="invoicePaidAmount">₹0.00</span>
        </div>
        <div class="flex justify-between text-slate-600">
            <span>Change Returned:</span>
            <span class="font-bold text-emerald-600 tabular-nums" id="invoiceChangeAmount">₹0.00</span>
        </div>
        <div class="pt-2 text-[11px] text-slate-500 font-mono bg-white p-2 rounded border border-slate-200/80" id="invoiceDenominations">
        </div>
    </div>
</div>

