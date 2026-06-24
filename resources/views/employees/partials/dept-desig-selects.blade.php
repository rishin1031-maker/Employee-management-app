@php
    $selectedDeptId  = old('department_id', $selectedDepartmentId ?? '');
    $selectedDesigId = old('designation_id', $selectedDesignationId ?? '');
    $designationList = $designations->map(fn ($d) => [
        'id'            => $d->id,
        'name'          => $d->name,
        'department_id' => $d->department_id,
    ])->values();
    $departmentList = $departments->map(fn ($d) => [
        'id'   => $d->id,
        'name' => $d->name,
    ])->values();
@endphp

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/css/tom-select.default.min.css">

<div>
    <div class="flex items-center justify-between mb-1">
        <label class="block text-sm font-medium text-gray-700">
            Department <span class="text-red-500">*</span>
        </label>
        <button type="button" id="open-add-dept-modal"
                class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
            <i class="fas fa-plus text-xs"></i> Add new
        </button>
    </div>
    <select name="department_id" id="department_id"
            class="w-full @error('department_id') ts-error @enderror">
        <option value="">— Select Department —</option>
        @foreach($departments as $dept)
            <option value="{{ $dept->id }}" {{ (string) $selectedDeptId === (string) $dept->id ? 'selected' : '' }}>
                {{ $dept->name }}
            </option>
        @endforeach
    </select>
    @error('department_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
</div>

<div>
    <div class="flex items-center justify-between mb-1">
        <label class="block text-sm font-medium text-gray-700">
            Designation <span class="text-red-500">*</span>
        </label>
        <button type="button" id="open-add-desig-modal"
                class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
            <i class="fas fa-plus text-xs"></i> Add new
        </button>
    </div>
    <select name="designation_id" id="designation_id"
            class="w-full @error('designation_id') ts-error @enderror">
        <option value="">— Select Designation —</option>
        @foreach($designations as $desig)
            <option value="{{ $desig->id }}"
                    data-dept="{{ $desig->department_id }}"
                    {{ (string) $selectedDesigId === (string) $desig->id ? 'selected' : '' }}>
                {{ $desig->name }}
            </option>
        @endforeach
    </select>
    @error('designation_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
</div>

{{-- Add Department Modal --}}
<div id="add-dept-modal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-xl">
        <h3 class="font-semibold text-gray-900 mb-4">Add Department</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                <input type="text" id="new-dept-name"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p id="new-dept-error" class="text-red-500 text-xs mt-1 hidden"></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="new-dept-desc" rows="2"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" id="save-new-dept"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2.5 rounded-lg transition">
                    Save Department
                </button>
                <button type="button" id="close-add-dept-modal"
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium py-2.5 rounded-lg transition">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Add Designation Modal --}}
<div id="add-desig-modal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-xl">
        <h3 class="font-semibold text-gray-900 mb-4">Add Designation</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Department <span class="text-red-500">*</span></label>
                <select id="new-desig-dept"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— Select Department —</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                <input type="text" id="new-desig-name"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p id="new-desig-error" class="text-red-500 text-xs mt-1 hidden"></p>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" id="save-new-desig"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2.5 rounded-lg transition">
                    Save Designation
                </button>
                <button type="button" id="close-add-desig-modal"
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium py-2.5 rounded-lg transition">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/js/tom-select.complete.min.js"></script>
<script>
(function () {
    const CSRF           = '{{ csrf_token() }}';
    const DEPT_STORE_URL = '{{ route('admin.departments.quick-store') }}';
    const DESIG_STORE_URL = '{{ route('admin.designations.quick-store') }}';

    let allDepartments   = @json($departmentList);
    let allDesignations  = @json($designationList);

    const deptTom = new TomSelect('#department_id', {
        placeholder: 'Search department…',
        allowEmptyOption: true,
        sortField: { field: 'text', direction: 'asc' },
    });

    const desigTom = new TomSelect('#designation_id', {
        placeholder: 'Search designation…',
        allowEmptyOption: true,
        sortField: { field: 'text', direction: 'asc' },
    });

    function filterDesignations(deptId, keepValue) {
        const current = keepValue ? desigTom.getValue() : '';
        desigTom.clear();
        desigTom.clearOptions();

        allDesignations
            .filter(d => !deptId || String(d.department_id) === String(deptId))
            .forEach(d => desigTom.addOption({ value: String(d.id), text: d.name }));

        desigTom.refreshOptions(false);

        if (current && allDesignations.some(d => String(d.id) === String(current) && (!deptId || String(d.department_id) === String(deptId)))) {
            desigTom.setValue(current, true);
        }
    }

    deptTom.on('change', function (deptId) {
        filterDesignations(deptId, false);
    });

    desigTom.on('change', function (desigId) {
        if (!desigId) return;
        const desig = allDesignations.find(d => String(d.id) === String(desigId));
        if (desig && String(deptTom.getValue()) !== String(desig.department_id)) {
            deptTom.setValue(String(desig.department_id), true);
            filterDesignations(desig.department_id, true);
        }
    });

    // Initial filter if department pre-selected
    const initialDept = deptTom.getValue();
    if (initialDept) {
        filterDesignations(initialDept, true);
    }

    // ── Modals ──────────────────────────────────────────────────────────────
    const deptModal  = document.getElementById('add-dept-modal');
    const desigModal = document.getElementById('add-desig-modal');

    document.getElementById('open-add-dept-modal').addEventListener('click', () => {
        document.getElementById('new-dept-name').value = '';
        document.getElementById('new-dept-desc').value = '';
        document.getElementById('new-dept-error').classList.add('hidden');
        deptModal.classList.remove('hidden');
    });
    document.getElementById('close-add-dept-modal').addEventListener('click', () => deptModal.classList.add('hidden'));
    deptModal.addEventListener('click', e => { if (e.target === deptModal) deptModal.classList.add('hidden'); });

    document.getElementById('open-add-desig-modal').addEventListener('click', () => {
        const deptSel = document.getElementById('new-desig-dept');
        deptSel.value = deptTom.getValue() || '';
        document.getElementById('new-desig-name').value = '';
        document.getElementById('new-desig-error').classList.add('hidden');
        desigModal.classList.remove('hidden');
    });
    document.getElementById('close-add-desig-modal').addEventListener('click', () => desigModal.classList.add('hidden'));
    desigModal.addEventListener('click', e => { if (e.target === desigModal) desigModal.classList.add('hidden'); });

    async function postJson(url, body) {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(body),
        });
        const data = await res.json();
        if (!res.ok) throw data;
        return data;
    }

    document.getElementById('save-new-dept').addEventListener('click', async () => {
        const name = document.getElementById('new-dept-name').value.trim();
        const errEl = document.getElementById('new-dept-error');
        errEl.classList.add('hidden');

        if (!name) {
            errEl.textContent = 'Department name is required.';
            errEl.classList.remove('hidden');
            return;
        }

        try {
            const data = await postJson(DEPT_STORE_URL, {
                name,
                description: document.getElementById('new-dept-desc').value.trim() || null,
            });

            allDepartments.push(data.department);
            deptTom.addOption({ value: String(data.department.id), text: data.department.name });
            deptTom.setValue(String(data.department.id), true);

            const deptSel = document.getElementById('new-desig-dept');
            const opt = document.createElement('option');
            opt.value = data.department.id;
            opt.textContent = data.department.name;
            deptSel.appendChild(opt);

            deptModal.classList.add('hidden');
        } catch (e) {
            errEl.textContent = e.message || (e.errors?.name?.[0]) || 'Could not create department.';
            errEl.classList.remove('hidden');
        }
    });

    document.getElementById('save-new-desig').addEventListener('click', async () => {
        const name = document.getElementById('new-desig-name').value.trim();
        const departmentId = document.getElementById('new-desig-dept').value;
        const errEl = document.getElementById('new-desig-error');
        errEl.classList.add('hidden');

        if (!departmentId) {
            errEl.textContent = 'Please select a department.';
            errEl.classList.remove('hidden');
            return;
        }
        if (!name) {
            errEl.textContent = 'Designation name is required.';
            errEl.classList.remove('hidden');
            return;
        }

        try {
            const data = await postJson(DESIG_STORE_URL, { name, department_id: departmentId });

            allDesignations.push(data.designation);
            deptTom.setValue(String(data.designation.department_id), true);
            filterDesignations(data.designation.department_id, false);
            desigTom.setValue(String(data.designation.id), true);

            desigModal.classList.add('hidden');
        } catch (e) {
            const msg = e.message
                || (e.errors?.name?.[0])
                || (e.errors?.department_id?.[0])
                || 'Could not create designation.';
            errEl.textContent = msg;
            errEl.classList.remove('hidden');
        }
    });
})();
</script>

<style>
    .ts-wrapper.single .ts-control { min-height: 42px; padding: 8px 12px; border-radius: 0.5rem; border-color: #d1d5db; }
    .ts-wrapper.single.focus .ts-control { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99,102,241,.25); }
    .ts-error + .ts-wrapper.single .ts-control { border-color: #f87171; }
</style>
