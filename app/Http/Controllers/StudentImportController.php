<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\PaEnrollment;
use App\Models\PaClass;
use App\Models\PaSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StudentImportController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SHOW IMPORT FORM
    |--------------------------------------------------------------------------
    */

    public function showImportForm()
    {
        $classes  = PaClass::orderBy('class_order')->get();
        $sessions = PaSession::where('is_active', 1)->get();

        return view('students.import', compact('classes', 'sessions'));
    }

    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD SAMPLE CSV
    |--------------------------------------------------------------------------
    */

    public function downloadSample()
    {
        $filename = 'peace_academy_students_sample.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = [
            'student_name',       // Required
            'father_name',        // Optional
            'mother_name',        // Optional
            'gender',             // Male / Female
            'date_of_birth',      // YYYY-MM-DD
            'blood_group',        // A+, A-, B+, B-, AB+, AB-, O+, O-
            'religion',           // Islam, Christianity, Hinduism, etc.
            'b_form_no',          // Child B-Form / CNIC
            'mobile_no',          // Student / parent mobile
            'whatsapp_no',        // WhatsApp number
            'address',            // Home address
            'guardian_name',      // Guardian full name
            'guardian_relation',  // Father / Mother / Uncle / etc.
            'guardian_mobile',    // Guardian contact
            'father_occupation',  // Occupation
            'admission_date',     // YYYY-MM-DD  (leave blank = today)
            'previous_school',    // Previous school name
            'previous_class',     // Class in previous school
        ];

        // Two sample rows
        $sampleRows = [
            [
                'Ali Hassan',
                'Hassan Ahmed',
                'Fatima Hassan',
                'Male',
                '2015-03-15',
                'A+',
                'Islam',
                '61101-1234567-1',
                '0300-1234567',
                '0300-1234567',
                'House 12, Street 5, Karachi',
                'Hassan Ahmed',
                'Father',
                '0300-9876543',
                'Business',
                date('Y-m-d'),
                'City School',
                'Grade 3',
            ],
            [
                'Sara Malik',
                'Malik Usman',
                'Nadia Malik',
                'Female',
                '2014-07-22',
                'B+',
                'Islam',
                '61101-7654321-2',
                '0321-5678901',
                '0321-5678901',
                'Block 4, Gulshan, Lahore',
                'Malik Usman',
                'Father',
                '0321-0987654',
                'Engineer',
                date('Y-m-d'),
                'Beacon House',
                'Grade 4',
            ],
        ];

        $callback = function () use ($columns, $sampleRows) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM so Excel opens correctly
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, $columns);

            foreach ($sampleRows as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /*
    |--------------------------------------------------------------------------
    | PROCESS IMPORT
    |--------------------------------------------------------------------------
    */

    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
            'class_id'    => 'required|exists:pa_classes,id',
            'session_id'  => 'required|exists:pa_sessions,id',
        ]);

        $file      = $request->file('import_file');
        $extension = strtolower($file->getClientOriginalExtension());

        // ── Parse file into rows array ──────────────────────────────────────
        if (in_array($extension, ['xlsx', 'xls'])) {
            $rows = $this->parseExcel($file->getRealPath());
        } else {
            $rows = $this->parseCsv($file->getRealPath());
        }

        if (empty($rows)) {
            return back()->withErrors(['import_file' => 'The file is empty or could not be parsed.']);
        }

        // ── Process rows ────────────────────────────────────────────────────
        $imported  = 0;
        $skipped   = 0;
        $errors    = [];

        DB::beginTransaction();

        try {
            foreach ($rows as $lineNumber => $row) {
                $line = $lineNumber + 2; // +2 because header is row 1

                // Skip completely empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                $data = $this->mapRow($row);

                // Validate required field
                if (empty($data['student_name'])) {
                    $errors[] = "Row {$line}: student_name is required — row skipped.";
                    $skipped++;
                    continue;
                }

                // Auto-generate admission number
                $year = date('Y');
                $count = Student::whereYear('created_at', $year)->count() + $imported + 1;
                $admissionNo = 'ADM-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

                // Ensure uniqueness in case of collision
                while (Student::where('admission_no', $admissionNo)->exists()) {
                    $count++;
                    $admissionNo = 'ADM-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
                }

                // Create student
                $student = Student::create([
                    'admission_no'      => $admissionNo,
                    'student_name'      => $data['student_name'],
                    'father_name'       => $data['father_name']       ?? null,
                    'mother_name'       => $data['mother_name']       ?? null,
                    'gender'            => in_array($data['gender'] ?? '', ['Male', 'Female'])
                                            ? $data['gender'] : null,
                    'date_of_birth'     => $this->parseDate($data['date_of_birth'] ?? null),
                    'blood_group'       => $data['blood_group']       ?? null,
                    'religion'          => $data['religion']          ?? null,
                    'b_form_no'         => $data['b_form_no']         ?? null,
                    'mobile_no'         => $data['mobile_no']         ?? null,
                    'whatsapp_no'       => $data['whatsapp_no']       ?? null,
                    'address'           => $data['address']           ?? null,
                    'guardian_name'     => $data['guardian_name']     ?? null,
                    'guardian_relation' => $data['guardian_relation'] ?? null,
                    'guardian_mobile'   => $data['guardian_mobile']   ?? null,
                    'father_occupation' => $data['father_occupation'] ?? null,
                    'admission_date'    => $this->parseDate($data['admission_date'] ?? null)
                                            ?? now()->toDateString(),
                    'previous_school'   => $data['previous_school']   ?? null,
                    'previous_class'    => $data['previous_class']    ?? null,
                    'student_image'     => null,
                    'is_active'         => 1,
                ]);

                // Create enrollment
                PaEnrollment::create([
                    'student_id'      => $student->id,
                    'class_id'        => $request->class_id,
                    'session_id'      => $request->session_id,
                    'roll_no'         => null,
                    'enrollment_date' => now()->toDateString(),
                    'status'          => 'active',
                    'is_active'       => 1,
                ]);

                $imported++;
            }

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()
                ->withErrors(['import_file' => 'Import failed: ' . $e->getMessage()])
                ->withInput();
        }

        $message = "{$imported} student(s) imported successfully.";
        if ($skipped > 0) {
            $message .= " {$skipped} row(s) skipped.";
        }

        return redirect()
            ->route('students.index')
            ->with('success', $message)
            ->with('import_errors', $errors);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Parse a CSV/TXT file into an array of associative rows.
     *
     * BOM-handling strategy:
     * The UTF-8 BOM (EF BB BF) can appear BEFORE the opening quote of the
     * first quoted header cell, e.g.  \xEF\xBB\xBF"student_name","father_name",...
     * When this happens PHP's fgetcsv() sees the BOM as part of the field value
     * and does NOT recognise the following " as a quote delimiter, so header[0]
     * comes back as the literal string  \xEF\xBB\xBF"student_name"  (with the
     * double-quotes included).  Stripping the BOM *after* fgetcsv() is therefore
     * useless — we must rewind and remove it from the raw byte stream first.
     */
    private function parseCsv(string $path): array
    {
        $rows   = [];
        $handle = fopen($path, 'r');

        if (! $handle) {
            return [];
        }

        // ── Strip UTF-8 BOM from the raw stream before any CSV parsing ──────
        // Peek at the first 3 bytes; if they are the BOM signature just consume
        // them so the file pointer now sits on the real first character.
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            // Not a BOM — rewind so we don't lose those bytes
            rewind($handle);
        }
        // If it IS the BOM we simply don't rewind; the pointer is already past it.

        // ── Read header row ──────────────────────────────────────────────────
        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            return [];
        }

        // Trim whitespace and any stray quote characters from every header cell
        // (belt-and-suspenders in case the file was saved with odd quoting)
        $header = array_map(function ($cell) {
            return trim(trim($cell), '"\'');
        }, $header);

        // ── Read data rows ───────────────────────────────────────────────────
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($header)) {
                $rows[] = array_combine($header, $row);
            } elseif (count($row) < count($header)) {
                // Pad short rows so array_combine doesn't fail
                $padded = array_pad($row, count($header), '');
                $rows[] = array_combine($header, $padded);
            }
            // rows with MORE columns than the header are silently dropped
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Parse an Excel file (.xlsx / .xls) using a pure-PHP approach.
     * We use PhpSpreadsheet if available, otherwise fall back to CSV-export.
     */
    private function parseExcel(string $path): array
    {
        // Try PhpSpreadsheet
        if (class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $sheet       = $spreadsheet->getActiveSheet();
            $data        = $sheet->toArray(null, true, true, false);

            if (empty($data)) {
                return [];
            }

            $header = array_map('trim', array_shift($data));
            $rows   = [];

            foreach ($data as $row) {
                if (count($row) === count($header)) {
                    $rows[] = array_combine($header, $row);
                }
            }

            return $rows;
        }

        // Fallback: tell user to use CSV
        throw new \RuntimeException(
            'Excel import requires PhpSpreadsheet. Please use a CSV file, '
            . 'or run: composer require phpoffice/phpspreadsheet'
        );
    }

    /**
     * Map a parsed row (keyed by column name) to student fields.
     * Accepts both exact column names and minor variations.
     */
    private function mapRow(array $row): array
    {
        // Normalise keys: lowercase + strip spaces
        $norm = [];
        foreach ($row as $k => $v) {
            $norm[strtolower(str_replace([' ', '-'], '_', trim($k)))] = trim($v);
        }

        return [
            'student_name'      => $norm['student_name']      ?? $norm['name']             ?? '',
            'father_name'       => $norm['father_name']        ?? $norm['father']           ?? '',
            'mother_name'       => $norm['mother_name']        ?? $norm['mother']           ?? '',
            'gender'            => ucfirst(strtolower($norm['gender'] ?? '')),
            'date_of_birth'     => $norm['date_of_birth']      ?? $norm['dob']              ?? '',
            'blood_group'       => $norm['blood_group']        ?? $norm['blood']            ?? '',
            'religion'          => $norm['religion']           ?? '',
            'b_form_no'         => $norm['b_form_no']          ?? $norm['b_form']           ?? '',
            'mobile_no'         => $norm['mobile_no']          ?? $norm['mobile']           ?? '',
            'whatsapp_no'       => $norm['whatsapp_no']        ?? $norm['whatsapp']         ?? '',
            'address'           => $norm['address']            ?? '',
            'guardian_name'     => $norm['guardian_name']      ?? $norm['guardian']         ?? '',
            'guardian_relation' => $norm['guardian_relation']  ?? $norm['relation']         ?? '',
            'guardian_mobile'   => $norm['guardian_mobile']    ?? '',
            'father_occupation' => $norm['father_occupation']  ?? $norm['occupation']       ?? '',
            'admission_date'    => $norm['admission_date']     ?? $norm['date_of_admission'] ?? '',
            'previous_school'   => $norm['previous_school']    ?? $norm['prev_school']      ?? '',
            'previous_class'    => $norm['previous_class']     ?? $norm['prev_class']       ?? '',
        ];
    }

    /**
     * Safely parse a date string; returns null on failure.
     */
    private function parseDate(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
