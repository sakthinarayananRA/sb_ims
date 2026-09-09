<!-- Payment Summary & Currency Calculation Section -->
<div class="bg-white rounded-2xl p-6 shadow-card border border-slate-200/80 transition-all hover:shadow-card-hover">
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-2.5">
            <span class="p-2 rounded-lg bg-emerald-50 text-emerald-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </span>
            <div>
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900">Payment Breakdown</h2>
                <p class="text-xs text-slate-500">Live order computation and automated currency balance</p>
            </div>
        </div>
        <span class="text-xs text-slate-400 font-medium">Step 3 of 3</span>
    </div>

    <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 space-y-3">
        <div class="flex justify-between items-center text-sm text-slate-600">
            <span>Subtotal (Net)</span>
            <span class="font-semibold text-slate-800 tabular-nums" id="displaySubtotal">₹0.00</span>
        </div>
        <div class="flex justify-between items-center text-sm text-slate-600">
            <span class="flex items-center gap-1.5">
                <span>Applicable Tax (GST)</span>
                <span class="text-[10px] bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded font-mono">Calculated</span>
            </span>
            <span class="font-semibold text-slate-800 tabular-nums" id="displayTax">₹0.00</span>
        </div>
        
        <div class="border-t border-slate-200 pt-3 flex justify-between items-baseline">
            <span class="text-base font-bold text-slate-900">Grand Total Due</span>
            <span class="text-2xl font-black text-slate-900 tabular-nums tracking-tight" id="displayGrandTotal">₹0.00</span>
        </div>

        <!-- Dashed line separator -->
        <div class="border-t border-dashed border-slate-300 my-4 pt-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                <label for="paidAmountInput" class="text-xs font-bold text-slate-700 uppercase tracking-wide">
                    Amount Given by Customer (Cash Tendered)
                </label>
                <!-- Quick-Cash Shortcuts -->
                <div class="flex items-center gap-1.5 text-[11px]">
                    <span class="text-slate-400">Quick:</span>
                    <button type="button" onclick="setQuickCash(activeGrandTotal)" class="px-2 py-0.5 bg-white border border-slate-300 hover:border-blue-500 hover:text-blue-600 rounded text-slate-600 font-medium transition cursor-pointer">Exact</button>
                    <button type="button" onclick="setQuickCash(100)" class="px-2 py-0.5 bg-white border border-slate-300 hover:border-blue-500 hover:text-blue-600 rounded text-slate-600 font-medium transition cursor-pointer">₹100</button>
                    <button type="button" onclick="setQuickCash(200)" class="px-2 py-0.5 bg-white border border-slate-300 hover:border-blue-500 hover:text-blue-600 rounded text-slate-600 font-medium transition cursor-pointer">₹200</button>
                    <button type="button" onclick="setQuickCash(250)" class="px-2 py-0.5 bg-white border border-slate-300 hover:border-blue-500 hover:text-blue-600 rounded text-slate-600 font-medium transition cursor-pointer">₹250</button>
                    <button type="button" onclick="setQuickCash(500)" class="px-2 py-0.5 bg-white border border-slate-300 hover:border-blue-500 hover:text-blue-600 rounded text-slate-600 font-medium transition cursor-pointer">₹500</button>
                </div>
            </div>

            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-semibold text-base">₹</span>
                <input type="number" id="paidAmountInput" name="paid_amount" step="any" min="0" placeholder="250"
                    class="w-full pl-8 pr-4 py-2.5 bg-white border border-slate-300 rounded-xl text-base font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 tabular-nums shadow-sm"
                    oninput="calculateChangeReturn(); clearFieldError('paidAmountInput')">
            </div>
            <!-- Dynamic Inline Validation Message for Paid Amount -->
            <p id="paidAmountError" class="hidden text-xs text-rose-600 font-semibold mt-1.5 flex items-center gap-1"></p>
        </div>

        <!-- Live Denominations Box -->
        <div class="bg-white border border-slate-200/90 rounded-xl p-3.5 mt-3 shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-xs">
                <span class="font-bold text-slate-700 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Balance to Return:
                </span>
                <div id="displayBalanceAndDenom" class="font-mono text-slate-800 text-xs sm:text-right font-medium">
                    ₹0.00
                </div>
            </div>
        </div>
    </div>
</div>
