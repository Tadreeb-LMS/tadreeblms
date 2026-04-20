<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function index()
    {
        $languages = \App\Models\Language::all();
        return view('backend.languages.index', compact('languages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required|unique:languages,code'
        ]);

        Language::create([
            'name' => $request->name,
            'code' => $request->code,
            'is_enabled' => 1
        ]);

        return back()->with('success', 'Language added');
    }

    public function toggle($id)
    {
       

         $language = Language::findOrFail($id);

    // ❌ Prevent disabling default language
    if ($language->is_default) {
        return back()->with('error', 'Default language cannot be disabled');
    }

    $language->is_enabled = !$language->is_enabled;
    $language->save();

    return back()->with('success', 'Language status updated');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:json',
            'code' => 'required'
        ]);

        $file = $request->file('file');
        $code = $request->code;

        // Save file to resources/lang
        $path = resource_path("lang/{$code}.json");

        // file_put_contents($path, file_get_contents($file));
        $content = file_get_contents($file);
        $json = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->with('error', 'Invalid JSON file');
        }

        file_put_contents($path, $content);

        // Insert into DB if not exists
        \App\Models\Language::updateOrCreate(
            ['code' => $code],
            [
                'name' => strtoupper($code),
                'is_enabled' => 1
            ]
        );

        return back()->with('success', 'Language uploaded successfully');
    }

    public function download($code)
    {
        $path = resource_path("lang/{$code}.json");

        if (!file_exists($path)) {
            return back()->with('error', 'File not found');
        }

        return response()->download($path);
    }
}