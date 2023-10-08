<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();
        $role = Role::create(['name' => 'super-admin']);

        $user = \App\Models\User::factory()->create([
            'name' => 'Admin',
            'member_id' => '123456789',
            'password' => bcrypt('password'),
        ]);

        $user->assignRole($role);
        $user->givePermissionTo(Permission::all());
    }
}
