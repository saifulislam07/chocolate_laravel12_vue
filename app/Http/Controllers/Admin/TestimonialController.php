<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TestimonialController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Testimonials/Index', [
            'testimonials' => Testimonial::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'quote' => 'required|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        Testimonial::create($validated);

        return redirect()->back()->with('success', 'Testimonial created successfully.');
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'quote' => 'required|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $testimonial->update($validated);

        return redirect()->back()->with('success', 'Testimonial updated successfully.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return redirect()->back()->with('success', 'Testimonial deleted successfully.');
    }
}
