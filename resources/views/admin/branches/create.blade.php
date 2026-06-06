@extends('admin.layouts.app')

@section('title', 'Create Branch')

@section('content')

{{-- Page Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Create New Branch</h1>
        <p class="text-sm text-gray-500 mt-1">Add a new restaurant branch to the system.</p>
    </div>
    <a href="{{ route('admin.branches.index') }}"
        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors shadow-sm text-sm font-medium">
        <i class="fas fa-arrow-left mr-2 text-gray-400"></i>Back to Branches
    </a>
</div>

@if($errors->any())
<div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4 flex items-start gap-3">
    <i class="fas fa-circle-exclamation text-red-500 mt-0.5"></i>
    <div>
        <p class="text-sm font-medium text-red-700">Please fix the following errors:</p>
        <ul class="mt-1 list-disc list-inside text-sm text-red-600 space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<form action="{{ route('admin.branches.store') }}" method="POST">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT: Preview Card --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- Branch Preview --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col items-center text-center">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center shadow-md mb-4">
                    <i class="fas fa-store text-white text-3xl"></i>
                </div>
                <p id="previewName" class="text-gray-400 text-sm italic">Branch name will appear here</p>
                <p id="previewLocation" class="text-xs text-gray-400 mt-1 flex items-center gap-1 justify-center">
                    <i class="fas fa-location-dot text-gray-300 text-xs"></i>
                    <span>No address set</span>
                </p>
                <div class="mt-4 w-full border-t border-gray-100 pt-4 grid grid-cols-2 gap-3">
                    <div class="text-center">
                        <p class="text-xs text-gray-400">Manager</p>
                        <p id="previewManager" class="text-sm font-medium text-gray-600 mt-0.5 truncate">—</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-gray-400">Status</p>
                        <span id="previewStatus"
                            class="inline-flex items-center gap-1 mt-0.5 text-xs font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700">
                            <i class="fas fa-circle text-[8px]"></i> Active
                        </span>
                    </div>
                </div>
            </div>

            {{-- Status Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fas fa-toggle-on text-emerald-500"></i> Branch Status
                </h3>
                <div class="flex gap-3">
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="status" value="active" class="sr-only peer"
                            {{ old('status', 'active') === 'active' ? 'checked' : '' }}
                            onchange="updateStatusPreview('active')">
                        <div class="peer-checked:bg-green-50 peer-checked:border-green-400 peer-checked:text-green-700
                            border border-gray-200 rounded-xl px-3 py-3 text-center text-sm font-medium text-gray-500
                            transition hover:border-green-300 hover:bg-green-50">
                            <i class="fas fa-circle-check text-lg mb-1 block"></i>
                            Active
                        </div>
                    </label>
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="status" value="inactive" class="sr-only peer"
                            {{ old('status') === 'inactive' ? 'checked' : '' }}
                            onchange="updateStatusPreview('inactive')">
                        <div class="peer-checked:bg-red-50 peer-checked:border-red-400 peer-checked:text-red-700
                            border border-gray-200 rounded-xl px-3 py-3 text-center text-sm font-medium text-gray-500
                            transition hover:border-red-300 hover:bg-red-50">
                            <i class="fas fa-circle-xmark text-lg mb-1 block"></i>
                            Inactive
                        </div>
                    </label>
                </div>
            </div>

            {{-- Tips --}}
            <div class="bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100 rounded-xl p-4 space-y-2.5">
                <p class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Quick Tips</p>
                <div class="flex items-start gap-2 text-xs text-emerald-700">
                    <i class="fas fa-circle-check mt-0.5 text-emerald-400"></i>
                    <span>Use a descriptive name like "Downtown Branch" or "Mall Outlet".</span>
                </div>
                <div class="flex items-start gap-2 text-xs text-emerald-700">
                    <i class="fas fa-circle-check mt-0.5 text-emerald-400"></i>
                    <span>A branch email allows customers to reach the location directly.</span>
                </div>
                <div class="flex items-start gap-2 text-xs text-emerald-700">
                    <i class="fas fa-circle-check mt-0.5 text-emerald-400"></i>
                    <span>Set status to Inactive if the branch is not yet operational.</span>
                </div>
            </div>
        </div>

        {{-- RIGHT: Form --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Basic Information --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-5 flex items-center gap-2">
                    <i class="fas fa-store text-emerald-500"></i> Branch Information
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">
                            Branch Name <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-store text-sm"></i>
                            </span>
                            <input type="text" name="name" id="nameInput" value="{{ old('name') }}"
                                placeholder="e.g. Downtown Branch"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-gray-50 transition"
                                required>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Address</label>
                        <div class="relative">
                            <span class="absolute top-3 left-0 pl-3 flex items-start text-gray-400 pointer-events-none">
                                <i class="fas fa-location-dot text-sm"></i>
                            </span>
                            <textarea name="address" id="addressInput" rows="2"
                                placeholder="Street address, city, state..."
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-gray-50 transition resize-none">{{ old('address') }}</textarea>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Phone Number</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-phone text-sm"></i>
                            </span>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                placeholder="+1 (555) 000-0000"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-gray-50 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Email Address</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-envelope text-sm"></i>
                            </span>
                            <input type="email" name="email" value="{{ old('email') }}"
                                placeholder="branch@restaurant.com"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-gray-50 transition">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Management --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-5 flex items-center gap-2">
                    <i class="fas fa-user-tie text-blue-500"></i> Management
                </h3>

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1.5">Branch Manager</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                            <i class="fas fa-user-tie text-sm"></i>
                        </span>
                        <select name="manager_name" id="managerSelect"
                            class="w-full pl-9 pr-10 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 transition appearance-none"
                            onchange="updateManagerPreview(this)">
                            <option value="">— No manager assigned —</option>
                            @foreach($managers ?? [] as $manager)
                                <option value="{{ $manager->name }}"
                                    data-email="{{ $manager->email }}"
                                    {{ old('manager_name') === $manager->name ? 'selected' : '' }}>
                                    {{ $manager->name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 pointer-events-none">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </span>
                    </div>
                    <p id="managerEmail" class="text-xs text-gray-400 mt-1.5 min-h-[1rem]"></p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.branches.index') }}"
                    class="px-5 py-2.5 border border-gray-300 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-lg text-sm font-semibold shadow-sm transition-all">
                    <i class="fas fa-plus mr-2"></i> Create Branch
                </button>
            </div>

        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    // Live name preview
    document.getElementById('nameInput').addEventListener('input', function () {
        const val = this.value.trim();
        const el = document.getElementById('previewName');
        el.textContent = val || 'Branch name will appear here';
        el.classList.toggle('text-gray-400', !val);
        el.classList.toggle('italic', !val);
        el.classList.toggle('text-gray-800', !!val);
        el.classList.toggle('font-semibold', !!val);
    });

    // Live address preview
    document.getElementById('addressInput').addEventListener('input', function () {
        const val = this.value.trim();
        const el = document.getElementById('previewLocation').querySelector('span');
        el.textContent = val || 'No address set';
    });

    // Live manager preview
    function updateManagerPreview(select) {
        const name = select.value;
        const email = select.options[select.selectedIndex].dataset.email || '';
        document.getElementById('previewManager').textContent = name || '—';
        document.getElementById('managerEmail').textContent = name && email ? email : '';
    }

    // Init manager preview on page load (handles old() repopulation)
    (function () {
        const sel = document.getElementById('managerSelect');
        if (sel && sel.value) updateManagerPreview(sel);
    })();

    // Status preview
    function updateStatusPreview(val) {
        const el = document.getElementById('previewStatus');
        if (val === 'active') {
            el.className = 'inline-flex items-center gap-1 mt-0.5 text-xs font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700';
            el.innerHTML = '<i class="fas fa-circle text-[8px]"></i> Active';
        } else {
            el.className = 'inline-flex items-center gap-1 mt-0.5 text-xs font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-600';
            el.innerHTML = '<i class="fas fa-circle text-[8px]"></i> Inactive';
        }
    }
</script>
@endpush
