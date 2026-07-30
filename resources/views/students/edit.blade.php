@extends('layouts.dashboard')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.page-hero {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    border-radius: 12px;
    color: #fff;
    padding: 26px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.page-hero::before {
    content: '';
    position: absolute;
    top: -50px; right: -50px;
    width: 180px; height: 180px;
    border-radius: 50%;
    background: rgba(255,255,255,.05);
    pointer-events: none;
}
.page-hero h2 { font-size: 1.3rem; font-weight: 700; margin: 0 0 4px; }
.page-hero p  { margin: 0; opacity: .7; font-size: .84rem; }

.adm-chip {
    background: rgba(255,255,255,.15);
    border-radius: 6px;
    padding: 5px 14px;
    font-size: .82rem;
    font-weight: 600;
    letter-spacing: .3px;
}

/* ── Section cards ────────────────────────────────────────────────────── */
.section-card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 1px 6px rgba(0,0,0,.08);
    margin-bottom: 20px;
    overflow: hidden;
}
.section-card .card-header {
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.section-card .card-header .s-icon {
    width: 28px; height: 28px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px; color: #fff;
    flex-shrink: 0;
}
.section-card .card-header h6 {
    margin: 0; font-weight: 700; font-size: .9rem; color: #1e293b;
}
.section-card .card-body { padding: 20px; }

/* ── Form controls ────────────────────────────────────────────────────── */
.form-label {
    font-size: .8rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 5px;
}
.form-control, .form-select {
    border-radius: 8px;
    font-size: .875rem;
    border-color: #e2e8f0;
    padding: 8px 12px;
}
.form-control:focus, .form-select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,.12);
}
.form-control[readonly] {
    background: #f8fafc;
    color: #94a3b8;
}

/* ── Photo box ────────────────────────────────────────────────────────── */
.photo-box {
    border: 2px dashed #e2e8f0;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    background: #f8fafc;
}
.photo-box img, .photo-box .initials-lg {
    width: 120px; height: 120px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 12px;
}
.photo-box .initials-lg {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    font-weight: 700;
}

/* ── Sticky sidebar ───────────────────────────────────────────────────── */
@media (min-width: 992px) {
    .sticky-side { position: sticky; top: 20px; }
}

.btn-save {
    background: linear-gradient(135deg, #1e293b, #334155);
    color: #fff;
    border: none;
    border-radius: 9px;
    padding: 12px;
    font-weight: 700;
    font-size: .95rem;
    width: 100%;
    transition: opacity .2s;
}
.btn-save:hover { opacity: .88; color: #fff; }
</style>

{{-- ── Hero ─────────────────────────────────────────────────────────────── --}}
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

        <div>
            <h2><i class="fas fa-user-edit me-2" style="opacity:.8;"></i>Edit Student Record</h2>
            <p>Update profile and admission information</p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="adm-chip">
                <i class="fas fa-id-badge me-1" style="opacity:.7;"></i>{{ $student->admission_no }}
            </span>
            <a href="{{ route('students.show', $student->id) }}"
               class="btn btn-sm" style="background:rgba(255,255,255,.12); color:#fff; border-radius:8px; font-weight:600; border:1px solid rgba(255,255,255,.2);">
                <i class="fas fa-eye me-1"></i> View Profile
            </a>
            <a href="{{ route('students.index') }}"
               class="btn btn-sm" style="background:rgba(255,255,255,.12); color:#fff; border-radius:8px; font-weight:600; border:1px solid rgba(255,255,255,.2);">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>

    </div>
</div>

{{-- ── Alerts ───────────────────────────────────────────────────────────── --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" style="border-radius:8px; font-size:.875rem;">
        <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" style="border-radius:8px; font-size:.875rem;">
        <strong><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ── Form ─────────────────────────────────────────────────────────────── --}}
<form method="POST" action="{{ route('students.update', $student->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-4">

        {{-- ════ LEFT — main fields ════ --}}
        <div class="col-lg-8">

            {{-- Personal Information --}}
            <div class="section-card card">
                <div class="card-header">
                    <span class="s-icon" style="background:#3b82f6;"><i class="fas fa-user"></i></span>
                    <h6>Personal Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-4">
                            <label class="form-label">Admission No</label>
                            <input type="text" name="admission_no" class="form-control"
                                   value="{{ old('admission_no', $student->admission_no) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Admission Date</label>
                            <input type="date" name="admission_date" class="form-control"
                                   value="{{ old('admission_date', $student->admission_date) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">— Select —</option>
                                <option value="Male"   {{ old('gender', $student->gender) == 'Male'   ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender', $student->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Student Name <span class="text-danger">*</span></label>
                            <input type="text" name="student_name" class="form-control @error('student_name') is-invalid @enderror"
                                   value="{{ old('student_name', $student->student_name) }}" required>
                            @error('student_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Father Name</label>
                            <input type="text" name="father_name" class="form-control"
                                   value="{{ old('father_name', $student->father_name) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Mother Name</label>
                            <input type="text" name="mother_name" class="form-control"
                                   value="{{ old('mother_name', $student->mother_name) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control"
                                   value="{{ old('date_of_birth', $student->date_of_birth) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Blood Group</label>
                            <select name="blood_group" class="form-select">
                                <option value="">— Select —</option>
                                @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                                    <option value="{{ $bg }}" {{ old('blood_group', $student->blood_group) == $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Religion</label>
                            <input type="text" name="religion" class="form-control"
                                   value="{{ old('religion', $student->religion) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">B-Form / CNIC No</label>
                            <input type="text" name="b_form_no" class="form-control"
                                   value="{{ old('b_form_no', $student->b_form_no) }}"
                                   placeholder="XXXXX-XXXXXXX-X">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Father Occupation</label>
                            <input type="text" name="father_occupation" class="form-control"
                                   value="{{ old('father_occupation', $student->father_occupation) }}">
                        </div>

                    </div>
                </div>
            </div>

            {{-- Contact Information --}}
            <div class="section-card card">
                <div class="card-header">
                    <span class="s-icon" style="background:#10b981;"><i class="fas fa-phone"></i></span>
                    <h6>Contact Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Father Mobile Number</label>
                            <input type="text" name="mobile_no" class="form-control"
                                   value="{{ old('mobile_no', $student->mobile_no) }}"
                                   placeholder="03XX-XXXXXXX">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Mother Mobile Number</label>
                            <input type="text" name="mother_mobile_no" class="form-control"
                                   value="{{ old('mother_mobile_no', $student->mother_mobile_no) }}"
                                   placeholder="03XX-XXXXXXX">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Father WhatsApp Number</label>
                            <input type="text" name="whatsapp_no" class="form-control"
                                   value="{{ old('whatsapp_no', $student->whatsapp_no) }}"
                                   placeholder="03XX-XXXXXXX">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Mother WhatsApp Number</label>
                            <input type="text" name="mother_whatsapp_no" class="form-control"
                                   value="{{ old('mother_whatsapp_no', $student->mother_whatsapp_no) }}"
                                   placeholder="03XX-XXXXXXX">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Emergency Contact</label>
                            <input type="text" name="emergency_contact" class="form-control"
                                   value="{{ old('emergency_contact', $student->emergency_contact) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"
                                      placeholder="Full home address…">{{ old('address', $student->address) }}</textarea>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Guardian Information --}}
            <div class="section-card card">
                <div class="card-header">
                    <span class="s-icon" style="background:#f59e0b;"><i class="fas fa-users"></i></span>
                    <h6>Guardian / Parent Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-4">
                            <label class="form-label">Guardian Name</label>
                            <input type="text" name="guardian_name" class="form-control"
                                   value="{{ old('guardian_name', $student->guardian_name) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Relation</label>
                            <input type="text" name="guardian_relation" class="form-control"
                                   value="{{ old('guardian_relation', $student->guardian_relation) }}"
                                   placeholder="Father / Mother / Uncle…">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Guardian Mobile</label>
                            <input type="text" name="guardian_mobile" class="form-control"
                                   value="{{ old('guardian_mobile', $student->guardian_mobile) }}">
                        </div>

                    </div>
                </div>
            </div>

            {{-- Academic --}}
            <div class="section-card card">
                <div class="card-header">
                    <span class="s-icon" style="background:#8b5cf6;"><i class="fas fa-graduation-cap"></i></span>
                    <h6>Previous Academic Info</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Previous School</label>
                            <input type="text" name="previous_school" class="form-control"
                                   value="{{ old('previous_school', $student->previous_school) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Previous Class</label>
                            <input type="text" name="previous_class" class="form-control"
                                   value="{{ old('previous_class', $student->previous_class) }}">
                        </div>

                    </div>
                </div>
            </div>

            {{-- Family / Siblings --}}
            <div class="section-card card">
                <div class="card-header">
                    <span class="s-icon" style="background:#ec4899;"><i class="fas fa-people-roof"></i></span>
                    <h6>Family / Siblings</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-5">
                            <label class="form-label">Family Code</label>
                            <input type="text" name="family_code" id="family_code" class="form-control"
                                   value="{{ old('family_code', $student->family_code) }}"
                                   placeholder="Auto-generated if left blank">
                            <small class="text-muted d-block mt-1">
                                Students sharing the same code are treated as siblings.
                            </small>
                        </div>

                        <div class="col-md-7" style="position:relative;">
                            <label class="form-label">Link an Existing Sibling</label>
                            <input type="text" id="siblingSearch" class="form-control"
                                   placeholder="Search by name or admission no…" autocomplete="off">
                            <div id="siblingResults" class="list-group shadow-sm"
                                 style="position:absolute; z-index:20; width:100%;"></div>
                            <small class="text-muted d-block mt-1">
                                Search a sibling already in the system to reuse their Family Code.
                            </small>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        {{-- ════ RIGHT — photo, status, save ════ --}}
        <div class="col-lg-4">
            <div class="sticky-side">

                {{-- Photo --}}
                <div class="section-card card">
                    <div class="card-header">
                        <span class="s-icon" style="background:#0ea5e9;"><i class="fas fa-camera"></i></span>
                        <h6>Student Photo</h6>
                    </div>
                    <div class="card-body">
                        <div class="photo-box" id="photoBox">
                            @if($student->photo_url)
                                <img src="{{ $student->photo_url }}"
                                     id="photoPreview" alt="Student Photo">
                            @else
                                <div class="initials-lg" id="photoInitials">
                                    {{ strtoupper(substr($student->student_name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <label class="form-label d-block mb-2">Upload New Photo</label>
                                <input type="file" name="student_image" id="photoInput"
                                       class="form-control" accept="image/jpeg,image/png">
                                <small class="text-muted d-block mt-1">JPG or PNG, max 2 MB</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Status --}}
                <div class="section-card card">
                    <div class="card-header">
                        <span class="s-icon" style="background:#64748b;"><i class="fas fa-toggle-on"></i></span>
                        <h6>Student Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch" style="padding-left: 3rem;">
                            <input class="form-check-input" type="checkbox" name="is_active"
                                   value="1" id="statusToggle"
                                   {{ $student->is_active ? 'checked' : '' }}
                                   style="width:2.5rem; height:1.3rem;">
                            <label class="form-check-label fw-semibold ms-2" for="statusToggle"
                                   style="font-size:.9rem; line-height:1.3rem;">
                                Active Student
                            </label>
                        </div>
                        <small class="text-muted d-block mt-2" style="font-size:.76rem;">
                            Inactive students are hidden from class lists and fee generation.
                        </small>
                    </div>
                </div>

                {{-- Save --}}
                <button type="submit" class="btn-save">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>

                <a href="{{ route('students.show', $student->id) }}"
                   class="btn btn-outline-secondary w-100 mt-2"
                   style="border-radius:9px; font-weight:600;">
                    Cancel
                </a>

            </div>
        </div>

    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Live photo preview on file select
document.getElementById('photoInput').addEventListener('change', function () {
    if (!this.files.length) return;
    const url = URL.createObjectURL(this.files[0]);
    const box = document.getElementById('photoBox');
    // Remove initials div if present
    const initials = document.getElementById('photoInitials');
    if (initials) initials.remove();
    // Show / update preview img
    let img = document.getElementById('photoPreview');
    if (!img) {
        img = document.createElement('img');
        img.id = 'photoPreview';
        img.style.cssText = 'width:120px;height:120px;border-radius:50%;object-fit:cover;margin-bottom:12px;';
        box.insertBefore(img, box.firstChild);
    }
    img.src = url;
});

// ── Sibling search / link widget ────────────────────────────────────────
(function () {
    const searchInput = document.getElementById('siblingSearch');
    const resultsBox   = document.getElementById('siblingResults');
    const codeInput    = document.getElementById('family_code');
    const csrfToken    = document.querySelector('input[name="_token"]').value;
    const excludeId    = {{ $student->id }};
    let debounceTimer;

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        const q = this.value.trim();
        if (q.length < 2) { resultsBox.innerHTML = ''; return; }

        debounceTimer = setTimeout(function () {
            fetch('{{ route('students.search') }}?q=' + encodeURIComponent(q) + '&exclude_id=' + excludeId)
                .then(r => r.json())
                .then(renderResults);
        }, 300);
    });

    function renderResults(data) {
        if (!data.length) {
            resultsBox.innerHTML = '<div class="list-group-item text-muted small">No matches found</div>';
            return;
        }

        resultsBox.innerHTML = data.map(function (s) {
            return '<button type="button" class="list-group-item list-group-item-action small sibling-pick" data-id="' + s.id + '">' +
                '<strong>' + s.student_name + '</strong> — ' + s.admission_no +
                (s.class_name ? ' · ' + s.class_name : '') +
                (s.family_code ? ' <span class="badge bg-light text-dark">' + s.family_code + '</span>' : '') +
                '</button>';
        }).join('');

        resultsBox.querySelectorAll('.sibling-pick').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const sid = this.dataset.id;
                fetch('/students/' + sid + '/family-code', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(function (res) {
                    codeInput.value = res.family_code;
                    resultsBox.innerHTML = '';
                    searchInput.value = '';
                });
            });
        });
    }

    document.addEventListener('click', function (e) {
        if (!resultsBox.contains(e.target) && e.target !== searchInput) {
            resultsBox.innerHTML = '';
        }
    });
})();
</script>

@endsection