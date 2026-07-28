<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Port;
use App\Models\Country;
use Illuminate\Http\Request;

class PortController extends Controller
{
    public function index()
    {
        $ports = Port::with('country')->paginate(15);
        $countries = Country::orderBy('name')->get();
        return view('admin.ports.index', compact('ports', 'countries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unlocode' => 'required|string|max:5|unique:ports',
            'country_id' => 'required|exists:countries,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'required|in:active,congested,closed'
        ]);

        Port::create($validated);
        return redirect()->route('ports.index')->with('success', 'Port created successfully');
    }

    public function update(Request $request, Port $port)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unlocode' => 'required|string|max:5|unique:ports,unlocode,' . $port->id,
            'country_id' => 'required|exists:countries,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'required|in:active,congested,closed'
        ]);

        $port->update($validated);
        return redirect()->route('ports.index')->with('success', 'Port updated successfully');
    }

    public function destroy(Port $port)
    {
        $port->delete();
        return redirect()->route('ports.index')->with('success', 'Port deleted successfully');
    }
}
