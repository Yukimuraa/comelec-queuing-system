@extends('layouts.app')

@section('styles')
<style>
    body {
        /* Custom darker TV layout background */
        background: radial-gradient(circle at 50% 50%, #ffffff 0%, #f3f4f6 100%) !important;
    }
    
    .serving-number {
        font-size: 11rem;
        line-height: 1;
        font-weight: 800;
        letter-spacing: -0.05em;
        text-shadow: 0 0 40px rgba(99, 102, 241, 0.4);
    }
    
    .pulse-glowing {
        animation: activePulse 2s infinite ease-in-out;
    }

    @keyframes activePulse {
        0%, 100% {
            box-shadow: 0 0 50px -10px rgba(99, 102, 241, 0.3), inset 0 0 30px rgba(99, 102, 241, 0.05);
            border-color: rgba(99, 102, 241, 0.2);
        }
        50% {
            box-shadow: 0 0 70px 0px rgba(99, 102, 241, 0.5), inset 0 0 40px rgba(99, 102, 241, 0.15);
            border-color: rgba(99, 102, 241, 0.5);
        }
    }

    .flip-card {
        transition: transform 0.6s;
        transform-style: preserve-3d;
    }

    /* Screen layout adjustment to make it feel full-screen monitor */
    main {
        max-width: 100% !important;
        padding-top: 1rem !important;
        padding-bottom: 1rem !important;
        height: calc(100vh - 100px);
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
</style>
@endsection

@section('content')
<div class="fade-in grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch h-full w-full">
    
    <!-- LEFT PANEL: NOW SERVING (7 columns) -->
    <div class="lg:col-span-8 flex flex-col justify-between p-10 rounded-3xl glass-panel pulse-glowing transition-all border border-indigo-500/20 text-center relative overflow-hidden flex-1 min-h-[450px]">
        <!-- Watermark / Background symbol -->
        <div class="absolute inset-0 opacity-[0.02] flex items-center justify-center pointer-events-none">
            <svg class="w-96 h-96 text-gray-900" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
        </div>

        <div class="z-10">
            <span class="text-xs font-bold uppercase tracking-widest text-indigo-400 bg-indigo-500/10 px-4 py-2 rounded-full border border-indigo-500/20">
                Now Serving
            </span>
        </div>

        <!-- Token Number Render Box -->
        <div class="my-auto z-10 py-6" id="servingContainer">
            <div class="serving-number text-gray-900 font-black tracking-widest font-mono" id="servingNumber">---</div>
            <div class="text-sm font-semibold uppercase tracking-widest text-gray-500 mt-4" id="servingStatusLabel">
                Please wait for the next call
            </div>
        </div>

        <!-- Sound Indicator Footer -->
        <div class="z-10 flex items-center justify-center gap-2 text-xs text-gray-600 font-semibold uppercase tracking-widest">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            TV Live System Connected &amp; Announcer Active
        </div>
    </div>

    <!-- RIGHT PANEL: QUEUE & STATS (4 columns) -->
    <div class="lg:col-span-4 flex flex-col gap-6 justify-between items-stretch">
        
        <!-- UP NEXT LIST -->
        <div class="flex-1 glass-panel rounded-3xl p-6 flex flex-col border border-gray-200">
            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2 border-b border-gray-300 pb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                </svg>
                Up Next / Waiting
            </h3>

            <!-- Upcoming token queue -->
            <div class="flex-1 flex flex-col gap-3 justify-center" id="pendingList">
                <!-- Fallback empty state -->
                <div class="text-center py-8 text-gray-600 font-semibold text-sm" id="emptyPendingLabel">
                    No clients waiting in queue
                </div>
            </div>
        </div>

        <!-- TOTAL SERVED STAT CARD -->
        <div class="glass-panel rounded-3xl p-6 flex items-center justify-between border border-gray-200 bg-gradient-to-r from-gray-50/80 to-gray-50/20">
            <div class="flex flex-col">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Served Today</span>
                <span class="text-4xl font-extrabold text-gray-900 mt-1" id="servedCount">0</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-400 border border-indigo-500/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const servingNumber = document.getElementById('servingNumber');
        const servingStatusLabel = document.getElementById('servingStatusLabel');
        const pendingList = document.getElementById('pendingList');
        const emptyPendingLabel = document.getElementById('emptyPendingLabel');
        const servedCount = document.getElementById('servedCount');

        let lastCalledId = null;
        let lastCalledTimestamp = null;
        let announcementQueue = [];

        // Synthesized Ding-Dong Chime using Web Audio API
        function playChime() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                
                // Primary Oscillator (Chime Bell)
                const osc = ctx.createOscillator();
                const gainNode = ctx.createGain();
                
                osc.type = 'sine';
                
                // Sound sequence: E5 (659.25Hz) immediately, then decay to C5 (523.25Hz)
                osc.frequency.setValueAtTime(659.25, ctx.currentTime); // E5
                osc.frequency.setValueAtTime(659.25, ctx.currentTime + 0.35); 
                osc.frequency.exponentialRampToValueAtTime(523.25, ctx.currentTime + 0.42); // C5
                
                gainNode.gain.setValueAtTime(0.25, ctx.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 1.2);
                
                osc.connect(gainNode);
                gainNode.connect(ctx.destination);
                
                osc.start();
                osc.stop(ctx.currentTime + 1.2);
            } catch (e) {
                console.error("Failed to generate synthesized chime audio.", e);
            }
        }

        // Voice Announcement using Web Speech API (Digit by Digit)
        function speakNumber(number) {
            if (!window.speechSynthesis) return;

            // Clear any active speak calls to prevent overlap lags
            window.speechSynthesis.cancel();

            // Format number to individual spoken digits (e.g. "0 4 2" -> "zero four two")
            const digitsArray = number.split('');
            const spokenDigits = digitsArray.map(d => {
                if (d === '0') return 'zero';
                return d;
            }).join(' ');

            const msg = new SpeechSynthesisUtterance();
            msg.text = `Now serving, token number ${spokenDigits}. Please proceed to the counter.`;
            msg.volume = 1.0;
            msg.rate = 0.85; // Read slightly slower for clear broadcast voice
            msg.pitch = 1.0;
            
            // Set english default voice if available
            const voices = window.speechSynthesis.getVoices();
            const englishVoice = voices.find(v => v.lang.startsWith('en'));
            if (englishVoice) {
                msg.voice = englishVoice;
            }

            window.speechSynthesis.speak(msg);
        }

        // Trigger announcement chain
        function announceToken(number) {
            // Stage 1: Play chime
            playChime();

            // Stage 2: Speak token digits after chime starts decaying (800ms)
            setTimeout(() => {
                speakNumber(number);
            }, 750);
        }

        // Polling function
        function pollQueueStatus() {
            fetch("{{ route('display.tv-status') }}")
                .then(res => res.json())
                .then(data => {
                    // Mark this page as the active voice announcer (so admin tab can stay silent)
                    try {
                        localStorage.setItem('qms_voice_owner', 'tv');
                        localStorage.setItem('qms_voice_owner_ts', String(Date.now()));
                    } catch (e) {}

                    // Update Served count today
                    servedCount.innerText = data.served_count;

                    // Update Currently Serving
                    if (data.serving) {
                        const tokenNum = data.serving.token_number;
                        const callId = data.serving.id;
                        const callTs = data.called_at_timestamp;

                        servingNumber.innerText = tokenNum;
                        servingNumber.classList.add('text-indigo-400');
                        servingStatusLabel.innerText = "Please proceed to Counter 1";
                        servingStatusLabel.classList.add('text-indigo-300');
                        servingStatusLabel.classList.remove('text-gray-500');

                        // Check if it's a NEW token called OR the SAME token recalled
                        if (callId !== lastCalledId || callTs !== lastCalledTimestamp) {
                            announceToken(tokenNum);
                            lastCalledId = callId;
                            lastCalledTimestamp = callTs;
                        }
                    } else {
                        servingNumber.innerText = "---";
                        servingNumber.classList.remove('text-indigo-400');
                        servingStatusLabel.innerText = "Please wait for the next call";
                        servingStatusLabel.classList.remove('text-indigo-300');
                        servingStatusLabel.classList.add('text-gray-500');
                        lastCalledId = null;
                        lastCalledTimestamp = null;
                    }

                    // Update Pending list
                    if (data.pending && data.pending.length > 0) {
                        emptyPendingLabel.classList.add('hidden');
                        
                        // Render pending token cards
                        let html = '';
                        data.pending.forEach((item, index) => {
                            html += `
                                <div class="flex items-center justify-between p-4 rounded-2xl bg-white/50 border border-gray-200/80 animate-pulse">
                                    <div class="flex items-center gap-3">
                                        <span class="w-6 h-6 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 text-xs flex items-center justify-center font-bold">
                                            ${index + 1}
                                        </span>
                                        <span class="text-xl font-bold text-gray-800 font-mono tracking-widest">${item.token_number}</span>
                                    </div>
                                    <span class="text-xs font-semibold text-gray-600 uppercase tracking-widest">Waiting</span>
                                </div>
                            `;
                        });
                        pendingList.innerHTML = html;
                    } else {
                        pendingList.innerHTML = '';
                        emptyPendingLabel.classList.remove('hidden');
                        pendingList.appendChild(emptyPendingLabel);
                    }
                })
                .catch(err => {
                    console.error("Display polling error:", err);
                });
        }

        // Initialize voice synthesis list (browser voice loading requirement)
        if (window.speechSynthesis) {
            window.speechSynthesis.getVoices();
            if (window.speechSynthesis.onvoiceschanged !== undefined) {
                window.speechSynthesis.onvoiceschanged = () => window.speechSynthesis.getVoices();
            }
        }

        // Poll immediately and set interval of 2 seconds
        pollQueueStatus();
        setInterval(pollQueueStatus, 2000);
    });
</script>
@endsection
