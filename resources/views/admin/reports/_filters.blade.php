<form method="GET" action="{{ route($route) }}"
    class="bg-card rounded-2xl border border-theme p-5 grid grid-cols-1 sm:grid-cols-4 gap-4 shadow-sm hover:shadow-md transition-shadow">
    <div>
        <label class="block text-xs font-medium text-secondary uppercase tracking-wider mb-1.5">From</label>
        <div class="relative">
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                <x-icon name="calendar" class="w-4 h-4" />
            </div>
            <input type="date" name="from" value="{{ $from->toDateString() }}"
                class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition">
        </div>
    </div>
    <div>
        <label class="block text-xs font-medium text-secondary uppercase tracking-wider mb-1.5">To</label>
        <div class="relative">
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                <x-icon name="calendar" class="w-4 h-4" />
            </div>
            <input type="date" name="to" value="{{ $to->toDateString() }}"
                class="w-full rounded-xl border-theme pl-9 pr-4 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition">
        </div>
    </div>
    <div>
        <label class="block text-xs font-medium text-secondary uppercase tracking-wider mb-1.5">Warehouse</label>
        <div class="relative">
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary opacity-40">
                <x-icon name="warehouse" class="w-4 h-4" />
            </div>
            <select name="warehouse_id"
                class="w-full rounded-xl border-theme pl-9 pr-10 py-2.5 bg-sage-50/50 dark:bg-sage-900/20 text-primary text-sm focus:ring-2 focus:ring-sage-400 dark:focus:ring-sage-500 focus:border-sage-400 dark:focus:border-sage-500 transition appearance-none cursor-pointer">
                <option value="">All warehouses</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected($warehouseId == $warehouse->id)>{{ $warehouse->name }}</option>
                @endforeach
            </select>
            {{-- <div class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary opacity-40 pointer-events-none">
                <x-icon name="chevron-down" class="w-4 h-4" />
            </div> --}}
        </div>
    </div>
    <div class="flex items-end">
        <button type="submit"
            class="w-full rounded-xl bg-sage-600 hover:bg-sage-700 dark:bg-sage-500 dark:hover:bg-sage-600 text-white text-sm font-medium px-4 py-2.5 shadow-sm hover:shadow-md transition flex items-center justify-center gap-2 group">
            <x-icon name="filter" class="w-4 h-4 group-hover:scale-110 transition-transform duration-200" />
            Apply
        </button>
    </div>
</form>
