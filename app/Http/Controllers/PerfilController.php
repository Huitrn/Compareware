<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class PerfilController extends Controller
{
    /**
     * Mostrar la vista de perfil
     */
    public function index()
    {
        $comparisons = \App\Models\ComparisonHistory::with([
            'periferico1.marca', 
            'periferico2.marca', 
            'periferico1.categoria', 
            'periferico2.categoria'
        ])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        return view('perfil', compact('comparisons'));
    }

    /**
     * Mostrar la vista de edición de perfil
     */
    public function editar()
    {
        return view('editar');
    }

    /**
     * Actualizar el perfil del usuario
     */
    public function actualizar(Request $request)
    {
        $user = auth()->user();

        // Validar los datos
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'Este correo electrónico ya está en uso.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Actualizar el usuario
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        // Log de la actualización
        \Log::info('PROFILE_UPDATED', [
            'user_id' => $user->id,
            'changes' => $request->only(['name', 'email']),
            'ip' => $request->ip()
        ]);

        return redirect()->route('perfil')
            ->with('success', '¡Perfil actualizado correctamente!');
    }

    /**
     * Actualizar la contraseña del usuario
     */
    public function actualizarPassword(Request $request)
    {
        $user = auth()->user();

        // Validar los datos
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers(), 'confirmed'],
        ], [
            'current_password.required' => 'La contraseña actual es obligatoria.',
            'password.required' => 'La nueva contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Verificar la contraseña actual
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'La contraseña actual es incorrecta.'])
                ->withInput();
        }

        // Actualizar la contraseña
        $user->password = Hash::make($request->password);
        $user->save();

        // Log de la actualización
        \Log::info('PASSWORD_UPDATED', [
            'user_id' => $user->id,
            'ip' => $request->ip()
        ]);

        return redirect()->route('perfil')
            ->with('success', '¡Contraseña actualizada correctamente!');
    }
}

