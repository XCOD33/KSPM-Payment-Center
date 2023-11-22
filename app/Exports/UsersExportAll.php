<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\User;


class UsersExportAll implements FromCollection, WithHeadings, WithColumnFormatting, WithColumnWidths, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $users = User::all();
        $totalUsers = $users->count();
        $filteredUsers = $users->map(function ($user, $index) use ($totalUsers) {
            return [
                'index' => $index + 1,
                'name' => $user->name,
                'member_id' => $user->member_id,
                'year' => $user->year,
                'position' => $user->position->name ?? 'Tidak ada',
                'email' => $user->email,
                'phone' => $user->phone,
            ];
        });
        return $filteredUsers;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Lengkap',
            'ID Anggota',
            'Tahun',
            'Jabatan',
            'Email',
            'No Ponsel',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'B' => '@',
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => '@',
            'E' => '@',
            'F' => '@',
            'G' => '@',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 30,
            'C' => 15,
            'D' => 15,
            'E' => 30,
            'F' => 30,
            'G' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
                'alignment' => [
                    'horizontal' => 'center',
                ],
            ],
            'A' => [
                'alignment' => [
                    'horizontal' => 'center',
                ],
            ],
            'C' => [
                'alignment' => [
                    'horizontal' => 'center',
                ],
            ],
            'D' => [
                'alignment' => [
                    'horizontal' => 'center',
                ],
            ],
            'E' => [
                'alignment' => [
                    'horizontal' => 'center',
                ],
            ],

        ];
    }
}
