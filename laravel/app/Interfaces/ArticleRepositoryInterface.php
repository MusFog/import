<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;

interface ArticleRepositoryInterface
{
    public function getAll(string $sortBy, string $direction): Collection;
}
