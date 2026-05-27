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
        $type = $request->input('type', 'regular');

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
                return redirect()->route('admin.dashboard')
                    ->with('warning', 'No pending priority tokens in the queue.');
            }
        } else {
            $nextToken = QueueToken::pending()->where('token_number', 'NOT LIKE', 'P-%')->first();
            if (!$nextToken) {
                return redirect()->route('admin.dashboard')
                    ->with('warning', 'No pending regular tokens in the queue.');
            }
        }

        // Mark next token as serving
        $nextToken->update([
            'status' => 'serving',
            'called_at' => Carbon::now()
        ]);

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
}
