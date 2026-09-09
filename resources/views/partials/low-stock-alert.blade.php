<!-- Low Stock Alert Card (Faithful to Wireframe & Uploaded Reference Image) -->
<div class="bg-[#fffdf7] rounded-2xl p-5 sm:p-6 border border-amber-200 shadow-sm relative overflow-hidden transition-all">
    
    <!-- Header with Actionable Synchronize Button -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-lg bg-amber-100/90 text-amber-800 flex items-center justify-center font-bold text-sm">
                ⚠
            </span>
            <h3 class="text-sm font-bold text-amber-900 uppercase tracking-wide">
                LOW STOCK ALERT
            </h3>
        </div>

        <!-- Actionable Sync Refresh Icon Button -->
        <button type="button" id="btnRefreshLowStock" onclick="fetchLowStockProductsApi(true)" 
            title="Synchronize low stock inventory" 
            class="w-8 h-8 rounded-lg border border-slate-300 hover:border-slate-400 bg-white text-slate-600 hover:text-slate-900 flex items-center justify-center transition shadow-2xs cursor-pointer active:scale-95 group">
            <svg id="syncIcon" class="w-4 h-4 transition group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
        </button>
    </div>

    <!-- Threshold Subtext -->
    <p class="text-xs text-[#92400e] mt-2 mb-3.5">
        Products under configured reorder thresholds:
    </p>

    <!-- Dynamic Inventory List rendered via billing.js -->
    <div id="lowStockList" class="space-y-2.5 mb-5">
        <div class="bg-white border border-amber-200/90 rounded-xl px-4 py-2.5 flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shrink-0"></span>
                <span class="font-bold text-slate-900 text-xs sm:text-sm">Loading items...</span>
            </div>
            <span class="text-xs font-bold text-amber-900 bg-amber-100/90 px-3 py-1 rounded-full">
                Checking
            </span>
        </div>
    </div>

    <!-- Threshold Policy Footer matching reference image -->
    <div class="pt-3 border-t border-amber-200/70 text-xs flex items-center justify-between text-[#92400e]">
        <span>Threshold Policy</span>
        <span class="font-bold text-amber-900 bg-amber-100/90 px-2.5 py-1 rounded-lg text-xs">
            Per-Product Limit
        </span>
    </div>

</div>

