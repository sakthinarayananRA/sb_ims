<!-- Customer Information Card -->
<div class="bg-white rounded-2xl p-6 shadow-card border border-slate-200/80 transition-all hover:shadow-card-hover">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2.5">
            <span class="p-2 rounded-lg bg-blue-50 text-blue-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </span>
            <div>
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900">Customer Details</h2>
                <p class="text-xs text-slate-500">Auto-fill existing customers or input new billing account</p>
            </div>
        </div>
        <span class="text-xs text-slate-400 font-medium">Step 1 of 3</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="customerEmail" class="block text-xs font-semibold text-slate-700 mb-1.5">
                Customer Email <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </span>
                <input type="email" id="customerEmail" name="customer_email" placeholder="e.g. thomas@example.com"
                    class="w-full pl-10 pr-3 py-2.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-900 font-medium transition shadow-sm"
                    onblur="lookupCustomerByEmail(this.value)"
                    oninput="clearFieldError('customerEmail')">
            </div>
            <!-- Dynamic Inline Validation Message for Email -->
            <p id="customerEmailError" class="hidden text-xs text-rose-600 font-semibold mt-1.5 flex items-center gap-1"></p>
            <p id="emailLookupHint" class="text-[11px] text-slate-400 mt-1 pl-1">
                Press Tab or click away to auto-fetch account name via API.
            </p>
        </div>

        <div>
            <label for="customerName" class="block text-xs font-semibold text-slate-700 mb-1.5">
                Full Name <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </span>
                <input type="text" id="customerName" name="customer_name" placeholder="auto-filled if email exists"
                    class="w-full pl-10 pr-3 py-2.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-900 font-medium transition shadow-sm"
                    oninput="clearFieldError('customerName')">
            </div>
            <!-- Dynamic Inline Validation Message for Name -->
            <p id="customerNameError" class="hidden text-xs text-rose-600 font-semibold mt-1.5 flex items-center gap-1"></p>
            <p class="text-[11px] text-slate-400 mt-1 pl-1">Auto-populated for registered customers.</p>
        </div>
    </div>
</div>
