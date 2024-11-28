<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class AuthHerriotRequisitesDataResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request|Collection $request
     *
     * @return array
     */
    public function toArray(Request|Collection $request): array
    {
        return [];
    }
}
