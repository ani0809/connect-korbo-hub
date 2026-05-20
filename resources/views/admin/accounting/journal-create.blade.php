@extends('admin.layouts.app')
@section('title', 'Manual Journal Entry')
@section('content')
<form method="post" action="{{ route('admin.accounting.journals.store') }}" x-data="journalForm()" class="space-y-4">
  @csrf
  <div class="bg-white border rounded-xl p-4 grid md:grid-cols-2 gap-3">
    <div><label class="text-sm font-medium block">Date</label><input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" class="w-full border rounded px-3 py-2" required></div>
    <div><label class="text-sm font-medium block">Description</label><input type="text" name="description" value="{{ old('description') }}" class="w-full border rounded px-3 py-2" required></div>
  </div>

  <div class="bg-white border rounded-xl p-4">
    <div class="flex items-center justify-between mb-2">
      <h3 class="font-semibold">Journal Lines</h3>
      <button type="button" class="btn-secondary text-sm" @click="addLine()">+ Add Line</button>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="text-left border-b"><th class="p-2">Account</th><th class="p-2">Description</th><th class="p-2">Debit</th><th class="p-2">Credit</th><th></th></tr></thead>
        <tbody>
          <template x-for="(line, idx) in lines" :key="idx">
            <tr class="border-b">
              <td class="p-2">
                <select class="border rounded px-2 py-1 w-full" :name="`lines[${idx}][account_id]`" x-model="line.account_id" required>
                  <option value="">Select account</option>
                  @foreach($accounts as $group => $items)
                    @foreach($items as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                    @endforeach
                  @endforeach
                </select>
              </td>
              <td class="p-2"><input class="border rounded px-2 py-1 w-full" :name="`lines[${idx}][description]`" x-model="line.description"></td>
              <td class="p-2"><input type="number" step="0.01" min="0" class="border rounded px-2 py-1 w-full" :name="`lines[${idx}][debit]`" x-model.number="line.debit"></td>
              <td class="p-2"><input type="number" step="0.01" min="0" class="border rounded px-2 py-1 w-full" :name="`lines[${idx}][credit]`" x-model.number="line.credit"></td>
              <td class="p-2"><button type="button" class="text-red-600" @click="removeLine(idx)">×</button></td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
    <div class="mt-3 text-sm">
      <div>Debits: <strong x-text="format(totalDebit)"></strong></div>
      <div>Credits: <strong x-text="format(totalCredit)"></strong></div>
      <div>Difference: <strong :class="isBalanced ? 'text-green-600' : 'text-red-600'" x-text="format(Math.abs(totalDebit-totalCredit))"></strong></div>
      <div :class="isBalanced ? 'text-green-600' : 'text-red-600'" x-text="isBalanced ? 'Balanced' : 'Not balanced'"></div>
    </div>
  </div>

  <button class="btn-primary" :disabled="!isBalanced || lines.length < 2">Save Journal Entry</button>
</form>

@push('scripts')
<script>
function journalForm() {
  return {
    lines: [{account_id: '', description: '', debit: 0, credit: 0}, {account_id: '', description: '', debit: 0, credit: 0}],
    addLine() { this.lines.push({account_id: '', description: '', debit: 0, credit: 0}); },
    removeLine(i) { if (this.lines.length > 2) this.lines.splice(i, 1); },
    get totalDebit() { return this.lines.reduce((s, l) => s + (Number(l.debit) || 0), 0); },
    get totalCredit() { return this.lines.reduce((s, l) => s + (Number(l.credit) || 0), 0); },
    get isBalanced() { return Math.abs(this.totalDebit - this.totalCredit) < 0.01; },
    format(n) { return (Number(n) || 0).toFixed(2); },
  }
}
</script>
@endpush
@endsection

