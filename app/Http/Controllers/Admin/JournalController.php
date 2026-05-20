<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Journal;
use App\Services\AccountingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JournalController extends Controller
{
    public function index(Request $request): View
    {
        $journals = Journal::query()
            ->with(['creator', 'lines.account'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('date', '<=', $request->date_to))
            ->when($request->filled('search'), function ($q) use ($request): void {
                $s = '%'.$request->search.'%';
                $q->where(fn ($q2) => $q2->where('journal_number', 'like', $s)->orWhere('description', 'like', $s));
            })
            ->latest()
            ->paginate(30);

        return view('admin.accounting.journals', compact('journals'));
    }

    public function create(): View
    {
        $accounts = Account::query()->where('is_active', true)->orderBy('code')->get()->groupBy('type');
        return view('admin.accounting.journal-create', compact('accounts'));
    }

    public function store(Request $request, AccountingService $accounting): RedirectResponse
    {
        $data = $request->validate([
            'date' => 'required|date',
            'description' => 'required|string',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounts,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
            'lines.*.description' => 'nullable|string|max:191',
        ]);

        $totalDebit = (float) collect($data['lines'])->sum(fn ($l) => (float) ($l['debit'] ?? 0));
        $totalCredit = (float) collect($data['lines'])->sum(fn ($l) => (float) ($l['credit'] ?? 0));
        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withErrors(['lines' => "Debits ({$totalDebit}) must equal Credits ({$totalCredit})"])->withInput();
        }

        $accounting->createJournal((string) $data['description'], $data['lines'], (string) $data['date'], 'Manual', null);
        return redirect()->route('admin.accounting.journals.index')->with('success', 'Journal entry posted.');
    }

    public function void(Request $request, int $id, AccountingService $accounting): RedirectResponse
    {
        $request->validate(['reason' => 'nullable|string|max:191']);
        $journal = Journal::query()->where(['id' => $id, 'status' => 'posted'])->with('lines')->firstOrFail();
        $reversedLines = $journal->lines->map(fn ($line) => [
            'account_id' => $line->account_id,
            'debit' => (float) $line->credit,
            'credit' => (float) $line->debit,
            'description' => 'VOID: '.($line->description ?? ''),
        ])->all();

        $accounting->createJournal('VOID: '.$journal->description, $reversedLines, now()->toDateString(), 'Manual', null);
        $journal->update([
            'status' => 'voided',
            'voided_at' => now(),
            'void_reason' => $request->input('reason'),
        ]);

        return back()->with('success', 'Journal voided and reversal posted.');
    }
}

