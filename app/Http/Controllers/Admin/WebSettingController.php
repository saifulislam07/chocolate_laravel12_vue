<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class WebSettingController extends Controller
{
    /** Settings that hold an uploaded picture rather than a value typed in. */
    private const IMAGE_FIELDS = ['logo', 'footer_logo', 'favicon', 'login_image', 'admin_login_image'];

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
            // What a visitor sees on the way in. All optional: an empty value
            // leaves the login screen showing the wording it ships with.
            'login_image' => 'nullable|image|max:4096',
            'login_cover_title' => 'nullable|string|max:120',
            'login_cover_text' => 'nullable|string|max:160',
            'login_form_title' => 'nullable|string|max:120',
            'login_form_text' => 'nullable|string|max:400',
            'admin_login_image' => 'nullable|image|max:4096',
            'admin_login_cover_title' => 'nullable|string|max:120',
            'admin_login_cover_text' => 'nullable|string|max:400',
            'admin_login_form_title' => 'nullable|string|max:120',
            'admin_login_form_text' => 'nullable|string|max:400',
            'maintenance_mode' => 'boolean',
            'maintenance_title' => 'nullable|string|max:255',
            'maintenance_message' => 'nullable|string',
            'facebook_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'youtube_url' => 'nullable|url',
            'whatsapp_url' => 'nullable|url',
            'tiktok_url' => 'nullable|url',
            'linkedin_url' => 'nullable|url',
            'pinterest_url' => 'nullable|url',
            'meta_pixel_enabled' => 'boolean',
            'meta_pixel_id' => [Rule::requiredIf($request->boolean('meta_pixel_enabled')), 'nullable', 'regex:/^[0-9]{5,30}$/'],
            'meta_ads_enabled' => 'boolean',
            'meta_ads_api_version' => ['nullable', 'regex:/^v[0-9]{2,3}\.[0-9]$/'],
            'meta_ads_account_id' => [Rule::requiredIf($request->boolean('meta_ads_enabled')), 'nullable', 'regex:/^(act_)?[0-9]{5,30}$/'],
            'meta_ads_access_token' => [Rule::requiredIf($request->boolean('meta_ads_enabled')), 'nullable', 'string'],
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
            'pathao_enabled' => 'boolean',
            'pathao_base_url' => 'nullable|url',
            'pathao_client_id' => 'nullable|string|max:255',
            'pathao_client_secret' => 'nullable|string|max:255',
            'pathao_username' => 'nullable|string|max:255',
            'pathao_password' => 'nullable|string|max:255',
            'pathao_store_id' => 'nullable|string|max:255',
            'steadfast_enabled' => 'boolean',
            'steadfast_base_url' => 'nullable|url',
            'steadfast_api_key' => 'nullable|string|max:255',
            'steadfast_secret_key' => 'nullable|string|max:255',
            'smtp_host' => 'nullable|string',
            'smtp_port' => 'nullable|string',
            'smtp_username' => 'nullable|string',
            'smtp_password' => 'nullable|string',
            'smtp_encryption' => 'nullable|string',
        ]);
        $validated['maintenance_mode'] = $request->boolean('maintenance_mode');
        $validated['meta_pixel_enabled'] = $request->boolean('meta_pixel_enabled');
        $validated['meta_ads_enabled'] = $request->boolean('meta_ads_enabled');
        $validated['meta_ads_api_version'] = ($validated['meta_ads_api_version'] ?? null) ?: 'v24.0';
        $validated['messenger_enabled'] = $request->boolean('messenger_enabled');
        $validated['messenger_theme_color'] = ($validated['messenger_theme_color'] ?? null) ?: '#B99D4B';
        $validated['bkash_enabled'] = $request->boolean('bkash_enabled');
        $validated['nagad_enabled'] = $request->boolean('nagad_enabled');
        $validated['bkash_mode'] = ($validated['bkash_mode'] ?? null) ?: 'sandbox';
        $validated['nagad_mode'] = ($validated['nagad_mode'] ?? null) ?: 'sandbox';
        $validated['pathao_enabled'] = $request->boolean('pathao_enabled');
        $validated['steadfast_enabled'] = $request->boolean('steadfast_enabled');

        foreach (self::IMAGE_FIELDS as $field) {
            if ($stored = $this->storeImage($request, $settings, $field)) {
                $validated[$field] = $stored;
            } else {
                // No new file: leave whatever is on record. The field arrives as
                // null on every save, and writing that through would wipe the
                // image each time any other setting is touched.
                unset($validated[$field]);
            }
        }

        if ($settings->id) {
            $settings->update($validated);
        } else {
            WebSetting::create($validated);
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Save a newly uploaded picture and return its public path, or null when
     * this save carried no file for the field. The one it replaces is deleted:
     * settings images are one-per-slot, and the old file has nothing left
     * pointing at it.
     */
    private function storeImage(Request $request, WebSetting $settings, string $field): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        if ($settings->{$field}) {
            Storage::disk('uploads')->delete(str_replace('/uploads/', '', $settings->{$field}));
        }

        return '/uploads/' . $request->file($field)->store('settings', 'uploads');
    }
}
