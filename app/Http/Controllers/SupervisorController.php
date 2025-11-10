<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Periferico;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SupervisorController extends Controller
{
    /**
     * Constructor - Aplica middleware de rol
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:supervisor']);
    }

    /**
     * Panel principal del supervisor
     */
    public function dashboard()
    {
        $stats = [
            'total_products' => Periferico::count(),
            'pending_approval' => Periferico::where('estado', 'pendiente')->count(),
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
        ];

        return view('supervisor.dashboard', compact('stats'));
    }

    /**
     * Listar productos para supervisión
     */
    public function products()
    {
        $products = Periferico::with(['marca', 'categoria'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('supervisor.products.index', compact('products'));
    }

    /**
     * Aprobar producto
     */
    public function approveProduct($id)
    {
        // Verificar permiso específico
        if (!auth()->user()->hasPermission('approve_products')) {
            abort(403, 'No tiene permiso para aprobar productos.');
        }

        $product = Periferico::findOrFail($id);
        $product->estado = 'aprobado';
        $product->approved_by = auth()->id();
        $product->approved_at = now();
        $product->save();

        return redirect()->back()->with('success', 'Producto aprobado exitosamente.');
    }

    /**
     * Rechazar producto
     */
    public function rejectProduct(Request $request, $id)
    {
        $request->validate([
            'motivo' => 'required|string|max:500'
        ]);

        $product = Periferico::findOrFail($id);
        $product->estado = 'rechazado';
        $product->motivo_rechazo = $request->motivo;
        $product->rejected_by = auth()->id();
        $product->rejected_at = now();
        $product->save();

        return redirect()->back()->with('success', 'Producto rechazado.');
    }

    /**
     * Ver usuarios (solo lectura y edición básica)
     */
    public function users()
    {
        if (!auth()->user()->hasPermission('view_users')) {
            abort(403, 'No tiene permiso para ver usuarios.');
        }

        $users = User::with('role')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('supervisor.users.index', compact('users'));
    }

    /**
     * Ver reportes
     */
    public function reports()
    {
        if (!auth()->user()->hasPermission('view_reports')) {
            abort(403, 'No tiene permiso para ver reportes.');
        }

        $reports = [
            'products_by_category' => Periferico::select('categoria_id', DB::raw('count(*) as total'))
                ->groupBy('categoria_id')
                ->get(),
            'products_by_brand' => Periferico::select('marca_id', DB::raw('count(*) as total'))
                ->groupBy('marca_id')
                ->get(),
            'products_by_status' => Periferico::select('estado', DB::raw('count(*) as total'))
                ->groupBy('estado')
                ->get(),
        ];

        return view('supervisor.reports', compact('reports'));
    }

    /**
     * Ver estadísticas
     */
    public function statistics()
    {
        if (!auth()->user()->hasPermission('view_statistics')) {
            abort(403);
        }

        $stats = [
            'total_products' => Periferico::count(),
            'approved_products' => Periferico::where('estado', 'aprobado')->count(),
            'pending_products' => Periferico::where('estado', 'pendiente')->count(),
            'rejected_products' => Periferico::where('estado', 'rechazado')->count(),
            'products_this_month' => Periferico::whereMonth('created_at', now()->month)->count(),
            'products_this_week' => Periferico::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];

        return view('supervisor.statistics', compact('stats'));
    }
}
