<?php

namespace App\Http\Controllers;

use App\Models\QueueToken;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display the Admin Dashboard.
     */
    public function index()
    {
        $serving = QueueToken::serving()->first();
        $pending = QueueToken::pending()->get();
        $served = QueueToken::served()->get();
        $skipped = QueueToken::skipped()->get();

        $dailyLimit = (int) Setting::get('daily_limit', 100);
        $totalToday = QueueToken::today()->count();
        $servedCount = QueueToken::today()->where('status', 'served')->count();

        return view('admin.dashboard', compact(
            'serving',
            'pending',
            'served',
            'skipped',
            'dailyLimit',
            'totalToday',
            'servedCount'
        ));
    }

    /**
     * Call the next token in the queue (FIFO).
     */
    public function callNext(Request $request)
    {
        $type = $request->input('type', 'next');

        // Automatically mark currently serving token as served to free the spot
        $currentServing = QueueToken::serving()->first();
        if ($currentServing) {
            $currentServing->update([
                'status' => 'served',
                'served_at' => Carbon::now()
            ]);
        }

        // Fetch the oldest pending token (FIFO) based on type
        if ($type === 'priority') {
            $nextToken = QueueToken::pending()->where('token_number', 'LIKE', 'P-%')->first();
            if (!$nextToken) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No pending priority tokens in the queue.'
                    ], 422);
                }
                return redirect()->route('admin.dashboard')
                    ->with('warning', 'No pending priority tokens in the queue.');
            }
        } elseif ($type === 'regular') {
            $nextToken = QueueToken::pending()->where('token_number', 'NOT LIKE', 'P-%')->first();
            if (!$nextToken) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No pending regular tokens in the queue.'
                    ], 422);
                }
                return redirect()->route('admin.dashboard')
                    ->with('warning', 'No pending regular tokens in the queue.');
            }
        } else {
            // General FIFO: Get the oldest pending token overall
            $nextToken = QueueToken::pending()->first();
            if (!$nextToken) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No pending tokens in the queue.'
                    ], 422);
                }
                return redirect()->route('admin.dashboard')
                    ->with('warning', 'No pending tokens in the queue.');
            }
        }

        // Mark next token as serving
        $nextToken->update([
            'status' => 'serving',
            'called_at' => Carbon::now()
        ]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Token {$nextToken->token_number} called.",
                'token' => $nextToken
            ]);
        }

        return redirect()->route('admin.dashboard')
            ->with('success', "Token {$nextToken->token_number} called.");
    }

    /**
     * Mark a token as served.
     */
    public function serve(QueueToken $token)
    {
        $token->update([
            'status' => 'served',
            'served_at' => Carbon::now()
        ]);

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Token {$token->token_number} marked as served."
            ]);
        }

        return redirect()->route('admin.dashboard')
            ->with('success', "Token {$token->token_number} marked as served.");
    }

    /**
     * Mark a token as skipped.
     */
    public function skip(QueueToken $token)
    {
        $token->update([
            'status' => 'skipped'
        ]);

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Token {$token->token_number} marked as skipped."
            ]);
        }

        return redirect()->route('admin.dashboard')
            ->with('info', "Token {$token->token_number} marked as skipped.");
    }

    /**
     * Recall a token (updates called_at to trigger TV chime/voice announcement again).
     */
    public function recall(QueueToken $token)
    {
        $token->update([
            'called_at' => Carbon::now()
        ]);

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Token {$token->token_number} recalled."
            ]);
        }

        return redirect()->route('admin.dashboard')
            ->with('success', "Token {$token->token_number} recalled.");
    }

    /**
     * Update the daily client capacity limit.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'daily_limit' => 'required|integer|min:1|max:9999'
        ]);

        Setting::set('daily_limit', $request->input('daily_limit'));

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Daily capacity limit updated successfully.',
                'daily_limit' => Setting::get('daily_limit')
            ]);
        }

        return redirect()->route('admin.dashboard')
            ->with('success', 'Daily capacity limit updated successfully.');
    }

    /**
     * Reset today's queue (Clear all token history).
     */
    public function resetQueue()
    {
        // Delete all queue tokens to clear/archive today's session
        QueueToken::truncate();

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Queue session reset successfully. All counters cleared.'
            ]);
        }

        return redirect()->route('admin.dashboard')
            ->with('success', 'Queue session reset successfully. All counters cleared.');
    }

    /**
     * Render the print templates.
     */
    public function printTemplate(Request $request)
    {
        $type = $request->query('type', 'batch');
        $prefix = $request->query('prefix', '');
        $tokens = [];

        if ($type === 'single') {
            $number = $request->query('number', '001');
            // Pad to 3 digits
            $number = str_pad(filter_var($number, FILTER_SANITIZE_NUMBER_INT), 3, '0', STR_PAD_LEFT);
            $tokens[] = $prefix . $number;
        } else {
            // Batch print range
            $start = (int) $request->query('start', 1);
            $end = (int) $request->query('end', 50);

            // Bounds checks
            if ($start < 1) $start = 1;
            if ($end > 999) $end = 999;
            if ($start > $end) {
                $temp = $start;
                $start = $end;
                $end = $temp;
            }

            for ($i = $start; $i <= $end; $i++) {
                $tokens[] = $prefix . str_pad($i, 3, '0', STR_PAD_LEFT);
            }
        }

        return view('admin.print', compact('tokens', 'type'));
    }

    /**
     * Get the current queue status for the Admin Dashboard (AJAX polling).
     */
    public function status()
    {
        $serving = QueueToken::serving()->first();
        $pending = QueueToken::pending()->get();
        $served = QueueToken::served()->get();

        $dailyLimit = (int) Setting::get('daily_limit', 100);
        $totalToday = QueueToken::today()->count();
        $servedCount = QueueToken::today()->where('status', 'served')->count();

        // Pending queue with position
        $pendingData = $pending->map(function ($item, $index) {
            return [
                'id' => $item->id,
                'token_number' => $item->token_number,
                'position' => $index + 1,
                'created_at_iso' => $item->created_at->toIso8601String(),
            ];
        });

        // History: only served tokens
        $historyData = $served->sortByDesc('served_at')->take(10)->map(function ($item) {
            return [
                'id' => $item->id,
                'token_number' => $item->token_number,
                'status' => $item->status,
                'served_at_iso' => $item->served_at ? $item->served_at->toIso8601String() : $item->updated_at->toIso8601String(),
            ];
        });

        return response()->json([
            'serving' => $serving ? [
                'id' => $serving->id,
                'token_number' => $serving->token_number,
                'called_at_iso' => $serving->called_at ? $serving->called_at->toIso8601String() : null,
            ] : null,
            'pending' => $pendingData,
            'history' => $historyData,
            'dailyLimit' => $dailyLimit,
            'totalToday' => $totalToday,
            'servedCount' => $servedCount,
            'capacityPercent' => $dailyLimit > 0 ? min(100, ($totalToday / $dailyLimit) * 100) : 0,
        ]);
    }

    /**
     * Display the Daily Report page.
     */
    public function report()
    {
        $tokens = QueueToken::today()->orderBy('created_at', 'asc')->get();

        $totalCount = $tokens->count();
        $servedCount = $tokens->where('status', 'served')->count();
        $skippedCount = $tokens->where('status', 'skipped')->count();
        $pendingCount = $tokens->where('status', 'pending')->count();
        $servingCount = $tokens->where('status', 'serving')->count();

        $priorityCount = $tokens->filter(function($t) { return str_starts_with($t->token_number, 'P'); })->count();
        $regularCount = $totalCount - $priorityCount;

        // Calculate average Wait Time (created_at to called_at) in seconds
        $waitTimes = [];
        $serviceTimes = [];

        foreach ($tokens as $token) {
            if ($token->called_at && $token->created_at) {
                $waitTimes[] = $token->called_at->diffInSeconds($token->created_at);
            }
            if ($token->served_at && $token->called_at) {
                $serviceTimes[] = $token->served_at->diffInSeconds($token->called_at);
            }
        }

        $avgWaitTime = count($waitTimes) > 0 ? round(array_sum($waitTimes) / count($waitTimes)) : 0;
        $avgServiceTime = count($serviceTimes) > 0 ? round(array_sum($serviceTimes) / count($serviceTimes)) : 0;

        // Format duration to human readable (e.g. 2m 14s)
        $formatDuration = function($seconds) {
            if ($seconds <= 0) return '0s';
            $m = floor($seconds / 60);
            $s = $seconds % 60;
            return $m > 0 ? "{$m}m {$s}s" : "{$s}s";
        };

        $avgWaitStr = $formatDuration($avgWaitTime);
        $avgServiceStr = $formatDuration($avgServiceTime);

        // Group tokens by registration hour to show peak hours (8 AM - 6 PM)
        $hourlyDistribution = [];
        for ($i = 8; $i <= 18; $i++) {
            $hourStr = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
            $hourlyDistribution[$hourStr] = 0;
        }

        foreach ($tokens as $token) {
            $hour = $token->created_at->format('H');
            $hourStr = $hour . ':00';
            if (isset($hourlyDistribution[$hourStr])) {
                $hourlyDistribution[$hourStr]++;
            } else {
                $hourlyDistribution[$hourStr] = 1;
            }
        }

        return view('admin.report', compact(
            'tokens',
            'totalCount',
            'servedCount',
            'skippedCount',
            'pendingCount',
            'servingCount',
            'priorityCount',
            'regularCount',
            'avgWaitStr',
            'avgServiceStr',
            'hourlyDistribution',
            'formatDuration'
        ));
    }
}
