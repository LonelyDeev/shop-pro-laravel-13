<?php

namespace Themes\WeblakShop\src\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    public function index()
    {
        $sitemap = app()->make('sitemap');
        $sitemap->setCache('laravel.sitemap', 60);

        if (!$sitemap->isCached()) {

            $sitemap->add(
                url()->to('/sitemap-posts'),
                '2017-08-25T20:10:00+02:00',
                '0.9',
                'daily'
            );
            $sitemap->add(
                url()->to('/sitemap-pages'),
                '2017-08-25T20:10:00+02:00',
                '0.9',
                'daily'
            );

            $sitemap->add(
                url()->to('/sitemap-products'),
                '2017-08-25T20:10:00+02:00',
                '0.9',
                'daily'
            );
        }

        return $sitemap->render();
    }

    public function posts()
    {
        $sitemap = app()->make('sitemap');
        $sitemap->setCache('laravel.sitemap.posts', 60);

        if (!$sitemap->isCached()) {
            $posts = Post::published()->latest('updated_at')->get();
            foreach ($posts as $post) {
                $sitemap->add(
                    route('front.articles.show', ['post' => $post]),
                    $post->updated_at,
                    '0.9',
                    'weekly'
                );
            }
        }

        return $sitemap->render();
    }

    public function pages()
    {
        $sitemap = app()->make('sitemap');
        $sitemap->setCache('laravel.sitemap.pages', 60);

        if (!$sitemap->isCached()) {
            $pages = Page::where('published', true)->latest('updated_at')->get();
            foreach ($pages as $page) {
                $sitemap->add(
                    route('front.pages.show', ['page' => $page]),
                    $page->updated_at,
                    '0.9',
                    'weekly'
                );
            }
        }

        return $sitemap->render();
    }

    public function products()
    {
        $sitemap = app()->make('sitemap');
        $sitemap->setCache('laravel.sitemap.products', 60);

        if (!$sitemap->isCached()) {

            $products = Product::published()->latest('updated_at')->get();

            foreach ($products as $product) {
                $sitemap->add(
                    route('front.products.show', ['product' => $product]),
                    $product->updated_at,
                    '0.9',
                    'daily'
                );
            }
        }

        return $sitemap->render();
    }
}
