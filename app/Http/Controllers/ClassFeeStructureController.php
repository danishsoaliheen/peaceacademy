<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\ClassFeeStructure;
use App\Models\PaClass;
use App\Models\FeeType;

class ClassFeeStructureController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Sortable columns whitelist
    |--------------------------------------------------------------------------
    |
    | Maps a safe "sort" key (as seen in the URL) to the actual SQL column
    | it maps to. Never interpolate $request->sort directly into orderBy() —
    | always go through this whitelist so an arbitrary column name can't be
    | injected via the query string.
    |--------------------------------------------------------------------------
    */

    private const SORTABLE_COLUMNS = [
        'class'      => 'pa_classes.class_name',
        'fee_type'   => 'fee_types.name',
        'amount'     => 'class_fee_structures.amount',
        'mandatory'  => 'class_fee_structures.is_mandatory',
        'discount'   => 'class_fee_structures.allow_discount',
        'status'     => 'class_fee_structures.is_active',
    ];

    // Sensible default direction the first time a column is clicked.
    private const SORT_DEFAULT_DIRECTIONS = [
        'class'      => 'asc',
        'fee_type'   => 'asc',
        'amount'     => 'desc',
        'mandatory'  => 'desc',
        'discount'   => 'desc',
        'status'     => 'desc',
    ];

    /**
     * Validate sort/direction from the request against the whitelist.
     * An empty/invalid sort falls back to '' (no explicit column sort),
     * which index() treats as "use the original class_id default".
     */
    private function resolveSort(Request $request): array
    {
        $sort = $request->string('sort')->toString();
        if ($sort !== '' && !array_key_exists($sort, self::SORTABLE_COLUMNS)) {
            $sort = '';
        }

        $direction = strtolower($request->string('direction')->toString());
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = $sort !== '' ? self::SORT_DEFAULT_DIRECTIONS[$sort] : 'asc';
        }

        return [$sort, $direction];
    }

    /**
     * Apply the whitelisted sort to the query, with class name as a stable
     * secondary sort so rows with equal values don't jump around between
     * page loads.
     */
    private function applySort($query, string $sort, string $direction)
    {
        if ($sort === '') {
            return $query->orderBy('class_fee_structures.class_id');
        }

        $query->orderBy(self::SORTABLE_COLUMNS[$sort], $direction);

        if ($sort !== 'class') {
            $query->orderBy('pa_classes.class_name', 'asc');
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | LIST
    |--------------------------------------------------------------------------
    */
public function index(Request $request)
{
    $classFeeTable = (new ClassFeeStructure())->getTable();
    $classTable    = (new PaClass())->getTable();
    $feeTypeTable  = (new FeeType())->getTable();

    [$sort, $direction] = $this->resolveSort($request);

    $query = ClassFeeStructure::query()
        ->join($classTable, "$classTable.id", '=', "$classFeeTable.class_id")
        ->join($feeTypeTable, "$feeTypeTable.id", '=', "$classFeeTable.fee_type_id")
        ->select("$classFeeTable.*")
        ->with(['class', 'feeType']);

    // Search
    if ($request->filled('search')) {

        $search = $request->search;

        $query->where(function ($q) use ($search, $classTable, $feeTypeTable, $classFeeTable) {

            $q->where("$classTable.class_name", 'like', "%{$search}%")
              ->orWhere("$feeTypeTable.name", 'like', "%{$search}%")
              ->orWhere("$classFeeTable.amount", 'like', "%{$search}%");

        });
    }

    // Class Filter
    if ($request->filled('class_id')) {

        $query->where(
            "$classFeeTable.class_id",
            $request->class_id
        );
    }

    // Fee Type Filter
    if ($request->filled('fee_type_id')) {

        $query->where(
            "$classFeeTable.fee_type_id",
            $request->fee_type_id
        );
    }

    // Status Filter
    if ($request->status !== null &&
        $request->status !== '') {

        $query->where(
            "$classFeeTable.is_active",
            $request->status
        );
    }

    $this->applySort($query, $sort, $direction);

    $structures = $query
        ->paginate(25)
        ->withQueryString();

    $classes = PaClass::orderBy('class_order')->get();

    $feeTypes = FeeType::orderBy('name')->get();

    return view(
        'class_fee_structures.index',
        compact(
            'structures',
            'classes',
            'feeTypes',
            'sort',
            'direction'
        )
    );
}

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $classes = PaClass::all();

        $feeTypes = FeeType::where(
            'is_active',
            1
        )->get();

        return view(
            'class_fee_structures.create',
            compact(
                'classes',
                'feeTypes'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([

            'class_id'    => 'required|exists:pa_classes,id',
            'fee_type_id' => 'required|exists:fee_types,id',
            'amount'      => 'required|numeric|min:0',

        ]);

        ClassFeeStructure::create([

            'class_id'       => $request->class_id,
            'fee_type_id'    => $request->fee_type_id,
            'amount'         => $request->amount,
            'is_mandatory'   => $request->has('is_mandatory'),
            'allow_discount' => $request->has('allow_discount'),
            'is_active'      => 1,
            'notes'          => $request->notes,

        ]);

        return redirect()
            ->route('class-fee-structures.index')
            ->with('success', 'Fee Structure Created Successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $structure = ClassFeeStructure::findOrFail($id);

        $classes = PaClass::all();

        $feeTypes = FeeType::where(
            'is_active',
            1
        )->get();

        return view(
            'class_fee_structures.edit',
            compact(
                'structure',
                'classes',
                'feeTypes'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $request->validate([

            'class_id'    => 'required|exists:pa_classes,id',
            'fee_type_id' => 'required|exists:fee_types,id',
            'amount'      => 'required|numeric|min:0',

        ]);

        $structure = ClassFeeStructure::findOrFail($id);

        $structure->update([

            'class_id'       => $request->class_id,
            'fee_type_id'    => $request->fee_type_id,
            'amount'         => $request->amount,
            'is_mandatory'   => $request->has('is_mandatory'),
            'allow_discount' => $request->has('allow_discount'),
            'is_active'      => $request->has('is_active'),
            'notes'          => $request->notes,

        ]);

        return redirect()
            ->route('class-fee-structures.index')
            ->with('success', 'Fee Structure Updated Successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        ClassFeeStructure::findOrFail($id)->delete();

        return redirect()
            ->route('class-fee-structures.index')
            ->with('success', 'Fee Structure Deleted Successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | BULK CREATE (manual row form)
    |--------------------------------------------------------------------------
    */

    public function bulkCreate()
    {
        $classes = PaClass::where(
            'is_active',
            1
        )->orderBy('class_order')->get();

        $feeTypes = FeeType::where(
            'is_active',
            1
        )->orderBy('name')
         ->get();

        return view(
            'class_fee_structures.bulk_create',
            compact(
                'classes',
                'feeTypes'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BULK STORE (manual row form)
    |--------------------------------------------------------------------------
    */

    public function bulkStore(Request $request)
    {
        $request->validate([

            'class_id' => 'required|exists:pa_classes,id',

        ]);

        /*
        |----------------------------------------------------------------------
        | FIX: Use upsert inside a transaction instead of delete-then-create.
        | Previously, deleting ALL structures before recreating meant that if
        | any row failed, the old data was already gone.
        |----------------------------------------------------------------------
        */

        DB::transaction(function () use ($request) {

            $classId   = $request->class_id;
            $submitted = [];

            if ($request->fee_type_id) {

                foreach ($request->fee_type_id as $index => $feeTypeId) {

                    $amount = $request->amount[$index] ?? 0;

                    if (empty($feeTypeId) || $amount <= 0) {
                        continue;
                    }

                    $submitted[$feeTypeId] = [

                        'class_id'       => $classId,
                        'fee_type_id'    => $feeTypeId,
                        'amount'         => $amount,
                        'is_mandatory'   => isset($request->is_mandatory[$index]) ? 1 : 0,
                        'allow_discount' => isset($request->allow_discount[$index]) ? 1 : 0,
                        'is_active'      => 1,
                        'notes'          => $request->notes[$index] ?? null,

                    ];
                }
            }

            /*
            |----------------------------------------------------------------------
            | Delete only structures for fee types NOT in the new submission,
            | then upsert the submitted ones.
            |----------------------------------------------------------------------
            */

            $feeTypeIds = array_keys($submitted);

            if (!empty($feeTypeIds)) {
                ClassFeeStructure::where('class_id', $classId)
                    ->whereNotIn('fee_type_id', $feeTypeIds)
                    ->delete();
            } else {
                ClassFeeStructure::where('class_id', $classId)->delete();
            }

            foreach ($submitted as $row) {
                ClassFeeStructure::updateOrCreate(
                    ['class_id' => $row['class_id'], 'fee_type_id' => $row['fee_type_id']],
                    $row
                );
            }
        });

        return redirect()
            ->route('class-fee-structures.index')
            ->with('success', 'Bulk Fee Structure Saved Successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT PAGE
    |--------------------------------------------------------------------------
    */

    public function importForm()
    {
        return view('class_fee_structures.import');
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT PROCESS (CSV / Excel)
    |--------------------------------------------------------------------------
    */

    public function importStore(Request $request)
    {
        $request->validate([

            'file' => 'required|file|mimes:csv,txt,xlsx,xls',

        ]);

        $file      = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        /*
        |--------------------------------------------------------------------------
        | Parse rows depending on file type
        |--------------------------------------------------------------------------
        */

        if (in_array($extension, ['xlsx', 'xls'])) {

            $rows = $this->parseExcel($file->getRealPath());

        } else {

            $rows = $this->parseCsv($file->getRealPath());

        }

        /*
        |--------------------------------------------------------------------------
        | Build lookup maps (case-insensitive name → id)
        |--------------------------------------------------------------------------
        */

        $classMap = PaClass::all()->keyBy(function ($c) {
            return strtolower(trim($c->class_name));
        })->map(fn($c) => $c->id);

        $feeTypeMap = FeeType::all()->keyBy(function ($f) {
            return strtolower(trim($f->name));
        })->map(fn($f) => $f->id);

        /*
        |--------------------------------------------------------------------------
        | Process each row
        |--------------------------------------------------------------------------
        */

        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($rows as $rowNum => $row) {

            // Expected columns: class_name, fee_type, amount,
            //                   is_mandatory, allow_discount, notes

            $className   = strtolower(trim($row['class_name']   ?? $row[0] ?? ''));
            $feeTypeName = strtolower(trim($row['fee_type']      ?? $row[1] ?? ''));
            $amount      = trim($row['amount']       ?? $row[2] ?? '');
            $isMandatory = trim($row['is_mandatory'] ?? $row[3] ?? '1');
            $allowDisc   = trim($row['allow_discount']?? $row[4] ?? '0');
            $notes       = trim($row['notes']        ?? $row[5] ?? '');

            /*
            |----------------------------------------------------------------------
            | Skip blank rows
            |----------------------------------------------------------------------
            */

            if ($className === '' && $feeTypeName === '' && $amount === '') {
                continue;
            }

            /*
            |----------------------------------------------------------------------
            | Validate class
            |----------------------------------------------------------------------
            */

            if (!isset($classMap[$className])) {
                $className_display = $row['class_name'] ?? $row[0] ?? '';
                $errors[] = "Row {$rowNum}: Class \"{$className_display}\" not found.";
                $skipped++;
                continue;
            }

            /*
            |----------------------------------------------------------------------
            | Validate fee type
            |----------------------------------------------------------------------
            */

            if (!isset($feeTypeMap[$feeTypeName])) {
                $feeType_display = $row['fee_type'] ?? $row[1] ?? '';
                $errors[] = "Row {$rowNum}: Fee Type \"{$feeType_display}\" not found.";
                $skipped++;
                continue;
            }

            /*
            |----------------------------------------------------------------------
            | Validate amount
            |----------------------------------------------------------------------
            */

            if (!is_numeric($amount) || $amount < 0) {
                $errors[] = "Row {$rowNum}: Invalid amount \"{$amount}\".";
                $skipped++;
                continue;
            }

            /*
            |----------------------------------------------------------------------
            | Upsert — update if same class+fee_type already exists
            |----------------------------------------------------------------------
            */

            ClassFeeStructure::updateOrCreate(

                [
                    'class_id'    => $classMap[$className],
                    'fee_type_id' => $feeTypeMap[$feeTypeName],
                ],

                [
                    'amount'         => (float) $amount,
                    'is_mandatory'   => in_array(strtolower($isMandatory), ['1', 'yes', 'true', 'y']),
                    'allow_discount' => in_array(strtolower($allowDisc),   ['1', 'yes', 'true', 'y']),
                    'is_active'      => 1,
                    'notes'          => $notes ?: null,
                ]

            );

            $imported++;
        }

        /*
        |--------------------------------------------------------------------------
        | Redirect with summary
        |--------------------------------------------------------------------------
        */

        $message = "{$imported} record(s) imported successfully.";

        if ($skipped > 0) {
            $message .= " {$skipped} row(s) skipped.";
        }

        return redirect()
            ->route('class-fee-structures.index')
            ->with('success', $message)
            ->with('import_errors', $errors);
    }

    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD SAMPLE CSV
    |--------------------------------------------------------------------------
    */

    public function sampleCsv()
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="fee_structure_sample.csv"',
        ];

        $callback = function () {

            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, [
                'class_name',
                'fee_type',
                'amount',
                'is_mandatory',
                'allow_discount',
                'notes',
            ]);

            // Sample rows
            fputcsv($handle, ['Class 1', 'Monthly Fee', '1500', '1', '0', 'Monthly tuition fee']);
            fputcsv($handle, ['Class 2', 'Admission Fee', '5000', '1', '0', '']);
            fputcsv($handle, ['Class 3', 'Transport Fee', '800', '0', '1', 'Optional transport']);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function parseCsv(string $path): array
    {
        $rows    = [];
        $headers = null;

        if (($handle = fopen($path, 'r')) === false) {
            return [];
        }

        // Strip UTF-8 BOM (\xEF\xBB\xBF) — present in Excel-saved CSVs
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $rowNum = 1;

        while (($line = fgetcsv($handle)) !== false) {

            if ($headers === null) {
                // Normalise header names
                $headers = array_map(
                    fn($h) => strtolower(trim(str_replace(' ', '_',
                        preg_replace('/^\xEF\xBB\xBF/', '', $h)
                    ))),
                    $line
                );
                $rowNum++;
                continue;
            }

            // If headers were recognised, map by name; else keep numeric
            if ($headers) {
                $row = array_combine($headers, array_pad($line, count($headers), ''));
            } else {
                $row = $line;
            }

            $rows[$rowNum] = $row;
            $rowNum++;
        }

        fclose($handle);

        return $rows;
    }

    private function parseExcel(string $path): array
    {
        // Uses PhpSpreadsheet if available, else falls back to parsing as CSV
        if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
            return $this->parseCsv($path);
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheet       = $spreadsheet->getActiveSheet();
        $data        = $sheet->toArray(null, true, true, false);

        if (empty($data)) {
            return [];
        }

        $headers = array_map(
            fn($h) => strtolower(trim(str_replace(' ', '_', (string) $h))),
            array_shift($data)
        );

        $rows   = [];
        $rowNum = 2;

        foreach ($data as $line) {
            $row = array_combine($headers, array_pad($line, count($headers), ''));
            $rows[$rowNum] = array_map(fn($v) => (string)($v ?? ''), $row);
            $rowNum++;
        }

        return $rows;
    }
}