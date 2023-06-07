<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FirebaseNotification extends Controller
{
    public function sendNotification(Request $request)
    {
        $title = $request->input('title');
        $body = $request->input('body');
        $token = $request->input('token');

        $data = [
            'to' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer YOUR_SERVER_KEY', // Replace with your Firebase server key
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', $data);

        if ($response->successful()) {
            return response()->json(['message' => 'Notification sent successfully']);
        } else {
            return response()->json(['error' => 'Failed to send notification'], 500);
        }
    }
}




