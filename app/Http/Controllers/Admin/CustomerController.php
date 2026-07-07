<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Customers/Index', [
            'customers' => Customer::with('division', 'district')->latest()->get(),
            'divisions' => Division::with('districts:id,division_id,name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'division_id' => 'nullable|exists:divisions,id',
            'district_id' => ['nullable', Rule::exists('districts', 'id')->where('division_id', $request->input('division_id'))],
        ]);

        $customer = Customer::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'customer' => $customer,
                'message' => 'Customer created successfully'
            ]);
        }

        return redirect()->back()->with('success', 'Customer created successfully.');
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'max:20', Rule::unique('customers', 'phone')->ignore($customer->id)],
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'division_id' => 'nullable|exists:divisions,id',
            'district_id' => ['nullable', Rule::exists('districts', 'id')->where('division_id', $request->input('division_id'))],
        ]);

        $customer->update($validated);

        return redirect()->back()->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->back()->with('success', 'Customer deleted successfully.');
    }
}
