<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Repositories\Eloquent\EloquentUnitRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UnitController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly EloquentUnitRepository $unitRepository,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:units.manage'),
        ];
    }

    public function index(): View
    {
        $units = $this->unitRepository->all();

        return view('admin.units.index', compact('units'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'symbol' => ['required', 'string', 'max:10'],
            'base_unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'conversion_factor' => ['required', 'numeric', 'min:0.0001'],
        ]);

        $this->unitRepository->create($data);

        return redirect()->route('admin.units.index')->with('success', 'Unit created.');
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'symbol' => ['required', 'string', 'max:10'],
            'base_unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'conversion_factor' => ['required', 'numeric', 'min:0.0001'],
        ]);

        $this->unitRepository->update($unit, $data);

        return redirect()->route('admin.units.index')->with('success', 'Unit updated.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        if ($unit->products()->exists()) {
            return redirect()
                ->route('admin.units.index')
                ->with('error', 'Cannot delete a unit still in use by products.');
        }

        $this->unitRepository->delete($unit);

        return redirect()->route('admin.units.index')->with('success', 'Unit deleted.');
    }
}
