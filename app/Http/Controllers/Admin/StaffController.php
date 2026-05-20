<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\NotificationService;
use App\Support\StaffPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(auth()->user()?->role === 'admin', 403);

            return $next($request);
        });
    }

    public function index(): View
    {
        $staff = User::query()
            ->where('role', 'staff')
            ->with('creator:id,name')
            ->withCount([
                'activityLogs as total_actions',
                'activityLogs as today_actions' => fn ($q) => $q->whereDate('created_at', today()),
            ])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => User::query()->where('role', 'staff')->count(),
            'active' => User::query()->where('role', 'staff')->where('status', 'active')->count(),
            'inactive' => User::query()->where('role', 'staff')->where('status', '!=', 'active')->count(),
        ];

        return view('admin.staff.index', compact('staff', 'stats'));
    }

    public function create(): View
    {
        $permissions = StaffPermissions::grouped();

        return view('admin.staff.create', compact('permissions'));
    }

    public function store(Request $request, NotificationService $notifications): RedirectResponse
    {
        $keys = array_values(array_filter(StaffPermissions::allKeys(), fn ($k) => $k !== 'manage_staff'));
        $keys[] = '*';

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:8',
            'permissions' => 'required|array',
            'permissions.*' => 'in:'.implode(',', $keys),
            'staff_notes' => 'nullable|string|max:5000',
        ]);

        $password = $validated['password'] ?? Str::password(16);

        $perms = array_values(array_filter($validated['permissions'], fn ($p) => $p !== 'manage_staff'));

        $staff = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($password),
            'role' => 'staff',
            'status' => 'active',
            'staff_permissions' => $perms,
            'staff_notes' => $validated['staff_notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        try {
            $notifications->send('staff.welcome', $staff, [
                'email' => $staff->email,
                'password' => $password,
            ], ['email']);
        } catch (\Throwable) {
        }

        return redirect()->route('admin.staff.index')->with('success', 'Staff member created.');
    }

    public function edit(int $id): View
    {
        $staff = User::query()->where('role', 'staff')->findOrFail($id);
        $permissions = StaffPermissions::grouped();

        return view('admin.staff.edit', compact('staff', 'permissions'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $staff = User::query()->where('role', 'staff')->findOrFail($id);

        $keys = array_values(array_filter(StaffPermissions::allKeys(), fn ($k) => $k !== 'manage_staff'));
        $keys[] = '*';

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,'.$staff->id,
            'phone' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:8',
            'permissions' => 'required|array',
            'permissions.*' => 'in:'.implode(',', $keys),
            'staff_notes' => 'nullable|string|max:5000',
        ]);

        $perms = array_values(array_filter($validated['permissions'], fn ($p) => $p !== 'manage_staff'));

        $staff->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'staff_permissions' => $perms,
            'staff_notes' => $validated['staff_notes'] ?? null,
        ]);

        if (! empty($validated['password'])) {
            $staff->password = Hash::make($validated['password']);
        }

        $staff->save();

        return redirect()->route('admin.staff.index')->with('success', 'Staff updated.');
    }

    public function toggleStatus(int $id): RedirectResponse
    {
        $staff = User::query()->where('role', 'staff')->findOrFail($id);
        $newStatus = $staff->status === 'active' ? 'inactive' : 'active';
        $staff->update(['status' => $newStatus]);

        return back()->with('success', 'Status updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $staff = User::query()->where('role', 'staff')->findOrFail($id);
        $staff->delete();

        return redirect()->route('admin.staff.index')->with('success', 'Staff removed.');
    }

    public function activityLog(Request $request, int $id): View
    {
        $staff = User::query()->where('role', 'staff')->findOrFail($id);

        $logs = ActivityLog::query()
            ->where('user_id', $id)
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->string('action')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('date_to')))
            ->latest('created_at')
            ->paginate(30)
            ->withQueryString();

        $actions = ActivityLog::query()
            ->where('user_id', $id)
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('admin.staff.activity', compact('staff', 'logs', 'actions'));
    }
}
