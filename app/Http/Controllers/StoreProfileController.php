<?php

namespace App\Http\Controllers;

use App\Models\StoreProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StoreProfileController extends Controller
{
    public function index()
    {
        $profile = StoreProfile::firstOrCreate(
            ['id' => 1],
            [
                'store_name' => 'ADI CELL',
                'address' => 'Jl. Toko No. 123',
                'phone' => '0812-3456-7890',
                'email' => '',
                'footer_text' => 'Terima kasih!',
            ]
        );

        return view('store-profile.index', compact('profile'));
    }

    public function update(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'store_name' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:100',
            'footer_text' => 'nullable|string|max:255',
        ])->validate();

        $profile = StoreProfile::firstOrCreate(['id' => 1]);
        $profile->update($validated);

        return redirect()->route('store-profile.index')->with('success', 'Profil toko berhasil diperbarui.');
    }
}
