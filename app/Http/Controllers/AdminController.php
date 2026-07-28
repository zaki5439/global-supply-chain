<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $users = \App\Models\User::all();
        $totalUsers = $users->count();
        $admins = $users->where('role', 'admin')->count();
        $analysts = $users->where('role', 'analyst')->count();
        // Mocked User Growth Data (Last 7 Days)
        $userGrowthData = [
            'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'data' => [12, 19, 25, 32, 45, 52, $totalUsers]
        ];

        // Mocked System Health
        $systemHealth = [
            'news_api' => ['status' => 'online', 'latency' => '45ms'],
            'currency_api' => ['status' => 'online', 'latency' => '120ms'],
            'port_dataset' => ['status' => 'online', 'last_sync' => '2 hours ago']
        ];

        // Mocked Activity Logs
        $activityLogs = [
            ['time' => '10 mins ago', 'action' => 'Admin Commander logged in', 'type' => 'info'],
            ['time' => '1 hour ago', 'action' => 'User Field Operator created', 'type' => 'success'],
            ['time' => '3 hours ago', 'action' => 'Port dataset synced successfully', 'type' => 'success'],
            ['time' => '5 hours ago', 'action' => 'Failed login attempt from IP 192.168.1.10', 'type' => 'danger'],
        ];

        return view('admin.dashboard', compact(
            'users', 'totalUsers', 'admins', 'analysts', 
            'userGrowthData', 'systemHealth', 'activityLogs'
        ));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,analyst,user',
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->back()->with('success', 'User ' . $user->name . ' created successfully.');
    }

    public function updateRole(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);
        $request->validate(['role' => 'required|in:admin,analyst,user']);
        
        $user->role = $request->role;
        $user->save();

        return response()->json(['status' => 'success', 'message' => 'Role updated to ' . $request->role]);
    }

    public function deleteUser($id)
    {
        $user = \App\Models\User::findOrFail($id);
        $user->delete();

        return response()->json(['status' => 'success', 'message' => 'User deleted successfully']);
    }
}
