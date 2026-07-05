<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ErrorLog;
use App\Models\Hotel;
use Illuminate\Http\Request;

class ErrorLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ErrorLog::with(['user', 'hotel'])->latest('last_occurred_at');

        if ($request->filled('code')) {
            $query->where('code', 'like', '%'.trim($request->code).'%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('hotel_id')) {
            $query->where('hotel_id', $request->hotel_id);
        }

        if ($request->filled('exception_class')) {
            $query->where('exception_class', 'like', "%{$request->exception_class}%");
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(30)->withQueryString();

        $hotels = Hotel::orderBy('name')->get(['id', 'name']);
        $exceptionClasses = ErrorLog::distinct()->orderBy('exception_class')->pluck('exception_class');

        $counts = [
            'open'     => ErrorLog::where('status', 'open')->count(),
            'resolved' => ErrorLog::where('status', 'resolved')->count(),
            'ignored'  => ErrorLog::where('status', 'ignored')->count(),
        ];

        return view('admin.error-logs.index', compact('logs', 'hotels', 'exceptionClasses', 'counts'));
    }

    public function show(ErrorLog $errorLog)
    {
        $errorLog->load(['user', 'hotel', 'resolvedBy']);

        return view('admin.error-logs.show', compact('errorLog'));
    }

    public function update(ErrorLog $errorLog, Request $request)
    {
        $data = $request->validate([
            'status'           => ['required', 'in:open,resolved,ignored'],
            'resolution_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $errorLog->status = $data['status'];
        $errorLog->resolution_notes = $data['resolution_notes'] ?? $errorLog->resolution_notes;

        if ($data['status'] === 'resolved') {
            $errorLog->resolved_by = auth()->id();
            $errorLog->resolved_at = now();
        } else {
            $errorLog->resolved_by = null;
            $errorLog->resolved_at = null;
        }

        $errorLog->save();

        return back()->with('success', "Error {$errorLog->code} marked as {$data['status']}.");
    }

    public function destroy(ErrorLog $errorLog)
    {
        abort_if($errorLog->status === 'open', 403, 'Resolve or ignore this error before deleting it.');

        $errorLog->delete();

        return redirect()->route('admin.error-logs.index')->with('success', "Error {$errorLog->code} deleted.");
    }
}
