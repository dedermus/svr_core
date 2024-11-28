<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiUserDistrictResource extends JsonResource
{

    /**
     * Transform the resource collection into an array.
     *
     * @param Request|Collection $request
     * @return array
     */
    public function toArray(Request|Collection $request): array
    {
        $district_data = [
            'district_id' => $this->resource['district_id'],
            'district_im'=> $this->resource['district_name'],
        ];

        if (isset($this->resource['user_district_id']))
        {
            $district_data['active'] = $this->resource['user_district_id'] == $this->resource['district_id'];
        }
        return $district_data;
    }
}
