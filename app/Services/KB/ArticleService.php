<?php

namespace App\Services\KB;

use App\Models\KB\Article;
use Illuminate\Support\Facades\Storage;

class ArticleService
{
    public static function normalizeArticle($article, $data) {
        $data['video'] = null;
        $data['body'] = null;

        switch ($data['type']) {
            case Article::TYPE_VIDEO_IN:
                {
                    if (isset($data[Article::TYPE_VIDEO_IN])) {
                        $data['video'] = Storage::disk('kb_article')->putFileAs(
                            $article->id,
                            $data[Article::TYPE_VIDEO_IN],
                            'video.' . $data[Article::TYPE_VIDEO_IN]->getClientOriginalExtension()
                        );
                    }
                    break;
                }
            case Article::TYPE_VIDEO_OUT:
                {
                    if (Storage::disk('kb_article')->exists($article->video)) {
                        Storage::disk('kb_article')->delete($article->video);
                    }

                    $data['video'] = $data[Article::TYPE_VIDEO_OUT];
                    break;
                }
            case Article::TYPE_TEXT:
                {
                    if (Storage::disk('kb_article')->exists($article->video)) {
                        Storage::disk('kb_article')->delete($article->video);
                    }

                    $data['body'] = $data[Article::TYPE_TEXT];
                    break;
                }
        }

        return $data;
    }
}
