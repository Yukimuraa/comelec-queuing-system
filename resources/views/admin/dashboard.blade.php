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

    /* Custom Toast slide animation */
    .toast-enter {
        animation: slideInRight 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    @keyframes slideInRight {
        from { transform: translateX(100%) translateY(0px); opacity: 0; }
        to { transform: translateX(0) translateY(0px); opacity: 1; }
    }
</style>
@endsection

@section('content')
<div class="fade-in grid grid-cols-1 lg:grid-cols-12 gap-6 items-start w-full relative">

    {{-- Toast Stack Container --}}
    <div id="toastContainer" class="fixed top-5 right-5 z-50 flex flex-col gap-3 max-w-sm w-full no-print"></div>

    {{-- ─── STAT CARDS (top row) ─────────────────────────────────────── --}}
    <div class="lg:col-span-12 grid grid-cols-1 md:grid-cols-3 gap-5">

        {{-- Capacity Card --}}
        <div class="glass-panel rounded-3xl p-6 border border-gray-200 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Daily Capacity</span>
                <span id="capacityLabel" class="text-xs px-2.5 py-1 bg-blue-500/10 text-blue-400 border border-blue-500/20 rounded-full font-bold">
                    {{ $totalToday }} / {{ $dailyLimit }}
                </span>
            </div>
            @php
                $pct      = $dailyLimit > 0 ? min(100, ($totalToday / $dailyLimit) * 100) : 0;
                $barColor = $pct >= 90 ? 'bg-rose-500' : ($pct >= 75 ? 'bg-amber-500' : 'bg-blue-500');
            @endphp
            <div id="capacityBarContainer" class="w-full bg-gray-50 h-3 rounded-full overflow-hidden border border-gray-300">
                <div id="capacityBar" class="h-full {{ $barColor }} transition-all duration-500" style="width:{{ $pct }}%"></div>
            </div>
            <form id="settingsForm" action="{{ route('admin.update-settings') }}" method="POST" class="mt-4 flex items-center gap-2">
                @csrf
                <input type="number" id="dailyLimitInput" name="daily_limit" value="{{ $dailyLimit }}" min="1" max="999"
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
                <span id="servedCountLabel" class="text-4xl font-extrabold text-gray-900 mt-1">{{ $servedCount }}</span>
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
                <span id="waitingCountLabel" class="text-4xl font-extrabold text-gray-900 mt-1">{{ $pending->count() }}</span>
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
                    <span id="activeStatusPing" class="w-2 h-2 rounded-full bg-gray-400"></span>
                    Counter Calling Board
                </h3>
                <span id="activeStatusLabel" class="text-xs font-bold text-gray-600 bg-gray-50 border border-gray-300 px-2.5 py-1 rounded-full">Idle</span>
            </div>

            {{-- Now Serving big number --}}
            <div id="callingConsoleContainer" class="text-center py-4">
                <div id="servingLabel" class="hidden text-xs font-bold text-indigo-400 uppercase tracking-widest mb-1">Now Serving</div>
                <div id="servingNum" data-token="" class="text-gray-600 font-semibold text-lg py-6">
                    No client is currently being served
                </div>
                <p id="calledTimeLabel" class="hidden text-xs text-gray-500 mt-2"></p>
            </div>

            {{-- Action Buttons --}}
            <div id="callingButtonsContainer" class="grid grid-cols-2 md:grid-cols-5 gap-3">
                <!-- Dynamically populated or updated by Javascript -->
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
                        <select name="prefix" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm text-center text-gray-900 focus:outline-none font-bold">
                            <option value="">Regular</option>
                            <option value="P-">Priority</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="text-[10px] uppercase font-bold text-gray-500 block mb-1">Start</label>
                        <input type="number" name="start" value="1" min="1" max="999"
                               class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm text-center text-gray-900 focus:outline-none font-bold">
                    </div>
                    <div class="flex-1">
                        <label class="text-[10px] uppercase font-bold text-gray-500 block mb-1">End</label>
                        <input type="number" name="end" value="50" min="1" max="999"
                               class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm text-center text-gray-900 focus:outline-none font-bold">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-gray-900 font-bold text-sm rounded-lg border border-blue-500 transition-colors shadow">
                        Print
                    </button>
                </form>
            </div>

            <div class="p-4 rounded-2xl bg-gray-50/50 border border-gray-300">
                <span class="text-xs uppercase text-gray-500 font-bold tracking-wider">Single Ticket Reprint</span>
                <form action="{{ route('admin.print') }}" method="GET" target="_blank" class="mt-3 flex items-end gap-2">
                    <input type="hidden" name="type" value="single">
                    <div class="w-24">
                        <select name="prefix" class="w-full px-2 py-2 bg-white border border-gray-200 rounded-lg text-sm text-center text-gray-900 focus:outline-none font-bold">
                            <option value="">Reg</option>
                            <option value="P-">Prio</option>
                        </select>
                    </div>
                    <input type="number" name="number" placeholder="e.g. 042" min="1" max="999" required
                           class="flex-1 px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm text-center text-gray-900 focus:outline-none font-bold">
                    <button type="submit" class="px-5 py-2 bg-gray-800 hover:bg-gray-700 text-gray-200 font-bold text-sm rounded-lg border border-gray-700 transition-colors shadow">
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
            <div id="pendingQueueList" class="max-h-64 overflow-y-auto flex flex-col gap-2 pr-1">
                <!-- Dynamically loaded -->
            </div>
        </div>

        {{-- HISTORY (SERVED ONLY) + RESET --}}
        <div class="glass-panel rounded-3xl p-6 border border-gray-200 flex flex-col gap-4">
            <h3 class="text-sm font-bold text-gray-600 uppercase tracking-widest flex items-center gap-2 border-b border-gray-300 pb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                History (Served)
            </h3>

            <div id="historyList" class="max-h-48 overflow-y-auto flex flex-col gap-2 pr-1">
                <!-- Dynamically loaded (Served tokens only) -->
            </div>

            <div class="flex justify-between items-center pt-4 border-t border-gray-300">
                <div>
                    <span class="text-xs font-bold text-gray-600 uppercase tracking-widest">Clear Daily Counter</span>
                    <p class="text-[10px] text-gray-650 font-semibold">Deletes all tokens and restarts from zero.</p>
                </div>
                <form id="resetQueueForm" action="{{ route('admin.reset') }}" method="POST" onsubmit="return confirmReset(event);">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 bg-rose-950 hover:bg-rose-900 text-rose-400 font-bold text-xs rounded-xl border border-rose-900/40 transition-colors shadow">
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
       1. VOICE & SOUND BROADCASTER
       ══════════════════════════════════════════════════════════ */
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

    function tvVoiceIsActive() {
        try {
            const owner = localStorage.getItem('qms_voice_owner');
            const tsRaw = localStorage.getItem('qms_voice_owner_ts');
            const ts = tsRaw ? Number(tsRaw) : 0;
            // If the TV display page is running (recent heartbeat), let it handle the voice.
            return owner === 'tv' && Number.isFinite(ts) && (Date.now() - ts) < 6000;
        } catch (e) {
            return false;
        }
    }

    if (window.speechSynthesis) {
        window.speechSynthesis.getVoices();
        if (window.speechSynthesis.onvoiceschanged !== undefined) {
            window.speechSynthesis.onvoiceschanged = () => window.speechSynthesis.getVoices();
        }
    }


    /* ══════════════════════════════════════════════════════════
       2. DYNAMIC FLOATING TOASTS
       ══════════════════════════════════════════════════════════ */
    function showToast(success, message) {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `p-4 rounded-2xl text-xs font-bold flex items-center gap-3 border shadow-xl bg-white/90 backdrop-blur-md toast-enter transition-all duration-300 ` + 
            (success 
                ? 'border-emerald-500/30 text-emerald-600' 
                : 'border-rose-500/30 text-rose-600');
                
        toast.innerHTML = `
            <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0 ${success ? 'bg-emerald-500/10' : 'bg-rose-500/10'}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    ${success 
                        ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                        : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>'
                    }
                </svg>
            </div>
            <span>${message}</span>
        `;
        
        container.appendChild(toast);
        
        // Remove after 3.5 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-10px)';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }


    /* ══════════════════════════════════════════════════════════
       3. INTERCEPT ALL FORMS & PROCESS VIA AJAX
       ══════════════════════════════════════════════════════════ */
    document.addEventListener('submit', (e) => {
        const form = e.target;
        
        // If a form has action, and is NOT a target="_blank" reprint template
        if (form.getAttribute('action') && !form.getAttribute('target')) {
            e.preventDefault();
            submitFormAjax(form);
        }
    });

    function submitFormAjax(form) {
        const url = form.getAttribute('action');
        const method = form.getAttribute('method') || 'POST';
        const formData = new FormData(form);

        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(data => { throw new Error(data.message || 'Action failed'); });
            }
            return res.json();
        })
        .then(data => {
            if (data.success) {
                showToast(true, data.message);
                // Immediately refresh status
                pollQueueStatus();
            } else {
                showToast(false, data.message || 'An error occurred.');
            }
        })
        .catch(err => {
            showToast(false, err.message || 'Operation failed. Please try again.');
        });
    }


    /* ══════════════════════════════════════════════════════════
       4. REAL-TIME QUEUE STATUS POLLING & DOM UPDATER
       ══════════════════════════════════════════════════════════ */
    let lastCalledId = null;
    let lastCalledTimestamp = null;

    function pollQueueStatus() {
        fetch("{{ route('admin.dashboard-status') }}")
            .then(res => res.json())
            .then(data => {
                // 1. Update stats counts
                document.getElementById('capacityLabel').textContent = `${data.totalToday} / ${data.dailyLimit}`;
                document.getElementById('dailyLimitInput').value = data.dailyLimit;
                document.getElementById('servedCountLabel').textContent = data.servedCount;
                document.getElementById('waitingCountLabel').textContent = data.pending.length;

                // 2. Capacity bar color & width
                const bar = document.getElementById('capacityBar');
                bar.style.width = `${data.capacityPercent}%`;
                bar.className = 'h-full transition-all duration-500 ' + 
                    (data.capacityPercent >= 90 ? 'bg-rose-500' : (data.capacityPercent >= 75 ? 'bg-amber-500' : 'bg-blue-500'));

                // 3. Now Serving calling console
                const consoleDiv = document.getElementById('callingConsoleContainer');
                const pingEl = document.getElementById('activeStatusPing');
                const activeLabel = document.getElementById('activeStatusLabel');

                if (data.serving) {
                    const tokenNum = data.serving.token_number;
                    const callId = data.serving.id;
                    const callTs = data.serving.called_at_iso;

                    // Header active markers
                    pingEl.className = 'w-2 h-2 rounded-full bg-indigo-500 animate-ping';
                    activeLabel.textContent = 'Active';
                    activeLabel.className = 'text-xs font-bold text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 px-2.5 py-1 rounded-full';

                    // Center big token
                    consoleDiv.innerHTML = `
                        <div id="servingLabel" class="text-xs font-bold text-indigo-400 uppercase tracking-widest mb-1">Now Serving</div>
                        <div id="servingNum"
                             data-token="${tokenNum}"
                             class="text-8xl font-black text-gray-900 tracking-widest font-mono serving-active">
                            ${tokenNum}
                        </div>
                        <p id="calledTimeLabel" class="text-xs text-gray-500 mt-2">
                            Called at <span class="device-time" data-timestamp="${callTs}" data-seconds="true"></span>
                        </p>
                    `;

                    // Generate calling action buttons
                    document.getElementById('callingButtonsContainer').innerHTML = `
                        <form action="/admin/serve/${callId}" method="POST">
                            @csrf
                            <button class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-gray-900 font-bold text-sm rounded-2xl border border-emerald-500 transition-colors flex items-center justify-center gap-1.5 shadow-lg">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                Serve
                            </button>
                        </form>
                        <form action="/admin/skip/${callId}" method="POST">
                            @csrf
                            <button class="w-full py-3 bg-gray-800 hover:bg-rose-950 hover:text-rose-400 hover:border-rose-800 text-gray-300 font-bold text-sm rounded-2xl border border-gray-700 transition-all flex items-center justify-center gap-1.5">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                Skip
                            </button>
                        </form>
                        <form action="/admin/recall/${callId}" method="POST">
                            @csrf
                            <button class="w-full py-3 bg-gray-800 hover:bg-gray-700 text-gray-200 font-bold text-sm rounded-2xl border border-gray-700 transition-colors flex items-center justify-center gap-1.5">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>
                                Re-call
                            </button>
                        </form>
                        <form action="{{ route('admin.call-next') }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="next">
                            <button class="w-full py-3 bg-indigo-650 text-white font-extrabold text-sm rounded-2xl border border-indigo-600 flex items-center justify-center gap-1.5 shadow-xl">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                Call Next
                            </button>
                        </form>
                        <div class="grid grid-cols-2 gap-2">
                            <form action="{{ route('admin.call-next') }}" method="POST">
                                @csrf
                                <input type="hidden" name="type" value="priority">
                                <button class="w-full py-3 bg-amber-500 hover:bg-amber-400 text-white font-bold text-xs rounded-2xl border border-amber-500 transition-colors flex items-center justify-center gap-1 shadow-xl">
                                    Prio
                                </button>
                            </form>
                            <form action="{{ route('admin.call-next') }}" method="POST">
                                @csrf
                                <input type="hidden" name="type" value="regular">
                                <button class="w-full py-3 bg-indigo-500 hover:bg-indigo-400 text-white font-bold text-xs rounded-2xl border border-indigo-500 transition-colors flex items-center justify-center gap-1 shadow-xl">
                                    Reg
                                </button>
                            </form>
                        </div>
                    `;

                    // Voice Broadcast Trigger if newly called or recalled
                    const announceKey = `${callId}-${callTs}`;
                    if (window.lastAnnouncedKey !== announceKey) {
                        window.lastAnnouncedKey = announceKey;
                        // Avoid double announcements when the TV display is open in another tab/window.
                        if (!tvVoiceIsActive()) {
                            playChime();
                            setTimeout(() => speakToken(tokenNum), 750);
                        }
                    }
                    lastCalledId = callId;
                    lastCalledTimestamp = callTs;
                } else {
                    // Counter idle state
                    pingEl.className = 'w-2 h-2 rounded-full bg-gray-400';
                    activeLabel.textContent = 'Idle';
                    activeLabel.className = 'text-xs font-bold text-gray-650 bg-gray-50 border border-gray-300 px-2.5 py-1 rounded-full';

                    consoleDiv.innerHTML = `
                        <div id="servingNum" data-token="" class="text-gray-600 font-semibold text-lg py-6">
                            No client is currently being served
                        </div>
                    `;

                    document.getElementById('callingButtonsContainer').innerHTML = `
                        <form action="{{ route('admin.call-next') }}" method="POST" class="col-span-2 md:col-span-3">
                            @csrf
                            <input type="hidden" name="type" value="next">
                            <button class="w-full py-3 bg-indigo-650 text-white font-extrabold text-sm rounded-2xl border border-indigo-600 flex items-center justify-center gap-1.5 shadow-xl">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                Call Next (FIFO)
                            </button>
                        </form>
                        <form action="{{ route('admin.call-next') }}" method="POST" class="col-span-1">
                            @csrf
                            <input type="hidden" name="type" value="priority">
                            <button class="w-full py-3 bg-amber-500 hover:bg-amber-400 text-white font-extrabold text-sm rounded-2xl border border-amber-500 transition-colors flex items-center justify-center gap-1.5 shadow-xl">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Priority
                            </button>
                        </form>
                        <form action="{{ route('admin.call-next') }}" method="POST" class="col-span-1">
                            @csrf
                            <input type="hidden" name="type" value="regular">
                            <button class="w-full py-3 bg-indigo-500 hover:bg-indigo-400 text-white font-extrabold text-sm rounded-2xl border border-indigo-500 transition-colors flex items-center justify-center gap-1.5 shadow-xl">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                Regular
                            </button>
                        </form>
                    `;

                    lastCalledId = null;
                    lastCalledTimestamp = null;
                }

                // 4. Update Pending Queue List
                const pendingListDiv = document.getElementById('pendingQueueList');
                if (data.pending && data.pending.length > 0) {
                    let pendingHtml = '';
                    data.pending.forEach(item => {
                        pendingHtml += `
                            <div class="flex items-center justify-between p-3 bg-white/40 rounded-xl border border-gray-300/60">
                                <div class="flex items-center gap-3">
                                    <span class="w-6 h-6 rounded-full bg-gray-50 text-gray-600 text-xs flex items-center justify-center font-bold">${item.position}</span>
                                    <span class="text-lg font-bold text-gray-900 font-mono tracking-widest">${item.token_number}</span>
                                </div>
                                <span class="text-[10px] text-gray-500 font-semibold uppercase device-time-diff" data-timestamp="${item.created_at_iso}">Waiting...</span>
                            </div>
                        `;
                    });
                    pendingListDiv.innerHTML = pendingHtml;
                } else {
                    pendingListDiv.innerHTML = '<div class="text-center py-10 text-gray-650 font-semibold text-sm">No clients currently waiting</div>';
                }

                // 5. Update History List (Served tokens only)
                const historyListDiv = document.getElementById('historyList');
                if (data.history && data.history.length > 0) {
                    let historyHtml = '';
                    data.history.forEach(item => {
                        historyHtml += `
                            <div class="flex items-center justify-between p-3 bg-white/20 rounded-xl border border-gray-300/40">
                                <span class="text-base font-bold text-gray-600 font-mono tracking-widest">${item.token_number}</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">Served</span>
                                    <span class="device-time text-[10px] text-gray-600 font-bold" data-timestamp="${item.served_at_iso}"></span>
                                </div>
                            </div>
                        `;
                    });
                    historyListDiv.innerHTML = historyHtml;
                } else {
                    historyListDiv.innerHTML = '<div class="text-center py-8 text-gray-650 font-semibold text-sm">No operations yet today</div>';
                }

                // Apply device local times formatting to the newly injected elements
                if (window.formatDeviceTimes) {
                    window.formatDeviceTimes();
                }
                
                // Formats relative time counters (e.g. 5m ago)
                updateQueueDiffs();
            })
            .catch(err => {
                console.error("Dashboard status sync error:", err);
            });
    }

    // Client device wait-time helper (e.g. "5 mins ago")
    function updateQueueDiffs() {
        const elements = document.querySelectorAll('.device-time-diff');
        elements.forEach(el => {
            const timestamp = el.getAttribute('data-timestamp');
            if (timestamp) {
                const created = new Date(timestamp);
                const now = new Date();
                const diffMs = now - created;
                const diffMins = Math.floor(diffMs / 60000);
                const diffSecs = Math.floor((diffMs % 60000) / 1000);
                
                if (diffMins > 0) {
                    el.textContent = `${diffMins}m ${diffSecs}s ago`;
                } else {
                    el.textContent = `${diffSecs}s ago`;
                }
            }
        });
    }

    // Update wait timers every second
    setInterval(updateQueueDiffs, 1000);


    /* ══════════════════════════════════════════════════════════
       5. EMBEDDED QR SCANNER SYSTEM (AJAX scanner integration)
       ══════════════════════════════════════════════════════════ */
    const toggleBtn    = document.getElementById('toggleScanner');
    const cameraSelect = document.getElementById('cameraSelect');
    const idleOverlay  = document.getElementById('scannerIdle');

    let scanner     = null;
    let scannerActive = false;

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
        startScanner();
    }).catch(() => {
        cameraSelect.innerHTML = '<option>Permission denied</option>';
    });

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
        if (raw === lastScanned && (now - lastScanTime) < 3000) return;
        lastScanned  = raw;
        lastScanTime = now;

        fetch("{{ route('client.scan-submit') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ token_number: raw })
        })
        .then(r => {
            if (!r.ok) {
                return r.json().then(data => { throw new Error(data.message || 'Scan failed'); });
            }
            return r.json();
        })
        .then(data => {
            playBeep('success');
            showToast(true, `✔ Token ${data.token_number} registered — Queue position #${data.position}`);
            pollQueueStatus();
        })
        .catch(err => {
            playBeep('error');
            showToast(false, '✘ ' + err.message);
        });
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


    /* ══════════════════════════════════════════════════════════
       6. BOOTSTRAP INITIALIZATION
       ══════════════════════════════════════════════════════════ */
    window.confirmReset = function(e) {
        const ok = confirm("WARNING: This will delete ALL tokens and reset today's counter to 0.\n\nAre you sure?");
        if (!ok) { e.preventDefault(); return false; }
        return true;
    };

    // Load initial queue status on load
    pollQueueStatus();

    // Poll status updates dynamically every 2 seconds (live sync)
    setInterval(pollQueueStatus, 2000);
});
</script>
@endsection
