<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiUserCompanyLocationResource extends JsonResource
{

    /**
     * Transform the resource collection into an array.
     *
     * @param Request|Collection $request
     * @return array
     */
    public function toArray(Request|Collection $request): array
    {
        $company_location_data = [
            'company_location_id' => $this->resource['company_location_id'],
            'company_id'=> $this->resource['company_id'],
            'company_name_short' => $this->resource['company_name_short'],
            'company_name_full' => $this->resource['company_name_full'],
            'country_name' => $this->resource['country_name'],
            'country_id' => $this->resource['country_id'],
            'region_name' => $this->resource['region_name'],
            'region_id' => $this->resource['region_id'],
            'district_name' => $this->resource['district_name'],
            'district_id' => $this->resource['district_id'],
        ];

        if (isset($this->resource['user_company_location_id']))
        {
            $company_location_data['active'] = $this->resource['user_company_location_id'] == $this->resource['company_location_id'];
        }
        return $company_location_data;
    }
}
