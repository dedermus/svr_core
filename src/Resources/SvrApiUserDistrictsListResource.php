<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class SvrApiUserDistrictsListResource extends JsonResource
{
    /**
     * Указывает, следует ли сохранить ключи коллекции ресурса.
     *
     * @var bool
     */
    public bool $preserveKeys = true;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request|Collection $request
     * @return array
     */
    public function toArray(Request|Collection $request): array
    {
        $returned_data = [];
        foreach ($this->resource['user_districts_list'] as $value)
        {
            $value->user_district_id = $this->resource['user_participation_info']['district_id'];
            $returned_data[$value->district_id] = new SvrApiUserDistrictResource(collect($value));
        }
        return $returned_data;
    }
}
