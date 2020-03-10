<?php

namespace App\Http\Controllers\Admin\KB;

use App\Http\Requests\KB\ArticleRequest;
use App\Models\KB\Article;
use App\Models\KB\Theme;
use App\Services\KB\ArticleService;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $articles = Article::query();
        $keyword = $request->get('search');

        if (!is_null($keyword)) {
            $articles = $articles->where('title', 'LIKE', "%$keyword%");
        }

        $articles = $articles->paginate(20);

        return view('admin.kb.article.index', [
            'articles' => $articles,
        ]);
    }

    public function create()
    {
        $themes = Theme::all()->pluck('name', 'id')->toArray();
        $types = Article::getAvailableTypes();
        $authors = User::query()->select('id', DB::raw("CONCAT(firstname,' ',lastname)  AS fullname"))
            ->pluck('fullname', 'id')->toArray();

        return view('admin.kb.article.create', [
            'article' => Article::class,
            'themes' => $themes,
            'types' => $types,
            'authors' => $authors,
        ]);
    }

    public function store(ArticleRequest $request)
    {
        $data = $request->all();

        $article = Article::create($data);
        $article->update(ArticleService::normalizeArticle($article, $data));

        foreach ($request->get('themes') as $item) {
            $article->themes()->attach($item);
        }
        if ($request->hasFile('img_preview')) {
            $article->img_preview = Storage::disk('kb_article')->putFileAs(
                $article->id, $request->file('img_preview'),
                'img_preview_' . time() . '.' . $request->file('img_preview')->getClientOriginalExtension()
            );
            $article->update();
        }

        return redirect()->route('admin.kb.article.edit', ['article' => $article]);
    }

    public function edit(Article $article)
    {
        $themes = Theme::all()->pluck('name', 'id')->toArray();
        $types = Article::getAvailableTypes();
        $articleThemes = $article->themes()->pluck('id')->toArray();
        $authors = User::query()->select('id', DB::raw("CONCAT(firstname,' ',lastname)  AS fullname"))
            ->pluck('fullname', 'id')->toArray();

        return view('admin.kb.article.edit', [
            'article' => $article,
            'themes' => $themes,
            'types' => $types,
            'articleThemes' => $articleThemes,
            'authors' => $authors,
        ]);
    }

    public function update(ArticleRequest $request, Article $article)
    {
        if ($request->hasFile('img_preview')) {
            Storage::disk('kb_article')->delete($article->img_preview);
        }

        $article->update(ArticleService::normalizeArticle($article, $request->all()));

        $article->themes()->detach();
        foreach ($request->get('themes') as $item) {
            $article->themes()->attach($item);
        }
        if ($request->hasFile('img_preview')) {
            $article->img_preview = Storage::disk('kb_article')->putFileAs(
                $article->id, $request->file('img_preview'),
                'img_preview_' . time() . '.' . $request->file('img_preview')->getClientOriginalExtension()
            );
            $article->update();
        }

        return redirect()->route('admin.kb.article.edit', ['article' => $article]);
    }

    public function destroy(Article $article)
    {
        //Storage::disk('kb_article')->deleteDirectory($article->id);
        $article->delete();

        return redirect()->route('admin.kb.article.index');
    }
}
