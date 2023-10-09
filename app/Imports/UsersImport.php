<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\withHeadingRow;
use Maatwebsite\Excel\Concerns\withMultipleSheets;

class UsersImport implements ToCollection, withHeadingRow, withMultipleSheets
{

    public function collection(Collection $rows)
    {
        // dd($rows);
        foreach ($rows as $row) {
            User::create([
                'name' => $row['nama_lengkap'],
                'member_id' => $row['id_anggota'],
                'year' => $row['tahun'],
                'position_id' => $row['jabatan_id'],
                'password' => bcrypt('password'),
            ]);
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
}
