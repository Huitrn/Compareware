<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class UpdateAdminRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:update-role {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update user role to admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email {$email} not found.");
            return 1;
        }
        
        $this->info("Current user: {$user->name}");
        $this->info("Current role: {$user->role}");
        
        $user->role = 'admin';
        $user->save();
        
        $this->info("✅ User role updated to 'admin' successfully!");
        $this->info("User can now access admin panel.");
        
        return 0;
    }
}
