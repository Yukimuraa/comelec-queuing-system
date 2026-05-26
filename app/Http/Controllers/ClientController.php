<?php

namespace App\Http\Controllers;

use App\Models\QueueToken;
use App\Models\Setting;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Show the Client Scan Station.
     */
    public function index()
    {
        return view('client.scan');
    }

    /**
     * Process scanned token input (API/AJAX).
     */
    public function scan(Request $request)
    {
        $rawNumber = $request->input('token_number');
        
        // Sanitize: keep only numeric digits
        $cleanNumber = preg_replace('/[^0-9]/', '', $rawNumber);

        // 1. Format Validation: must be numeric and represent 1-999
        if (empty($cleanNumber)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid scan. Please scan a valid 3-digit QR token.'
            ], 422);
        }

        $intNumber = (int) $cleanNumber;
        if ($intNumber < 1 || $intNumber > 999) {
            return response()->json([
                'success' => false,
                'message' => 'Token number out of range (must be 001 - 999).'
            ], 422);
        }

        // Pad to exactly 3 digits (e.g., 42 -> 042)
        $tokenNumber = str_pad($intNumber, 3, '0', STR_PAD_LEFT);

        // 2. Capacity Check: check if daily limit is reached
        $dailyLimit = (int) Setting::get('daily_limit', 100);
        $totalRegisteredToday = QueueToken::today()->count();

        if ($totalRegisteredToday >= $dailyLimit) {
            return response()->json([
                'success' => false,
                'message' => "Daily capacity limit of {$dailyLimit} reached. No more tokens can be accommodated today."
            ], 422);
        }

        // 3. Duplication Check: check if token was already scanned today
        $alreadyScanned = QueueToken::today()->where('token_number', $tokenNumber)->exists();
        if ($alreadyScanned) {
            return response()->json([
                'success' => false,
                'message' => "Token {$tokenNumber} has already been scanned today."
            ], 422);
        }

        // 4. Registration: insert token into database
        $token = QueueToken::create([
            'token_number' => $tokenNumber,
            'status' => 'pending'
        ]);

        // Calculate queue position (how many pending tokens are ahead of this one, including this one)
        $position = QueueToken::today()
            ->where('status', 'pending')
            ->where('created_at', '<=', $token->created_at)
            ->count();

        return response()->json([
            'success' => true,
            'token_number' => $tokenNumber,
            'position' => $position,
            'message' => "Token {$tokenNumber} successfully registered."
        ]);
    }
}
