<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Inertia\Inertia;

class PublicPageController extends Controller
{
    /**
     * Photos the detail page shares with its homepage teaser, so a visitor who
     * clicks through from the homepage lands on the same visual.
     */
    private const HERO_IMAGES = [
        'about-us' => '/images/cococraft-v2/about_arch.jpg',
    ];

    public function show($slug)
    {
        $page = Page::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return Inertia::render('Frontend/Page', [
            'page' => $page,
            'heroImage' => self::HERO_IMAGES[$slug] ?? null,
        ]);
    }
}
