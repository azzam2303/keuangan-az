<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['username' => config('admin.username')],
            [
                'name' => 'Administrator',
                'password' => config('admin.password'),
            ],
        );

        if (! $admin->is_admin) {
            $admin->forceFill([
                'is_admin' => true,
                'password' => config('admin.password'),
            ])->save();
        }
    }
}
