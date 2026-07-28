<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Port;
use Illuminate\Http\Request;

class PortController extends Controller
{
    public function searchApi(Request $request)
    {
        $query = Port::query();

        if ($request->has('country')) {
            $query->whereHas('country', function($q) use ($request) {
                $q->where('iso3', $request->country);
            });
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $ports = $query->get(['id', 'name', 'latitude', 'longitude', 'unlocode']);

        return response()->json([
            'status' => 'success',
            'count' => $ports->count(),
            'data' => $ports
        ]);
    }
}
