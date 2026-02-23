<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function updateGeneral(Request $request)
    {
        $request->validate([
            'app_name'   => 'required|string|max:255',
            'app_url'    => 'required|url',
            'site_logo'  => 'nullable|image|max:10240'
        ]);

        // Save Text Settings
        Setting::updateOrCreate(
            ['key' => 'app_name'],
            ['value' => $request->app_name]
        );

        Setting::updateOrCreate(
            ['key' => 'app_url'],
            ['value' => $request->app_url]
        );

        // Handle Logo Upload
        if ($request->hasFile('site_logo')) {

            $logoPath = $request->file('site_logo')
                                ->store('settings', 'public');

            Setting::updateOrCreate(
                ['key' => 'site_logo'],
                ['value' => $logoPath]
            );
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}