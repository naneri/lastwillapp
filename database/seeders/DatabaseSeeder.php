<?php

namespace Database\Seeders;

use App\Domain\Services\CheckInLifecycle;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $lifecycle = app(CheckInLifecycle::class);

        // User::factory(10)->create();

        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $lifecycle->initialize($testUser);

        $kanat = User::updateOrCreate(
            ['email' => 'ktnaneri@gmail.com'],
            [
                'name' => 'Kanat Tabaldiev',
                'password' => Hash::make('Thegosu88'),
                'email_verified_at' => now(),
            ],
        );
        $lifecycle->initialize($kanat);
    }
}
