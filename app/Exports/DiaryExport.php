<?php

namespace App\Exports;

use App\Models\Patient;
use App\Models\PatientStatistic;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DiaryExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $patient_id;
    protected $userName;
    protected $userBirth;
    protected $userGender;
    protected $userWeight;
    protected $userDiagnosis;

    public function __construct($patient_id,$userName,$userBirth,$userGender,$userWeight,$userDiagnosis) {
        $this->patient_id = $patient_id;
        $this->userName = $userName;
        $this->userBirth = $userBirth;
        $this->userGender = $userGender;
        $this->userWeight = $userWeight;
        $this->userDiagnosis = $userDiagnosis;
    }

    public function collection() {
        date_default_timezone_set('Europe/Athens');

        $data = PatientStatistic::where('patient_id', $this->patient_id)
            ->select('created_at', 'glucose_old', 'food_carbo', 'insulin_dose', 'glucose_new','weight')
            ->orderBy('created_at', 'desc')
            ->take(90)
            ->get()
            ->map(function ($item) {
                $item->created_at = (string) date('d-m-Y H:i:s', strtotime($item->created_at));
                return [
                    'created_at' => $item->created_at,
                    'glucose_old' => $item->glucose_old,
                    'food_carbo' => $item->food_carbo,
                    'insulin_dose' => $item->insulin_dose,
                    'glucose_new' => $item->glucose_new,
                    'weight' => $item->weight,
                ];
            });

        return $data;
    }

    public function headings(): array {
        // Blank row to start the "Patient Info" section
        return [

            ["Patient's Info"], // Title row with "Patient Info"
            ['Name', $this->userName], // Row for Name
            ['Gender', $this->userGender], // Row for Gender
            ['Birth date', $this->userBirth], // Row for Birth date
            ['Weight', $this->userWeight], // Row for Weight
            ['Diagnosis', $this->userDiagnosis], // Row for Diagnosis
            [], // Blank row between Patient Info and Data Table headings

            ["Patient's Records"], // Blank row between Patient Info and Data Table headings
            ['Date', 'Glucose Before', 'Food Carbo', 'Insulin Dose', 'Glucose After', 'Weight'], // Column headings for the main data table
        ];
    }

    public function styles(Worksheet $sheet) {

        // Style the "Patient Info" title row
        $sheet->getStyle('A1:C1')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFB7CDE9'); // Light blue background
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->mergeCells('A1:C1');

        $sheet->getStyle('A2:A6')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFF0F8FF'); // Light background color for labels
        $sheet->getStyle('A1:A6')->getFont()->setBold(true); // Make "Name", "Gender", etc. bold
        // Apply styles for Patient Info section
        $sheet->mergeCells('B2:C2');
        $sheet->mergeCells('B3:C3');
        $sheet->mergeCells('B4:C4');
        $sheet->mergeCells('B5:C5');
        $sheet->mergeCells('B6:C6');
        $sheet->getStyle('A1:C6')->getAlignment()->setHorizontal('center'); // Center align for consistency

        $sheet->getStyle("A1:C6")->getBorders()->getAllBorders()->setBorderStyle('thin');

        // Merge D1:F6 for the logo area
        $sheet->mergeCells('D1:F6');

        // Insert the logo image at D1
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Company Logo');
        $drawing->setPath(public_path('chatgluco_logo/diary_export_logo.png')); // Specify the path to your logo image
        $drawing->setHeight(50); // Adjust the height as needed
        $drawing->setCoordinates('D2');

        // Center the logo within D1:F6
        // Adjust offsets to approximate centering in merged cells
        $drawing->setOffsetX(21); // Horizontal offset in pixels, adjust as needed
        $drawing->setOffsetY(15); // Vertical offset in pixels, adjust as needed

        $drawing->setWorksheet($sheet);


        // Header row styles for main data table
        $sheet->getStyle('A8:F8')->getFont()->setBold(true);
        $sheet->getStyle('A8:F8')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFB7CDE9'); // Light blue background
        $sheet->mergeCells('A8:F8');

        $sheet->getStyle('A9:F9')->getFont()->setBold(true);
        $sheet->getStyle('A9:F9')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFF0F8FF'); // Light background color for labels
        $sheet->getStyle("A8:F9")->getBorders()->getAllBorders()->setBorderStyle('thin');

                // Data row alternating colors
        foreach (range(10, $sheet->getHighestRow()) as $row) {
            $sheet->getStyle("A{$row}")->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFF0F8FF');
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:F{$row}")->getBorders()->getAllBorders()->setBorderStyle('thin');
        }

        // Center-align data rows
        $sheet->getStyle('A:F')->getAlignment()->setHorizontal('center');

//



        return $sheet;
    }

    public function columnWidths(): array {
        return [
            'A' => 27.67,
            'B' => 14,
            'C' => 14,
            'D' => 14,
            'E' => 14,
        ];
    }
}
