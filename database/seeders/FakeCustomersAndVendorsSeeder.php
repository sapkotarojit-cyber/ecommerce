<?php

namespace Database\Seeders;

use App\Models\Dokan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FakeCustomersAndVendorsSeeder extends Seeder
{
    public function run(): void
    {
        // Only use this seeder in local/testing environments.
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->error(
                'Aborted: fake accounts can only be seeded locally or in testing.'
            );

            return;
        }

        $password = Hash::make('TestPassword123!');

        // Create 1,000 fake customers.
        for ($i = 1; $i <= 1000; $i++) {
            $email = sprintf(
                'fakecustomer%04d@example.test',
                $i
            );

            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => fake()->name(),
                    'password' => $password,
                ]
            );
        }

        // Create 1,000 fake vendors and their linked users.
        for ($i = 1; $i <= 1000; $i++) {
            $email = sprintf(
                'fakevendor%04d@example.test',
                $i
            );

            $companyName = sprintf(
                'Test Medical Supplies %04d',
                $i
            );

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $companyName,
                    'password' => $password,
                ]
            );

            Dokan::firstOrCreate(
                ['email' => $email],
                [
                    'user_id' => $user->id,
                    'company_name' => $companyName,
                    'name' => $companyName,
                    'password' => $password,
                    'logo' => 'test-logo.png',
                    'reg_no' => sprintf('TEST-REG-%04d', $i),
                    'contact_number' => sprintf('TEST-%07d', $i),
                    'status' => Dokan::STATUS_PENDING,
                ]
            );
        }

        $this->command?->info(
            'Fake customer and vendor seeding completed.'
        );
    }
}
