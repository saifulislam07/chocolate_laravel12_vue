<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'image' => 'nullable|image|max:2048',
            'rating' => 'nullable|integer|min:1|max:5',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('testimonials', 'uploads');
            $validated['image'] = '/uploads/' . $path;
        }

        Testimonial::create($validated);

        return redirect()->back()->with('success', 'Testimonial created successfully.');
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'quote' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'rating' => 'nullable|integer|min:1|max:5',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($testimonial->image) {
                $oldPath = str_replace('/uploads/', '', $testimonial->image);
                Storage::disk('uploads')->delete($oldPath);
            }
            $path = $request->file('image')->store('testimonials', 'uploads');
            $validated['image'] = '/uploads/' . $path;
        } else {
            unset($validated['image']);
        }

        $testimonial->update($validated);

        return redirect()->back()->with('success', 'Testimonial updated successfully.');
    }

    public function destroy(Testimonial $testimonial)
    {
        if ($testimonial->image) {
            $oldPath = str_replace('/uploads/', '', $testimonial->image);
            Storage::disk('uploads')->delete($oldPath);
        }
        $testimonial->delete();

        return redirect()->back()->with('success', 'Testimonial deleted successfully.');
    }
}
