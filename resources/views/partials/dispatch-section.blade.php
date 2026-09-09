<!-- Order Action / Complete & Dispatch Card -->
<div class="bg-white rounded-2xl p-6 shadow-card border border-slate-200/80 space-y-4">
    <div class="space-y-1">
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Complete & Dispatch</h3>
        <p class="text-xs text-slate-500">Atomic inventory deduction, movement logging, and bill generation.</p>
    </div>

    <button type="button" id="btnGenerateBill" onclick="submitOrderViaApi()" 
        class="w-full bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-base py-3.5 px-6 rounded-xl shadow-glow-green hover:shadow-lg transition-all flex items-center justify-center gap-2.5 group cursor-pointer">
        <svg class="w-5 h-5 transition group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>Generate Bill</span>
    </button>

    <div class="bg-slate-50 rounded-xl p-3 text-xs text-slate-500 space-y-1.5 border border-slate-100 font-sans">
        <div class="flex items-center gap-1.5 text-slate-700 font-medium">
            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>Instant on-screen bill invoice</span>
        </div>
        <div class="flex items-center gap-1.5 text-slate-700 font-medium">
            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>Audit trail recorded to stock movements</span>
        </div>
    </div>
</div>

