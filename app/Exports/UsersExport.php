<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class UsersExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithStyles, WithCustomValueBinder
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return User::where('role', 'customer')->latest()->get();
    }

    /**
     * Define the headings for the Excel file.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'First Name',
            'Last Name',
            'Email',
            'FCA Number',
            'Company Name',
            'Account Status',
            'Created Date'
        ];
    }

    /**
     * Map the data to the desired format.
     *
     * @param mixed $user
     * @return array
     */
    public function map($user): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $user->first_name,
            $user->last_name,
            $user->email,
            $user->fca_number,
            $user->company_name,
            $user->status == 'active' ? 'Active' : 'Inactive',
            \Carbon\Carbon::parse($user->created_at)->format('d-m-Y')
        ];
    }

    /**
     * Apply styles to the worksheet.
     *
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    // 'rgb' => '59E659', // light Green color
                    'rgb' => 'F4D106', // our theme color
                ],
            ],
        ]);

        // Get the highest row with data
        $highestRow = $sheet->getHighestRow();

        // Apply conditional formatting to the "Account Status" column (G)
        for ($row = 2; $row <= $highestRow; $row++) {
            $statusCell = 'G' . $row;
            $status = $sheet->getCell($statusCell)->getValue();

            if ($status === 'Active') {
                $sheet->getStyle($statusCell)->applyFromArray([
                    'font' => [
                        'color' => ['rgb' => '000000'], // Black color
                    ],
                ]);
            } elseif ($status === 'Inactive') {
                $sheet->getStyle($statusCell)->applyFromArray([
                    'font' => [
                        'color' => ['rgb' => 'FF6961'], // Red color
                        'bold' => true,
                    ],
                ]);
            }
        }
    }

    /**
     * Bind values to cells.
     *
     * @param Cell $cell
     * @param $value
     * @return bool
     */
    public function bindValue(Cell $cell, $value)
    {
        if (is_numeric($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_NUMERIC);
            return true;
        }

        // else return default behavior
        return parent::bindValue($cell, $value);
    }
}
