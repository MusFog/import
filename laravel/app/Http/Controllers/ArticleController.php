<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleListRequest;
use App\Interfaces\ArticleServiceInterface;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    public function __construct(
        private ArticleServiceInterface $articleServiceInterface
    ) {}


    /**
     * List articles.
     * JSON standard via toResponse().
     *
     * @param ArticleListRequest $listRequest Validated filters/sort.
     * @return JsonResponse
     */
    public function list(ArticleListRequest $listRequest): JsonResponse
    {
        return $this->toResponse($this->articleServiceInterface->list($listRequest));
    }
}
