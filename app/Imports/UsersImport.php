<?php

namespace App\Imports;

use App\Models\Position;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Reader\Xml\Style\NumberFormat;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UsersImport implements ToCollection, WithHeadingRow, WithMultipleSheets
{

    public function collection(Collection $rows)
    {
        try {
            foreach ($rows as $row) {
                $row['nim'] = strval($row['nim']);
                $row['no_ponsel'] = strval($row['no_ponsel']);

                $role = Role::where('name', $row['roles'])->first();
                $position = Position::where('name', $row['jabatan'])->first()->id;

                $user = User::create([
                    'name' => $row['nama_lengkap'],
                    'member_id' => $row['id_anggota'],
                    'nim' => $row['nim'],
                    'year' => $row['tahun'],
                    'email' => $row['email'],
                    'phone' => $row['no_ponsel'],
                    'password' => bcrypt('password'),
                    'position_id' => $position,
                ]);

                $user->assignRole($role);
                $user->givePermissionTo(Permission::all());
            }
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function sheets(): array
    {
        return [
            0 => new UsersImport(),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function rules()
    {
        return [
            'nama_lengkap' => 'required|string',
            'id_anggota' => 'required|string|unique:users,member_id|min:9|max:9',
            'password' => 'required|string',
            'nim' => 'required|string|unique:users,nim|min:10|max:10',
            'email' => 'required|email|unique:users,email',
            'no_ponsel' => 'required|string|min:10|max:13',
            'jabatan' => 'integer',
            'tahun' => 'required|integer',
        ];
    }
}
