<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Queue Tokens - QMS</title>
    
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <!-- Play CDN Fallback for styling without compiling -->
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @endif

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: #ffffff;
            color: #000000;
            padding: 20px;
        }

        /* Ticket Card Styles */
        .ticket-card {
            border: 2px dashed #000000;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
            background: #ffffff;
            width: 190px;
            height: 270px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            box-sizing: border-box;
            page-break-inside: avoid;
        }

        .ticket-title {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .ticket-subtitle {
            font-size: 8px;
            color: #666666;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .ticket-number {
            font-size: 40px;
            font-weight: 900;
            line-height: 1;
            font-family: monospace;
            letter-spacing: 0.05em;
        }

        .qr-code-box {
            width: 110px;
            height: 110px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-code-box img {
            width: 100% !important;
            height: 100% !important;
        }

        .ticket-footer {
            font-size: 8px;
            color: #555555;
            font-weight: 500;
        }

        /* Non-printing Control bar */
        .no-print-bar {
            background: #111827;
            color: #f3f4f6;
            padding: 16px 24px;
            margin-bottom: 30px;
            border-radius: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #1f2937;
        }

        /* Grid layout for Legal/Folio size */
        .tickets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
            gap: 15px;
            justify-items: center;
            max-width: 1000px;
            margin: 0 auto;
        }

        /* Printing Specific Styles */
        @media print {
            .no-print-bar {
                display: none !important;
            }
            body {
                padding: 0 !important;
                background: #ffffff;
            }
            .tickets-grid {
                display: grid;
                /* Maximize space utilization on paper */
                grid-template-columns: repeat(4, 190px);
                gap: 15px 10px;
                max-width: 100%;
                margin: 0;
            }
            /* Legal/Folio print optimizations */
            @page {
                size: legal portrait; /* fits standard Legal/Folio */
                margin: 0.5in;
            }
        }
    </style>
</head>
<body>

    <!-- Print control bar (hidden in print preview) -->
    <div class="no-print-bar">
        <div>
            <h1 class="text-lg font-bold">Print Preview</h1>
            <p class="text-xs text-gray-400">
                @if($type === 'single')
                    Reprinting 1 Token Ticket Card
                @else
                    Batch Printing {{ count($tokens) }} Token Ticket Cards
                @endif
            </p>
        </div>
        <div class="flex gap-3">
            <button onclick="window.close()" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-200 text-xs font-bold rounded-lg border border-gray-700 transition-colors">
                Cancel
            </button>
            <button onclick="window.print()" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-extrabold rounded-lg border border-blue-500 transition-colors shadow">
                Print Tickets
            </button>
        </div>
    </div>

    <!-- Printable grid of ticket cards -->
    <div class="tickets-grid">
        @foreach($tokens as $token)
        <div class="ticket-card">
            <div>
                <div class="ticket-title">COMELEC QMS</div>
                <div class="ticket-subtitle">Queue Token Card</div>
            </div>

            <!-- QR code slot (JS generates canvas inside) -->
            <div class="qr-code-box" data-token="{{ $token }}"></div>

            <div>
                <div class="ticket-number">{{ $token }}</div>
                <div class="ticket-footer mt-1">Scan QR at Scanner Station</div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Load QRCode Generator JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const qrContainers = document.querySelectorAll('.qr-code-box');
            qrContainers.forEach(container => {
                const token = container.getAttribute('data-token');
                
                // Clear any fallback text
                container.innerHTML = '';
                
                // Render QR code
                new QRCode(container, {
                    text: token,
                    width: 110,
                    height: 110,
                    colorDark : "#000000",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.M
                });
            });
            
            // Auto open browser print popup after QR codes are fully drawn (300ms)
            setTimeout(() => {
                window.print();
            }, 350);
        });
    </script>
</body>
</html>
