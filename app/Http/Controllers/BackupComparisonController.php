<?php

namespace App\Http\Controllers;

use App\Models\BackupComparison;
use App\Services\BackupComparisonService;
use Illuminate\Http\Request;

class BackupComparisonController extends Controller
{
    public function __construct(
        protected BackupComparisonService $comparison
    ) {}

    public function index()
    {
        $comparisons = BackupComparison::orderByDesc('compared_at')->get();

        return view('backup-comparisons.index', compact('comparisons'));
    }

    public function compare(Request $request)
    {
        $validated = $request->validate([
            'file_a' => 'required|string',
            'file_b' => 'required|string|different:file_a',
        ]);

        $result = $this->comparison->compare(
            $validated['file_a'],
            $validated['file_b'],
            auth()->id()
        );

        return redirect()
            ->route('backup-comparisons.index')
            ->with('success', 'Backups compared successfully.')
            ->with('comparison_result', $result);
    }
}
