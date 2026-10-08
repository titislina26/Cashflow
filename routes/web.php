<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProjectionController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\JournalEntryController;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Account Switching & Sample Data
Route::get('/switch-account/{account}', [AccountController::class, 'switchAccount'])->name('switch-account');
Route::post('/load-sample-data', [AccountController::class, 'loadSampleData'])->name('load-sample-data');
Route::post('/reset-data', [AccountController::class, 'resetData'])->name('reset-data');

// Jobs (CRUD)
Route::resource('jobs', JobController::class)->except(['create', 'show', 'edit']);

// Transactions (CRUD & Helpers)
Route::prefix('transactions')->name('transactions.')->group(function () {
    Route::get('/', [TransactionController::class, 'index'])->name('index');
    Route::post('/', [TransactionController::class, 'store'])->name('store');
    Route::get('/import-template', [TransactionController::class, 'downloadTemplate'])->name('import-template');
    Route::post('/import', [TransactionController::class, 'import'])->name('import');
    Route::put('/{id}', [TransactionController::class, 'update'])->name('update');
    Route::delete('/bulk-delete', [TransactionController::class, 'bulkDelete'])->name('bulk-delete');
    Route::delete('/{id}', [TransactionController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/restore', [TransactionController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force', [TransactionController::class, 'forceDelete'])->name('force-delete');
    Route::get('/export-csv', [TransactionController::class, 'exportCsv'])->name('export-csv');
});

// General Journal (Record Journal Entry ala MYOB)
Route::prefix('journal-entries')->name('journal-entries.')->group(function () {
    Route::get('/', [JournalEntryController::class, 'index'])->name('index');
    Route::post('/', [JournalEntryController::class, 'store'])->name('store');
    Route::get('/export-csv', [JournalEntryController::class, 'exportCsv'])->name('export-csv');
    Route::get('/{id}', [JournalEntryController::class, 'show'])->name('show');
    Route::delete('/{id}', [JournalEntryController::class, 'destroy'])->name('destroy');
});

// Categories (CRUD)
Route::prefix('categories')->name('categories.')->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->name('index');
    Route::get('/import-template', [CategoryController::class, 'downloadTemplate'])->name('import-template');
    Route::post('/', [CategoryController::class, 'store'])->name('store');
    Route::post('/import', [CategoryController::class, 'import'])->name('import');
    Route::put('/{id}', [CategoryController::class, 'update'])->name('update');
    Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('destroy');
});

// Reports
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/lsd', [ReportController::class, 'lsd'])->name('reports.lsd');

// Trial Balance (Neraca Saldo ala MYOB)
Route::get('/reports/trial-balance', [ReportController::class, 'trialBalance'])->name('reports.trial-balance');
Route::get('/reports/trial-balance/export-csv', [ReportController::class, 'exportTrialBalanceCsv'])->name('reports.trial-balance.export-csv');
Route::get('/reports/trial-balance/print', [ReportController::class, 'printTrialBalance'])->name('reports.trial-balance.print');

// General Ledger (Buku Besar ala MYOB)
Route::get('/reports/general-ledger', [ReportController::class, 'generalLedger'])->name('reports.general-ledger');
Route::get('/reports/general-ledger/export-csv', [ReportController::class, 'exportGeneralLedgerCsv'])->name('reports.general-ledger.export-csv');
Route::get('/reports/general-ledger/print', [ReportController::class, 'printGeneralLedger'])->name('reports.general-ledger.print');

// Reports Export & Print
Route::get('/reports/neraca/export-csv', [ReportController::class, 'exportNeracaCsv'])->name('reports.neraca.export-csv');
Route::get('/reports/neraca/print', [ReportController::class, 'printNeraca'])->name('reports.neraca.print');
Route::get('/reports/summary/export-csv', [ReportController::class, 'exportSummaryCsv'])->name('reports.summary.export-csv');
Route::get('/reports/summary/print', [ReportController::class, 'printSummary'])->name('reports.summary.print');
Route::get('/reports/pnl/export-csv', [ReportController::class, 'exportPnlCsv'])->name('reports.pnl.export-csv');
Route::get('/reports/pnl/print', [ReportController::class, 'printPnl'])->name('reports.pnl.print');

// Projections
Route::get('/projections', [ProjectionController::class, 'index'])->name('projections.index');
