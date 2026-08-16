<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;

class SitemapController extends Controller
{
    /** XML-карта сайта для поисковиков. */
    public function index()
    {
        $urls = [];

        $urls[] = ['loc' => route('home'), 'freq' => 'hourly', 'pri' => '1.0'];
        $urls[] = ['loc' => route('fixtures'), 'freq' => 'daily', 'pri' => '0.7'];
        $urls[] = ['loc' => route('table'), 'freq' => 'daily', 'pri' => '0.7'];
        $urls[] = ['loc' => route('transfers'), 'freq' => 'daily', 'pri' => '0.6'];

        foreach (Category::ordered()->get() as $c) {
            $urls[] = ['loc' => route('category', $c->slug), 'freq' => 'daily', 'pri' => '0.6'];
        }

        Article::published()->get()->each(function (Article $a) use (&$urls) {
            $urls[] = [
                'loc'     => route('article.show', $a),
                'lastmod' => optional($a->published_at)->toAtomString(),
                'freq'    => 'weekly',
                'pri'     => '0.8',
            ];
        });

        $xml = view('sitemap', compact('urls'))->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function robots()
    {
        $body = "User-agent: *\n"
            . "Allow: /\n"
            . "Disallow: /admin\n"
            . "Disallow: /login\n"
            . "Disallow: /dashboard\n\n"
            . 'Sitemap: ' . url('/sitemap.xml') . "\n";

        return response($body, 200)->header('Content-Type', 'text/plain');
    }
}
