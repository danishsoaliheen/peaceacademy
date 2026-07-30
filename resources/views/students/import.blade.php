@extends('layouts.dashboard')

@section('content')

<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="fw-bold mb-1">
                <i class="fas fa-file-import me-2 text-primary"></i>
                Bulk Student Import
            </h2>
            <p class="text-muted mb-0">
                Upload a CSV or Excel file to register multiple students at once
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('students.import.sample') }}"
               class="btn btn-outline-success">
                <i class="fas fa-download me-1"></i>
                Download Sample CSV
            </a>
            <a href="{{ route('students.index') }}"
               class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Back to Students
            </a>
        </div>

    </div>

    {{-- Errors --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- LEFT: Upload Form --}}
        <div class="col-lg-7">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-upload me-2"></i>
                        Upload Student File
                    </h5>
                </div>

                <div class="card-body p-4">

                    <form method="POST"
                          action="{{ route('students.import.process') }}"
                          enctype="multipart/form-data"
                          id="importForm">

                        @csrf

                        {{-- File Upload --}}
                        <div class="mb-4">

                            <label class="form-label fw-semibold">
                                Student File
                                <span class="text-danger">*</span>
                            </label>

                            <div class="upload-area border-2 border-dashed rounded-3 text-center py-5 px-3 position-relative"
                                 id="uploadArea"
                                 style="border-color: #dee2e6; cursor:pointer; transition: all .2s;">

                                <input type="file"
                                       name="import_file"
                                       id="importFile"
                                       accept=".csv,.xlsx,.xls,.txt"
                                       class="position-absolute top-0 start-0 w-100 h-100 opacity-0"
                                       style="cursor:pointer; z-index:2;"
                                       required>

                                <div id="uploadPlaceholder">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3 d-block"></i>
                                    <p class="fw-semibold text-dark mb-1">
                                        Drag & drop your file here or click to browse
                                    </p>
                                    <small class="text-muted">
                                        Supported formats: <strong>CSV, Excel (.xlsx, .xls)</strong> — Max 5 MB
                                    </small>
                                </div>

                                <div id="uploadPreview" class="d-none">
                                    <i class="fas fa-file-csv fa-3x text-success mb-3 d-block"></i>
                                    <p class="fw-semibold text-success mb-1" id="fileName">—</p>
                                    <small class="text-muted" id="fileSize">—</small>
                                    <br>
                                    <small class="text-primary" style="cursor:pointer;" onclick="resetFile()">
                                        <i class="fas fa-redo me-1"></i>Choose different file
                                    </small>
                                </div>

                            </div>

                        </div>

                        {{-- Class Selection --}}
                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Assign to Class
                                <span class="text-danger">*</span>
                            </label>

                            <select name="class_id"
                                    class="form-select @error('class_id') is-invalid @enderror"
                                    required>

                                <option value="">— Select Class —</option>

                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}"
                                        {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->class_name }}
                                    </option>
                                @endforeach

                            </select>

                            @error('class_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <small class="text-muted">
                                All imported students will be enrolled in this class
                            </small>

                        </div>

                        {{-- Session Selection --}}
                        <div class="mb-4">

                            <label class="form-label fw-semibold">
                                Academic Session
                                <span class="text-danger">*</span>
                            </label>

                            <select name="session_id"
                                    class="form-select @error('session_id') is-invalid @enderror"
                                    required>

                                <option value="">— Select Session —</option>

                                @foreach($sessions as $session)
                                    <option value="{{ $session->id }}"
                                        {{ old('session_id') == $session->id ? 'selected' : '' }}>
                                        {{ $session->session_name }}
                                    </option>
                                @endforeach

                            </select>

                            @error('session_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                        </div>

                        {{-- Submit --}}
                        <div class="d-grid">

                            <button type="submit"
                                    class="btn btn-primary btn-lg"
                                    id="submitBtn">

                                <i class="fas fa-file-import me-2"></i>
                                Import Students

                            </button>

                        </div>

                        {{-- Progress Bar (hidden until submit) --}}
                        <div class="mt-3 d-none" id="progressSection">
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                     style="width: 100%"></div>
                            </div>
                            <small class="text-muted mt-1 d-block text-center">
                                Processing import, please wait…
                            </small>
                        </div>

                    </form>

                </div>

            </div>

        </div>

        {{-- RIGHT: Instructions --}}
        <div class="col-lg-5">

            {{-- How it works --}}
            <div class="card shadow-sm border-0 mb-4">

                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="fas fa-info-circle me-2"></i>
                        How It Works
                    </h6>
                </div>

                <div class="card-body">

                    <div class="d-flex mb-3">
                        <div class="step-badge bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                             style="width:32px;height:32px;font-weight:700;">1</div>
                        <div>
                            <strong>Download the Sample CSV</strong>
                            <p class="text-muted small mb-0">
                                Click "Download Sample CSV" to get a ready-to-fill template with correct column headers.
                            </p>
                        </div>
                    </div>

                    <div class="d-flex mb-3">
                        <div class="step-badge bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                             style="width:32px;height:32px;font-weight:700;">2</div>
                        <div>
                            <strong>Fill in Student Data</strong>
                            <p class="text-muted small mb-0">
                                Open in Excel or Google Sheets. Only <strong>student_name</strong> is required — all other columns are optional.
                            </p>
                        </div>
                    </div>

                    <div class="d-flex mb-3">
                        <div class="step-badge bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                             style="width:32px;height:32px;font-weight:700;">3</div>
                        <div>
                            <strong>Select Class & Session</strong>
                            <p class="text-muted small mb-0">
                                Choose which class and academic session all imported students will be enrolled in.
                            </p>
                        </div>
                    </div>

                    <div class="d-flex">
                        <div class="step-badge bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                             style="width:32px;height:32px;font-weight:700;">4</div>
                        <div>
                            <strong>Upload &amp; Import</strong>
                            <p class="text-muted small mb-0">
                                Admission numbers are auto-generated. Student photos can be added individually after import.
                            </p>
                        </div>
                    </div>

                </div>

            </div>

            {{-- Column Reference --}}
            <div class="card shadow-sm border-0">

                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="fas fa-table me-2"></i>
                        CSV Column Reference
                    </h6>
                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-sm table-hover mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Column Name</th>
                                    <th>Required</th>
                                    <th>Example</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td class="ps-3"><code>student_name</code></td>
                                    <td><span class="badge bg-danger">Required</span></td>
                                    <td class="text-muted small">Ali Hassan</td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><code>father_name</code></td>
                                    <td><span class="badge bg-secondary">Optional</span></td>
                                    <td class="text-muted small">Hassan Ahmed</td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><code>gender</code></td>
                                    <td><span class="badge bg-secondary">Optional</span></td>
                                    <td class="text-muted small">Male / Female</td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><code>date_of_birth</code></td>
                                    <td><span class="badge bg-secondary">Optional</span></td>
                                    <td class="text-muted small">2015-03-15</td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><code>mobile_no</code></td>
                                    <td><span class="badge bg-secondary">Optional</span></td>
                                    <td class="text-muted small">0300-1234567</td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><code>blood_group</code></td>
                                    <td><span class="badge bg-secondary">Optional</span></td>
                                    <td class="text-muted small">A+, B-, O+…</td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><code>admission_date</code></td>
                                    <td><span class="badge bg-secondary">Optional</span></td>
                                    <td class="text-muted small">2025-04-01</td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><code>address</code></td>
                                    <td><span class="badge bg-secondary">Optional</span></td>
                                    <td class="text-muted small">House 12, Karachi</td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><code>guardian_name</code></td>
                                    <td><span class="badge bg-secondary">Optional</span></td>
                                    <td class="text-muted small">Hassan Ahmed</td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><code>guardian_mobile</code></td>
                                    <td><span class="badge bg-secondary">Optional</span></td>
                                    <td class="text-muted small">0300-9876543</td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><code>previous_school</code></td>
                                    <td><span class="badge bg-secondary">Optional</span></td>
                                    <td class="text-muted small">City School</td>
                                </tr>
                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

{{-- JS: file preview + progress bar --}}
@push('scripts')
<script>
    const fileInput    = document.getElementById('importFile');
    const placeholder  = document.getElementById('uploadPlaceholder');
    const preview      = document.getElementById('uploadPreview');
    const fileNameEl   = document.getElementById('fileName');
    const fileSizeEl   = document.getElementById('fileSize');
    const uploadArea   = document.getElementById('uploadArea');
    const submitBtn    = document.getElementById('submitBtn');
    const progressSec  = document.getElementById('progressSection');

    fileInput.addEventListener('change', function () {
        if (this.files.length > 0) {
            showPreview(this.files[0]);
        }
    });

    uploadArea.addEventListener('dragover', function (e) {
        e.preventDefault();
        uploadArea.style.borderColor = '#0d6efd';
        uploadArea.style.backgroundColor = '#f0f5ff';
    });

    uploadArea.addEventListener('dragleave', function () {
        uploadArea.style.borderColor = '#dee2e6';
        uploadArea.style.backgroundColor = '';
    });

    uploadArea.addEventListener('drop', function (e) {
        e.preventDefault();
        uploadArea.style.borderColor = '#dee2e6';
        uploadArea.style.backgroundColor = '';
        if (e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            showPreview(e.dataTransfer.files[0]);
        }
    });

    function showPreview(file) {
        fileNameEl.textContent = file.name;
        fileSizeEl.textContent = (file.size / 1024).toFixed(1) + ' KB';
        placeholder.classList.add('d-none');
        preview.classList.remove('d-none');
        uploadArea.style.borderColor = '#198754';
        uploadArea.style.backgroundColor = '#f0fff4';
    }

    function resetFile() {
        fileInput.value = '';
        preview.classList.add('d-none');
        placeholder.classList.remove('d-none');
        uploadArea.style.borderColor = '#dee2e6';
        uploadArea.style.backgroundColor = '';
    }

    document.getElementById('importForm').addEventListener('submit', function () {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Importing…';
        progressSec.classList.remove('d-none');
    });
</script>
@endpush

@endsection
