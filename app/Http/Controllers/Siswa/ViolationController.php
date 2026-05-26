<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Violation;
use Illuminate\Http\Request;

class ViolationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'exam_type' => ['required', 'in:academic,psychology'],
            'action' => ['required', 'string', 'max:100'],
            'violation_count' => ['required', 'integer', 'min:1'],
            'device_info' => ['nullable', 'array'],
        ]);

        $student = auth()->user()->student;

        Violation::create([
            'student_id' => $student->id,
            'exam_type' => $validated['exam_type'],
            'action' => $validated['action'],
            'violation_count' => $validated['violation_count'],
            'device_info' => $validated['device_info'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'occurred_at' => now(),
        ]);

        return response()->json([
            'message' => 'Pelanggaran dicatat.',
        ]);
    }
}
