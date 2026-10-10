<?php

namespace App\Observers;

use App\Models\Post;
use App\Support\SitemapBuilder;

class PostObserver
{
    public function saved(Post $post): void
    {
        SitemapBuilder::flush();
    }

    public function deleted(Post $post): void
    {
        SitemapBuilder::flush();
    }
}
