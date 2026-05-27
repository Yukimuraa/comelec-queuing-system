@extends('layouts.app')

@section('styles')
<style>
    @media print {
        header, nav, footer, .no-print, button, input, select {
            display: none !important;
        }
        body {
            background: white !important;
            color: black !important;
            font-size: 12pt;
        }
        main {
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .print-container {
            border: none !important;
            box-shadow: none !important;
            background: transparent !important;
            padding: 0 !important;
        }
        .print-header {
            display: block !important;
            margin-bottom: 2rem;
            text-align: center;
        }
    }
    .print-header {
        display: none;
    }
</style>
@endsection

@section('content')
<div class="fade-in flex flex-col gap-6 w-full print-container">

    {{-- Print Header (Only visible on paper printout) --}}
    <div class="print-header">
        <h1 class="text-2xl font-black uppercase text-gray-900">COMELEC Queue Management System</h1>
        <p class="text-sm font-semibold uppercase text-gray-500 tracking-wider mt-1">Daily Queue Operations Report</p>
        <div class="border-b border-gray-400 my-4"></div>
        <p class="text-xs text-gray-600 font-bold">
            Printed on: <span class="device-time" data-timestamp="{{ now()->toIso8601String() }}" data-seconds="true"></span>
        </p>
    </div>

    {{-- Top Action Header (hidden on print) --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 pb-5 no-print">
        <div class="flex flex-col">
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Daily Operations Report</h1>
            <p class="text-sm font-semibold text-gray-500 mt-1">Real-time statistics, metrics and log for today's queue session.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold rounded-xl border border-indigo-600 shadow-md hover:shadow-indigo-500/20 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print Report
            </button>
            <button onclick="exportReportToCSV()" class="px-5 py-2.5 bg-gray-800 hover:bg-gray-700 text-gray-200 text-sm font-bold rounded-xl border border-gray-700 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export CSV
            </button>
        </div>
    </div>

    {{-- Reset Alert (hidden on print) --}}
    <div class="p-4 bg-amber-500/10 border border-amber-500/20 text-amber-500 rounded-2xl flex items-center gap-3 no-print">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <span class="text-xs font-semibold">Note: The system archives queue history daily when starting a new day. A reset clears this database table. Ensure you print or export before clearing.</span>
    </div>

    {{-- METRIC STAT CARDS --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
        
        {{-- Total Issued --}}
        <div class="glass-panel rounded-3xl p-6 border border-gray-200 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Total Issued</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-500">Tokens</span>
            </div>
            <div class="flex items-baseline gap-2 mt-4">
                <span class="text-4xl font-extrabold text-gray-900">{{ $totalCount }}</span>
                <span class="text-xs text-gray-500 font-semibold">tickets</span>
            </div>
            <div class="flex justify-between items-center text-[10px] text-gray-500 font-semibold border-t border-gray-150 pt-3 mt-4">
                <span>Regular: <strong>{{ $regularCount }}</strong></span>
                <span>Priority: <strong>{{ $priorityCount }}</strong></span>
            </div>
        </div>

        {{-- Total Served --}}
        <div class="glass-panel rounded-3xl p-6 border border-gray-200 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Served Rate</span>
                @php
                    $servedRate = $totalCount > 0 ? round(($servedCount / $totalCount) * 100) : 0;
                    $badgeColor = $servedRate >= 80 ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-500' : 'bg-amber-500/10 border-amber-500/20 text-amber-500';
                @endphp
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $badgeColor }}">{{ $servedRate }}%</span>
            </div>
            <div class="flex items-baseline gap-2 mt-4">
                <span class="text-4xl font-extrabold text-gray-900">{{ $servedCount }}</span>
                <span class="text-xs text-gray-500 font-semibold">completed</span>
            </div>
            <div class="flex justify-between items-center text-[10px] text-gray-500 font-semibold border-t border-gray-150 pt-3 mt-4">
                <span>Skipped: <strong>{{ $skippedCount }}</strong></span>
                <span>In Queue: <strong>{{ $pendingCount }}</strong></span>
            </div>
        </div>

        {{-- Average Wait Time --}}
        <div class="glass-panel rounded-3xl p-6 border border-gray-200 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Avg Wait Time</span>
                <div class="w-6 h-6 rounded-lg bg-indigo-500/10 text-indigo-500 flex items-center justify-center border border-indigo-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3" />
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2 mt-4">
                <span class="text-4xl font-extrabold text-gray-900">{{ $avgWaitStr }}</span>
            </div>
            <span class="text-[10px] text-gray-500 font-semibold border-t border-gray-150 pt-3 mt-4">Created to Called duration</span>
        </div>

        {{-- Average Service Time --}}
        <div class="glass-panel rounded-3xl p-6 border border-gray-200 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Avg Service Time</span>
                <div class="w-6 h-6 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center border border-emerald-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2 mt-4">
                <span class="text-4xl font-extrabold text-gray-900">{{ $avgServiceStr }}</span>
            </div>
            <span class="text-[10px] text-gray-500 font-semibold border-t border-gray-150 pt-3 mt-4">Called to Served duration</span>
        </div>

    </div>

    {{-- LOWER GRID: PEAK HOURS & LIST --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        {{-- Peak Hours Distribution --}}
        <div class="lg:col-span-4 glass-panel rounded-3xl p-6 border border-gray-200 flex flex-col">
            <h3 class="text-sm font-bold text-gray-600 uppercase tracking-widest mb-5 border-b border-gray-250 pb-3 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Hourly Load Peak
            </h3>
            
            <div class="flex flex-col gap-3">
                @php $maxLoad = max(array_values($hourlyDistribution)) ?: 1; @endphp
                @foreach($hourlyDistribution as $hour => $count)
                <div class="flex items-center gap-3">
                    <span class="w-12 text-xs font-bold text-gray-500">{{ $hour }}</span>
                    <div class="flex-1 bg-gray-100 border border-gray-250 h-5 rounded-full overflow-hidden">
                        @php $percent = ($count / $maxLoad) * 100; @endphp
                        <div class="bg-indigo-500 h-full rounded-full transition-all duration-500 flex items-center justify-end px-2" style="width: {{ $percent }}%">
                            @if($count > 0)
                            <span class="text-[9px] font-extrabold text-white">{{ $count }}</span>
                            @endif
                        </div>
                    </div>
                    @if($count == 0)
                    <span class="w-4 text-center text-[10px] font-bold text-gray-300">0</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Token Details Table --}}
        <div class="lg:col-span-8 glass-panel rounded-3xl p-6 border border-gray-200 flex flex-col">
            
            {{-- Search & Filter Bar (hidden on print) --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5 border-b border-gray-250 pb-4 no-print">
                <h3 class="text-sm font-bold text-gray-600 uppercase tracking-widest flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    Token Transaction Logs
                </h3>
                <div class="flex items-center gap-2">
                    <input type="text" id="reportSearch" placeholder="Search token..." onkeyup="filterReportTable()"
                           class="px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-700 focus:outline-none focus:border-indigo-500 font-semibold w-40">
                    <select id="reportFilter" onchange="filterReportTable()"
                            class="px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-700 focus:outline-none focus:border-indigo-500 font-semibold">
                        <option value="ALL">All Statuses</option>
                        <option value="SERVED">Served</option>
                        <option value="SKIPPED">Skipped</option>
                        <option value="SERVING">Serving</option>
                        <option value="PENDING">Pending</option>
                    </select>
                </div>
            </div>

            {{-- Table Container --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="tokensReportTable">
                    <thead>
                        <tr class="border-b border-gray-300 text-[10px] uppercase font-bold text-gray-500 tracking-wider">
                            <th class="py-3 px-2">Token #</th>
                            <th class="py-3 px-2">Type</th>
                            <th class="py-3 px-2">Status</th>
                            <th class="py-3 px-2">Created At</th>
                            <th class="py-3 px-2">Called At</th>
                            <th class="py-3 px-2">Served At</th>
                            <th class="py-3 px-2 text-right">Wait Time</th>
                            <th class="py-3 px-2 text-right">Service Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-150">
                        @forelse($tokens as $token)
                        @php
                            $isPrio = str_starts_with($token->token_number, 'P');
                            $wait = $token->called_at ? $token->called_at->diffInSeconds($token->created_at) : null;
                            $service = $token->served_at ? $token->served_at->diffInSeconds($token->called_at) : null;
                        @endphp
                        <tr class="text-xs font-semibold text-gray-700 hover:bg-gray-50/50 transition-colors report-row" data-status="{{ strtoupper($token->status) }}">
                            <td class="py-3 px-2 font-mono font-bold text-gray-900 text-sm tracking-wider">{{ $token->token_number }}</td>
                            <td class="py-3 px-2">
                                @if($isPrio)
                                <span class="px-2 py-0.5 bg-amber-500/10 text-amber-500 border border-amber-500/25 rounded-md text-[10px]">Priority</span>
                                @else
                                <span class="px-2 py-0.5 bg-indigo-500/10 text-indigo-500 border border-indigo-500/25 rounded-md text-[10px]">Regular</span>
                                @endif
                            </td>
                            <td class="py-3 px-2">
                                @if($token->status === 'served')
                                <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-500 border border-emerald-500/25 rounded-md text-[10px] uppercase font-bold">Served</span>
                                @elseif($token->status === 'skipped')
                                <span class="px-2 py-0.5 bg-rose-500/10 text-rose-500 border border-rose-500/25 rounded-md text-[10px] uppercase font-bold">Skipped</span>
                                @elseif($token->status === 'serving')
                                <span class="px-2 py-0.5 bg-indigo-500/10 text-indigo-500 border border-indigo-500/25 rounded-md text-[10px] uppercase font-bold animate-pulse">Serving</span>
                                @else
                                <span class="px-2 py-0.5 bg-gray-500/10 text-gray-500 border border-gray-500/25 rounded-md text-[10px] uppercase font-bold">Pending</span>
                                @endif
                            </td>
                            <td class="py-3 px-2 font-mono text-gray-500 device-time" data-timestamp="{{ $token->created_at->toIso8601String() }}"></td>
                            <td class="py-3 px-2 font-mono text-gray-500 device-time" data-timestamp="{{ $token->called_at ? $token->called_at->toIso8601String() : '' }}">---</td>
                            <td class="py-3 px-2 font-mono text-gray-500 device-time" data-timestamp="{{ $token->served_at ? $token->served_at->toIso8601String() : '' }}">---</td>
                            <td class="py-3 px-2 text-right font-mono text-gray-600">{{ $wait !== null ? $formatDuration($wait) : '---' }}</td>
                            <td class="py-3 px-2 text-right font-mono text-gray-600">{{ $service !== null ? $formatDuration($service) : '---' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-10 text-gray-600 font-semibold text-sm">No transaction tokens found for today.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    </div>

</div>
@endsection

@section('scripts')
<script>
    function filterReportTable() {
        const searchInput = document.getElementById('reportSearch').value.toUpperCase();
        const filterVal = document.getElementById('reportFilter').value;
        const rows = document.querySelectorAll('.report-row');

        rows.forEach(row => {
            const tokenCell = row.cells[0].textContent.toUpperCase();
            const statusAttr = row.getAttribute('data-status');

            const matchesSearch = tokenCell.includes(searchInput);
            const matchesFilter = (filterVal === 'ALL' || statusAttr === filterVal);

            if (matchesSearch && matchesFilter) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function exportReportToCSV() {
        const rows = document.querySelectorAll('#tokensReportTable tr');
        let csvContent = "data:text/csv;charset=utf-8,";
        
        // Add Header
        csvContent += "Token Number,Type,Status,Created At,Called At,Served At,Wait Time,Service Time\r\n";

        // Add Body rows
        rows.forEach((row, index) => {
            if (index === 0) return; // skip table header row
            if (row.style.display === 'none') return; // skip hidden rows

            const cells = row.querySelectorAll('td');
            if (cells.length < 8) return;

            const token = cells[0].textContent.trim();
            const type = cells[1].textContent.trim();
            const status = cells[2].textContent.trim();
            const created = cells[3].textContent.trim();
            const called = cells[4].textContent.trim();
            const served = cells[5].textContent.trim();
            const wait = cells[6].textContent.trim();
            const service = cells[7].textContent.trim();

            csvContent += `"${token}","${type}","${status}","${created}","${called}","${served}","${wait}","${service}"\r\n`;
        });

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        
        const now = new Date();
        const dateString = now.toISOString().split('T')[0];
        
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", `COMELEC_QMS_Report_${dateString}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Trigger device time formatting helper after custom rendering
        if (window.formatDeviceTimes) {
            window.formatDeviceTimes();
        }
    });
</script>
@endsection
