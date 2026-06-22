<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateCurrencySettingsRequest;
use App\Http\Requests\Settings\UpdateReceiptSettingsRequest;
use App\Http\Requests\Settings\UpdateStoreSettingsRequest;
use App\Http\Requests\Settings\UpdateTaxSettingsRequest;
use App\Services\BackupService;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SettingController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly SettingService $settingService,
        private readonly BackupService $backupService,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:settings.store|settings.tax|settings.currency|settings.receipt|settings.backup', only: ['edit']),
            new Middleware('permission:settings.store', only: ['updateStore']),
            new Middleware('permission:settings.tax', only: ['updateTax']),
            new Middleware('permission:settings.currency', only: ['updateCurrency']),
            new Middleware('permission:settings.receipt', only: ['updateReceipt']),
            new Middleware('permission:settings.backup', only: ['createBackup', 'downloadBackup', 'deleteBackup']),
            new Middleware('permission:settings.restore', only: ['restoreBackup']),
        ];
    }

    public function edit(): View
    {
        $store = $this->settingService->storeInfo();
        $backups = auth()->user()->can('settings.backup') ? $this->backupService->listBackups() : [];

        return view('admin.settings.index', compact('store', 'backups'));
    }

    public function updateStore(UpdateStoreSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('store', 'public');
            $this->settingService->set('store', 'logo_path', $path);
        }

        $this->settingService->updateStoreInfo($data);

        return redirect()->route('admin.settings.edit')->with('success', 'Store information updated.');
    }

    public function updateTax(UpdateTaxSettingsRequest $request): RedirectResponse
    {
        $this->settingService->updateTaxSettings($request->validated());

        return redirect()->route('admin.settings.edit')->with('success', 'Tax settings updated.');
    }

    public function updateCurrency(UpdateCurrencySettingsRequest $request): RedirectResponse
    {
        $this->settingService->updateCurrencySettings($request->validated());

        return redirect()->route('admin.settings.edit')->with('success', 'Currency settings updated.');
    }

    public function updateReceipt(UpdateReceiptSettingsRequest $request): RedirectResponse
    {
        $this->settingService->updateReceiptSettings($request->validated());

        return redirect()->route('admin.settings.edit')->with('success', 'Receipt settings updated.');
    }

    public function createBackup(): RedirectResponse
    {
        try {
            $filename = $this->backupService->create();
            return redirect()
                ->route('admin.settings.edit')
                ->with('success', 'Backup created: ' . basename($filename));
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.settings.edit')
                ->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function downloadBackup(string $filename): Response|BinaryFileResponse
    {
        $path = "backups/{$filename}";
        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->download(Storage::disk('local')->path($path));
    }

    public function restoreBackup(string $filename): RedirectResponse
    {
        request()->validate([
            'confirmation' => ['required', 'in:RESTORE'],
        ], [
            'confirmation.in' => 'You must type RESTORE exactly to confirm this irreversible action.',
        ]);

        try {
            $this->backupService->restore($filename);
            return redirect()
                ->route('admin.settings.edit')
                ->with('success', 'Database restored successfully.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.settings.edit')
                ->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    public function deleteBackup(string $filename): RedirectResponse
    {
        $this->backupService->deleteBackup($filename);
        return redirect()->route('admin.settings.edit')->with('success', 'Backup deleted.');
    }
}
