<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::latest('published_at')->paginate(15);
        return view('admin.articles.index', compact('articles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'url' => 'required|url|unique:articles',
            'source_name' => 'required|string|max:255',
            'published_at' => 'required|date',
            'sentiment_label' => 'nullable|in:positive,neutral,negative'
        ]);

        Article::create($validated);
        return redirect()->route('articles.index')->with('success', 'Article added successfully');
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'url' => 'required|url|unique:articles,url,' . $article->id,
            'source_name' => 'required|string|max:255',
            'published_at' => 'required|date',
            'sentiment_label' => 'nullable|in:positive,neutral,negative'
        ]);

        $article->update($validated);
        return redirect()->route('articles.index')->with('success', 'Article updated successfully');
    }

    public function destroy(Article $article)
    {
        $article->delete();
        return redirect()->route('articles.index')->with('success', 'Article deleted successfully');
    }
}
