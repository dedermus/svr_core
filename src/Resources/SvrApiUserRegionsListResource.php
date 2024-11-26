<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiUserRegionsListResource extends JsonResource
{
    /**
     * Указывает, следует ли сохранить ключи коллекции ресурса.
     *
     * @var bool
     */
    public $preserveKeys = true;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request|Collection $request
     * @return array
     */
    public function toArray(Request|Collection $request): array
    {
        $returned_data = [];
        foreach ($this->resource['user_regions_list'] as $value)
        {
            $value->user_region_id = $this->resource['user_participation_info']['region_id'];
            $returned_data[$value->region_id] = new SvrApiUserRegionResource(collect($value));
        }
        return $returned_data;
    }
}
