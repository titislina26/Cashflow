<?php

namespace App\Http\Controllers;

use App\Models\AccountingJob;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index()
    {
        $activeAccount = session('active_account', 'petty_cash');
        $jobs = AccountingJob::academicOrder()->get();
        return view('jobs', compact('activeAccount', 'jobs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:accounting_jobs,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        AccountingJob::create($request->all());

        return redirect()->back()->with('success', 'Proyek (Job) berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $job = AccountingJob::findOrFail($id);

        $request->validate([
            'code' => 'required|string|max:50|unique:accounting_jobs,code,' . $job->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        $job->update($request->all());

        return redirect()->back()->with('success', 'Proyek (Job) berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $job = AccountingJob::findOrFail($id);
        $job->delete();

        return redirect()->back()->with('success', 'Proyek (Job) berhasil dihapus!');
    }
}
