<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TestSession;
use App\Models\TestSessionClass;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class TestSessionController extends Controller
{
    public function index()
    {
        $baseQuery = TestSession::query();

        $summary = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('is_active', true)->count(),
            'inactive' => (clone $baseQuery)->where('is_active', false)->count(),
        ];

        $sessions = TestSession::with('classes')
            ->withCount(['classes', 'students'])
            ->orderByDesc('is_active')
            ->orderBy('test_date')
            ->orderBy('start_time')
            ->paginate(10);

        return view('admin.test-sessions.index', compact('sessions', 'summary'));
    }

    public function store(Request $request, ActivityLogService $logger)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'test_date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required', 'after:start_time'],
            'test_type' => ['required', 'in:academic,psychology,both'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $session = TestSession::create($validated + [
            'is_active' => $request->boolean('is_active'),
        ]);

        $logger->log('test_session', 'create', $session);

        return back()->with('success', 'Sesi tes berhasil dibuat.');
    }

    public function update(Request $request, TestSession $testSession, ActivityLogService $logger)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'test_date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required', 'after:start_time'],
            'test_type' => ['required', 'in:academic,psychology,both'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $testSession->update($validated + [
            'is_active' => $request->boolean('is_active'),
        ]);

        $logger->log('test_session', 'update', $testSession);

        return back()->with('success', 'Sesi tes berhasil diperbarui.');
    }

    public function destroy(TestSession $testSession, ActivityLogService $logger)
    {
        $logger->log('test_session', 'delete', $testSession);

        $testSession->delete();

        return back()->with('success', 'Sesi tes berhasil dihapus.');
    }

    public function storeClass(Request $request, TestSession $testSession)
    {
        $validated = $request->validate([
            'origin_class' => ['required', 'string', 'max:20'],
        ]);

        TestSessionClass::firstOrCreate([
            'test_session_id' => $testSession->id,
            'origin_class' => strtoupper($validated['origin_class']),
        ]);

        return back()->with('success', 'Kelas berhasil ditambahkan ke sesi.');
    }

    public function destroyClass(TestSession $testSession, TestSessionClass $testSessionClass)
    {
        abort_if($testSessionClass->test_session_id !== $testSession->id, 404);

        $testSessionClass->delete();

        return back()->with('success', 'Kelas berhasil dihapus dari sesi.');
    }
}
