<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // =============================================
    // TÜM KULLANICILARI LİSTELE
    // GET /api/admin/users
    // =============================================
    public function index(Request $request)
    {
        $query = User::with(['roles', 'entrepreneurProfile', 'company', 'memberships.package']);

        // Arama
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Rol filtresi
        if ($request->role) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Aktiflik filtresi
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $users = $query->orderBy('created_at', 'desc')
                       ->paginate($request->per_page ?? 20);

        return response()->json($users);
    }

    // =============================================
    // KULLANICI DETAYI
    // GET /api/admin/users/{id}
    // =============================================
    public function show($id)
    {
        $user = User::with([
            'roles',
            'entrepreneurProfile',
            'company',
            'memberships.package',
            'goals',
            'interests',
            'photoGallery',
        ])->find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Kullanıcı bulunamadı',
            ], 404);
        }

        return response()->json([
            'user' => $user,
        ]);
    }

    // =============================================
    // KULLANICI AKTİF/PASİF YAP (BAN)
    // PUT /api/admin/users/{id}/toggle-active
    // =============================================
    public function toggleActive($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Kullanıcı bulunamadı',
            ], 404);
        }

        // Super admin ban'lanamaz
        if ($user->roles()->where('name', 'super_admin')->exists()) {
            return response()->json([
                'message' => 'Super admin hesabı değiştirilemez',
            ], 403);
        }

        $user->update([
            'is_active' => !$user->is_active,
        ]);

        return response()->json([
            'message'   => $user->is_active ? 'Kullanıcı aktif edildi' : 'Kullanıcı pasif edildi',
            'is_active' => $user->is_active,
        ]);
    }

    // =============================================
    // KULLANICIYA ROL ATA
    // POST /api/admin/users/{id}/assign-role
    // =============================================
    public function assignRole(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Kullanıcı bulunamadı',
            ], 404);
        }

        $request->validate([
            'role_name' => 'required|string|exists:roles,name',
        ]);

        $role = Role::where('name', $request->role_name)->first();
        $admin = auth('api')->user();

        // Zaten bu rolü var mı
        if ($user->roles()->where('role_id', $role->id)->exists()) {
            return response()->json([
                'message' => 'Kullanıcı zaten bu role sahip',
            ], 409);
        }

        $user->roles()->attach($role->id, [
            'assigned_by' => $admin->id,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json([
            'message' => "'{$role->name}' rolü atandı",
            'roles'   => $user->roles()->get(['roles.id', 'roles.name']),
        ]);
    }

    // =============================================
    // KULLANICIDAN ROL KALDIR
    // POST /api/admin/users/{id}/remove-role
    // =============================================
    public function removeRole(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Kullanıcı bulunamadı',
            ], 404);
        }

        $request->validate([
            'role_name' => 'required|string|exists:roles,name',
        ]);

        $role = Role::where('name', $request->role_name)->first();

        // user rolü kaldırılamaz
        if ($role->name === 'user') {
            return response()->json([
                'message' => 'Varsayılan user rolü kaldırılamaz',
            ], 403);
        }

        $user->roles()->detach($role->id);

        return response()->json([
            'message' => "'{$role->name}' rolü kaldırıldı",
            'roles'   => $user->roles()->get(['roles.id', 'roles.name']),
        ]);
    }
}