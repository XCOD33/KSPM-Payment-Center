<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class UsersExport implements WithHeadings, WithColumnFormatting, WithColumnWidths, WithStyles
{

    public function headings(): array
    {
        return [
            'Nama Lengkap',
            'ID Anggota',
            'NIM',
            'Tahun',
            'Email',
            'No Ponsel',
            'Roles',
            'Jabatan',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => '@',
            'B' => NumberFormat::FORMAT_NUMBER,
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => '@',
            'E' => '@',
            'F' => '@',
            'G' => '@',
            'H' => '@',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 15,
            'C' => 15,
            'D' => 10,
            'E' => 30,
            'F' => 15,
            'G' => 15,
            'H' => 30,
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
        ];
    }
}
