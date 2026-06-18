<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('articles.edit') || $user->can('articles.review');
    }

    public function create(User $user): bool
    {
        return $user->can('articles.create');
    }

    public function update(User $user, Article $article): bool
    {
        return $user->can('articles.edit') || $user->id === $article->author_id;
    }

    public function review(User $user): bool
    {
        return $user->can('articles.review');
    }

    public function publish(User $user): bool
    {
        return $user->can('articles.publish');
    }
}
