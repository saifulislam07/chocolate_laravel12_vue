<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WebSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The artwork and wording on both login screens are settings, so the shop can
 * change what a visitor reads on the way in without a deploy.
 */
class LoginPageSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function settingsManager(): User
    {
        Permission::findOrCreate('view_settings', 'web');
        Permission::findOrCreate('edit_settings', 'web');

        $user = User::factory()->create();
        $user->givePermissionTo(['view_settings', 'edit_settings']);

        return $user;
    }

    public function test_both_login_screens_carry_the_settings_to_the_page(): void
    {
        WebSetting::create([
            'site_name' => 'Coco Craft',
            'login_image' => '/uploads/settings/shop-door.jpg',
            'login_cover_title' => 'Made by hand, every day',
            'login_form_title' => 'Good to see you',
            'admin_login_image' => '/uploads/settings/back-office.jpg',
            'admin_login_cover_title' => 'The back office',
        ]);

        $this->get('/login')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Auth/Login')
                ->where('webSettings.login_image', '/uploads/settings/shop-door.jpg')
                ->where('webSettings.login_cover_title', 'Made by hand, every day')
                ->where('webSettings.login_form_title', 'Good to see you'));

        $this->get('/admin/login')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Auth/AdminLogin')
                ->where('webSettings.admin_login_image', '/uploads/settings/back-office.jpg')
                ->where('webSettings.admin_login_cover_title', 'The back office'));
    }

    /**
     * Nothing has to be filled in for the screens to work: the pages fall back
     * to the wording they ship with, and a settings row need not even exist.
     */
    public function test_the_login_screens_render_without_any_settings_saved(): void
    {
        $this->assertNull(WebSetting::first());

        $this->get('/login')->assertOk();
        $this->get('/admin/login')->assertOk();
    }

    public function test_an_admin_can_save_login_wording_and_artwork(): void
    {
        Storage::fake('uploads');
        WebSetting::create(['site_name' => 'Coco Craft']);

        $this->actingAs($this->settingsManager())
            ->post('/admin/settings', [
                'site_name' => 'Coco Craft',
                'login_cover_title' => 'Made by hand, every day',
                'login_form_text' => 'Sign in to follow your orders.',
                'admin_login_form_title' => 'Staff entrance',
                'login_image' => UploadedFile::fake()->image('cover.jpg'),
                'admin_login_image' => UploadedFile::fake()->image('office.jpg'),
            ])
            ->assertRedirect();

        $settings = WebSetting::first();

        $this->assertSame('Made by hand, every day', $settings->login_cover_title);
        $this->assertSame('Sign in to follow your orders.', $settings->login_form_text);
        $this->assertSame('Staff entrance', $settings->admin_login_form_title);
        $this->assertStringStartsWith('/uploads/settings/', $settings->login_image);
        $this->assertStringStartsWith('/uploads/settings/', $settings->admin_login_image);
        $this->assertNotSame($settings->login_image, $settings->admin_login_image);

        Storage::disk('uploads')->assertExists(str_replace('/uploads/', '', $settings->login_image));
    }

    /**
     * The file inputs are empty on every save that is not changing a picture.
     * Writing that emptiness through would clear the artwork whenever any other
     * setting on the page was touched.
     */
    public function test_saving_other_settings_leaves_the_artwork_alone(): void
    {
        WebSetting::create([
            'site_name' => 'Coco Craft',
            'login_image' => '/uploads/settings/shop-door.jpg',
            'admin_login_image' => '/uploads/settings/back-office.jpg',
        ]);

        $this->actingAs($this->settingsManager())
            ->post('/admin/settings', [
                'site_name' => 'Coco Craft Chocolatier',
                'login_image' => null,
                'admin_login_image' => null,
            ])
            ->assertRedirect();

        $settings = WebSetting::first();

        $this->assertSame('Coco Craft Chocolatier', $settings->site_name);
        $this->assertSame('/uploads/settings/shop-door.jpg', $settings->login_image);
        $this->assertSame('/uploads/settings/back-office.jpg', $settings->admin_login_image);
    }

    public function test_a_login_image_must_be_an_image(): void
    {
        Storage::fake('uploads');
        WebSetting::create(['site_name' => 'Coco Craft']);

        $this->actingAs($this->settingsManager())
            ->post('/admin/settings', [
                'site_name' => 'Coco Craft',
                'login_image' => UploadedFile::fake()->create('prices.pdf', 20, 'application/pdf'),
            ])
            ->assertSessionHasErrors('login_image');

        $this->assertNull(WebSetting::first()->login_image);
    }
}
