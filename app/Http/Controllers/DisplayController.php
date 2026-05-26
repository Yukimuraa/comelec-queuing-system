<?php

namespace App\Http\Controllers;

use App\Models\QueueToken;
use Illuminate\Http\Request;

class DisplayController extends Controller
{
    /**
     * Show the TV Live Display screen.
     */
    public function index()
    {
        return view('display.tv');
    }

    /**
     * Get the current queue status for the TV Live Display (AJAX polling).
     */
    public function status()
    {
        // Fetch the currently serving token
        $serving = QueueToken::serving()->first();

        // Fetch the next 4 pending tokens in queue (FIFO)
        $pending = QueueToken::pending()
            ->limit(4)
            ->get(['id', 'token_number']);

        // Count of served tokens today
        $servedCount = QueueToken::today()
            ->where('status', 'served')
            ->count();

        return response()->json([
            'serving' => $serving ? [
                'id' => $serving->id,
                'token_number' => $serving->token_number,
                'called_at' => $serving->called_at ? $serving->called_at->toIso8601String() : null,
                'called_at_timestamp' => $serving->called_at ? $serving->called_at->timestamp : null,
            ] : null,
            'pending' => $pending,
            'served_count' => $servedCount,
            'last_called_id' => $serving ? $serving->id : null,
            'called_at_timestamp' => $serving && $serving->called_at ? $serving->called_at->timestamp : null
        ]);
    }
}
