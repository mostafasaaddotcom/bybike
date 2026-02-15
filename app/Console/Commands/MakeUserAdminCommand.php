<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeUserAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-admin {email : The email of the user to promote}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Promote a user to admin role by email';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("User with email [{$email}] not found.");

            return self::FAILURE;
        }

        if ($user->isAdmin()) {
            $this->info("User [{$email}] is already an admin.");

            return self::SUCCESS;
        }

        $user->update(['role' => 'admin']);

        $this->info("User [{$email}] has been promoted to admin.");

        return self::SUCCESS;
    }
}
