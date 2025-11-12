<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestSimpleMail extends Command
{
    protected $signature = 'test:simple-mail {email}';
    protected $description = 'Enviar un correo de prueba simple';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("🔧 Configuración de correo:");
        $this->line("   Host: " . config('mail.mailers.smtp.host'));
        $this->line("   Port: " . config('mail.mailers.smtp.port'));
        $this->line("   Username: " . config('mail.mailers.smtp.username'));
        $this->line("   Encryption: " . config('mail.mailers.smtp.encryption'));
        $this->line("   From: " . config('mail.from.address'));
        $this->newLine();

        $this->info("📧 Enviando correo de prueba a: {$email}");
        
        try {
            Mail::raw('Este es un correo de prueba desde CompareWare. Si recibes este mensaje, la configuración de correo funciona correctamente.', function ($message) use ($email) {
                $message->to($email)
                    ->subject('🔔 Prueba de Correo - CompareWare');
            });
            
            $this->info("✅ Correo enviado exitosamente!");
            $this->newLine();
            
            // Verificar si hay errores en los logs
            $this->info("📋 Revisa tu bandeja de entrada (y spam)");
            
        } catch (\Exception $e) {
            $this->error("❌ Error al enviar correo:");
            $this->error($e->getMessage());
            Log::error('Mail test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
        
        return 0;
    }
}
