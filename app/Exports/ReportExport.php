<?php

namespace App\Exports;

use App\Models\Category;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportExport implements FromCollection, ShouldAutoSize, WithColumnWidths, WithHeadings, WithStyles
{
    public function __construct(
        private int $month,
        private int $year,
        private int $userId,
    ) {
    }

    public function collection()
    {

        $data = Transaction::whereMonth('date', $this->month)
            ->whereYear('date', $this->year)
            ->where('transactions.user_id', $this->userId)
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->select('date', 'title', 'amount', 'description', 'categories.type', 'categories.name')
            ->orderBy('date', 'desc')
            ->get();

        return collect([$this->headings()])->concat($data);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'Transactions of month '.$this->month.'/'.$this->year);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $typeColumn = 'E';
        $rowCount = $sheet->getHighestDataRow($typeColumn);

        $categoryTypes = config('category.categorytypes');

        for ($row = 3; $row <= $rowCount; $row++) {
            $cellRange = $typeColumn.$row;

            $categoryCell = $sheet->getCell('F'.$row);
            $category = Category::where('name', $categoryCell->getValue())->first();
            if ($category->type !== $categoryTypes['INCOMES']) {
                $cellColor = 'F97315';
            } else {
                $cellColor = '22C55D';
            }

            $sheet->getStyle($cellRange)->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $cellColor],
                ],
            ]);
        }

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => '000000']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['argb' => 'E9D5FF'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],
            2 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['argb' => 'A855F7'],
                ],
            ],
            'C' => [
                'font' => ['bold' => true],
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Date',
            'Title',
            'Amount',
            'Description',
            'Type',
            'Category',
        ];
    }

    private function getHeadings(): array
    {
        return array_fill(0, count($this->headings()), '');
    }

    public function columnWidths(): array
    {
        return [
            'D' => 40,
        ];
    }
}
