<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Plan;
use App\Models\Product;
use App\Services\LicenseKeyGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function index(Request $request)
    {
        $query = License::with(['product','plan']);
        if ($request->filled('status')) { $query->where('status', $request->string('status')); }
        if ($request->filled('search')) { $query->where('license_key', 'like', '%'.$request->string('search').'%'); }
        $licenses = $query->latest()->paginate(20);
        return view('admin.licenses.index', compact('licenses'));
    }

    public function create() { return view('admin.licenses.create', ['products'=>Product::all(),'plans'=>Plan::all()]); }

    public function store(Request $request, LicenseKeyGenerator $generator): RedirectResponse
    {
        $data = $request->validate(['product_id'=>'required|exists:products,id','plan_id'=>'required|exists:plans,id','buyer_name'=>'required|string','buyer_email'=>'required|email','max_domains'=>'required|integer|min:1']);
        $data['license_key'] = $generator->generate();
        $data['status'] = 'active';
        License::create($data);
        return redirect()->route('admin.licenses.index')->with('ok', 'License created.');
    }

    public function show(License $license) { $license->load('activations'); return view('admin.licenses.show', compact('license')); }

    public function update(Request $request, License $license): RedirectResponse
    {
        $license->update($request->validate(['status'=>'required|in:active,suspended,expired']));
        return back()->with('ok', 'License updated.');
    }
}
