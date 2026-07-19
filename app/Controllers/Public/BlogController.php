<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Core\Response;

class BlogController extends BaseController
{
    private function posts(): array
    {
        return require __DIR__ . '/../../../resources/data/blog-posts.php';
    }

    public function index(): void
    {
        $this->view('public/blog/index', [
            'pageTitle' => 'Blog — NovaTrust',
            'metaDescription' => 'Plain-language explanations of how banking and payments actually work.',
            'posts' => $this->posts(),
        ]);
    }

    public function show(string $slug): void
    {
        $post = null;

        foreach ($this->posts() as $candidate) {
            if ($candidate['slug'] === $slug) {
                $post = $candidate;
                break;
            }
        }

        if ($post === null) {
            Response::abort(404, 'Post not found');
            return;
        }

        $this->view('public/blog/show', [
            'pageTitle' => $post['title'] . ' — NovaTrust',
            'metaDescription' => $post['excerpt'],
            'post' => $post,
        ]);
    }
}
