<?php

namespace App\Http\Controllers\Admin\QA;

use App\Models\QA\Theme;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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

        return view('admin.qa.theme.index', [
            'themes' => $themes,
        ]);
    }

    public function create()
    {
        return view('admin.qa.theme.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);

        $theme = Theme::create($request->all());

        return redirect()->route('admin.qa.theme.edit', ['theme' => $theme]);
    }

    public function edit(Theme $theme)
    {
        return view('admin.qa.theme.edit', [
            'theme' => $theme,
        ]);
    }

    public function update(Request $request, Theme $theme)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);

        $theme->update($request->all());

        return redirect()->route('admin.qa.theme.edit', ['theme' => $theme]);
    }

    public function destroy($id)
    {
        $theme = Theme::find($id);
        $theme->delete();

        return redirect()->route('admin.qa.theme.index');
    }
}
