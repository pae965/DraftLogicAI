<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ArticlePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Article $article): bool
    {
        if ($user->isEditor()) return true;
        if ($article->primary_author_id === $user->id) return true;
        // co-author / advisor ก็ดูได้
        if ($article->authors()->where('user_id', $user->id)->exists()) return true;
        // public published article
        return $article->isPublished();
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [User::ROLE_AUTHOR, User::ROLE_EDITOR, User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN], true);
    }

    public function update(User $user, Article $article): bool
    {
        if ($user->isEditor()) return true;
        if ($article->primary_author_id === $user->id) return true;
        // co-author แก้ได้, advisor ก็แก้ได้
        return $article->authors()
            ->where('user_id', $user->id)
            ->whereIn('role', ['co_author', 'advisor'])
            ->exists();
    }

    public function delete(User $user, Article $article): bool
    {
        if ($user->isAdmin()) return true;
        return $article->primary_author_id === $user->id;
    }

    public function restore(User $user, Article $article): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Article $article): bool
    {
        return $user->isSuperAdmin();
    }
}
