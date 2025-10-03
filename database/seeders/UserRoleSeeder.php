<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the roles
        $adminRole = Role::where('name', 'admin')->first();
        $cashierRole = Role::where('name', 'cashier')->first();
        $userRole = Role::where('name', 'user')->first();

        // Assign roles to users
        $users = User::all();

        foreach ($users as $user) {
            // If it's the first user or has email like admin@example.com, assign admin role
            if ($user->email === 'admin@example.com' || $user->id === 1) {
                $user->roles()->attach($adminRole->id);
            } else {
                // Assign user role to other users by default
                $user->roles()->attach($userRole->id);
            }
        }

        // Create a default admin user if one doesn't exist
        if (!User::where('email', 'admin@example.com')->exists()) {
            $adminUser = User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
            ]);
            $adminUser->roles()->attach($adminRole->id);
        }

        // Create a default cashier user if one doesn't exist
        if (!User::where('email', 'cashier@example.com')->exists()) {
            $cashierUser = User::create([
                'name' => 'Cashier User',
                'email' => 'cashier@example.com',
                'password' => bcrypt('password'),
            ]);
            $cashierUser->roles()->attach($cashierRole->id);
        }
    }
}