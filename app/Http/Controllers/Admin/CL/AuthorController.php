<?php

namespace App\Http\Controllers\Admin\CL;

use App\Models\CL\Author;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthorController extends Controller
{
    public function index(Request $request)
    {
        $authors = Author::query();
        $keyword = $request->get('search');

        if (!is_null($keyword)) {
            $authors = $authors->where('firstname', 'LIKE', "%$keyword%")
                ->orWhere('lastname', 'LIKE', "%$keyword%")
                ->orWhere('middlename', 'LIKE', "%$keyword%");
        }

        $authors = $authors->paginate(20);

        return view('admin.cl.author.index', [
            'authors' => $authors,
        ]);
    }

    public function create()
    {
        return view('admin.cl.author.create', [
            'author' => Author::class,
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'firstname' => 'required',
            'lastname' => 'required',
            'about' => '',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $request->all();

        $author = new Author();
        $author->fill($data);
        $author->save();

        if ($request->hasFile('avatar')) {
            $data['avatar'] = Storage::disk('cl_author')->putFileAs(
                $author->id, $data['avatar'], 'avatar_' . time() . '.' . $data['avatar']->getClientOriginalExtension()
            );
        }

        $author->update($data);

        return redirect()->route('admin.cl.author.edit', ['author' => $author]);
    }

    public function edit(Author $author)
    {
        return view('admin.cl.author.edit', [
            'author' => $author,
        ]);
    }

    public function update(Author $author, Request $request)
    {
        $this->validate($request, [
            'firstname' => 'required',
            'lastname' => 'required',
            'about' => '',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('avatar')) {
            Storage::disk('cl_author')->delete($author->avatar);
            $data['avatar'] = Storage::disk('cl_author')->putFileAs(
                $author->id, $data['avatar'], 'avatar_' . time() . '.' . $data['avatar']->getClientOriginalExtension()
            );
        }

        $author->update($data);

        return redirect()->route('admin.cl.author.edit', ['author' => $author]);
    }

    public function destroy(Author $author)
    {
        //Storage::disk('cl_author')->deleteDirectory($author->id);
        $author->delete();

        return redirect()->route('admin.cl.author.index');
    }
}
