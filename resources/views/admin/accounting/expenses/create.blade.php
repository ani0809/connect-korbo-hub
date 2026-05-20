@extends('admin.layouts.app')
@section('title', 'Record Expense')
@section('content')
<form method="post" action="{{ isset($expense) ? route('admin.accounting.expenses.update', $expense->id) : route('admin.accounting.expenses.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
  @csrf
  @if(isset($expense)) @method('PUT') @endif
  <div class="lg:col-span-2 space-y-4">
    <div class="bg-white border rounded-xl p-4 space-y-3">
      <label class="text-sm font-medium block">Description *</label>
      <textarea name="description" rows="3" class="w-full border rounded px-3 py-2" required>{{ old('description', $expense->description ?? '') }}</textarea>

      <div class="grid md:grid-cols-2 gap-3">
        <div>
          <label class="text-sm font-medium block">Category *</label>
          <select name="category_id" class="w-full border rounded px-3 py-2" required>
            <option value="">Select category</option>
            @foreach($categories as $cat)
              <option value="{{ $cat->id }}" @selected((string)old('category_id', $expense->category_id ?? '')===(string)$cat->id)>{{ $cat->name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="text-sm font-medium block">Date *</label>
          <input type="date" name="date" value="{{ old('date', isset($expense) ? $expense->date?->format('Y-m-d') : now()->toDateString()) }}" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
          <label class="text-sm font-medium block">Amount *</label>
          <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $expense->amount ?? '') }}" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
          <label class="text-sm font-medium block">Tax Amount</label>
          <input type="number" step="0.01" min="0" name="tax_amount" value="{{ old('tax_amount', $expense->tax_amount ?? 0) }}" class="w-full border rounded px-3 py-2">
        </div>
      </div>
    </div>

    <div class="bg-white border rounded-xl p-4 space-y-3">
      <div class="font-semibold">Payment Details</div>
      <div class="grid md:grid-cols-2 gap-3">
        <div>
          <label class="text-sm font-medium block">Payment Method *</label>
          <select name="payment_method" class="w-full border rounded px-3 py-2" required>
            @foreach(['cash'=>'Cash','bank'=>'Bank Transfer','mobile'=>'bKash/Nagad','card'=>'Card'] as $k => $v)
              <option value="{{ $k }}" @selected(old('payment_method', $expense->payment_method ?? '')===$k)>{{ $v }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="text-sm font-medium block">Paid From (account)</label>
          <select name="payment_account_id" class="w-full border rounded px-3 py-2">
            <option value="">Auto (Cash in Hand)</option>
            @foreach($paymentAccounts as $acc)
              <option value="{{ $acc->id }}" @selected((string)old('payment_account_id', $expense->payment_account_id ?? '')===(string)$acc->id)>{{ $acc->code }} - {{ $acc->name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="text-sm font-medium block">Reference #</label>
          <input type="text" name="reference" value="{{ old('reference', $expense->reference ?? '') }}" class="w-full border rounded px-3 py-2">
        </div>
        <div>
          <label class="text-sm font-medium block">Attachment</label>
          <input type="file" name="attachment" class="w-full border rounded px-3 py-2">
        </div>
      </div>
    </div>
  </div>

  <div class="space-y-4">
    <div class="bg-white border rounded-xl p-4 space-y-3 sticky top-24">
      <div class="font-semibold">Recurring</div>
      <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_recurring" value="1" @checked(old('is_recurring', $expense->is_recurring ?? false))> This is recurring</label>
      <div>
        <label class="text-sm font-medium block">Repeat</label>
        <select name="recurring_interval" class="w-full border rounded px-3 py-2">
          <option value="">None</option>
          @foreach(['weekly','monthly','yearly'] as $r)<option value="{{ $r }}" @selected(old('recurring_interval', $expense->recurring_interval ?? '')===$r)>{{ ucfirst($r) }}</option>@endforeach
        </select>
      </div>
      <div>
        <label class="text-sm font-medium block">Next Due</label>
        <input type="date" name="next_due_date" value="{{ old('next_due_date', isset($expense) ? $expense->next_due_date?->format('Y-m-d') : '') }}" class="w-full border rounded px-3 py-2">
      </div>
      <button class="btn-primary w-full">{{ isset($expense) ? 'Update Expense' : 'Record Expense' }}</button>
    </div>
  </div>
</form>
@endsection

