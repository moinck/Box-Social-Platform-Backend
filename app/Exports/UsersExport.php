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
    public $users;
    public function __construct($users)
    {
        $this->users = $users;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->users;
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
            'User BrandConfiguration',
            'User Verified',
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
            $user->hasBrandKit() ? 'Yes' : 'No',
            $user->is_verified ? 'Yes' : 'No',
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
        $sheet->getStyle('A1:J1')->applyFromArray([
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
                        'color' => ['rgb' => 'FF0000'], // Red color
                        // 'bold' => true,
                    ],
                ]);
            }

            // for row H
            $brandConfigurationCell = 'H' . $row;
            $brandConfiguration = $sheet->getCell($brandConfigurationCell)->getValue();
    
            if ($brandConfiguration === 'Yes') {
                $sheet->getStyle($brandConfigurationCell)->applyFromArray([
                    'font' => [
                        'color' => ['rgb' => '000000'], // Black color
                    ],
                ]);
            } elseif ($brandConfiguration === 'No') {
                $sheet->getStyle($brandConfigurationCell)->applyFromArray([
                    'font' => [
                        'color' => ['rgb' => 'FF0000'], // Red color
                        // 'bold' => true,
                    ],
                ]);
            }

            // for row I
            $verifiedCell = 'I' . $row;
            $verified = $sheet->getCell($verifiedCell)->getValue();
    
            if ($verified === 'Yes') {
                $sheet->getStyle($verifiedCell)->applyFromArray([
                    'font' => [
                        'color' => ['rgb' => '000000'], // Black color
                    ],
                ]);
            } elseif ($verified === 'No') {
                $sheet->getStyle($verifiedCell)->applyFromArray([
                    'font' => [
                        'color' => ['rgb' => 'FF0000'], // Red color
                        // 'bold' => true,
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
