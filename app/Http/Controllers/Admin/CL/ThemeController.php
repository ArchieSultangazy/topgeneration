<?php

namespace App\Http\Controllers\Admin\CL;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CL\Theme;

class ThemeController extends Controller
{
    public function index(Request $request)
    {
        $themes = Theme::query();
        $keyword = $request->get('search');

        if (!is_null($keyword)) {
            $themes = $themes->where('name', 'LIKE', "%$keyword%");
        }

        $themes = $themes->paginate(20);

        return view('admin.cl.theme.index', [
            'themes' => $themes,
        ]);
    }

    public function create()
    {
        return view('admin.cl.theme.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);

        $theme = Theme::create($request->all());

        return redirect()->route('admin.cl.theme.edit', ['theme' => $theme]);
    }

    public function edit(Theme $theme)
    {
        return view('admin.cl.theme.edit', [
            'theme' => $theme,
        ]);
    }

    public function update(Request $request, Theme $theme)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);

        $theme->update($request->all());

        return redirect()->route('admin.cl.theme.edit', ['theme' => $theme]);
    }

    public function destroy(Theme $theme)
    {
        $theme->delete();

        return redirect()->route('admin.cl.theme.index');
    }
}
