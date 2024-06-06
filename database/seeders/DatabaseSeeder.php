<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Pembayaran;
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
            'Sekretaris Umum',
            'Bendahara Umum',
            'Koordinator HRD',
            'Koordinator RnD',
            'Koordinator PRD',
            'Koordinator Edukasi',
            'Koordinator Investasi',
            'Staff HRD',
            'Staff RnD',
            'Staff PRD',
            'Staff Edukasi',
            'Staff Investasi',
            'Anggota Magang',
            'super-admin',
        ];

        foreach ($positions as $index => $position) {
            Position::create([
                'name' => $position,
            ]);
        }

        $role1 = Role::create(['name' => 'super-admin']);
        Role::create(['name' => 'pengurus']);
        Role::create(['name' => 'magang']);

        $user = \App\Models\User::factory()->create([
            'name' => 'Admin',
            'member_id' => '123456789',
            'password' => bcrypt('password'),
            'email' => 'admin@email.com',
            'phone' => '08' . fake()->numerify('##########'),
            'nim' => '1234567890',
            'year' => '2023',
            'position_id' => Position::where('name', 'super-admin')->first()->id,
        ]);

        $user->assignRole($role1);
        $user->givePermissionTo(Permission::all());

        // for ($i = 1; $i < 100; $i++) {
        //     if ($i < 10) {
        //         $user = \App\Models\User::factory([
        //             'position_id' => $i,
        //         ])->create();
        //     } else {
        //         $user = \App\Models\User::factory([
        //             'position_id' => random_int(10, 14),
        //         ])->create();
        //     }
        // }
    }
}
