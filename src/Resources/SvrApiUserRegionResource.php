<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class SvrApiUserRegionResource extends JsonResource
{

    /**
     * Transform the resource collection into an array.
     *
     * @param Request|Collection $request
     * @return array
     */
    public function toArray(Request|Collection $request): array
    {
        $region_data = [
            'region_id' => $this->resource['region_id'],
            'region_im'=> $this->resource['region_name'],
        ];

        if (isset($this->resource['user_region_id']))
        {
            $region_data['active'] = $this->resource['user_region_id'] == $this->resource['region_id'];
        }
        return $region_data;
    }
}
