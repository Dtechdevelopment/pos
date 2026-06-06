@extends('admin.layouts.app')

@section('title', 'Create Menu Item')

@section('content')

{{-- Page Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Create Menu Item</h1>
        <p class="text-sm text-gray-500 mt-1">Add a new item to your restaurant menu.</p>
    </div>
    <a href="{{ route('admin.menu.index') }}"
        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors shadow-sm text-sm font-medium">
        <i class="fas fa-arrow-left mr-2 text-gray-400"></i>Back to Menu
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

<form action="{{ route('admin.menu.store') }}" method="POST" enctype="multipart/form-data" id="menuForm">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT: Image & Status --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- Image Upload Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fas fa-image text-orange-500"></i> Item Image
                </h3>

                {{-- Drop Zone --}}
                <div id="dropZone"
                    class="relative border-2 border-dashed border-gray-200 rounded-xl overflow-hidden cursor-pointer hover:border-orange-400 transition-colors group"
                    style="height: 200px"
                    onclick="document.getElementById('imageInput').click()">

                    {{-- Placeholder --}}
                    <div id="imagePlaceholder" class="absolute inset-0 flex flex-col items-center justify-center gap-2">
                        <div class="w-14 h-14 rounded-full bg-orange-50 flex items-center justify-center group-hover:bg-orange-100 transition">
                            <i class="fas fa-cloud-arrow-up text-orange-400 text-xl"></i>
                        </div>
                        <p class="text-sm text-gray-500 font-medium">Click to upload image</p>
                        <p class="text-xs text-gray-400">PNG, JPG, WEBP up to 2MB</p>
                    </div>

                    {{-- Preview --}}
                    <img id="imagePreview" src="" alt="Preview"
                        class="hidden absolute inset-0 w-full h-full object-cover rounded-xl">

                    {{-- Remove button --}}
                    <button type="button" id="removeImage"
                        class="hidden absolute top-2 right-2 w-7 h-7 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition shadow"
                        onclick="event.stopPropagation(); clearImage()">
                        <i class="fas fa-xmark text-xs"></i>
                    </button>
                </div>
                <input type="file" id="imageInput" name="image" accept="image/*" class="hidden" onchange="previewImage(this)">
            </div>

            {{-- Live Preview Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fas fa-eye text-blue-400"></i> Preview
                </h3>
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <div class="flex items-start gap-3">
                        <div id="previewThumb" class="w-12 h-12 rounded-lg bg-orange-100 flex items-center justify-center flex-shrink-0 overflow-hidden">
                            <i class="fas fa-utensils text-orange-400"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p id="previewName" class="text-sm font-semibold text-gray-700 truncate italic text-gray-400">Item name...</p>
                            <p id="previewCategory" class="text-xs text-gray-400 mt-0.5">No category</p>
                            <div class="flex items-center justify-between mt-2">
                                <div>
                                    <p class="text-xs text-gray-400">Selling Price</p>
                                    <p id="previewPrice" class="text-base font-bold text-orange-600">$0.00</p>
                                </div>
                                <span id="previewBadge"
                                    class="text-xs px-2 py-0.5 rounded-full font-semibold bg-green-100 text-green-700">
                                    Available
                                </span>
                            </div>
                        </div>
                    </div>
                    <p id="previewDesc" class="text-xs text-gray-400 mt-3 italic line-clamp-2">No description yet...</p>
                </div>
            </div>

            {{-- Availability & Visibility --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fas fa-toggle-on text-green-500"></i> Availability
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Available to Order</p>
                            <p class="text-xs text-gray-400">Customers can order this item</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="is_available" value="0">
                            <input type="checkbox" name="is_available" value="1" id="isAvailable"
                                class="sr-only peer" checked onchange="updateAvailabilityPreview()">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-green-400 rounded-full peer
                                peer-checked:bg-green-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5
                                after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all
                                peer-checked:after:translate-x-5"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Active on Menu</p>
                            <p class="text-xs text-gray-400">Item appears in the menu list</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" id="isActive"
                                class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-blue-400 rounded-full peer
                                peer-checked:bg-blue-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5
                                after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all
                                peer-checked:after:translate-x-5"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT: Details --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Basic Info --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-5 flex items-center gap-2">
                    <i class="fas fa-utensils text-orange-500"></i> Item Details
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">
                            Item Name <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-utensils text-sm"></i>
                            </span>
                            <input type="text" name="name" id="nameInput" value="{{ old('name') }}"
                                placeholder="e.g. Grilled Chicken Burger"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 bg-gray-50 transition"
                                required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Category <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-tag text-sm"></i>
                            </span>
                            <select name="category_id" id="categorySelect"
                                class="w-full pl-9 pr-10 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 bg-gray-50 transition appearance-none"
                                onchange="updateCategoryPreview(this)" required>
                                <option value="">— Select Category —</option>
                                @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Branch</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-store text-sm"></i>
                            </span>
                            <select name="branch_id"
                                class="w-full pl-9 pr-10 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 bg-gray-50 transition appearance-none">
                                <option value="">All Branches</option>
                                @foreach($branches ?? [] as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">SKU</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-barcode text-sm"></i>
                            </span>
                            <input type="text" name="sku" value="{{ old('sku') }}"
                                placeholder="e.g. ITEM-001"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 bg-gray-50 transition">
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">Leave blank to auto-generate.</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Description</label>
                        <textarea name="description" id="descInput" rows="3"
                            placeholder="Describe the dish — ingredients, flavors, preparation style..."
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 bg-gray-50 transition resize-none">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Pricing --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-5 flex items-center gap-2">
                    <i class="fas fa-dollar-sign text-green-500"></i> Pricing
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">
                            Cost Price <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 pointer-events-none font-medium text-sm">$</span>
                            <input type="number" name="cost_price" value="{{ old('cost_price') }}"
                                step="0.01" min="0" placeholder="0.00"
                                class="w-full pl-7 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-400 bg-gray-50 transition"
                                required>
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">What it costs to make.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">
                            Selling Price <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 pointer-events-none font-medium text-sm">$</span>
                            <input type="number" name="selling_price" id="sellingPriceInput" value="{{ old('selling_price') }}"
                                step="0.01" min="0" placeholder="0.00"
                                class="w-full pl-7 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-400 bg-gray-50 transition"
                                onchange="updatePricePreview(this.value)" oninput="updatePricePreview(this.value)"
                                required>
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">Customer-facing price.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Tax (%)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 pointer-events-none font-medium text-sm">%</span>
                            <input type="number" name="tax" value="{{ old('tax', 0) }}"
                                step="0.01" min="0" max="100" placeholder="0.00"
                                class="w-full pl-7 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-400 bg-gray-50 transition">
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">Applied at checkout.</p>
                    </div>
                </div>

                {{-- Margin Indicator --}}
                <div class="mt-5 bg-gray-50 rounded-lg p-4 border border-gray-100">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-medium text-gray-500">Profit Margin</p>
                        <p id="marginPct" class="text-xs font-bold text-gray-400">—</p>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div id="marginBar" class="h-2 rounded-full bg-gray-300 transition-all duration-300" style="width:0%"></div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.menu.index') }}"
                    class="px-5 py-2.5 border border-gray-300 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white rounded-lg text-sm font-semibold shadow-sm transition-all">
                    <i class="fas fa-plus mr-2"></i> Create Menu Item
                </button>
            </div>

        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    // ── Image upload & preview ──────────────────────────────────────────
    function previewImage(input) {
        if (!input.files || !input.files[0]) return;
        const reader = new FileReader();
        reader.onload = e => {
            const src = e.target.result;
            document.getElementById('imagePlaceholder').classList.add('hidden');
            const img = document.getElementById('imagePreview');
            img.src = src;
            img.classList.remove('hidden');
            document.getElementById('removeImage').classList.remove('hidden');

            // Update preview thumb
            const thumb = document.getElementById('previewThumb');
            thumb.innerHTML = `<img src="${src}" class="w-full h-full object-cover">`;
        };
        reader.readAsDataURL(input.files[0]);
    }

    function clearImage() {
        document.getElementById('imageInput').value = '';
        document.getElementById('imagePreview').classList.add('hidden');
        document.getElementById('imagePreview').src = '';
        document.getElementById('imagePlaceholder').classList.remove('hidden');
        document.getElementById('removeImage').classList.add('hidden');
        document.getElementById('previewThumb').innerHTML = '<i class="fas fa-utensils text-orange-400"></i>';
    }

    // Drag & drop
    const dropZone = document.getElementById('dropZone');
    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('border-orange-400', 'bg-orange-50'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('border-orange-400', 'bg-orange-50'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('border-orange-400', 'bg-orange-50');
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            const dt = new DataTransfer();
            dt.items.add(file);
            document.getElementById('imageInput').files = dt.files;
            previewImage(document.getElementById('imageInput'));
        }
    });

    // ── Name preview ────────────────────────────────────────────────────
    document.getElementById('nameInput').addEventListener('input', function () {
        const val = this.value.trim();
        const el = document.getElementById('previewName');
        el.textContent = val || 'Item name...';
        el.classList.toggle('italic', !val);
        el.classList.toggle('text-gray-400', !val);
        el.classList.toggle('text-gray-800', !!val);
    });

    // ── Description preview ─────────────────────────────────────────────
    document.getElementById('descInput').addEventListener('input', function () {
        const val = this.value.trim();
        const el = document.getElementById('previewDesc');
        el.textContent = val || 'No description yet...';
        el.classList.toggle('italic', !val);
        el.classList.toggle('text-gray-400', !val);
        el.classList.toggle('text-gray-600', !!val);
    });

    // ── Category preview ────────────────────────────────────────────────
    function updateCategoryPreview(select) {
        const label = select.options[select.selectedIndex]?.text ?? 'No category';
        document.getElementById('previewCategory').textContent = select.value ? label : 'No category';
    }

    // ── Price preview & margin bar ──────────────────────────────────────
    function updatePricePreview(val) {
        const price = parseFloat(val) || 0;
        document.getElementById('previewPrice').textContent = '$' + price.toFixed(2);
        updateMargin();
    }

    function updateMargin() {
        const cost = parseFloat(document.querySelector('[name="cost_price"]').value) || 0;
        const sell = parseFloat(document.querySelector('[name="selling_price"]').value) || 0;
        const pctEl = document.getElementById('marginPct');
        const barEl = document.getElementById('marginBar');

        if (sell <= 0) {
            pctEl.textContent = '—';
            pctEl.className = 'text-xs font-bold text-gray-400';
            barEl.style.width = '0%';
            barEl.className = 'h-2 rounded-full bg-gray-300 transition-all duration-300';
            return;
        }

        const margin = ((sell - cost) / sell) * 100;
        pctEl.textContent = margin.toFixed(1) + '%';

        let color = 'bg-red-400';
        let textColor = 'text-red-500';
        if (margin >= 60) { color = 'bg-green-500'; textColor = 'text-green-600'; }
        else if (margin >= 35) { color = 'bg-yellow-400'; textColor = 'text-yellow-600'; }
        else if (margin >= 10) { color = 'bg-orange-400'; textColor = 'text-orange-500'; }

        pctEl.className = `text-xs font-bold ${textColor}`;
        barEl.style.width = Math.min(Math.max(margin, 0), 100) + '%';
        barEl.className = `h-2 rounded-full ${color} transition-all duration-300`;
    }

    document.querySelector('[name="cost_price"]').addEventListener('input', updateMargin);

    // ── Availability badge preview ───────────────────────────────────────
    function updateAvailabilityPreview() {
        const checked = document.getElementById('isAvailable').checked;
        const badge = document.getElementById('previewBadge');
        badge.textContent = checked ? 'Available' : 'Unavailable';
        badge.className = `text-xs px-2 py-0.5 rounded-full font-semibold ${
            checked ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'
        }`;
    }

    // ── Init ─────────────────────────────────────────────────────────────
    updateCategoryPreview(document.getElementById('categorySelect'));
    updatePricePreview(document.getElementById('sellingPriceInput').value);
</script>
@endpush
