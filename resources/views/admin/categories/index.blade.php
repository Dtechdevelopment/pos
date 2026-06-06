@extends('admin.layouts.app')

@section('title', 'Categories')

@section('content')

{{-- Page Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Categories</h1>
        <p class="text-sm text-gray-500 mt-1">Manage your menu categories.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 flex items-center gap-3">
    <i class="fas fa-circle-check text-green-500"></i>
    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
</div>
@endif

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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT: Add / Edit Form --}}
    <div class="lg:col-span-1 space-y-6">

        {{-- Preview Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col items-center text-center">
            <div id="previewIconWrap"
                class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-400 to-purple-600 flex items-center justify-center shadow-md mb-3">
                <i id="previewIcon" class="fas fa-tag text-white text-2xl"></i>
            </div>
            <p id="previewName" class="text-sm font-semibold text-gray-700 italic text-gray-400">Category name...</p>
            <p id="previewDesc" class="text-xs text-gray-400 mt-1 line-clamp-2 italic">No description yet...</p>
            <div class="mt-3 flex items-center gap-2">
                <span id="previewStatus"
                    class="text-xs px-2.5 py-0.5 rounded-full font-semibold bg-green-100 text-green-700">
                    Active
                </span>
                <span id="previewSort" class="text-xs text-gray-400">Sort: 0</span>
            </div>
        </div>

        {{-- Form --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-5 flex items-center gap-2">
                <i class="fas fa-plus-circle text-indigo-500"></i>
                <span id="formTitle">Add New Category</span>
            </h3>

            <form action="{{ route('admin.categories.store') }}" method="POST" id="categoryForm">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="_edit_id" id="editId" value="">

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-tag text-sm"></i>
                            </span>
                            <input type="text" name="name" id="nameInput" value="{{ old('name') }}"
                                placeholder="e.g. Beverages"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-gray-50 transition"
                                required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Description</label>
                        <textarea name="description" id="descInput" rows="3"
                            placeholder="Short description of this category..."
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-gray-50 transition resize-none">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Sort Order</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-sort text-sm"></i>
                            </span>
                            <input type="number" name="sort_order" id="sortInput" value="{{ old('sort_order', 0) }}"
                                min="0" placeholder="0"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-gray-50 transition">
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">Lower numbers appear first.</p>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Active</p>
                            <p class="text-xs text-gray-400">Show in menu</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" id="isActive"
                                class="sr-only peer" checked onchange="updateStatusPreview()">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-indigo-400 rounded-full peer
                                peer-checked:bg-indigo-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5
                                after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all
                                peer-checked:after:translate-x-5"></div>
                        </label>
                    </div>

                    <div class="flex gap-2 pt-1">
                        <button type="submit" id="submitBtn"
                            class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-lg text-sm font-semibold shadow-sm transition-all">
                            <i class="fas fa-plus mr-2" id="submitIcon"></i>
                            <span id="submitLabel">Add Category</span>
                        </button>
                        <button type="button" id="cancelEditBtn" onclick="cancelEdit()"
                            class="hidden px-4 py-2.5 border border-gray-300 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- RIGHT: Categories List --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

            {{-- Table Header --}}
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <i class="fas fa-list text-indigo-400"></i>
                    <span class="text-sm font-semibold text-gray-700">All Categories</span>
                    <span class="text-xs px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded-full font-medium">
                        {{ $categories->total() ?? count($categories) }}
                    </span>
                </div>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" id="tableSearch" placeholder="Search..."
                        class="pl-8 pr-4 py-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-gray-50 w-44 transition">
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="categoriesTable">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">#</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Name</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Slug</th>
                            <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Items</th>
                            <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Sort</th>
                            <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Status</th>
                            <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="categoriesTbody">
                        @forelse($categories as $category)
                        <tr class="border-b border-gray-50 hover:bg-indigo-50/30 transition-colors category-row"
                            data-name="{{ strtolower($category->name) }}">
                            <td class="py-3 px-4 text-gray-400 text-xs">{{ $category->id }}</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-tag text-indigo-400 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $category->name }}</p>
                                        @if($category->description)
                                            <p class="text-xs text-gray-400 truncate max-w-[160px]">{{ $category->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <code class="text-xs bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">{{ $category->slug }}</code>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-semibold
                                    {{ ($category->menu_items_count ?? 0) > 0 ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-400' }}">
                                    {{ $category->menu_items_count ?? 0 }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center text-gray-500 text-xs">{{ $category->sort_order }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $category->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                                    <i class="fas fa-circle text-[6px]"></i>
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button"
                                        onclick="editCategory({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ addslashes($category->description ?? '') }}', {{ $category->sort_order }}, {{ $category->is_active ? 'true' : 'false' }})"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition"
                                        title="Edit">
                                        <i class="fas fa-pen text-xs"></i>
                                    </button>
                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST"
                                        onsubmit="return confirm('Delete \'{{ addslashes($category->name) }}\'?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition"
                                            title="Delete">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center">
                                <i class="fas fa-tag text-4xl text-gray-200 mb-3 block"></i>
                                <p class="text-gray-400 text-sm">No categories yet. Add your first one!</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($categories instanceof \Illuminate\Pagination\LengthAwarePaginator && $categories->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $categories->links() }}
            </div>
            @endif
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
    // ── Live preview ────────────────────────────────────────────────────
    document.getElementById('nameInput').addEventListener('input', function () {
        const val = this.value.trim();
        const el = document.getElementById('previewName');
        el.textContent = val || 'Category name...';
        el.classList.toggle('italic', !val);
        el.classList.toggle('text-gray-400', !val);
        el.classList.toggle('text-gray-800', !!val);
    });

    document.getElementById('descInput').addEventListener('input', function () {
        const val = this.value.trim();
        const el = document.getElementById('previewDesc');
        el.textContent = val || 'No description yet...';
        el.classList.toggle('italic', !val);
    });

    document.getElementById('sortInput').addEventListener('input', function () {
        document.getElementById('previewSort').textContent = 'Sort: ' + (this.value || 0);
    });

    function updateStatusPreview() {
        const active = document.getElementById('isActive').checked;
        const el = document.getElementById('previewStatus');
        el.textContent = active ? 'Active' : 'Inactive';
        el.className = `text-xs px-2.5 py-0.5 rounded-full font-semibold ${
            active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'
        }`;
    }

    // ── Edit mode ────────────────────────────────────────────────────────
    function editCategory(id, name, description, sortOrder, isActive) {
        // Populate form
        document.getElementById('nameInput').value = name;
        document.getElementById('descInput').value = description;
        document.getElementById('sortInput').value = sortOrder;
        document.getElementById('isActive').checked = isActive;
        document.getElementById('editId').value = id;

        // Change form action to update route
        document.getElementById('categoryForm').action = '/admin/categories/' + id;
        document.getElementById('formMethod').value = 'PUT';

        // Update UI labels
        document.getElementById('formTitle').textContent = 'Edit Category';
        document.getElementById('submitIcon').className = 'fas fa-save mr-2';
        document.getElementById('submitLabel').textContent = 'Update Category';
        document.getElementById('cancelEditBtn').classList.remove('hidden');

        // Update preview
        document.getElementById('previewName').textContent = name;
        document.getElementById('previewName').classList.remove('italic', 'text-gray-400');
        document.getElementById('previewName').classList.add('text-gray-800');
        document.getElementById('previewDesc').textContent = description || 'No description yet...';
        document.getElementById('sortInput').dispatchEvent(new Event('input'));
        updateStatusPreview();

        // Scroll to form
        document.getElementById('categoryForm').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function cancelEdit() {
        document.getElementById('categoryForm').reset();
        document.getElementById('categoryForm').action = '{{ route('admin.categories.store') }}';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('editId').value = '';
        document.getElementById('formTitle').textContent = 'Add New Category';
        document.getElementById('submitIcon').className = 'fas fa-plus mr-2';
        document.getElementById('submitLabel').textContent = 'Add Category';
        document.getElementById('cancelEditBtn').classList.add('hidden');
        document.getElementById('isActive').checked = true;

        // Reset preview
        document.getElementById('previewName').textContent = 'Category name...';
        document.getElementById('previewName').className = 'text-sm font-semibold italic text-gray-400';
        document.getElementById('previewDesc').textContent = 'No description yet...';
        document.getElementById('previewSort').textContent = 'Sort: 0';
        updateStatusPreview();
    }

    // ── Table search ─────────────────────────────────────────────────────
    document.getElementById('tableSearch').addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('.category-row').forEach(row => {
            row.style.display = !q || row.dataset.name.includes(q) ? '' : 'none';
        });
    });
</script>
@endpush
