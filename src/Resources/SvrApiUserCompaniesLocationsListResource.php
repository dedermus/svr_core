<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class SvrApiUserCompaniesLocationsListResource extends JsonResource
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
        foreach ($this->resource['user_companies_locations_list'] as $value)
        {
            if (isset($this->resource['user_participation_info']['company_location_id'])) {
                $value->user_company_location_id = $this->resource['user_participation_info']['company_location_id'];
                $returned_data[$value->company_location_id] = new SvrApiUserCompanyLocationResource(collect($value));
            }
        }
        return $returned_data;
    }
}
