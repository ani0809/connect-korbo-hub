@extends('admin.layouts.app')

@section('title', 'Create New Page')

@section('content')
<div class="page-header">
    <div><h1 class="page-title">Create New Page</h1></div>
</div>

<div style="max-width:600px">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Page Details</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.builder.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label required">Page Name</label>
                    <input type="text" name="name" class="form-input" placeholder="e.g. Summer Sale 2024" required autofocus>
                    <p class="form-hint">This is the internal name (admin only)</p>
                </div>

                <div class="form-group">
                    <label class="form-label required">Page Type</label>
                    <select name="page_type" class="form-select" required>
                        <option value="custom">📄 Custom Page</option>
                        <option value="home">🏠 Homepage (replaces default)</option>
                        <option value="shop">🛒 Shop Page</option>
                        <option value="product">📦 Product Template</option>
                        <option value="category">🗂️ Category Template</option>
                        <option value="checkout">💳 Checkout Page</option>
                        <option value="thank_you">✅ Thank You Page</option>
                        <option value="about">ℹ️ About Page</option>
                        <option value="contact">📞 Contact Page</option>
                        <option value="faq">❓ FAQ Page</option>
                        <option value="blank">⬜ Blank (no header/footer)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">URL Slug (optional)</label>
                    <div style="display:flex;align-items:center;gap:6px">
                        <span style="color:var(--gray-400);font-size:13px;white-space:nowrap">/p/</span>
                        <input type="text" name="slug" class="form-input" placeholder="auto-generated">
                    </div>
                    <p class="form-hint">Leave empty to auto-generate from page name</p>
                </div>

                <div style="display:flex;gap:10px">
                    <button type="submit" class="btn btn-primary">Create Page & Open Builder →</button>
                    <a href="{{ route('admin.builder.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
