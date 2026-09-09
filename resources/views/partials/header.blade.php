<!-- Top Navigation Header -->
<header class="bg-slate-900 text-white shadow-md sticky top-0 z-30 border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        
        <!-- Left: Brand & POS Identifier -->
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white shadow-md shadow-blue-500/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-base font-bold tracking-tight text-white">Store Billing POS</h1>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                        v1.0
                    </span>
                </div>
                <p class="text-xs text-slate-400">Order & Inventory Management Terminal</p>
            </div>
        </div>

        <!-- Right: Real-Time API Status & History Trigger -->
        <div class="flex items-center gap-3 sm:gap-4">
            <div class="hidden sm:flex items-center gap-2 text-xs font-medium text-slate-300 bg-slate-800/80 px-3 py-1.5 rounded-lg border border-slate-700/60">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>REST API Connected</span>
            </div>

            <button type="button" onclick="openOrderHistoryModal()" id="btnHistory" 
                class="inline-flex items-center gap-1.5 text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white px-3.5 py-1.5 rounded-lg border border-slate-700 transition shadow-sm cursor-pointer">
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Order History</span>
            </button>
        </div>

    </div>
</header>

