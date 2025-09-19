<?php

namespace App\Exports;

use App\Helpers\Helpers;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class UsersSubscriptionExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithStyles, WithCustomValueBinder
{
    public $subscriptions;
    public function __construct($subscriptions)
    {
        $this->subscriptions = $subscriptions;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->subscriptions;
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
            'User',
            'Subscription Plan',
            'Status',
            'Start Date',
            'End Date',
            'Created Date',
            'Updated Date',
        ];
    }

    /**
     * Map the data to the desired format.
     *
     * @param mixed $subscription
     * @return array
     */
    public function map($subscription): array
    {
        static $no = 0;
        $no++;

        if ($subscription->status == 'active' || $subscription->current_period_start != null) {
            $start_date = date('d-m-Y', strtotime($subscription->current_period_start));
        } else {
            $start_date = 'N/A';
        }

        if ($subscription->status == 'active' || $subscription->current_period_end != null) {
            $end_date = date('d-m-Y', strtotime($subscription->current_period_end));
        } else {
            $end_date = 'N/A';
        }

        return [
            $no,
            $subscription->user->first_name." ".$subscription->user->last_name,
            $subscription->plan->name,
            strtoupper($subscription->status),
            $start_date,
            $end_date,
            Helpers::dateFormate($subscription->created_at),
            Helpers::dateFormate($subscription->updated_at),
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
            $statusCell = 'D' . $row;
            $status = $sheet->getCell($statusCell)->getValue();

            if ($status === 'ACTIVE') {
                $sheet->getStyle($statusCell)->applyFromArray([
                    'font' => [
                        'color' => ['rgb' => '72e128'], // Green color
                    ],
                ]);
            } else {
                $sheet->getStyle($statusCell)->applyFromArray([
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
