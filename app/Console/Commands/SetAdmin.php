<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:set-admin {email}')]
#[Description('Grant admin access to the user with the given email')]
class SetAdmin extends Command
{
    public function handle(): int
    {
        $email = $this->argument('email');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No user found with email [{$email}].");

            return self::FAILURE;
        }

        $user->is_admin = true;
        $user->save();

        $this->info("{$user->email} is now an admin.");

        return self::SUCCESS;
    }
}
