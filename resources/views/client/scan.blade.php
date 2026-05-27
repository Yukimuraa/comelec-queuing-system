@extends('layouts.app')

@section('styles')
<style>
    .scanner-target {
        position: relative;
    }
    .scanner-target::before {
        content: '';
        position: absolute;
        inset: -15px;
        border: 2px solid #3b82f6;
        border-radius: 1.5rem;
        opacity: 0.3;
        animation: pulseBorder 2s infinite ease-in-out;
        pointer-events: none;
    }
    @keyframes pulseBorder {
        0%, 100% { transform: scale(1); opacity: 0.3; }
        50% { transform: scale(1.05); opacity: 0.6; border-color: #6366f1; }
    }
    .scan-laser {
        position: absolute;
        height: 3px;
        width: 100%;
        background: linear-gradient(90deg, transparent, #3b82f6, #6366f1, #3b82f6, transparent);
        box-shadow: 0 0 15px #6366f1;
        animation: laserMove 2s infinite linear;
        pointer-events: none;
        z-index: 10;
    }
    @keyframes laserMove {
        0% { top: 10%; }
        50% { top: 90%; }
        100% { top: 10%; }
    }
</style>
@endsection

@section('content')
<div class="fade-in max-w-2xl mx-auto w-full flex flex-col items-center justify-center my-auto px-4 py-8">
    
    <!-- Title -->
    <div class="text-center mb-8">
        <h2 class="text-3xl font-extrabold text-gray-900">Client Scan Station</h2>
        <p class="text-gray-600 mt-2 text-sm">Scan your 3-digit physical token code to register in the queue</p>
    </div>

    <!-- Main Scan Card -->
    <div class="w-full glass-panel rounded-3xl p-8 flex flex-col items-center glow-blue scanner-target">
        <div class="scan-laser" id="laser"></div>

        <!-- Scanning Icon Visual -->
        <div class="w-24 h-24 rounded-2xl bg-blue-500/10 flex items-center justify-center text-blue-400 mb-6 relative border border-blue-500/20">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0a8 8 0 11-16 0 8 8 0 0116 0z" />
            </svg>
        </div>

        <!-- Scanner Form -->
        <form id="scanForm" class="w-full flex flex-col items-center">
            @csrf
            
            <!-- Focus Message -->
            <div id="focusBanner" class="mb-4 text-xs font-semibold px-3 py-1.5 rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20 flex items-center gap-1.5 animate-pulse">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                Scanner Input Focused
            </div>

            <!-- Stylized Barcode Input Box -->
            <div class="w-full max-w-sm relative">
                <input 
                    type="text" 
                    id="tokenInput" 
                    name="token_number" 
                    placeholder="Enter or scan token..." 
                    autocomplete="off"
                    maxlength="5"
                    class="w-full px-6 py-4 bg-gray-50/80 rounded-2xl text-center text-3xl font-bold tracking-widest text-gray-900 border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition-all"
                >
            </div>
            
            <p class="text-xs text-gray-500 mt-3">
                Click anywhere on the screen to refocus the scanner input.
            </p>
        </form>

        <!-- Divider -->
        <div class="flex items-center my-6 w-full max-w-sm">
            <hr class="flex-1 border-gray-200">
            <span class="px-3 text-xs text-gray-500 font-bold uppercase tracking-wider">or</span>
            <hr class="flex-1 border-gray-200">
        </div>

        <!-- Camera Button -->
        <button 
            type="button" 
            id="toggleCameraBtn" 
            class="px-6 py-3 bg-white hover:bg-gray-700 text-gray-800 text-sm font-semibold rounded-xl flex items-center gap-2 border border-gray-300 transition-colors shadow-lg"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Scan with System Camera
        </button>

        <!-- Webcam Container (Hidden by default) -->
        <div id="cameraContainer" class="w-full max-w-sm mt-6 rounded-2xl overflow-hidden border border-gray-200 hidden bg-gray-50">
            <div id="cameraPreview" class="w-full aspect-video"></div>
            <div class="p-3 bg-white flex justify-between items-center text-xs text-gray-600">
                <span>Align QR inside frame</span>
                <button type="button" id="closeCameraBtn" class="text-rose-400 hover:underline font-semibold">Close Camera</button>
            </div>
        </div>

    </div>
</div>

<!-- Dynamic Feedback Overlay -->
<div id="feedbackOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-white/80 backdrop-blur-lg hidden fade-in transition-all">
    <div class="w-full max-w-md p-8 rounded-3xl text-center glass-panel shadow-2xl flex flex-col items-center" id="feedbackCard">
        
        <!-- Status Icon -->
        <div class="w-24 h-24 rounded-full flex items-center justify-center mb-6 border-4" id="feedbackIconContainer">
            <!-- Rendered by JS -->
        </div>

        <!-- Content -->
        <h3 class="text-3xl font-black mb-2 text-gray-900" id="feedbackTitle">Status</h3>
        <p class="text-gray-700 text-lg mb-6 leading-relaxed" id="feedbackMessage">Message details here...</p>

        <!-- Dynamic details (Position) -->
        <div id="positionDetails" class="hidden">
            <span class="text-xs uppercase text-gray-500 font-bold tracking-wider">Your Position in Queue</span>
            <div class="text-6xl font-black text-emerald-400 mt-1 mb-2" id="queuePosition">#5</div>
            <span class="text-xs text-gray-600">Please wait for your number to be called on the TV screen</span>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Load Html5Qrcode only if they click the Camera button -->
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode/html5-qrcode.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tokenInput = document.getElementById('tokenInput');
        const focusBanner = document.getElementById('focusBanner');
        const scanForm = document.getElementById('scanForm');
        const toggleCameraBtn = document.getElementById('toggleCameraBtn');
        const cameraContainer = document.getElementById('cameraContainer');
        const closeCameraBtn = document.getElementById('closeCameraBtn');
        const laser = document.getElementById('laser');

        const feedbackOverlay = document.getElementById('feedbackOverlay');
        const feedbackCard = document.getElementById('feedbackCard');
        const feedbackIconContainer = document.getElementById('feedbackIconContainer');
        const feedbackTitle = document.getElementById('feedbackTitle');
        const feedbackMessage = document.getElementById('feedbackMessage');
        const positionDetails = document.getElementById('positionDetails');
        const queuePosition = document.getElementById('queuePosition');

        let html5QrScanner = null;

        // Auto-focus input on page load and lock it
        tokenInput.focus();

        // Keep input focused unless camera is active
        document.addEventListener('click', (e) => {
            // Only refocus if camera is NOT active
            if (cameraContainer.classList.contains('hidden') && e.target !== toggleCameraBtn) {
                tokenInput.focus();
            }
        });

        // Track focus styling
        tokenInput.addEventListener('focus', () => {
            focusBanner.classList.remove('hidden');
            focusBanner.classList.add('flex');
        });
        tokenInput.addEventListener('blur', () => {
            // Keep focusing back
            if (cameraContainer.classList.contains('hidden')) {
                setTimeout(() => tokenInput.focus(), 10);
            } else {
                focusBanner.classList.add('hidden');
                focusBanner.classList.remove('flex');
            }
        });

        // Trigger submit when input matches token format
        tokenInput.addEventListener('input', (e) => {
            // Filter non-numbers and P-
            let val = e.target.value.toUpperCase().replace(/[^0-9P\-]/g, '');
            e.target.value = val;
            
            if (val.match(/^(?:P-)?\d{3}$/)) {
                submitScan(val);
            }
        });

        // Intercept form submit
        scanForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const val = tokenInput.value;
            if (val.length > 0) {
                submitScan(val);
            }
        });

        // API Submission function
        function submitScan(tokenNumber) {
            tokenInput.disabled = true;
            laser.classList.add('hidden');

            fetch("{{ route('client.scan-submit') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ token_number: tokenNumber })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showFeedback(true, data.token_number, data.message, data.position);
                } else {
                    showFeedback(false, null, data.message);
                }
            })
            .catch(err => {
                showFeedback(false, null, "Server communication error. Please try again.");
            })
            .finally(() => {
                // Clear input
                tokenInput.value = '';
                tokenInput.disabled = false;
                laser.classList.remove('hidden');
                // Ensure focus is restored
                if (cameraContainer.classList.contains('hidden')) {
                    tokenInput.focus();
                }
            });
        }

        // Web Audio Synthesizer sound generator (Plays chime/buzz)
        function playAlertSound(type) {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                if (type === 'success') {
                    // Success dual-tone electronic bell chime
                    const osc1 = ctx.createOscillator();
                    const osc2 = ctx.createOscillator();
                    const gainNode = ctx.createGain();

                    osc1.type = 'sine';
                    osc1.frequency.setValueAtTime(523.25, ctx.currentTime); // C5
                    osc1.frequency.exponentialRampToValueAtTime(659.25, ctx.currentTime + 0.12); // E5

                    osc2.type = 'triangle';
                    osc2.frequency.setValueAtTime(659.25, ctx.currentTime); // E5
                    osc2.frequency.exponentialRampToValueAtTime(783.99, ctx.currentTime + 0.12); // G5

                    gainNode.gain.setValueAtTime(0.12, ctx.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);

                    osc1.connect(gainNode);
                    osc2.connect(gainNode);
                    gainNode.connect(ctx.destination);

                    osc1.start();
                    osc2.start();
                    osc1.stop(ctx.currentTime + 0.35);
                    osc2.stop(ctx.currentTime + 0.35);
                } else {
                    // Fail low frequency alert buzz
                    const osc = ctx.createOscillator();
                    const gainNode = ctx.createGain();

                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(150, ctx.currentTime);
                    osc.frequency.linearRampToValueAtTime(110, ctx.currentTime + 0.25);

                    gainNode.gain.setValueAtTime(0.15, ctx.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);

                    osc.connect(gainNode);
                    gainNode.connect(ctx.destination);

                    osc.start();
                    osc.stop(ctx.currentTime + 0.3);
                }
            } catch (e) {
                console.error("Audio Context not supported or allowed by browser user interaction policy.", e);
            }
        }

        // Overlay Feedback display logic
        function showFeedback(isSuccess, tokenNumber, message, position = null) {
            feedbackOverlay.classList.remove('hidden');
            feedbackOverlay.classList.add('flex');

            if (isSuccess) {
                playAlertSound('success');
                // Success styling
                feedbackCard.className = "w-full max-w-md p-8 rounded-3xl text-center glass-panel shadow-2xl flex flex-col items-center border border-emerald-500/20 glow-emerald";
                feedbackIconContainer.className = "w-24 h-24 rounded-full flex items-center justify-center mb-6 border-4 border-emerald-500 bg-emerald-500/10 text-emerald-400";
                feedbackIconContainer.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>`;
                feedbackTitle.innerText = `Token Registered`;
                feedbackMessage.innerText = message;
                
                if (position) {
                    positionDetails.classList.remove('hidden');
                    queuePosition.innerText = `#${position}`;
                } else {
                    positionDetails.classList.add('hidden');
                }
            } else {
                playAlertSound('error');
                // Failure styling
                feedbackCard.className = "w-full max-w-md p-8 rounded-3xl text-center glass-panel shadow-2xl flex flex-col items-center border border-rose-500/20";
                feedbackIconContainer.className = "w-24 h-24 rounded-full flex items-center justify-center mb-6 border-4 border-rose-500 bg-rose-500/10 text-rose-400";
                feedbackIconContainer.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>`;
                feedbackTitle.innerText = `Scan Failed`;
                feedbackMessage.innerText = message;
                positionDetails.classList.add('hidden');
            }

            // Close after 3 seconds
            setTimeout(() => {
                feedbackOverlay.classList.add('hidden');
                feedbackOverlay.classList.remove('flex');
                if (cameraContainer.classList.contains('hidden')) {
                    tokenInput.focus();
                }
            }, 3000);
        }

        // --- Camera Scanning Logic using html5-qrcode ---
        toggleCameraBtn.addEventListener('click', () => {
            if (cameraContainer.classList.contains('hidden')) {
                // Open camera
                cameraContainer.classList.remove('hidden');
                toggleCameraBtn.classList.add('bg-blue-600', 'text-gray-900');
                tokenInput.blur();

                // Initialize scanner
                html5QrScanner = new Html5Qrcode("cameraPreview");
                const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                html5QrScanner.start(
                    { facingMode: "user" }, // Use front/built-in camera
                    config,
                    (decodedText) => {
                        // Scan success
                        stopCamera();
                        submitScan(decodedText);
                    },
                    (errorMessage) => {
                        // Normal search errors, can be ignored
                    }
                ).catch(err => {
                    console.error("Camera access failed", err);
                    showFeedback(false, null, "Failed to start camera. Please verify permissions.");
                    stopCamera();
                });
            } else {
                stopCamera();
            }
        });

        closeCameraBtn.addEventListener('click', stopCamera);

        function stopCamera() {
            if (html5QrScanner) {
                html5QrScanner.stop().then(() => {
                    html5QrScanner = null;
                }).catch(err => console.error(err));
            }
            cameraContainer.classList.add('hidden');
            toggleCameraBtn.classList.remove('bg-blue-600', 'text-gray-900');
            // Restore autofocus on physical input
            setTimeout(() => tokenInput.focus(), 50);
        }
    });
</script>
@endsection
