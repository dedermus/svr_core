<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiPaginationResponseResource extends JsonResource
{

    /**
     * Transform the resource collection into an array.
     *
     * @param Request|Collection $request
     * @return array
     */
    public function toArray(Request|Collection $request): array
    {
        $this->resource['per_page'] = $this->resource['per_page'] == 0 ? 1: $this->resource['per_page'];
        return [
            'total_records' => $this->resource['total_records'],
            'per_page' => $this->resource['per_page'],
            'cur_page' => $this->resource['cur_page'],
            'max_page' => ceil($this->resource['total_records']/ $this->resource['per_page']),
        ];

    }
}
