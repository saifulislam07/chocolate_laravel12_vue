<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WebSettingController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Settings/Index', [
            'settings' => WebSetting::first()
        ]);
    }

    public function update(Request $request)
    {
        $settings = WebSetting::first() ?? new WebSetting();

        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'facebook_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'smtp_host' => 'nullable|string',
            'smtp_port' => 'nullable|string',
            'smtp_username' => 'nullable|string',
            'smtp_password' => 'nullable|string',
            'smtp_encryption' => 'nullable|string',
        ]);

        if ($request->hasFile('logo')) {
            if ($settings->logo) {
                $oldPath = str_replace('/uploads/', '', $settings->logo);
                \Illuminate\Support\Facades\Storage::disk('uploads')->delete($oldPath);
            }
            $path = $request->file('logo')->store('settings', 'uploads');
            $validated['logo'] = '/uploads/' . $path;
        }

        if ($request->hasFile('footer_logo')) {
            if ($settings->footer_logo) {
                $oldPath = str_replace('/uploads/', '', $settings->footer_logo);
                \Illuminate\Support\Facades\Storage::disk('uploads')->delete($oldPath);
            }
            $path = $request->file('footer_logo')->store('settings', 'uploads');
            $validated['footer_logo'] = '/uploads/' . $path;
        }
        
        if ($request->hasFile('favicon')) {
            if ($settings->favicon) {
                $oldPath = str_replace('/uploads/', '', $settings->favicon);
                \Illuminate\Support\Facades\Storage::disk('uploads')->delete($oldPath);
            }
            $path = $request->file('favicon')->store('settings', 'uploads');
            $validated['favicon'] = '/uploads/' . $path;
        }

        if ($settings->id) {
            $settings->update($validated);
        } else {
            WebSetting::create($validated);
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
