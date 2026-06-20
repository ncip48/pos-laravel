<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Name <span class="text-red-500">*</span></label>
        <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" required
            class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Parent Category</label>
        <select name="parent_id"
            class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">— None (top level) —</option>
            @foreach ($categories ?? \App\Models\Category::rootOnly()->get() as $option)
                @continue(isset($category) && $option->id === $category->id)
                <option value="{{ $option->id }}" @selected(old('parent_id', $category->parent_id ?? null) == $option->id)>
                    {{ $option->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
        <textarea name="description" rows="2"
            class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $category->description ?? '') }}</textarea>
    </div>
    <label class="flex items-center gap-2 text-sm text-slate-700">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))
            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
        Active
    </label>
</div>
