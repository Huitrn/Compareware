<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Constructor - Middleware aplicado desde rutas
     */
    public function __construct()
    {
        // Middleware aplicado desde web.php, no necesario aquí
    }

    /**
     * Verificar permisos de admin
     */
    private function checkAdminAccess()
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'No tienes permisos de administrador.');
        }
    }

    /**
     * Panel principal de administración
     */
    public function dashboard()
    {
        $this->checkAdminAccess();
        
        $adminRole = Role::where('slug', 'administrador')->first();
        $supervisorRole = Role::where('slug', 'supervisor')->first();
        $developerRole = Role::where('slug', 'desarrollador')->first();
        
        $stats = [
            'total_users' => User::count(),
            'admins' => User::where('role_id', $adminRole->id)->count(),
            'supervisors' => User::where('role_id', $supervisorRole->id)->count(),
            'developers' => User::where('role_id', $developerRole->id)->count(),
            'users_without_role' => User::whereNull('role_id')->count(),
            'recent_users' => User::with('userRole')->latest()->take(5)->get()
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Gestión de usuarios con búsqueda y filtrado
     */
    public function users(Request $request)
    {
        $this->checkAdminAccess();
        
        $query = User::with('userRole');
        
        // 🔍 BÚSQUEDA por nombre o email
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }
        
        // 🎯 FILTRO por rol
        if ($request->filled('role_id') && $request->get('role_id') !== 'all') {
            $query->where('role_id', $request->get('role_id'));
        }
        
        // 📊 FILTRO por estado
        if ($request->filled('status')) {
            if ($request->get('status') === 'active') {
                $query->where('is_suspended', false);
            } elseif ($request->get('status') === 'suspended') {
                $query->where('is_suspended', true);
            }
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Cargar todos los roles para el filtro
        $roles = Role::all();
        
        // Mantener parámetros de búsqueda en la paginación
        $users->appends($request->query());
        
        return view('admin.users', compact('users', 'roles'));
    }

    /**
     * Cambiar rol de usuario
     */
    public function changeRole(Request $request, User $user)
    {
        // Solo administradores pueden cambiar roles
        if (!Auth::user()->isAdmin()) {
            return back()->with('error', 'Solo los administradores pueden cambiar roles de usuario.');
        }

        $request->validate([
            'role_id' => 'nullable|exists:roles,id'
        ]);

        // Obtener el rol de administrador
        $adminRole = Role::where('slug', 'administrador')->first();

        // No permitir que un admin se quite a sí mismo el rol de admin
        if ($user->id === Auth::id() && $request->role_id != $adminRole->id) {
            return back()->with('error', 'No puedes cambiar tu propio rol de administrador.');
        }

        // No permitir eliminar el último administrador
        if ($user->isAdmin() && (!$request->role_id || $request->role_id != $adminRole->id)) {
            $totalAdmins = User::where('role_id', $adminRole->id)->count();
            if ($totalAdmins <= 1) {
                return back()->with('error', 'No puedes cambiar el rol del último administrador del sistema.');
            }
        }

        $oldRoleName = $user->userRole ? $user->userRole->nombre : 'Sin rol';
        
        // Actualizar role_id
        $user->role_id = $request->role_id;
        $user->save();
        
        // Recargar la relación para obtener el nuevo rol
        $user->load('userRole');
        $newRoleName = $user->userRole ? $user->userRole->nombre : 'Sin rol';

        return back()->with('success', "✅ Rol de {$user->name} cambiado de '{$oldRoleName}' a '{$newRoleName}' exitosamente.");
    }

    /**
     * Suspender/Activar usuario
     */
    public function toggleStatus(User $user)
    {
        // No permitir suspender al propio admin
        if ($user->id === Auth::id()) {
            return back()->with('error', 'No puedes suspender tu propia cuenta.');
        }

        $user->is_suspended = !($user->is_suspended ?? false);
        $user->save();

        $status = $user->is_suspended ? 'suspendido' : 'activado';
        return back()->with('success', "Usuario {$user->name} {$status} exitosamente.");
    }

    /**
     * Ver detalles de un usuario
     */
    public function userDetails(User $user)
    {
        return view('admin.user-details', compact('user'));
    }

    /**
     * Eliminar usuario definitivamente
     */
    public function deleteUser(User $user)
    {
        $this->checkAdminAccess();
        
        // No permitir que un admin se elimine a sí mismo
        if ($user->id === Auth::id()) {
            return back()->with('error', 'No puedes eliminarte a ti mismo.');
        }
        
        // Verificar que no sea el único admin
        if ($user->isAdmin()) {
            $adminRole = Role::where('slug', 'administrador')->first();
            $totalAdmins = User::where('role_id', $adminRole->id)->count();
            if ($totalAdmins <= 1) {
                return back()->with('error', 'No puedes eliminar al único administrador del sistema.');
            }
        }
        
        $userName = $user->name;
        $userEmail = $user->email;
        
        // Eliminar definitivamente
        $user->delete();
        
        // Log de seguridad
        \Log::channel('security')->info('Usuario eliminado definitivamente', [
            'admin_user' => Auth::user()->name,
            'admin_email' => Auth::user()->email,
            'deleted_user' => $userName,
            'deleted_email' => $userEmail,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
        
        return redirect()->route('admin.users')->with('success', "Usuario '{$userName}' eliminado definitivamente.");
    }
}