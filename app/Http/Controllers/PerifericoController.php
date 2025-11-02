<?php

namespace App\Http\Controllers;

use App\Models\Periferico;
use App\Http\Requests\SecurePerifericoRequest;
use App\Services\SecurityLogger;
use Illuminate\Http\Request;

class PerifericoController extends Controller
{
    protected SecurityLogger $securityLogger;

    public function __construct(SecurityLogger $securityLogger)
    {
        $this->securityLogger = $securityLogger;
        
        // Aplicar middlewares de seguridad
        $this->middleware(['sql.security:strict', 'rate.limit', 'auth:sanctum']);
    }

    // GET: Obtener todos los periféricos (solo usuarios autenticados)
    public function index()
    {
        $this->securityLogger->logSecurityEvent('PERIFERICOS_LIST_ACCESS', [
            'user_id' => auth()->id(),
            'ip' => request()->ip()
        ], 'LOW');

        return response()->json(Periferico::all());
    }

    // GET: Obtener un periférico por ID
    public function show($id)
    {
        // Validar que el ID sea un número entero positivo
        if (!is_numeric($id) || $id <= 0) {
            $this->securityLogger->logSecurityEvent('INVALID_PERIFERICO_ID', [
                'provided_id' => $id,
                'ip' => request()->ip()
            ], 'MEDIUM');

            return response()->json(['error' => 'Invalid ID'], 400);
        }

        return Periferico::findOrFail($id);
    }

    // POST: Crear un nuevo periférico (solo admin)
    public function store(SecurePerifericoRequest $request)
    {
        if (auth()->user()->role !== 'admin') {
            $this->securityLogger->logSecurityEvent('UNAUTHORIZED_PERIFERICO_CREATE', [
                'user_id' => auth()->id(),
                'ip' => request()->ip()
            ], 'HIGH');

            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Usar solo datos validados
        $validatedData = $request->validated();
        $periferico = Periferico::create($validatedData);

        $this->securityLogger->logSecurityEvent('PERIFERICO_CREATED', [
            'periferico_id' => $periferico->id,
            'user_id' => auth()->id(),
            'ip' => request()->ip()
        ], 'LOW');

        return response()->json($periferico, 201);
    }

    // PUT: Actualizar un periférico existente (solo admin)
    public function update(SecurePerifericoRequest $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            $this->securityLogger->logSecurityEvent('UNAUTHORIZED_PERIFERICO_UPDATE', [
                'user_id' => auth()->id(),
                'periferico_id' => $id,
                'ip' => request()->ip()
            ], 'HIGH');

            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Validar ID
        if (!is_numeric($id) || $id <= 0) {
            return response()->json(['error' => 'Invalid ID'], 400);
        }

        $periferico = Periferico::findOrFail($id);
        
        // Usar solo datos validados
        $validatedData = $request->validated();
        $periferico->update($validatedData);

        $this->securityLogger->logSecurityEvent('PERIFERICO_UPDATED', [
            'periferico_id' => $id,
            'user_id' => auth()->id(),
            'ip' => request()->ip()
        ], 'LOW');

        return response()->json($periferico, 200);
    }

    // DELETE: Eliminar un periférico (solo admin)
    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        $periferico = Periferico::findOrFail($id);
        $periferico->delete();
        return response()->json(null, 204);
    }
}