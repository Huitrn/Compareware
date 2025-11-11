<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class PasswordResetController extends Controller
{
    /**
     * Mostrar el formulario de solicitud de restablecimiento de contraseña
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Enviar el enlace de restablecimiento de contraseña por email
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debes ingresar un correo electrónico válido.',
        ]);

        // Log para debug
        \Log::info('Intentando enviar enlace de recuperación', [
            'email' => $request->email
        ]);

        // Verificar si el usuario existe
        $user = \App\Models\User::where('email', $request->email)->first();
        \Log::info('Usuario encontrado', [
            'exists' => $user ? 'Sí' : 'No',
            'user_id' => $user ? $user->id : null
        ]);

        // Enviar el enlace de restablecimiento
        $status = Password::sendResetLink(
            $request->only('email')
        );

        \Log::info('Estado del envío', [
            'status' => $status,
            'expected' => Password::RESET_LINK_SENT
        ]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', '¡Enlace de recuperación enviado! Revisa tu correo electrónico.');
        }

        return back()->withErrors(['email' => 'No encontramos una cuenta con ese correo electrónico.']);
    }

    /**
     * Mostrar el formulario de restablecimiento de contraseña
     */
    public function showResetForm(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    /**
     * Restablecer la contraseña
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debes ingresar un correo electrónico válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', '¡Contraseña restablecida! Ya puedes iniciar sesión.');
        }

        return back()->withErrors(['email' => 'No se pudo restablecer la contraseña. El enlace puede haber expirado.']);
    }
}

