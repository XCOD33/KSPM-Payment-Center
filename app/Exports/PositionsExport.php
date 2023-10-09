<?php

namespace App\Exports;

use App\Models\Position;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\withHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\withColumnWidths;
use Maatwebsite\Excel\Concerns\withStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PositionsExport implements FromCollection, withHeadings, WithColumnFormatting, withColumnWidths, withStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $positions = Position::all();
        $filteredPositions = $positions->map(function ($position) {
            return [
                'id' => $position->id,
                'name' => $position->name,
                'description' => $position->description,
            ];
        });

        return $filteredPositions;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Jabatan',
            'Deskripsi',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => '@',
            'B' => '@',
            'C' => '@',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 30,
            'C' => 50,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }
}
