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
            'site_logo'  => 'nullable|image|max:10240',
            // PAYMENT SETTINGS
            'app__currency' => 'required',

            'services__stripe__key' => 'required_if:services__stripe__active,1',

            'services__stripe__secret' => 'required_if:services__stripe__active,1',

        ], [

            'app__currency.required' =>
            'Currency field is required.',

            'services__stripe__key.required_if' =>  'Stripe publishable key is required when Stripe is enabled.',
            'services__stripe__secret.required_if' => 'Stripe secret key is required when Stripe is enabled.',
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

        Setting::updateOrCreate(
            ['key' => 'app__currency'],
            ['value' => $request->app__currency]
        );

        Setting::updateOrCreate(
            ['key' => 'services__stripe__active'],
            ['value' => $request->has('services__stripe__active') ? 1 : 0]
        );

        Setting::updateOrCreate(
            ['key' => 'services__stripe__key'],
            ['value' => $request->services__stripe__key]
        );

        Setting::updateOrCreate(
            ['key' => 'services__stripe__secret'],
            ['value' => $request->services__stripe__secret]
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