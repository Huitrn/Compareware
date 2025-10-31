<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'No tienes permisos de administrador.');
        }
    }

    /**
     * Panel principal de administración
     */
    public function dashboard()
    {
        $this->checkAdminAccess();
        
        $stats = [
            'total_users' => User::count(),
            'admins' => User::where('role', 'admin')->count(),
            'users' => User::where('role', 'user')->count(),
            'recent_users' => User::latest()->take(5)->get()
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Gestión de usuarios con búsqueda y filtrado
     */
    public function users(Request $request)
    {
        $this->checkAdminAccess();
        
        $query = User::query();
        
        // 🔍 BÚSQUEDA por nombre o email
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }
        
        // 🎯 FILTRO por rol
        if ($request->filled('role') && $request->get('role') !== 'all') {
            $query->where('role', $request->get('role'));
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
        
        // Mantener parámetros de búsqueda en la paginación
        $users->appends($request->query());
        
        return view('admin.users', compact('users'));
    }

    /**
     * Cambiar rol de usuario
     */
    public function changeRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:admin,user,moderator'
        ]);

        // No permitir que un admin se quite a sí mismo el rol de admin
        if ($user->id === Auth::id() && $request->role !== 'admin') {
            return back()->with('error', 'No puedes cambiar tu propio rol de administrador.');
        }

        $oldRole = $user->role;
        $user->role = $request->role;
        $user->save();

        return back()->with('success', "Rol de {$user->name} cambiado de '{$oldRole}' a '{$request->role}' exitosamente.");
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
        if ($user->role === 'admin') {
            $totalAdmins = User::where('role', 'admin')->count();
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