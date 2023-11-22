<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Reader\Xml\Style\NumberFormat;

class UsersImport implements ToCollection, WithHeadingRow, WithMultipleSheets
{

    public function collection(Collection $rows)
    {
        // dd($rows);
        foreach ($rows as $row) {
            User::create([
                'name' => $row['nama_lengkap'],
                'member_id' => $row['id_anggota'],
                'nim' => $row['nim'],
                'year' => $row['tahun'],
                'email' => $row['email'],
                'phone' => $row['no_ponsel'],
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

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER,
        ];
    }
}
