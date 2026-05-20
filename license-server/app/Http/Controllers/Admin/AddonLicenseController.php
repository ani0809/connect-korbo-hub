<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AddonLicense;
use App\Models\License;
use App\Services\LicenseKeyGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AddonLicenseController extends Controller
{
    public function index() { return view('admin.addon-licenses.index', ['rows' => AddonLicense::with('parentLicense')->latest()->paginate(20)]); }
    public function create() { return view('admin.addon-licenses.create', ['licenses' => License::all()]); }
    public function store(Request $request, LicenseKeyGenerator $generator): RedirectResponse
    {
        $data = $request->validate(['parent_license_id'=>'required|exists:licenses,id','addon_slug'=>'required|string','addon_name'=>'required|string']);
        $data['addon_license_key'] = $generator->generate();
        $data['status'] = 'active';
        AddonLicense::create($data);
        return redirect()->route('admin.addons.index')->with('ok', 'Addon key created.');
    }
}
