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
        // \App\Models\User::factory(3)->create();

        $role1 = Role::create(['name' => 'super-admin']);
        $role2 = Role::create(['name' => 'pengurus-inti']);
        $role3 = Role::create(['name' => 'koordinator']);
        $role4 = Role::create(['name' => 'pengurus']);

        $roles = [$role2, $role3, $role4];

        $user = \App\Models\User::factory()->create([
            'name' => 'Admin',
            'member_id' => '123456789',
            'password' => bcrypt('password'),
        ]);

        $user->assignRole($role1);
        $user->givePermissionTo(Permission::all());

        for ($i = 0; $i < 10; $i++) {
            $user = \App\Models\User::factory()->create();

            $user->assignRole($roles[rand(0, 2)]);
        }
    }
}
