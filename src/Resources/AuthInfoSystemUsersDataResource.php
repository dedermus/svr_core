<?php

namespace Svr\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


class AuthInfoSystemUsersDataResource extends JsonResource
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
            'user_id' => $this->resource['user']['user_id'],
            'user_token' => $this->resource['user_token'],
            'user_first' => $this->resource['user']['user_first'],
            'user_middle' => $this->resource['user']['user_middle'],
            'user_last' => $this->resource['user']['user_last'],
            'user_avatar_small' => $this->resource['avatars']['user_avatar_small'],
            'user_avatar_big' => $this->resource['avatars']['user_avatar_big'],
            'user_roles_list' => collect($this->resource['user_roles_list'])->pluck('role_id'),
            'user_companies_locations_list' => collect($this->resource['user_companies_locations_list'])->pluck('company_location_id'),
            'user_regions_list' => collect($this->resource['user_regions_list'])->pluck('region_id'),
        ];
    }
}
