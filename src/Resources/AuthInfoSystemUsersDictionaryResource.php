<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class AuthInfoSystemUsersDictionaryResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request|Collection $request
     * @return array
     */
    public function toArray(Request|Collection $request): array
    {
        return [
            'user_roles_list' => new SvrApiUserRolesListResource($this->resource),
            'user_companies_locations_list' => new SvrApiUserCompaniesLocationsListResource($this->resource),
            'user_regions_list' => new SvrApiUserRegionsListResource($this->resource),
            'user_districts_list' => new SvrApiUserDistrictsListResource($this->resource),
        ];
    }
}
