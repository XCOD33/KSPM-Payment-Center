<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Position;
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

        $positions = [
            'Ketua Umum',
            'Wakil Ketua Umum',
            'Sekrestaris',
            'Bendahara',
            'Ketua Bidang HRD',
            'Ketua Bidang RND',
            'Ketua Bidang PRD',
            'Ketua Bidang Edukasi',
            'Ketua Bidang Investasi',
            'Anggota Bidang HRD',
            'Anggota Bidang RND',
            'Anggota Bidang PRD',
            'Anggota Bidang Edukasi',
            'Anggota Bidang Investasi',
            'Anggota Magang',
        ];

        foreach ($positions as $index => $position) {
            Position::create([
                'name' => $position,
                'description' => fake()->sentence(10),
                'can_duplicate' => $index < 9 ? 'no' : 'yes',
            ]);
        }

        $role1 = Role::create(['name' => 'super-admin']);
        $role2 = Role::create(['name' => 'pengurus-inti']);
        $role3 = Role::create(['name' => 'koordinator']);
        $role4 = Role::create(['name' => 'pengurus']);

        $roles = [$role2, $role3, $role4];

        $user = \App\Models\User::factory()->create([
            'name' => 'Admin',
            'member_id' => '123456789',
            'password' => bcrypt('password'),
            'position_id' => random_int(1, 14),
        ]);

        $user->assignRole($role1);
        $user->givePermissionTo(Permission::all());

        for ($i = 1; $i < 100; $i++) {
            if ($i < 10) {
                $user = \App\Models\User::factory([
                    'position_id' => $i,
                ])->create();
            } else {
                $user = \App\Models\User::factory([
                    'position_id' => random_int(10, 14),
                ])->create();
            }


            // $user->assignRole($roles[rand(0, 2)]);
        }
    }
}
