<?php

namespace App\Http\Controllers;

use App\Models\ContactAttempt;
use App\Models\Lead;
use Illuminate\Http\Request;

class ContactAttemptController extends Controller
{
    public function store(Request $request, Lead $lead)
    {
        if ($lead->user_id !== auth()->id()) {
            return response()->json(['message' => 'Nicht erlaubt'], 403);
        }

        $data = $request->validate([
            'kanal'          => 'required|in:email,telefon,linkedin,persoenlich,sonstiges',
            'notiz'          => 'nullable|string|max:500',
            'kontaktiert_am' => 'required|date|before_or_equal:today',
        ]);

        $attempt = $lead->contactAttempts()->create($data);

        return response()->json($attempt, 201);
    }

    public function destroy(Lead $lead, ContactAttempt $attempt)
    {
        if ($lead->user_id !== auth()->id() || $attempt->lead_id !== $lead->id) {
            return response()->json(['message' => 'Nicht erlaubt'], 403);
        }

        $attempt->delete();

        return response()->json(['message' => 'Gelöscht'], 200);
    }
}
