<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class SettingController extends Controller
{
    public function index()
    {
        $definitions = Setting::groupedDefinitions();
        $values = [];

        foreach (array_keys(Setting::definitions()) as $key) {
            $values[$key] = Setting::getSetting($key);
        }

        return view('admin.settings.index', compact('definitions', 'values'));
    }

    public function update(Request $request, ActivityLogService $logger)
    {
        $rules = [];

        foreach (Setting::definitions() as $key => $definition) {
            $rules[$key] = $definition['rules'] ?? ['nullable'];
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('logo_path')) {
            $oldLogoPath = Setting::getSetting('logo_path');
            $validated['logo_path'] = $request->file('logo_path')->store('settings/logo', 'public');

            if ($oldLogoPath && Storage::disk('public')->exists($oldLogoPath)) {
                Storage::disk('public')->delete($oldLogoPath);
            }
        }

        Setting::setMany($validated);
        $logger->log('setting', 'update', null, ['keys' => array_keys(Setting::definitions())]);

        return back()->with('success', 'Pengaturan aplikasi berhasil diperbarui.');
    }

    public function updatePassword(Request $request, ActivityLogService $logger)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'password')->withInput();
        }

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        $logger->log('setting', 'update_password', null, []);

        return back()->with('success_password', 'Password berhasil diperbarui.');
    }
}
