<?php

namespace App\Services;

use App\Http\Requests\ArticleListRequest;
use App\Interfaces\ArticleRepositoryInterface;
use App\Interfaces\ArticleServiceInterface;
use Illuminate\Support\Collection;

class ArticleService implements ArticleServiceInterface
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository
    ) {}

    /**
     * Fetch articles with normalized sort.
     * Standard: sanitize inputs to safe defaults.
     *
     * @param ArticleListRequest $listRequest
     * @return Collection
     */
    public function list(ArticleListRequest $listRequest): Collection
    {
        $sortBy = $listRequest->sortBy === 'published_at' ? 'published_at' : 'title';
        $direction = $listRequest->direction === 'desc' ? 'desc' : 'asc';

        return $this->articleRepository->getAll($sortBy, $direction);
    }
}
