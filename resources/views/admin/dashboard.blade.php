@extends('layouts.app')

@section('styles')
<style>


    /* Pulse glow on "Now Serving" number when active */
    .serving-active {
        animation: servingPulse 2s infinite ease-in-out;
    }
    @keyframes servingPulse {
        0%,100% { text-shadow: 0 0 20px rgba(99,102,241,0.3); }
        50%      { text-shadow: 0 0 50px rgba(99,102,241,0.7), 0 0 80px rgba(99,102,241,0.3); }
    }

    /* Toast slide-in */
    #scanToast {
        transition: opacity 0.3s ease, transform 0.3s ease;
    }
    #scanToast.hidden-toast {
        opacity: 0;
        transform: translateY(10px);
        pointer-events: none;
    }
</style>
@endsection

@section('content')
<div class="fade-in grid grid-cols-1 lg:grid-cols-12 gap-6 items-start w-full">

    {{-- ─── Flash Messages ───────────────────────────────────────────── --}}
    @if(session('success') || session('warning') || session('info'))
    <div class="lg:col-span-12">
        @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-sm font-semibold">{{ session('success') }}</span>
        </div>
        @endif
        @if(session('warning'))
        <div class="p-4 bg-amber-500/10 border border-amber-500/20 text-amber-400 rounded-2xl flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span class="text-sm font-semibold">{{ session('warning') }}</span>
        </div>
        @endif
        @if(session('info'))
        <div class="p-4 bg-blue-500/10 border border-blue-500/20 text-blue-400 rounded-2xl flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-sm font-semibold">{{ session('info') }}</span>
        </div>
        @endif
    </div>
    @endif

    {{-- ─── STAT CARDS (top row) ─────────────────────────────────────── --}}
    <div class="lg:col-span-12 grid grid-cols-1 md:grid-cols-3 gap-5">

        {{-- Capacity Card --}}
        <div class="glass-panel rounded-3xl p-6 border border-gray-200 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Daily Capacity</span>
                <span class="text-xs px-2.5 py-1 bg-blue-500/10 text-blue-400 border border-blue-500/20 rounded-full font-bold">
                    {{ $totalToday }} / {{ $dailyLimit }}
                </span>
            </div>
            @php
                $pct      = $dailyLimit > 0 ? min(100, ($totalToday / $dailyLimit) * 100) : 0;
                $barColor = $pct >= 90 ? 'bg-rose-500' : ($pct >= 75 ? 'bg-amber-500' : 'bg-blue-500');
            @endphp
            <div class="w-full bg-gray-50 h-3 rounded-full overflow-hidden border border-gray-300">
                <div class="h-full {{ $barColor }} transition-all duration-500" style="width:{{ $pct }}%"></div>
            </div>
            <form action="{{ route('admin.update-settings') }}" method="POST" class="mt-4 flex items-center gap-2">
                @csrf
                <input type="number" name="daily_limit" value="{{ $dailyLimit }}" min="1" max="999"
                       class="flex-1 px-3 py-1.5 bg-gray-50 rounded-lg text-sm text-center text-gray-900 border border-gray-200 focus:border-blue-500 focus:outline-none font-bold">
                <button type="submit" class="px-4 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-200 text-xs font-bold rounded-lg border border-gray-700 transition-colors">
                    Set Limit
                </button>
            </form>
        </div>

        {{-- Total Served --}}
        <div class="glass-panel rounded-3xl p-6 border border-gray-200 flex items-center justify-between bg-gradient-to-r from-gray-950/50 to-indigo-950/10">
            <div class="flex flex-col">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Total Served</span>
                <span class="text-4xl font-extrabold text-gray-900 mt-1">{{ $servedCount }}</span>
                <span class="text-xs text-gray-500 mt-1">Completed today</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-400 border border-indigo-500/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </div>

        {{-- Waiting --}}
        <div class="glass-panel rounded-3xl p-6 border border-gray-200 flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Waiting in Queue</span>
                <span class="text-4xl font-extrabold text-gray-900 mt-1">{{ $pending->count() }}</span>
                <span class="text-xs text-gray-500 mt-1">In pending state</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-400 border border-amber-500/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

    </div>

    {{-- ─── LEFT COLUMN: CALLER + SCANNER (7 cols) ─────────────────── --}}
    <div class="lg:col-span-7 flex flex-col gap-6">

        {{-- CALLING CONSOLE --}}
        <div class="glass-panel rounded-3xl p-7 border border-gray-200 flex flex-col gap-5">

            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-gray-300 pb-4">
                <h3 class="text-sm font-bold text-gray-600 uppercase tracking-widest flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 animate-ping"></span>
                    Counter Calling Board
                </h3>
                @if($serving)
                <span class="text-xs font-bold text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 px-2.5 py-1 rounded-full">Active</span>
                @else
                <span class="text-xs font-bold text-gray-600 bg-gray-50 border border-gray-300 px-2.5 py-1 rounded-full">Idle</span>
                @endif
            </div>

            {{-- Now Serving big number --}}
            <div class="text-center py-4">
                @if($serving)
                <div class="text-xs font-bold text-indigo-400 uppercase tracking-widest mb-1">Now Serving</div>
                <div id="servingNum"
                     data-token="{{ $serving->token_number }}"
                     class="text-8xl font-black text-gray-900 tracking-widest font-mono serving-active">
                    {{ $serving->token_number }}
                </div>
                <p class="text-xs text-gray-500 mt-2">Called at {{ $serving->called_at->format('h:i:s A') }}</p>
                @else
                <div id="servingNum" data-token="" class="text-gray-600 font-semibold text-lg py-6">
                    No client is currently being served
                </div>
                @endif
            </div>

            {{-- Action Buttons --}}
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                @if($serving)
                <form action="{{ route('admin.serve', $serving->id) }}" method="POST">
                    @csrf
                    <button class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-gray-900 font-bold text-sm rounded-2xl border border-emerald-500 transition-colors flex items-center justify-center gap-1.5 shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Serve
                    </button>
                </form>
                <form action="{{ route('admin.skip', $serving->id) }}" method="POST">
                    @csrf
                    <button class="w-full py-3 bg-gray-800 hover:bg-rose-950 hover:text-rose-400 hover:border-rose-800 text-gray-300 font-bold text-sm rounded-2xl border border-gray-700 transition-all flex items-center justify-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        Skip
                    </button>
                </form>
                <form action="{{ route('admin.recall', $serving->id) }}" method="POST">
                    @csrf
                    <button class="w-full py-3 bg-gray-800 hover:bg-gray-700 text-gray-200 font-bold text-sm rounded-2xl border border-gray-700 transition-colors flex items-center justify-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>
                        Re-call
                    </button>
                </form>
                @else
                <div></div><div></div><div></div>
                @endif

                <form action="{{ route('admin.call-next') }}" method="POST"
                      class="{{ $serving ? '' : 'col-span-1 md:col-span-1' }}">
                    @csrf
                    <input type="hidden" name="type" value="priority">
                    <button class="w-full py-3 bg-amber-500 hover:bg-amber-400 text-white font-extrabold text-sm rounded-2xl border border-amber-500 transition-colors flex items-center justify-center gap-1.5 shadow-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Priority
                    </button>
                </form>
                <form action="{{ route('admin.call-next') }}" method="POST"
                      class="{{ $serving ? '' : 'col-span-1 md:col-span-1' }}">
                    @csrf
                    <input type="hidden" name="type" value="regular">
                    <button class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-sm rounded-2xl border border-indigo-500 transition-colors flex items-center justify-center gap-1.5 shadow-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        Regular
                    </button>
                </form>
            </div>
        </div>

        {{-- ── QR CAMERA SCANNER (embedded, external camera) ─────────── --}}
        <div class="glass-panel rounded-3xl p-6 border border-gray-200 flex flex-col gap-4">
            <div class="flex items-center justify-between border-b border-gray-300 pb-3">
                <h3 class="text-sm font-bold text-gray-600 uppercase tracking-widest flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0a8 8 0 11-16 0 8 8 0 0116 0z"/>
                    </svg>
                    QR Token Scanner
                </h3>
                <div class="flex items-center gap-2">
                    {{-- Camera selector --}}
                    <select id="cameraSelect"
                            class="text-xs bg-white border border-gray-700 text-gray-300 rounded-lg px-2 py-1 focus:outline-none">
                        <option value="">Loading cameras…</option>
                    </select>
                    <button id="toggleScanner"
                            class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-gray-900 text-xs font-bold rounded-lg border border-emerald-500 transition-colors">
                        Start Scanner
                    </button>
                </div>
            </div>

            {{-- Camera preview area --}}
            <div class="relative rounded-2xl overflow-hidden bg-gray-50 border border-gray-300" style="min-height:220px;">
                <div id="scannerPreview" class="w-full" style="min-height:220px;"></div>

                {{-- Idle overlay --}}
                <div id="scannerIdle" class="absolute inset-0 flex flex-col items-center justify-center text-gray-700 gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="text-sm font-semibold">Waiting for camera permission…</span>
                </div>
            </div>

            {{-- Scan result toast --}}
            <div id="scanToast" class="hidden-toast opacity-0 px-4 py-3 rounded-2xl text-sm font-semibold flex items-center gap-3 border"></div>
        </div>

    </div>

    {{-- ─── RIGHT COLUMN: PRINT CENTER + QUEUE LISTS (5 cols) ──────── --}}
    <div class="lg:col-span-5 flex flex-col gap-6">

        {{-- PRINT CENTER --}}
        <div class="glass-panel rounded-3xl p-6 border border-gray-200 flex flex-col gap-4">
            <h3 class="text-sm font-bold text-gray-600 uppercase tracking-widest flex items-center gap-2 border-b border-gray-300 pb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Token QR Print Center
            </h3>

            <div class="p-4 rounded-2xl bg-gray-50/50 border border-gray-300">
                <span class="text-xs uppercase text-gray-500 font-bold tracking-wider">Batch Print</span>
                <form action="{{ route('admin.print') }}" method="GET" target="_blank" class="mt-3 flex items-end gap-2">
                    <input type="hidden" name="type" value="batch">
                    <div class="flex-1">
                        <label class="text-[10px] uppercase font-bold text-gray-500 block mb-1">Queue Type</label>
                        <select name="prefix" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm text-center text-gray-900 focus:outline-none">
                            <option value="">Regular</option>
                            <option value="P-">Priority</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="text-[10px] uppercase font-bold text-gray-500 block mb-1">Start</label>
                        <input type="number" name="start" value="1" min="1" max="999"
                               class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm text-center text-gray-900 focus:outline-none">
                    </div>
                    <div class="flex-1">
                        <label class="text-[10px] uppercase font-bold text-gray-500 block mb-1">End</label>
                        <input type="number" name="end" value="50" min="1" max="999"
                               class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm text-center text-gray-900 focus:outline-none">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-gray-900 font-bold text-sm rounded-lg border border-blue-500 transition-colors">
                        Print
                    </button>
                </form>
            </div>

            <div class="p-4 rounded-2xl bg-gray-50/50 border border-gray-300">
                <span class="text-xs uppercase text-gray-500 font-bold tracking-wider">Single Ticket Reprint</span>
                <form action="{{ route('admin.print') }}" method="GET" target="_blank" class="mt-3 flex items-end gap-2">
                    <input type="hidden" name="type" value="single">
                    <div class="w-24">
                        <select name="prefix" class="w-full px-2 py-2 bg-white border border-gray-200 rounded-lg text-sm text-center text-gray-900 focus:outline-none">
                            <option value="">Reg</option>
                            <option value="P-">Prio</option>
                        </select>
                    </div>
                    <input type="number" name="number" placeholder="e.g. 042" min="1" max="999" required
                           class="flex-1 px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm text-center text-gray-900 focus:outline-none">
                    <button type="submit" class="px-5 py-2 bg-gray-800 hover:bg-gray-700 text-gray-200 font-bold text-sm rounded-lg border border-gray-700 transition-colors">
                        Print
                    </button>
                </form>
            </div>
        </div>

        {{-- PENDING FIFO LIST --}}
        <div class="glass-panel rounded-3xl p-6 border border-gray-200 flex flex-col">
            <h3 class="text-sm font-bold text-gray-600 uppercase tracking-widest mb-4 flex items-center gap-2 border-b border-gray-300 pb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2"/>
                </svg>
                Pending Queue (FIFO)
            </h3>
            <div class="max-h-64 overflow-y-auto flex flex-col gap-2 pr-1">
                @forelse($pending as $index => $item)
                <div class="flex items-center justify-between p-3 bg-white/40 rounded-xl border border-gray-300/60">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-gray-50 text-gray-600 text-xs flex items-center justify-center font-bold">{{ $index + 1 }}</span>
                        <span class="text-lg font-bold text-gray-900 font-mono tracking-widest">{{ $item->token_number }}</span>
                    </div>
                    <span class="text-[11px] text-gray-500 font-semibold uppercase">{{ $item->created_at->diffForHumans() }}</span>
                </div>
                @empty
                <div class="text-center py-10 text-gray-600 font-semibold text-sm">No clients currently waiting</div>
                @endforelse
            </div>
        </div>

        {{-- HISTORY + RESET --}}
        <div class="glass-panel rounded-3xl p-6 border border-gray-200 flex flex-col gap-4">
            <h3 class="text-sm font-bold text-gray-600 uppercase tracking-widest flex items-center gap-2 border-b border-gray-300 pb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                History (Served / Skipped)
            </h3>

            <div class="max-h-48 overflow-y-auto flex flex-col gap-2 pr-1">
                @php $history = $served->merge($skipped)->sortByDesc('updated_at')->take(10); @endphp
                @forelse($history as $item)
                <div class="flex items-center justify-between p-3 bg-white/20 rounded-xl border border-gray-300/40">
                    <span class="text-base font-bold text-gray-600 font-mono tracking-widest">{{ $item->token_number }}</span>
                    <div class="flex items-center gap-2">
                        @if($item->status === 'served')
                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">Served</span>
                        @else
                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400">Skipped</span>
                        @endif
                        <span class="text-[10px] text-gray-600">{{ $item->updated_at->format('h:i A') }}</span>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-600 font-semibold text-sm">No operations yet today</div>
                @endforelse
            </div>

            <div class="flex justify-between items-center pt-4 border-t border-gray-300">
                <div>
                    <span class="text-xs font-bold text-gray-600 uppercase tracking-widest">Clear Daily Counter</span>
                    <p class="text-[10px] text-gray-600">Deletes all tokens and restarts from zero.</p>
                </div>
                <form action="{{ route('admin.reset') }}" method="POST" onsubmit="return confirmReset(event);">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 bg-rose-950 hover:bg-rose-900 text-rose-400 font-bold text-xs rounded-xl border border-rose-900/40 transition-colors">
                        Start New Day
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>
@endsection

@section('scripts')
{{-- html5-qrcode for external camera scanning --}}
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode/html5-qrcode.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ══════════════════════════════════════════════════════════
       1. VOICE ANNOUNCEMENT – speak Now Serving number on load
       ══════════════════════════════════════════════════════════ */
    const servingEl = document.getElementById('servingNum');
    const servingToken = servingEl ? servingEl.dataset.token : '';

    function speakToken(token) {
        if (!token || !window.speechSynthesis) return;
        window.speechSynthesis.cancel();
        const digits = token.split('').map(d => d === '0' ? 'zero' : d).join(' ');
        const msg = new SpeechSynthesisUtterance(
            `Now serving, token number ${digits}. Please proceed to the window.`
        );
        msg.rate  = 0.88;
        msg.pitch = 1.0;
        msg.volume = 1.0;
        const voices = window.speechSynthesis.getVoices();
        const eng = voices.find(v => v.lang.startsWith('en'));
        if (eng) msg.voice = eng;
        window.speechSynthesis.speak(msg);
    }

    function playChime() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(659.25, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(523.25, ctx.currentTime + 0.4);
            gain.gain.setValueAtTime(0.28, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 1.1);
            osc.connect(gain); gain.connect(ctx.destination);
            osc.start(); osc.stop(ctx.currentTime + 1.1);
        } catch(e) {}
    }

    // Pre-load voices (Chrome async requirement)
    if (window.speechSynthesis) {
        window.speechSynthesis.getVoices();
        if (window.speechSynthesis.onvoiceschanged !== undefined) {
            window.speechSynthesis.onvoiceschanged = () => window.speechSynthesis.getVoices();
        }
    }

    // Announce on page load only if there is an active serving token
    // (page reloads after "Call Next", so token will be fresh every time)
    if (servingToken) {
        // Small delay so the browser voice engine is ready
        setTimeout(() => {
            playChime();
            setTimeout(() => speakToken(servingToken), 750);
        }, 600);
    }

    /* ══════════════════════════════════════════════════════════
       2. EMBEDDED QR SCANNER  (external USB/webcam camera)
       ══════════════════════════════════════════════════════════ */
    const toggleBtn    = document.getElementById('toggleScanner');
    const cameraSelect = document.getElementById('cameraSelect');
    const previewDiv   = document.getElementById('scannerPreview');
    const idleOverlay  = document.getElementById('scannerIdle');
    const toast        = document.getElementById('scanToast');

    let scanner     = null;
    let scannerActive = false;

    // Populate camera list then auto-start on first available camera
    Html5Qrcode.getCameras().then(cams => {
        cameraSelect.innerHTML = '';
        if (!cams || cams.length === 0) {
            cameraSelect.innerHTML = '<option>No cameras found</option>';
            return;
        }
        cams.forEach(cam => {
            const opt = document.createElement('option');
            opt.value = cam.id;
            opt.text  = cam.label || `Camera ${cam.id.substr(0,6)}`;
            cameraSelect.appendChild(opt);
        });
        // Auto-start with the first camera
        startScanner();
    }).catch(() => {
        cameraSelect.innerHTML = '<option>Permission denied</option>';
    });

    // Camera change → restart scanner on new device
    cameraSelect.addEventListener('change', () => {
        if (scannerActive) stopScanner().then(() => startScanner());
        else startScanner();
    });

    toggleBtn.addEventListener('click', () => {
        if (scannerActive) stopScanner();
        else startScanner();
    });

    function startScanner() {
        const camId = cameraSelect.value;
        if (!camId) { showToast(false, 'No camera selected. Please allow camera access.'); return; }

        scanner = new Html5Qrcode('scannerPreview');
        return scanner.start(
            camId,
            { fps: 12, qrbox: { width: 220, height: 220 } },
            decodedText => handleScan(decodedText),
            () => {}
        ).then(() => {
            scannerActive = true;
            idleOverlay.classList.add('hidden');
            toggleBtn.textContent = 'Stop Scanner';
            toggleBtn.classList.replace('bg-emerald-600', 'bg-rose-700');
            toggleBtn.classList.replace('hover:bg-emerald-500', 'hover:bg-rose-600');
            toggleBtn.classList.replace('border-emerald-500', 'border-rose-600');
        }).catch(err => {
            showToast(false, 'Could not start camera: ' + err);
        });
    }

    function stopScanner() {
        return new Promise(resolve => {
            if (scanner) {
                scanner.stop().catch(() => {}).finally(() => {
                    scanner = null;
                    scannerActive = false;
                    idleOverlay.classList.remove('hidden');
                    toggleBtn.textContent = 'Start Scanner';
                    toggleBtn.classList.replace('bg-rose-700', 'bg-emerald-600');
                    toggleBtn.classList.replace('hover:bg-rose-600', 'hover:bg-emerald-500');
                    toggleBtn.classList.replace('border-rose-600', 'border-emerald-500');
                    resolve();
                });
            } else resolve();
        });
    }

    let lastScanned = '';
    let lastScanTime = 0;

    function handleScan(raw) {
        const now = Date.now();
        // Debounce: ignore same code re-scanned within 3 seconds
        if (raw === lastScanned && (now - lastScanTime) < 3000) return;
        lastScanned  = raw;
        lastScanTime = now;

        fetch("{{ route('client.scan-submit') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ token_number: raw })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                playBeep('success');
                showToast(true, `✔ Token ${data.token_number} registered — Queue position #${data.position}`);
            } else {
                playBeep('error');
                showToast(false, '✘ ' + data.message);
            }
        })
        .catch(() => showToast(false, 'Server error. Please try again.'));
    }

    function playBeep(type) {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc  = ctx.createOscillator();
            const gain = ctx.createGain();
            if (type === 'success') {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(523.25, ctx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(659.25, ctx.currentTime + 0.1);
            } else {
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(160, ctx.currentTime);
                osc.frequency.linearRampToValueAtTime(110, ctx.currentTime + 0.25);
            }
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
            osc.connect(gain); gain.connect(ctx.destination);
            osc.start(); osc.stop(ctx.currentTime + 0.35);
        } catch(e) {}
    }

    let toastTimer = null;
    function showToast(success, msg) {
        toast.className = success
            ? 'px-4 py-3 rounded-2xl text-sm font-semibold flex items-center gap-3 border bg-emerald-500/10 border-emerald-500/30 text-emerald-300'
            : 'px-4 py-3 rounded-2xl text-sm font-semibold flex items-center gap-3 border bg-rose-500/10 border-rose-500/30 text-rose-300';
        toast.textContent = msg;
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';

        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
        }, 4000);
    }

    /* ══════════════════════════════════════════════════════════
       3. RESET CONFIRMATION
       ══════════════════════════════════════════════════════════ */
    window.confirmReset = function(e) {
        const ok = confirm("WARNING: This will delete ALL tokens and reset today's counter to 0.\n\nAre you sure?");
        if (!ok) { e.preventDefault(); return false; }
        return true;
    };
});
</script>
@endsection
