@props([
    'id' => 'alertBanner',
    'type' => 'info', // 'error', 'warning', 'success', 'info'
    'title' => null,
    'message' => null,
    'details' => [],
    'dismissible' => true,
])

@php
    // Detect server-side session or validation errors automatically
    if ($errors->any()) {
        $type = 'error';
        $title = $title ?? 'Validation Error';
        $message = $message ?? 'Please correct the following errors:';
        $details = !empty($details) ? $details : $errors->all();
    } elseif (session('error')) {
        $type = 'error';
        $title = $title ?? 'Error';
        $message = session('error');
    } elseif (session('warning')) {
        $type = 'warning';
        $title = $title ?? 'Warning';
        $message = session('warning');
    } elseif (session('success')) {
        $type = 'success';
        $title = $title ?? 'Success';
        $message = session('success');
    }

    $typeConfig = [
        'error' => [
            'bg' => 'bg-rose-50/95',
            'border' => 'border-rose-300',
            'text' => 'text-rose-950',
            'iconBg' => 'bg-rose-100 text-rose-700',
            'badge' => 'bg-rose-200 text-rose-800',
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            'defaultTitle' => 'API Error Encountered',
        ],
        'warning' => [
            'bg' => 'bg-amber-50/95',
            'border' => 'border-amber-300',
            'text' => 'text-amber-950',
            'iconBg' => 'bg-amber-100 text-amber-800',
            'badge' => 'bg-amber-200 text-amber-900',
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
            'defaultTitle' => 'API Warning Notice',
        ],
        'success' => [
            'bg' => 'bg-emerald-50/95',
            'border' => 'border-emerald-300',
            'text' => 'text-emerald-950',
            'iconBg' => 'bg-emerald-100 text-emerald-700',
            'badge' => 'bg-emerald-200 text-emerald-900',
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            'defaultTitle' => 'Action Completed',
        ],
        'info' => [
            'bg' => 'bg-blue-50/95',
            'border' => 'border-blue-300',
            'text' => 'text-blue-950',
            'iconBg' => 'bg-blue-100 text-blue-700',
            'badge' => 'bg-blue-200 text-blue-900',
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            'defaultTitle' => 'System Information',
        ],
    ];

    $cfg = $typeConfig[$type] ?? $typeConfig['info'];
    $hasInitialContent = !empty($message) || !empty($title) || !empty($details);
@endphp

<!-- Reusable Blade Alert Component for API & Validation Errors -->
<div id="{{ $id }}" 
     class="{{ $hasInitialContent ? '' : 'hidden' }} {{ $cfg['bg'] }} {{ $cfg['border'] }} {{ $cfg['text'] }} rounded-2xl p-4 sm:p-5 border shadow-sm transition-all duration-300 relative overflow-hidden mb-6" 
     role="alert"
     data-alert-component>

    <div class="flex items-start gap-3.5">
        
        <!-- Status Severity Icon -->
        <div id="{{ $id }}Icon" class="{{ $cfg['iconBg'] }} shrink-0 w-9 h-9 rounded-xl flex items-center justify-center font-bold shadow-2xs">
            {!! $cfg['icon'] !!}
        </div>

        <!-- Alert Title, Message & Itemized Breakdown -->
        <div class="flex-1 min-w-0 pt-0.5">
            <div class="flex items-center gap-2 flex-wrap">
                <h4 id="{{ $id }}Title" class="text-xs sm:text-sm font-bold tracking-tight uppercase">
                    {{ $title ?? $cfg['defaultTitle'] }}
                </h4>
                <span id="{{ $id }}Badge" class="{{ $cfg['badge'] }} text-[10px] font-mono uppercase px-2 py-0.5 rounded-full font-bold">
                    {{ strtoupper($type) }}
                </span>
            </div>

            <p id="{{ $id }}Message" class="text-xs sm:text-sm mt-1 font-medium leading-relaxed">
                {{ $message }}
            </p>

            <!-- Itemized Details / Validation Errors List -->
            <ul id="{{ $id }}Details" class="{{ !empty($details) ? '' : 'hidden' }} mt-2.5 space-y-1.5 text-xs border-t border-black/10 pt-2 font-medium">
                @if(!empty($details))
                    @foreach($details as $detail)
                        <li class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70 shrink-0"></span>
                            <span>{{ $detail }}</span>
                        </li>
                    @endforeach
                @endif
            </ul>
        </div>

        <!-- Dismiss Button -->
        @if($dismissible)
            <button type="button" 
                    onclick="dismissAlert('{{ $id }}')" 
                    title="Dismiss alert"
                    class="shrink-0 w-7 h-7 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-black/5 flex items-center justify-center text-sm font-bold transition cursor-pointer">
                ✕
            </button>
        @endif

    </div>
</div>
