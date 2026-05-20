<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\UpdateRelease;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateReleaseController extends Controller
{
    public function index() { return view('admin.releases.index', ['releases' => UpdateRelease::with('product')->latest()->paginate(20)]); }
    public function create() { return view('admin.releases.create', ['products' => Product::all()]); }
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['product_id'=>'required|exists:products,id','version'=>'required|string','min_php'=>'required|string','changelog'=>'required|string','zip'=>'required|file']);
        $data['zip_path'] = $request->file('zip')->store('updates', 'local');
        $data['is_published'] = (bool) $request->boolean('is_published');
        $data['published_at'] = $data['is_published'] ? now() : null;
        UpdateRelease::create($data);
        return redirect()->route('admin.releases.index')->with('ok', 'Release saved.');
    }
}
