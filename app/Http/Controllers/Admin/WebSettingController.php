<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
            'maintenance_mode' => 'boolean',
            'maintenance_title' => 'nullable|string|max:255',
            'maintenance_message' => 'nullable|string',
            'facebook_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'messenger_enabled' => 'boolean',
            'messenger_page_id' => [Rule::requiredIf($request->boolean('messenger_enabled')), 'nullable', 'string', 'max:80'],
            'messenger_theme_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'messenger_logged_in_greeting' => 'nullable|string|max:255',
            'messenger_logged_out_greeting' => 'nullable|string|max:255',
            'bkash_enabled' => 'boolean',
            'bkash_mode' => 'nullable|in:sandbox,live',
            'bkash_base_url' => 'nullable|url',
            'bkash_app_key' => 'nullable|string|max:255',
            'bkash_app_secret' => 'nullable|string|max:255',
            'bkash_username' => 'nullable|string|max:255',
            'bkash_password' => 'nullable|string|max:255',
            'nagad_enabled' => 'boolean',
            'nagad_mode' => 'nullable|in:sandbox,live',
            'nagad_base_url' => 'nullable|url',
            'nagad_merchant_id' => 'nullable|string|max:255',
            'nagad_merchant_number' => 'nullable|string|max:255',
            'nagad_public_key' => 'nullable|string',
            'nagad_private_key' => 'nullable|string',
            'smtp_host' => 'nullable|string',
            'smtp_port' => 'nullable|string',
            'smtp_username' => 'nullable|string',
            'smtp_password' => 'nullable|string',
            'smtp_encryption' => 'nullable|string',
        ]);
        $validated['maintenance_mode'] = $request->boolean('maintenance_mode');
        $validated['messenger_enabled'] = $request->boolean('messenger_enabled');
        $validated['messenger_theme_color'] = $validated['messenger_theme_color'] ?: '#B99D4B';
        $validated['bkash_enabled'] = $request->boolean('bkash_enabled');
        $validated['nagad_enabled'] = $request->boolean('nagad_enabled');
        $validated['bkash_mode'] = $validated['bkash_mode'] ?: 'sandbox';
        $validated['nagad_mode'] = $validated['nagad_mode'] ?: 'sandbox';

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
