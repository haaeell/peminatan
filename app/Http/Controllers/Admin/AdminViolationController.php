<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Violation;

class AdminViolationController extends Controller
{
    public function index()
    {
        $violations = Violation::with('student')
            ->latest()
            ->paginate(30);

        return view('admin.violations.index', compact('violations'));
    }
}
