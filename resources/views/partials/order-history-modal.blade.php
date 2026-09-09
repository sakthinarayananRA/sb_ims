<!-- Modal: Customer Order History -->
<div id="orderHistoryModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl max-h-[90vh] flex flex-col border border-slate-200">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-slate-200 pb-4 mb-4">
            <div class="flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </span>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Customer Order History</h3>
                    <p class="text-xs text-slate-500" id="historyEmailTarget">Orders for customer</p>
                </div>
            </div>
            <button type="button" onclick="closeOrderHistoryModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center font-bold transition cursor-pointer">
                ✕
            </button>
        </div>

        <!-- Filter Controls -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4 text-xs bg-slate-50 p-3.5 rounded-xl border border-slate-200">
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Status</label>
                <select id="historyStatusFilter" onchange="fetchCustomerOrderHistoryApi()" class="w-full border border-slate-300 rounded-lg px-2.5 py-1.5 bg-white text-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500 font-medium cursor-pointer">
                    <option value="">All Statuses</option>
                    <option value="completed">Completed</option>
                    <option value="pending">Pending</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Min Grand Total</label>
                <input type="number" id="historyMinTotalFilter" placeholder="e.g. 100" oninput="fetchCustomerOrderHistoryApi()" class="w-full border border-slate-300 rounded-lg px-2.5 py-1.5 bg-white text-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500 font-medium">
            </div>
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Sort Direction</label>
                <select id="historySortDir" onchange="fetchCustomerOrderHistoryApi()" class="w-full border border-slate-300 rounded-lg px-2.5 py-1.5 bg-white text-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500 font-medium cursor-pointer">
                    <option value="desc">Newest First</option>
                    <option value="asc">Oldest First</option>
                </select>
            </div>
        </div>

        <!-- History Results Container -->
        <div class="overflow-y-auto flex-1 space-y-3 pr-1" id="historyOrdersContainer">
            <p class="text-center text-slate-400 py-8 text-xs">No orders loaded yet.</p>
        </div>

        <!-- Modal Footer -->
        <div class="border-t border-slate-200 pt-4 mt-4 flex justify-between items-center">
            <span class="text-[11px] text-slate-400">Powered by GET /api/v1/customers/orders</span>
            <button type="button" onclick="closeOrderHistoryModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition cursor-pointer">
                Close
            </button>
        </div>
    </div>
</div>

