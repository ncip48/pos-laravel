@extends('layouts.admin')

@section('page-title', 'Dashboard')

@section('content')
    <div class="space-y-5">

        <form method="GET" class="flex justify-end">
            <select name="warehouse_id" onchange="this.form.submit()"
                class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All Warehouses</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected($warehouseId == $warehouse->id)>{{ $warehouse->name }}</option>
                @endforeach
            </select>
        </form>

        {{-- Stat cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card label="Today's Revenue" :value="$summary['today_revenue']->formatted()" :change="$summary['today_revenue_change_percent']" icon="chart-bar" />
            <x-stat-card label="Today's Transactions" :value="$summary['today_transaction_count']" icon="receipt" />
            <x-stat-card label="This Month's Revenue" :value="$summary['month_revenue']->formatted()" :change="$summary['month_revenue_change_percent']" icon="document-report" />
            <x-stat-card label="Low Stock Items" :value="$lowStockCount" icon="exclamation" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            {{-- Revenue chart --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900 mb-4">Revenue & Profit (Last 14 Days)</h3>
                <canvas id="revenue-chart" height="100"></canvas>
            </div>

            {{-- Payment method breakdown --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900 mb-4">Payment Methods (This Month)</h3>
                @if (empty($paymentBreakdown))
                    <p class="text-sm text-slate-400 py-8 text-center">No sales recorded yet.</p>
                @else
                    <canvas id="payment-chart"></canvas>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            {{-- Low stock alerts --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-900">Low Stock Alerts</h3>
                    <a href="{{ route('admin.reports.inventory') }}"
                        class="text-xs text-indigo-600 hover:text-indigo-800">View report &rarr;</a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($lowStockItems as $stockLevel)
                        <div class="px-5 py-3 flex items-center justify-between">
                            <div class="min-w-0">
                                <p class="font-medium text-slate-900 text-sm truncate">{{ $stockLevel->product->name }}</p>
                                <p class="text-xs text-slate-400">{{ $stockLevel->warehouse->name }}</p>
                            </div>
                            <x-badge color="amber">{{ $stockLevel->quantity }} left</x-badge>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-slate-400">All products are sufficiently stocked.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Best sellers --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h3 class="font-semibold text-slate-900">Best-Selling Products (30 Days)</h3>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($bestSelling as $product)
                        <div class="px-5 py-3 flex items-center justify-between">
                            <div class="min-w-0">
                                <p class="font-medium text-slate-900 text-sm truncate">{{ $product->name }}</p>
                                <p class="text-xs text-slate-400 font-mono-num">{{ $product->sku }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-mono-num font-medium text-sm">{{ $product->total_quantity_sold }} sold</p>
                                <p class="text-xs text-slate-400 font-mono-num">
                                    {{ \App\Support\Money::fromAmount($product->total_revenue_cents)->formatted() }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-slate-400">No sales recorded in the last 30 days.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Recent transactions --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-semibold text-slate-900">Recent Transactions</h3>
                <a href="{{ route('admin.sales.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800">View all
                    &rarr;</a>
            </div>
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-slate-500">Invoice</th>
                        <th class="px-4 py-2 text-left font-medium text-slate-500">Customer</th>
                        <th class="px-4 py-2 text-left font-medium text-slate-500">Cashier</th>
                        <th class="px-4 py-2 text-right font-medium text-slate-500">Total</th>
                        <th class="px-4 py-2 text-left font-medium text-slate-500">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentTransactions as $sale)
                        <tr class="hover:bg-slate-50/75 cursor-pointer"
                            onclick="window.location='{{ route('admin.sales.show', $sale) }}'">
                            <td class="px-4 py-2.5 font-mono-num font-medium text-indigo-600">{{ $sale->invoice_number }}
                            </td>
                            <td class="px-4 py-2.5 text-slate-600">
                                {{ $sale->customer->is_guest ? 'Walk-in' : $sale->customer->name }}</td>
                            <td class="px-4 py-2.5 text-slate-600">{{ $sale->cashier->name }}</td>
                            <td class="px-4 py-2.5 text-right font-mono-num font-medium">{{ $sale->total()->formatted() }}
                            </td>
                            <td class="px-4 py-2.5 text-slate-400">{{ $sale->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">No transactions yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        $(function() {
            const revenueData = @json($revenueSeries);

            new Chart(document.getElementById('revenue-chart'), {
                type: 'line',
                data: {
                    labels: revenueData.map(d => new Date(d.date).toLocaleDateString(undefined, {
                        month: 'short',
                        day: 'numeric'
                    })),
                    datasets: [{
                            label: 'Revenue',
                            data: revenueData.map(d => d.revenue),
                            borderColor: '#4F46E5',
                            backgroundColor: 'rgba(79, 70, 229, 0.08)',
                            fill: true,
                            tension: 0.3,
                        },
                        {
                            label: 'Profit',
                            data: revenueData.map(d => d.profit),
                            borderColor: '#10B981',
                            backgroundColor: 'rgba(16, 185, 129, 0.08)',
                            fill: true,
                            tension: 0.3,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                },
            });

            @if (!empty($paymentBreakdown))
                const paymentData = @json(collect($paymentBreakdown)->map(fn($p) => ['method' => $p['method'], 'total' => $p['total']->amount()]));

                new Chart(document.getElementById('payment-chart'), {
                    type: 'doughnut',
                    data: {
                        labels: paymentData.map(p => p.method.replace('_', ' ').replace(/\b\w/g, c => c
                            .toUpperCase())),
                        datasets: [{
                            data: paymentData.map(p => p.total),
                            backgroundColor: ['#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                                '#6B7280'
                            ],
                        }],
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        },
                    },
                });
            @endif
        });
    </script>
@endpush
