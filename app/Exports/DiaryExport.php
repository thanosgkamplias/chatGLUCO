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
    // Δημιουργία μεταβλητών που θα χρησιμοποιηθούν στην κλάση
    protected $patient_id;
    protected $userName;
    protected $userBirth;
    protected $userGender;
    protected $userWeight;
    protected $userDiagnosis;

    // Constructor για την αρχικοποίηση των μεταβλητών
    public function __construct($patient_id,$userName,$userBirth,$userGender,$userWeight,$userDiagnosis) {
        $this->patient_id = $patient_id; // ID ασθενούς
        $this->userName = $userName; // Όνομα ασθενούς
        $this->userBirth = $userBirth; // Ημερομηνία γέννησης ασθενούς
        $this->userGender = $userGender; // Φύλο ασθενούς
        $this->userWeight = $userWeight; // Βάρος ασθενούς
        $this->userDiagnosis = $userDiagnosis; // Διάγνωση ασθενούς
    }

    // Μέθοδος για την συλλογή δεδομένων που θα γίνουν Export
    public function collection() {
        date_default_timezone_set('Europe/Athens'); // Ρύθμιση ζώνης ώρας

        $data = PatientStatistic::where('patient_id', $this->patient_id)
            ->select('created_at', 'glucose_old', 'food_carbo', 'insulin_dose', 'glucose_new','weight')
            ->orderBy('created_at', 'desc') // Ταξινόμηση κατά φθίνουσα ημερομηνία
            ->get()
            ->map(function ($item) {
                // Μετατροπή της ημερομηνίας σε ευανάγνωστη μορφή
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

    // Επικεφαλίδες για το Excel
    public function headings(): array {
        return [

            ["Patient's Info"], // Τίτλος για τα στοιχεία του ασθενούς
            ['Name', $this->userName], // Όνομα ασθενούς
            ['Gender', $this->userGender], // Φύλο ασθενούς
            ['Birth date', $this->userBirth], // Ημερομηνία γέννησης
            ['Weight', $this->userWeight], // Βάρος ασθενούς
            ['Diagnosis', $this->userDiagnosis], // Διάγνωση
            [], // Κενή γραμμή
            ["Patient's Records"], // Τίτλος για τα αρχεία ασθενούς
            ['Date', 'Glucose Before', 'Food Carbo', 'Insulin Dose', 'Glucose After', 'Weight'], // Επικεφαλίδες δεδομένων
        ];
    }

    // Στυλ του φύλλου Excel
    public function styles(Worksheet $sheet) {

        // Στυλ για τον τίτλο "Patient Info"
        $sheet->getStyle('A1:C1')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFB7CDE9'); // Ανοιχτό μπλε φόντο
        $sheet->getStyle('A1:C1')->getFont()->setBold(true); // Έντονα γράμματα
        $sheet->mergeCells('A1:C1'); // Συγχώνευση κελιών για τον τίτλο

        // Στυλ για τις ετικέτες πληροφοριών ασθενούς
        $sheet->getStyle('A2:A6')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFF0F8FF'); // Ανοιχτό φόντο για τις ετικέτες
        $sheet->getStyle('A1:A6')->getFont()->setBold(true); // Έντονα γράμματα για τις ετικέτες

        // Συγχώνευση κελιών στις γραμμές πληροφοριών ασθενούς (Patient's Info)
        $sheet->mergeCells('B2:C2');
        $sheet->mergeCells('B3:C3');
        $sheet->mergeCells('B4:C4');
        $sheet->mergeCells('B5:C5');
        $sheet->mergeCells('B6:C6');

        // Κεντράρισμα του περιεχομένου
        $sheet->getStyle('A1:C6')->getAlignment()->setHorizontal('center'); // Center align for consistency

        // Δημιουργία περιγράμματος για την περιοχή "Patient Info"
        $sheet->getStyle("A1:C6")->getBorders()->getAllBorders()->setBorderStyle('thin');

        // Συγχώνευση των κελιών D1:F6 για την περιοχή του λογοτύπου
        $sheet->mergeCells('D1:F6');

        // Προσθήκη λογότυπου
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('chatGLUCO');
        $drawing->setDescription('chatGLUCO logo');
        $drawing->setPath(public_path('chatgluco_logo/diary_export_logo.png')); // Specify the path to your logo image
        $drawing->setHeight(50);
        $drawing->setCoordinates('D2');

        // Κεντράρισμα του περιεχομένου μέσα στο D1:F6
        $drawing->setOffsetX(21); // Horizontal offset in pixels
        $drawing->setOffsetY(15); // Vertical offset in pixels

        $drawing->setWorksheet($sheet);

        // Στυλ για τον τίτλο Patient's Records
        $sheet->getStyle('A8:F8')->getFont()->setBold(true);
        $sheet->getStyle('A8:F8')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFB7CDE9'); // Ανοιχτό μπλε φόντο
        $sheet->mergeCells('A8:F8');

        // Στυλ για τις ετικέτες εγγραφών ασθενούς (Patient's Records)
        $sheet->getStyle('A9:F9')->getFont()->setBold(true);
        $sheet->getStyle('A9:F9')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFF0F8FF'); // Light background color for labels
        $sheet->getStyle("A8:F9")->getBorders()->getAllBorders()->setBorderStyle('thin');

        foreach (range(10, $sheet->getHighestRow()) as $row) {
            $sheet->getStyle("A{$row}")->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFF0F8FF');
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:F{$row}")->getBorders()->getAllBorders()->setBorderStyle('thin');
        }

        // Κεντράρισμα δεδομένων
        $sheet->getStyle('A:F')->getAlignment()->setHorizontal('center');

        return $sheet;
    }

    // Ρύθμιση πλάτους στηλών
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
