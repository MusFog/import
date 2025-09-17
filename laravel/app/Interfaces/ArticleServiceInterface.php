<?php

namespace App\Interfaces;
use App\Http\Requests\ArticleListRequest;
use Illuminate\Support\Collection;

interface ArticleServiceInterface
{
    public function list(ArticleListRequest $listRequest): Collection;
}
