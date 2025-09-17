<?php

namespace App\Repositories;
use App\Interfaces\ArticleRepositoryInterface;
use App\Models\Article;
use Illuminate\Support\Collection;

class ArticleRepository implements ArticleRepositoryInterface
{
    /**
     * Get articles.
     * Standard: consistent order.
     *
     * @param string $sortBy
     * @param string $direction asc|desc
     * @return Collection
     */
    public function getAll(string $sortBy, string $direction): Collection
    {
        return Article::query()->orderBy($sortBy, $direction)->get();
    }
}
