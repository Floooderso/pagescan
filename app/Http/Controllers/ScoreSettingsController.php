<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class ScoreSettingsController extends Controller
{
    public function show()
    {
        $weights  = auth()->user()->getScoreWeights();
        $profiles = User::PROFILES;
        return view('settings.score', compact('weights', 'profiles'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'http_ok'   => 'required|integer|min:0',
            'ssl'       => 'required|integer|min:0',
            'mobile'    => 'required|integer|min:0',
            'contact'   => 'required|integer|min:0',
            'impressum' => 'required|integer|min:0',
            'favicon'   => 'required|integer|min:0',
        ]);

        $sum = array_sum($data);
        if ($sum !== 100) {
            return back()->withErrors(['sum' => "Die Summe aller Gewichte muss 100 ergeben (aktuell: {$sum})."]);
        }

        auth()->user()->update(['score_weights' => $data]);

        return back()->with('success', 'Score-Gewichtung gespeichert.');
    }
}
