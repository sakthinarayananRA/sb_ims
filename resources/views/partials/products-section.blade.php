<!-- Order Products Section -->
<div class="bg-white rounded-2xl p-6 shadow-card border border-slate-200/80 transition-all hover:shadow-card-hover">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2.5">
            <span class="p-2 rounded-lg bg-indigo-50 text-indigo-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </span>
            <div>
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900">Order Items</h2>
                <p class="text-xs text-slate-500">Add products, configure quantities, and review live totals</p>
            </div>
        </div>

        <button type="button" onclick="addProductRow()" 
            class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold px-3.5 py-2 rounded-xl shadow-sm hover:shadow transition cursor-pointer">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Add Product</span>
        </button>
    </div>

    <!-- Products Table Container -->
    <div id="orderItemsTableContainer" class="border border-slate-200 rounded-xl overflow-hidden shadow-sm transition">
        <table class="w-full text-left border-collapse text-sm" id="orderItemsTable">
            <thead>
                <tr class="bg-slate-50/90 text-slate-600 font-semibold text-xs uppercase tracking-wider border-b border-slate-200">
                    <th class="py-3 px-4">Product Item</th>
                    <th class="py-3 px-3 w-28 text-center">Qty</th>
                    <th class="py-3 px-3 w-28 text-right">Price</th>
                    <th class="py-3 px-4 w-32 text-right">Line Total</th>
                    <th class="py-3 px-2 w-10 text-center"></th>
                </tr>
            </thead>
            <tbody id="productRowsContainer" class="divide-y divide-slate-100 bg-white">
                <!-- Populated dynamically via fetch() in billing.js -->
            </tbody>
            <tfoot>
                <tr class="bg-slate-50/50 hover:bg-slate-100/70 border-t border-slate-200 cursor-pointer transition" onclick="addProductRow()">
                    <td colspan="5" class="py-3 px-4 text-xs font-medium text-slate-500 hover:text-blue-600 flex items-center gap-2 select-none">
                        <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">+</span>
                        <span>Click here or use "+ Add Product" to include more line items</span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Dynamic Inline Validation Message for Line Items -->
    <p id="orderItemsError" class="hidden text-xs text-rose-600 font-semibold mt-2.5 flex items-center gap-1"></p>
</div>
