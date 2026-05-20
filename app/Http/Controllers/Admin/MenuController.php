<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::query()->with(['items' => fn ($q) => $q->orderBy('sort_order')])->get();
        $currentMenu = $menus->first();
        return view('admin.menus.index', compact('menus', 'currentMenu'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100', 'slug' => 'required|string|max:100|unique:menus,slug', 'location' => 'required|in:primary,footer,mobile']);
        Menu::query()->create($request->only(['name', 'slug', 'location']));
        return back()->with('success', 'Menu created.');
    }

    public function storeItem(Request $request, int $menu)
    {
        $request->validate(['label' => 'required|string|max:191', 'url' => 'required|string|max:255', 'parent_id' => 'nullable|integer']);
        MenuItem::query()->create([
            'menu_id' => $menu,
            'parent_id' => $request->input('parent_id'),
            'label' => $request->input('label'),
            'url' => $request->input('url'),
            'target' => '_self',
            'has_dropdown' => false,
            'is_mega' => false,
            'sort_order' => (int) MenuItem::query()->where('menu_id', $menu)->max('sort_order') + 1,
            'is_active' => true,
        ]);
        return back()->with('success', 'Menu item added.');
    }

    public function updateItem(Request $request, int $id)
    {
        $item = MenuItem::query()->findOrFail($id);
        $item->update($request->only(['label', 'url', 'target', 'icon', 'has_dropdown', 'is_mega', 'mega_type', 'mega_content', 'mega_category_id', 'is_active']));
        return back()->with('success', 'Menu item updated.');
    }

    public function destroyItem(int $id)
    {
        MenuItem::query()->where('id', $id)->orWhere('parent_id', $id)->delete();
        return back()->with('success', 'Menu item removed.');
    }

    public function reorder(Request $request): JsonResponse
    {
        $request->validate(['items' => 'required|array']);
        foreach ($request->items as $index => $row) {
            MenuItem::query()->where('id', $row['id'])->update(['sort_order' => $index + 1, 'parent_id' => $row['parent_id'] ?? null]);
        }
        return response()->json(['success' => true, 'message' => 'Menu order saved.', 'data' => []]);
    }
}
