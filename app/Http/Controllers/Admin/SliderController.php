<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Sliders/Index', [
            'sliders' => Slider::orderBy('sort_order')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'subtitle' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'required|image|max:2048',
            'bg_color' => 'nullable|string',
            'text_color' => 'nullable|string',
            'button_text' => 'nullable|string',
            'button_link' => 'nullable|string',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('sliders', 'uploads');
            $validated['image'] = '/uploads/' . $path;
        }

        Slider::create($validated);

        return redirect()->back()->with('success', 'Slider created successfully.');
    }

    public function update(Request $request, Slider $slider)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'subtitle' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'bg_color' => 'nullable|string',
            'text_color' => 'nullable|string',
            'button_text' => 'nullable|string',
            'button_link' => 'nullable|string',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($slider->image) {
                $oldPath = str_replace('/uploads/', '', $slider->image);
                Storage::disk('uploads')->delete($oldPath);
            }
            $path = $request->file('image')->store('sliders', 'uploads');
            $validated['image'] = '/uploads/' . $path;
        } else {
            unset($validated['image']);
        }

        $slider->update($validated);

        return redirect()->back()->with('success', 'Slider updated successfully.');
    }

    public function destroy(Slider $slider)
    {
        if ($slider->image) {
            $oldPath = str_replace('/uploads/', '', $slider->image);
            Storage::disk('uploads')->delete($oldPath);
        }
        $slider->delete();

        return redirect()->back()->with('success', 'Slider deleted successfully.');
    }
}
